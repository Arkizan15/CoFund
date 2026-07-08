<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BackingController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicStatsController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleRequestController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WalletController;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Sitemap (public)
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Public: Xendit webhook callbacks (no CSRF, no auth)
Route::post('/xendit/callback', [WalletController::class, 'handleCallback']);
// Public routes — with rate limiting for auth endpoints
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,60');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,60');
Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,60');
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:5,60');
Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware(['signed'])->name('verification.verify');

Route::get('/campaigns', [CampaignController::class, 'index']);
Route::get('/campaigns/{slug}', [CampaignController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

// Public platform stats (no auth required)
Route::get('/platform/stats', [PublicStatsController::class, 'index']);

// Authenticated routes
Route::middleware(['auth:sanctum', 'check.user.suspended'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/email/resend', [AuthController::class, 'resend']);

    // Campaign
    Route::post('/campaigns', [CampaignController::class, 'store']);
    Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
    Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy']);
    Route::post('/campaigns/{id}/submit', [CampaignController::class, 'submitForReview']);
    Route::post('/campaigns/{id}/updates', [CampaignController::class, 'storeUpdate']);
    Route::post('/campaigns/{id}/images', [CampaignController::class, 'uploadImage']);
    Route::post('/campaigns/{id}/images/{imageId}/primary', [CampaignController::class, 'setPrimaryImage']);
    Route::delete('/campaigns/{id}/images/{imageId}', [CampaignController::class, 'deleteImage']);
    Route::delete('/campaigns/{id}/images', [CampaignController::class, 'deleteImage']);
    Route::get('/my/campaigns', [CampaignController::class, 'myCampaigns']);

    // Backing
    Route::post('/backings/invoice', [BackingController::class, 'createBackingInvoice']);
    Route::post('/backings', [BackingController::class, 'store']);
    Route::post('/backings/{id}/pay', [BackingController::class, 'simulatePayment']);
    Route::get('/my/backings', [BackingController::class, 'history']);

    // Wallet
    Route::post('/wallet/top-up', [WalletController::class, 'createTopUp']);
    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::post('/wallet/withdraw', [WalletController::class, 'createWithdraw']);

    // Dashboard
    Route::get('/creator/stats', [DashboardController::class, 'creatorStats']);
    Route::get('/creator/funding-chart', [DashboardController::class, 'fundingChart']);
    Route::get('/backer/stats', [DashboardController::class, 'backerStats']);

    // Role Request
    Route::post('/role-requests', [RoleRequestController::class, 'store']);
    Route::get('/role-requests', [RoleRequestController::class, 'index']);
    Route::get('/my/role-requests', [RoleRequestController::class, 'myRequests']);
    Route::put('/role-requests/{id}', [RoleRequestController::class, 'update']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Admin
    Route::get('/admin/overview', [AdminController::class, 'platformOverview']);
    Route::get('/admin/pending-approvals', [AdminController::class, 'pendingApprovals']);
    Route::get('/admin/campaigns/all', [AdminController::class, 'allCampaigns']);
    Route::get('/admin/campaigns/active', [AdminController::class, 'activeCampaigns']);
    Route::get('/admin/campaigns/ended', [AdminController::class, 'endedCampaigns']);
    Route::post('/admin/campaigns/{id}/approve', [AdminController::class, 'approveCampaign']);
    Route::post('/admin/campaigns/{id}/reject', [AdminController::class, 'rejectCampaign']);
    Route::post('/admin/campaigns/{id}/ban', [AdminController::class, 'banCampaign']);
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::get('/admin/users/{id}', [AdminController::class, 'userDetail']);
    Route::post('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus']);
    Route::post('/admin/announcements', [AdminController::class, 'sendAnnouncement']);

    // Profile: Avatar Upload
    Route::post('/profile/avatar', [App\Http\Controllers\Auth\AuthController::class, 'uploadAvatar']);

    // Admin: Activity Logs
    Route::get('/admin/activity-logs', function (Request $request) {
        $query = ActivityLog::with('user')->latest();
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->resource_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        return response()->json([
            'success' => true,
            'data' => $query->paginate(20),
        ]);
    });

    // Export CSV
    Route::get('/admin/export/campaigns', [ExportController::class, 'exportCampaigns']);
    Route::get('/admin/export/users', [ExportController::class, 'exportUsers']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
