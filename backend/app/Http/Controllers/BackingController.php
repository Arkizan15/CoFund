<?php

namespace App\Http\Controllers;

use App\Enums\BackingStatus;
use App\Enums\CampaignStatus;
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

class BackingController extends Controller
{
    protected XenditService $xendit;

    public function __construct(XenditService $xendit)
    {
        $this->xendit = $xendit;
    }

    /**
     * Create a Xendit Invoice for backing a campaign.
     * Returns the invoice URL for the frontend to redirect the user to Xendit checkout.
     * Use this when Xendit payment gateway is configured.
     */
    public function createBackingInvoice(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail() === false) {
            return response()->json([
                'success' => false,
                'message' => 'Email harus diverifikasi sebelum melakukan backing.',
            ], 403);
        }

        $validated = $request->validate([
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'tier_id' => ['nullable', 'exists:campaign_tiers,id'],
            'amount' => ['required', 'numeric', 'min:10000'],
            'success_redirect_url' => ['nullable', 'url'],
            'failure_redirect_url' => ['nullable', 'url'],
        ]);

        $campaign = Campaign::findOrFail($validated['campaign_id']);

        if ($campaign->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa mendanai kampanye milik sendiri.',
            ], 403);
        }

        if ($campaign->status !== CampaignStatus::ACTIVE) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kampanye aktif yang dapat didanai.',
            ], 422);
        }

        $amount = (float) $validated['amount'];

        if (isset($validated['tier_id'])) {
            $tier = CampaignTier::findOrFail($validated['tier_id']);

            if ($tier->campaign_id !== $campaign->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tier tidak sesuai dengan kampanye ini.',
                ], 422);
            }

            if ($tier->remaining_quota === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuota tier ini sudah habis.',
                ], 422);
            }

            if ($amount < (float) $tier->min_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal minimal untuk tier ini adalah Rp ' . number_format($tier->min_amount, 0, ',', '.'),
                ], 422);
            }
        }

        // Check if backing amount exceeds remaining campaign target
        $collected = (float) $campaign->collected_amount;
        $target = (float) $campaign->target_amount;
        $remaining = max(0, $target - $collected);
        if ($amount > $remaining) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal donasi melebihi sisa dana yang dibutuhkan kampanye. Sisa dana yang diperlukan: Rp ' . number_format($remaining, 0, ',', '.'),
            ], 422);
        }

        // Create pending backing
        $backing = Backing::create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'tier_id' => $validated['tier_id'] ?? null,
            'amount' => $amount,
            'status' => BackingStatus::PENDING,
        ]);

        // External ID for Xendit — embedding backing ID so callback can find it
        $externalId = 'COFUND-BACKING-' . $backing->id;

        try {
            $invoice = $this->xendit->createInvoice([
                'external_id' => $externalId,
                'amount' => $amount,
                'payer_email' => $user->email,
                'description' => 'Pendanaan Kampanye "' . $campaign->title . '" — Rp ' . number_format($amount, 0, ',', '.'),
                'success_redirect_url' => $validated['success_redirect_url'] ?? null,
                'failure_redirect_url' => $validated['failure_redirect_url'] ?? null,
                'metadata' => [
                    'user_id' => $user->id,
                    'backing_id' => $backing->id,
                    'campaign_id' => $campaign->id,
                    'type' => 'backing',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice pembayaran berhasil dibuat.',
                'data' => [
                    'invoice_url' => $invoice->getInvoiceUrl(),
                    'invoice_id' => $invoice->getId(),
                    'external_id' => $externalId,
                    'amount' => $amount,
                    'backing_id' => $backing->id,
                ],
            ], 200);
        } catch (\Exception $e) {
            // Mark backing as failed
            $backing->status = BackingStatus::REFUNDED;
            $backing->save();

            Log::error('Xendit backing invoice creation failed', [
                'user_id' => $user->id,
                'campaign_id' => $campaign->id,
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
     * Wallet-based backing (instant mock payment).
     *
     * Validates business rules, deducts user balance, creates backing and transaction
     * records, updates campaign collected_amount, and sends notifications.
     *
     * POST /api/backings
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // 1. Email verification check
        if ($user->hasVerifiedEmail() === false) {
            return response()->json([
                'success' => false,
                'message' => 'Email harus diverifikasi sebelum melakukan backing.',
            ], 403);
        }

        $validated = $request->validate([
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'tier_id' => ['nullable', 'exists:campaign_tiers,id'],
            'amount' => ['required', 'numeric', 'min:10000'],
        ]);

        $campaign = Campaign::findOrFail($validated['campaign_id']);
        $amount = (float) $validated['amount'];

        // 2. Creator restriction: cannot back own campaign
        if ($campaign->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa mendanai kampanye milik sendiri.',
            ], 403);
        }

        // 3. Campaign must be active
        if ($campaign->status !== CampaignStatus::ACTIVE) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kampanye aktif yang dapat didanai.',
            ], 422);
        }

        // 4. Tier validation (if selected)
        if (isset($validated['tier_id'])) {
            $tier = CampaignTier::findOrFail($validated['tier_id']);

            if ($tier->campaign_id !== $campaign->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tier tidak sesuai dengan kampanye ini.',
                ], 422);
            }

            if ($tier->remaining_quota === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kuota tier ini sudah habis.',
                ], 422);
            }

            if ($amount < (float) $tier->min_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal minimal untuk tier ini adalah Rp ' . number_format($tier->min_amount, 0, ',', '.'),
                ], 422);
            }
        }

        // 5. Balance check
        if ($user->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi. Silakan top up terlebih dahulu.',
            ], 422);
        }

        // 6. Check if backing amount exceeds remaining target
        $collected = (float) $campaign->collected_amount;
        $target = (float) $campaign->target_amount;
        $remaining = max(0, $target - $collected);
        if ($amount > $remaining) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal donasi melebihi sisa dana yang dibutuhkan. Sisa: Rp ' . number_format($remaining, 0, ',', '.'),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 7. Deduct balance first
            $user->balance -= $amount;
            $user->save();

            // 8. Create backing with COMPLETED status (instant mock payment)
            $backing = Backing::create([
                'user_id' => $user->id,
                'campaign_id' => $campaign->id,
                'tier_id' => $validated['tier_id'] ?? null,
                'amount' => $amount,
                'status' => BackingStatus::COMPLETED,
            ]);

            // 9. Create transaction record
            $paymentRef = 'PAY-' . strtoupper(uniqid());
            Transaction::create([
                'user_id' => $user->id,
                'backing_id' => $backing->id,
                'type' => 'payment',
                'amount' => $amount,
                'status' => 'success',
                'reference' => $paymentRef,
            ]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'payment',
                'amount' => $amount,
                'status' => 'success',
                'reference' => $paymentRef,
                'description' => 'Pendanaan untuk kampanye "' . $campaign->title . '" — Rp ' .
                    number_format($amount, 0, ',', '.'),
            ]);

            // 10. Update tier quota and campaign collected amount
            if (isset($validated['tier_id'])) {
                CampaignTier::where('id', $validated['tier_id'])->decrement('remaining_quota');
            }

            $campaign->increment('collected_amount', $amount);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Backing failed', [
                'user_id' => $user->id,
                'campaign_id' => $campaign->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pendanaan. Silakan coba lagi.',
            ], 500);
        }

        // Non-critical post-commit operations: notifications, disbursement, email.
        // Also wrap the final response so any serialization error still returns success JSON
        // (the DB transaction has already committed at this point).
        try {
            // Auto-disburse if target is reached
            if ((float) $campaign->fresh()->collected_amount >= (float) $campaign->target_amount) {
                CampaignSettlementService::processDisbursement($campaign);
            }

            // Send notification to backer
            Notification::create([
                'user_id' => $user->id,
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
            if ($campaign->user_id !== $user->id) {
                Notification::create([
                    'user_id' => $campaign->user_id,
                    'type' => 'backing_received',
                    'title' => 'Pendanaan Baru Masuk!',
                    'body' => 'Kampanye "' . $campaign->title . '" menerima pendanaan sebesar Rp ' .
                        number_format($amount, 0, ',', '.') .
                        ' dari ' . $user->name . '.',
                    'data' => [
                        'campaign_id' => $campaign->id,
                        'campaign_slug' => $campaign->slug,
                        'amount' => $amount,
                        'backer_name' => $user->name,
                        'backing_id' => $backing->id,
                    ],
                    'created_at' => now(),
                ]);
            }

            // Send email notification to backer
            try {
                Mail::to($user->email)->send(new NotifikasiEmail(
                    'Konfirmasi Pendanaan',
                    'Halo ' . $user->name . '!',
                    'Terima kasih! Pendanaan Anda sebesar Rp ' . number_format($amount, 0, ',', '.') .
                    ' untuk kampanye "' . $campaign->title . '" telah berhasil diproses.',
                    'Lihat Detail Kampanye',
                    url('/campaigns/' . $campaign->slug)
                ));
            } catch (\Exception $e) {
                Log::warning('Gagal kirim email notifikasi backing: ' . $e->getMessage());
            }

            // Fresh response data — catch any serialization error
            $responseData = [
                'backing' => $backing->fresh(),
                'balance' => $user->fresh()->balance,
            ];
        } catch (\Exception $e) {
            // Backing already committed successfully — just log the failure
            Log::error('Backing notification/disbursement/response failed', [
                'backing_id' => $backing->id ?? null,
                'error' => $e->getMessage(),
            ]);
            // Fallback without fresh() to avoid re-throwing if the DB is flaky
            try {
                $fallbackBalance = $user->fresh()->balance;
            } catch (\Exception $_) {
                $fallbackBalance = 0;
            }
            $responseData = [
                'backing_id' => $backing->id ?? null,
                'balance' => $fallbackBalance,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Pendanaan berhasil!',
            'data' => $responseData,
        ], 201);
    }

    /**
     * Mock payment gateway simulation.
     * In production, this would integrate with a real payment provider.
     * Currently simulates instant success for valid transactions.
     */
    private function mockPaymentGateway($user, float $amount): bool
    {
        // Simulate payment processing checks:
        // 1. Verify user has sufficient balance (already done above)
        if ($user->balance < $amount) {
            return false;
        }

        // 2. Simulate random gateway failure (1% chance for testing)
        if (random_int(1, 100) === 1) {
            return false;
        }

        // 3. Simulate processing delay (0.5s)
        usleep(500000);

        // Payment successful
        return true;
    }

    public function simulatePayment(int $backingId): JsonResponse
    {
        $backing = Backing::with('campaign')->findOrFail($backingId);

        if ($backing->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($backing->status !== BackingStatus::PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran sudah diproses.',
            ], 422);
        }

        $user = Auth::user();
        $amount = (float) $backing->amount;

        // Process payment
        $paymentSuccess = $this->mockPaymentGateway($user, $amount);

        if (!$paymentSuccess) {
            $backing->status = BackingStatus::REFUNDED;
            $backing->save();

            return response()->json([
                'success' => false,
                'message' => 'Pembayaran gagal diproses.',
            ], 422);
        }

        // Complete the backing
        $user->balance -= $amount;
        $user->save();

        $backing->status = BackingStatus::COMPLETED;
        $backing->save();

        if ($backing->tier_id) {
            CampaignTier::where('id', $backing->tier_id)->decrement('remaining_quota');
        }

        $backing->campaign->increment('collected_amount', $amount);

        if ((float) $backing->campaign->fresh()->collected_amount >= (float) $backing->campaign->target_amount) {
            CampaignSettlementService::processDisbursement($backing->campaign);
        }

        // Create transaction record
        $paymentRef = 'PAY-' . strtoupper(uniqid());
        Transaction::create([
            'user_id' => $user->id,
            'backing_id' => $backing->id,
            'type' => 'payment',
            'amount' => $amount,
            'status' => 'success',
            'reference' => $paymentRef,
        ]);

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'payment',
            'amount' => $amount,
            'status' => 'success',
            'reference' => $paymentRef,
            'description' => 'Pendanaan untuk kampanye "' . $backing->campaign->title . '" — Rp ' .
                number_format($amount, 0, ',', '.'),
        ]);

        // Notification
        Notification::create([
            'user_id' => $user->id,
            'type' => 'backing_success',
            'title' => 'Pembayaran Berhasil!',
            'body' => 'Pembayaran untuk "' . $backing->campaign->title . '" telah berhasil diproses.',
            'data' => [
                'campaign_id' => $backing->campaign->id,
                'campaign_slug' => $backing->campaign->slug,
                'amount' => $amount,
                'backing_id' => $backing->id,
            ],
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil!',
            'data' => [
                'backing' => $backing->fresh(),
                'balance' => $user->fresh()->balance,
            ],
        ], 200);
    }

    public function history(): JsonResponse
    {
        $user = Auth::user();

        $backings = Backing::with(['user', 'campaign', 'tier'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $backings,
        ], 200);
    }
}
