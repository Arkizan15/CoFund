<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\Payout\CreatePayoutRequest;
use Xendit\Payout\DigitalPayoutChannelProperties;
use Xendit\Payout\PayoutApi;
use Xendit\XenditSdkException;

class XenditService
{
    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
    }

    /**
     * Create a Xendit Invoice for wallet top-up.
     *
     * @param array $params
     * @return \Xendit\Invoice\Invoice
     *
     * @throws \Xendit\XenditSdkException
     */
    public function createInvoice(array $params)
    {
        $invoiceRequest = new CreateInvoiceRequest([
            'external_id' => $params['external_id'],
            'amount' => (float) $params['amount'],
            'payer_email' => $params['payer_email'] ?? null,
            'description' => $params['description'] ?? 'Top Up Saldo CoFund',
            'currency' => 'IDR',
            'success_redirect_url' => $params['success_redirect_url'] ?? null,
            'failure_redirect_url' => $params['failure_redirect_url'] ?? null,
            'metadata' => $params['metadata'] ?? null,
        ]);

        $apiInstance = new InvoiceApi();

        try {
            $result = $apiInstance->createInvoice($invoiceRequest);
            Log::info('Xendit invoice created', [
                'external_id' => $params['external_id'],
                'invoice_id' => $result->getId(),
                'invoice_url' => $result->getInvoiceUrl(),
            ]);
            return $result;
        } catch (XenditSdkException $e) {
            Log::error('Xendit create invoice failed', [
                'external_id' => $params['external_id'],
                'error' => $e->getMessage(),
                'raw_response' => $e->getRawResponse(),
                'error_code' => $e->getErrorCode(),
            ]);
            throw $e;
        }
    }

    /**
     * Get invoice by ID.
     *
     * @param string $invoiceId
     * @return \Xendit\Invoice\Invoice
     *
     * @throws \Xendit\XenditSdkException
     */
    public function getInvoice(string $invoiceId)
    {
        $apiInstance = new InvoiceApi();

        try {
            return $apiInstance->getInvoiceById($invoiceId);
        } catch (XenditSdkException $e) {
            Log::error('Xendit get invoice failed', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a payout (disbursement) to a bank account or e-wallet.
     *
     * @param array $params
     * @return \Xendit\Payout\GetPayouts200ResponseDataInner
     *
     * @throws \Xendit\XenditSdkException
     */
    public function createPayout(array $params)
    {
        $channelProperties = new DigitalPayoutChannelProperties([
            'account_holder_name' => $params['account_holder_name'],
            'account_number' => $params['account_number'],
        ]);

        $payoutRequest = new CreatePayoutRequest([
            'reference_id' => $params['reference_id'],
            'channel_code' => $params['channel_code'],
            'channel_properties' => $channelProperties,
            'amount' => (float) $params['amount'],
            'currency' => 'IDR',
            'description' => $params['description'] ?? 'Pencairan Dana CoFund',
            'metadata' => $params['metadata'] ?? null,
        ]);

        $apiInstance = new PayoutApi();
        $idempotencyKey = $params['idempotency_key'] ?? (string) \Illuminate\Support\Str::uuid();

        try {
            $result = $apiInstance->createPayout($idempotencyKey, null, $payoutRequest);
            Log::info('Xendit payout created', [
                'reference_id' => $params['reference_id'],
                'payout_id' => $result->getId(),
            ]);
            return $result;
        } catch (XenditSdkException $e) {
            Log::error('Xendit create payout failed', [
                'reference_id' => $params['reference_id'],
                'error' => $e->getMessage(),
                'raw_response' => $e->getRawResponse(),
                'error_code' => $e->getErrorCode(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify Xendit webhook callback token.
     *
     * @param string|null $token
     * @return bool
     */
    public static function verifyWebhookToken(?string $token): bool
    {
        $expectedToken = config('services.xendit.webhook_token');

        if (empty($expectedToken)) {
            Log::warning('XENDIT_WEBHOOK_TOKEN is not configured, skipping verification');
            return true;
        }

        return $token === $expectedToken;
    }
}
