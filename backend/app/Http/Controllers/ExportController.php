<?php

namespace App\Http\Controllers;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\User;
use App\Enums\BackingStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportCampaigns(Request $request): \Illuminate\Http\Response
    {
        $query = Campaign::with(['user', 'category']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $campaigns = $query->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="campaigns_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($campaigns) {
            $output = fopen('php://output', 'w');
            // BOM for UTF-8
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            // Header
            fputcsv($output, ['ID', 'Judul', 'Kategori', 'Creator', 'Target', 'Terkumpul', 'Progress %', 'Status', 'Backer', 'Dibuat']);
            foreach ($campaigns as $c) {
                $backerCount = Backing::where('campaign_id', $c->id)
                    ->where('status', BackingStatus::COMPLETED)
                    ->count();
                $progress = $c->target_amount > 0
                    ? min(100, round(($c->collected_amount / $c->target_amount) * 100, 1))
                    : 0;
                fputcsv($output, [
                    $c->id,
                    $c->title,
                    $c->category?->name ?? 'Umum',
                    $c->user?->name ?? 'N/A',
                    (float) $c->target_amount,
                    (float) $c->collected_amount,
                    $progress . '%',
                    $c->status,
                    $backerCount,
                    $c->created_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportUsers(Request $request): \Illuminate\Http\Response
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->withCount(['campaigns', 'backings'])->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="users_' . now()->format('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($users) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, ['ID', 'Nama', 'Email', 'Role', 'Saldo', 'Kampanye', 'Backing', 'Status', 'Bergabung']);
            foreach ($users as $u) {
                fputcsv($output, [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->role,
                    (float) $u->balance,
                    $u->campaigns_count,
                    $u->backings_count,
                    $u->suspended_at ? 'Dinonaktifkan' : 'Aktif',
                    $u->created_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
