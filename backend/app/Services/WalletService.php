<?php

namespace App\Services;

use App\Helpers\RupiahHelper;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function processTopUp(WalletTransaction $walletTransaction, User $user, float $amount, string $externalId): void
    {
        DB::transaction(function () use ($walletTransaction, $user, $amount, $externalId) {
            $walletTransaction->update([
                'status' => 'success',
                'amount' => $amount,
                'description' => 'Top up saldo berhasil — ' . RupiahHelper::format($amount),
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
        });

        Log::info('Top-up completed', [
            'user_id' => $user->id,
            'amount' => $amount,
            'external_id' => $externalId,
        ]);
    }

    public function processWithdraw(WalletTransaction $walletTransaction, User $user, float $amount, string $externalId): bool
    {
        try {
            DB::transaction(function () use ($walletTransaction, $user, $amount, $externalId, &$success) {
                if ($user->balance < $amount) {
                    $walletTransaction->update(['status' => 'failed']);
                    $success = false;
                    return;
                }

                $walletTransaction->update([
                    'status' => 'success',
                    'amount' => $amount,
                    'description' => 'Penarikan dana berhasil — ' . RupiahHelper::format($amount),
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

                $success = true;
            });

            if ($success) {
                Log::info('Withdraw completed', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'external_id' => $externalId,
                ]);
            } else {
                Log::warning('Withdraw failed: insufficient balance', [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'external_id' => $externalId,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Withdraw processing failed', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
