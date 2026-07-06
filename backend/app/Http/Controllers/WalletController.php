<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
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

        $transactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $user->balance,
                'transactions' => $transactions,
            ],
        ], 200);
    }
}
