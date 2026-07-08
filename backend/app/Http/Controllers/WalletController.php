<?php

namespace App\Http\Controllers;

use App\Enums\BackingStatus;
use App\Mail\NotifikasiEmail;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Services\CampaignSettlementService;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WalletController extends Controller
{
    protected XenditService $xendit;

    public function __construct(XenditService $xendit)
    {
        $this->xendit = $xendit;
    }

    /**
     * Create a Xendit Invoice for wallet top-up.
     * Returns the invoice URL for the frontend to redirect the user.
     */
    public function createTopUp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'success_redirect_url' => ['nullable', 'url'],
            'failure_redirect_url' => ['nullable', 'url'],
        ]);

        $user = Auth::user();
        $amount = (float) $validated['amount'];

        // Generate unique external ID
        $externalId = 'COFUND-TOPUP-' . time() . '-' . $user->id;

        // Create a pending wallet transaction
        $walletTransaction = WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'top_up',
            'amount' => $amount,
            'status' => 'pending',
            'reference' => $externalId,
            'description' => 'Top up saldo via Xendit — Rp ' . number_format($amount, 0, ',', '.'),
        ]);

        try {
            // Create Xendit invoice
            $invoice = $this->xendit->createInvoice([
                'external_id' => $externalId,
                'amount' => $amount,
                'payer_email' => $user->email,
                'description' => 'Top Up Saldo CoFund — Rp ' . number_format($amount, 0, ',', '.'),
                'success_redirect_url' => $validated['success_redirect_url'] ?? null,
                'failure_redirect_url' => $validated['failure_redirect_url'] ?? null,
                'metadata' => [
                    'user_id' => $user->id,
                    'wallet_transaction_id' => $walletTransaction->id,
                    'type' => 'top_up',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice top-up berhasil dibuat.',
                'data' => [
                    'invoice_url' => $invoice->getInvoiceUrl(),
                    'invoice_id' => $invoice->getId(),
                    'external_id' => $externalId,
                    'amount' => $amount,
                ],
            ], 200);
        } catch (\Exception $e) {
            // Mark the pending transaction as failed
            $walletTransaction->update(['status' => 'failed']);

            Log::error('Xendit top-up invoice creation failed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice pembayaran. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Handle Xendit webhook callback for invoice status updates.
     * This endpoint must be public and excluded from CSRF verification.
     */
    public function handleCallback(Request $request): JsonResponse
    {
        // Verify webhook token
        $callbackToken = $request->header('x-callback-token');
        if (!XenditService::verifyWebhookToken($callbackToken)) {
            Log::warning('Xendit webhook: invalid callback token', [
                'token' => $callbackToken,
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        Log::info('Xendit callback received', ['payload' => $payload]);

        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;
        $paidAmount = $payload['paid_amount'] ?? null;

        if (!$externalId || !$status) {
            Log::warning('Xendit callback: missing required fields', ['payload' => $payload]);
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        // Find the pending wallet transaction by external_id (reference)
        $walletTransaction = WalletTransaction::where('reference', $externalId)
            ->where('status', 'pending')
            ->first();

        if (!$walletTransaction) {
            Log::info('Xendit callback: transaction not found or already processed', [
                'external_id' => $externalId,
            ]);
            return response()->json(['success' => true, 'message' => 'Already processed'], 200);
        }

        // Determine transaction type from external_id prefix
        $isTopUp = str_starts_with($externalId, 'COFUND-TOPUP-');
        $isWithdraw = str_starts_with($externalId, 'COFUND-WITHDRAW-');
        $isBacking = str_starts_with($externalId, 'COFUND-BACKING-');

        // Handle backing callback — no WalletTransaction lookup needed
        if ($isBacking) {
            return $this->handleBackingCallback($externalId, $status, $paidAmount);
        }

        // Check if payment was successful
        $isPaid = in_array(strtoupper($status), ['PAID', 'SETTLED']);

        if ($isPaid) {
            DB::beginTransaction();
            try {
                $amount = (float) ($paidAmount ?? $walletTransaction->amount);
                $user = $walletTransaction->user;

                if ($isTopUp) {
                    // TOP-UP: Increment balance
                    $walletTransaction->update([
                        'status' => 'success',
                        'amount' => $amount,
                        'description' => 'Top up saldo berhasil — Rp ' . number_format($amount, 0, ',', '.'),
                    ]);

                    $user->balance += $amount;
                    $user->save();

                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'topup',
                        'amount' => $amount,
                        'status' => 'success',
                        'reference' => $externalId,
                    ]);

                    DB::commit();

                    Log::info('Xendit top-up completed', [
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'external_id' => $externalId,
                    ]);
                } elseif ($isWithdraw) {
                    // WITHDRAW: Deduct balance (already put on hold or deduct now)
                    // For staging invoice-based withdraw, the balance was NOT pre-deducted.
                    // Deduct now since the invoice was "paid".
                    if ($user->balance < $amount) {
                        DB::rollBack();
                        $walletTransaction->update(['status' => 'failed']);
                        Log::warning('Xendit withdraw callback: insufficient balance', [
                            'user_id' => $user->id,
                            'amount' => $amount,
                            'external_id' => $externalId,
                        ]);
                        return response()->json(['error' => 'Insufficient balance'], 422);
                    }

                    $walletTransaction->update([
                        'status' => 'success',
                        'amount' => $amount,
                        'description' => 'Penarikan dana berhasil — Rp ' . number_format($amount, 0, ',', '.'),
                    ]);

                    $user->balance -= $amount;
                    $user->save();

                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'disbursement',
                        'amount' => $amount,
                        'status' => 'success',
                        'reference' => $externalId,
                    ]);

                    DB::commit();

                    Log::info('Xendit withdraw completed via invoice callback', [
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'external_id' => $externalId,
                    ]);
                } else {
                    DB::rollBack();
                    Log::warning('Xendit callback: unknown transaction type', [
                        'external_id' => $externalId,
                    ]);
                    return response()->json(['error' => 'Unknown transaction type'], 400);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Xendit callback: failed to process transaction', [
                    'external_id' => $externalId,
                    'type' => $isTopUp ? 'topup' : 'withdraw',
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['error' => 'Processing failed'], 500);
            }
        } else {
            // Payment failed or expired
            $walletTransaction->update(['status' => 'failed']);
            Log::info('Xendit payment not successful', [
                'external_id' => $externalId,
                'status' => $status,
            ]);
        }

        return response()->json(['success' => true], 200);
    }

    /**
     * Handle Xendit callback for campaign backing payments.
     */
    private function handleBackingCallback(string $externalId, string $status, ?float $paidAmount): JsonResponse
    {
        // Extract backing ID from external_id: COFUND-BACKING-{id}
        $backingId = (int) str_replace('COFUND-BACKING-', '', $externalId);

        $backing = Backing::with('campaign', 'user')->find($backingId);

        if (!$backing || $backing->status !== BackingStatus::PENDING) {
            Log::info('Xendit backing callback: already processed or not found', [
                'backing_id' => $backingId,
                'external_id' => $externalId,
            ]);
            return response()->json(['success' => true, 'message' => 'Already processed'], 200);
        }

        $isPaid = in_array(strtoupper($status), ['PAID', 'SETTLED']);

        if (!$isPaid) {
            $backing->status = BackingStatus::REFUNDED;
            $backing->save();

            Log::info('Xendit backing payment not successful', [
                'backing_id' => $backingId,
                'status' => $status,
                'external_id' => $externalId,
            ]);
            return response()->json(['success' => true], 200);
        }

        // Payment successful — process backing completion
        DB::beginTransaction();
        try {
            $amount = (float) ($paidAmount ?? $backing->amount);
            $campaign = $backing->campaign;
            $backer = $backing->user;

            // Mark backing as completed
            $backing->status = BackingStatus::COMPLETED;
            $backing->save();

            // Create transaction record (payment type = dana masuk escrow via Xendit)
            $paymentRef = 'XPAY-' . strtoupper(uniqid());
            Transaction::create([
                'user_id' => $backer->id,
                'backing_id' => $backing->id,
                'type' => 'payment',
                'amount' => $amount,
                'status' => 'success',
                'reference' => $paymentRef,
            ]);

            // Update tier quota if tier selected
            if ($backing->tier_id) {
                CampaignTier::where('id', $backing->tier_id)->decrement('remaining_quota');
            }

            // Update campaign collected_amount
            $campaign->increment('collected_amount', $amount);

            // Send in-app notification to backer
            Notification::create([
                'user_id' => $backer->id,
                'type' => 'backing_success',
                'title' => 'Pendanaan Berhasil!',
                'body' => 'Dana sebesar Rp ' . number_format($amount, 0, ',', '.') . ' untuk "' . $campaign->title . '" telah masuk ke escrow.',
                'data' => [
                    'campaign_id' => $campaign->id,
                    'campaign_slug' => $campaign->slug,
                    'amount' => $amount,
                    'backing_id' => $backing->id,
                ],
                'created_at' => now(),
            ]);

            // Notify campaign creator about new backing
            if ($campaign->user_id !== $backer->id) {
                Notification::create([
                    'user_id' => $campaign->user_id,
                    'type' => 'backing_received',
                    'title' => 'Pendanaan Baru Masuk!',
                    'body' => 'Kampanye "' . $campaign->title . '" menerima pendanaan sebesar Rp ' .
                        number_format($amount, 0, ',', '.') .
                        ' dari ' . $backer->name . '.',
                    'data' => [
                        'campaign_id' => $campaign->id,
                        'campaign_slug' => $campaign->slug,
                        'amount' => $amount,
                        'backer_name' => $backer->name,
                        'backing_id' => $backing->id,
                    ],
                    'created_at' => now(),
                ]);
            }

            // Send email notification to backer
            try {
                Mail::to($backer->email)->send(new NotifikasiEmail(
                    'Konfirmasi Pendanaan',
                    'Halo ' . $backer->name . '!',
                    'Terima kasih! Pendanaan Anda sebesar Rp ' . number_format($amount, 0, ',', '.') .
                    ' untuk kampanye "' . $campaign->title . '" telah berhasil diproses.',
                    'Lihat Detail Kampanye',
                    url('/campaigns/' . $campaign->slug)
                ));
            } catch (\Exception $e) {
                Log::warning('Gagal kirim email notifikasi backing: ' . $e->getMessage());
            }

            DB::commit();

            // Auto-disburse if target is reached
            if ((float) $campaign->fresh()->collected_amount >= (float) $campaign->target_amount) {
                CampaignSettlementService::processDisbursement($campaign);
            }

            Log::info('Xendit backing completed', [
                'backing_id' => $backing->id,
                'user_id' => $backer->id,
                'campaign_id' => $campaign->id,
                'amount' => $amount,
                'external_id' => $externalId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xendit backing callback: failed to process', [
                'backing_id' => $backingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }

        return response()->json(['success' => true], 200);
    }

    /**
     * Legacy direct top-up (admin use only / fallback).
     */
    public function topUp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
        ]);

        $user = Auth::user();
        $amount = $validated['amount'];

        $user->balance += $amount;
        $user->save();

        $reference = 'TXN-' . strtoupper(uniqid());

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'top_up',
            'amount' => $amount,
            'status' => 'success',
            'reference' => $reference,
            'description' => 'Top up saldo sebesar Rp ' . number_format($amount, 0, ',', '.'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'topup',
            'amount' => $amount,
            'status' => 'success',
            'reference' => $reference,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Saldo berhasil ditambahkan.',
            'data' => [
                'balance' => $user->balance,
            ],
        ], 200);
    }

    public function balance(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = WalletTransaction::where('user_id', $user->id);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $transactions = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $user->balance,
                'transactions' => $transactions,
            ],
        ], 200);
    }

    /**
     * Create a Xendit Invoice for wallet withdrawal (staging-friendly).
     * No bank details needed — uses Xendit Checkout page like top-up.
     * The callback will deduct the balance upon successful payment.
     */
    public function createWithdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'success_redirect_url' => ['nullable', 'url'],
            'failure_redirect_url' => ['nullable', 'url'],
        ]);

        $user = Auth::user();
        $amount = (float) $validated['amount'];

        // Check sufficient balance
        if ($user->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi.',
            ], 422);
        }

        // Generate unique external ID
        $externalId = 'COFUND-WITHDRAW-' . time() . '-' . $user->id;

        // Create a pending wallet transaction (balance NOT deducted yet — will deduct on callback)
        $walletTransaction = WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'withdraw',
            'amount' => $amount,
            'status' => 'pending',
            'reference' => $externalId,
            'description' => 'Penarikan dana via Xendit — Rp ' . number_format($amount, 0, ',', '.'),
        ]);

        try {
            // Create Xendit invoice (same flow as top-up)
            $invoice = $this->xendit->createInvoice([
                'external_id' => $externalId,
                'amount' => $amount,
                'payer_email' => $user->email,
                'description' => 'Penarikan Saldo CoFund — Rp ' . number_format($amount, 0, ',', '.'),
                'success_redirect_url' => $validated['success_redirect_url'] ?? null,
                'failure_redirect_url' => $validated['failure_redirect_url'] ?? null,
                'metadata' => [
                    'user_id' => $user->id,
                    'wallet_transaction_id' => $walletTransaction->id,
                    'type' => 'withdraw',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice penarikan dana berhasil dibuat.',
                'data' => [
                    'invoice_url' => $invoice->getInvoiceUrl(),
                    'invoice_id' => $invoice->getId(),
                    'external_id' => $externalId,
                    'amount' => $amount,
                ],
            ], 200);
        } catch (\Exception $e) {
            // Mark the pending transaction as failed
            $walletTransaction->update(['status' => 'failed']);

            Log::error('Xendit withdraw invoice creation failed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice penarikan. Silakan coba lagi.',
            ], 500);
        }
    }
}
