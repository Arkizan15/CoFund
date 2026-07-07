<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    public function withdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
        ]);

        $user = Auth::user();
        $amount = (float) $validated['amount'];

        if ($user->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi untuk melakukan withdraw.',
            ], 422);
        }

        // Mock withdraw — in production, this would integrate with a payment gateway
        $user->balance -= $amount;
        $user->save();

        $reference = 'WTH-' . strtoupper(uniqid());

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'withdraw',
            'amount' => $amount,
            'status' => 'success',
            'reference' => $reference,
            'description' => 'Penarikan dana sebesar Rp ' . number_format($amount, 0, ',', '.'),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'disbursement',
            'amount' => $amount,
            'status' => 'success',
            'reference' => $reference,
        ]);

        Log::info("Withdraw mock: User #{$user->id} menarik Rp {$amount}");

        return response()->json([
            'success' => true,
            'message' => 'Penarikan dana berhasil diproses (mock).',
            'data' => [
                'balance' => $user->balance,
                'amount' => $amount,
            ],
        ], 200);
    }
}
