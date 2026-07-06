<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BackingController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleRequestController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware(['signed']);

Route::get('/campaigns', [CampaignController::class, 'index']);
Route::get('/campaigns/{slug}', [CampaignController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/email/resend', [AuthController::class, 'resend']);

    // Campaign
    Route::post('/campaigns', [CampaignController::class, 'store']);
    Route::put('/campaigns/{id}', [CampaignController::class, 'update']);
    Route::post('/campaigns/{id}/submit', [CampaignController::class, 'submitForReview']);
    Route::post('/campaigns/{id}/updates', [CampaignController::class, 'storeUpdate']);
    Route::post('/campaigns/{id}/images', [CampaignController::class, 'uploadImage']);
    Route::delete('/campaigns/{id}/images', [CampaignController::class, 'deleteImage']);
    Route::get('/my/campaigns', [CampaignController::class, 'myCampaigns']);

    // Wallet
    Route::post('/wallet/top-up', [WalletController::class, 'topUp']);
    Route::get('/wallet/balance', [WalletController::class, 'balance']);

    // Backing
    Route::post('/backings', [BackingController::class, 'store']);
    Route::get('/my/backings', [BackingController::class, 'history']);

    // Role Request
    Route::post('/role-requests', [RoleRequestController::class, 'store']);
    Route::get('/role-requests', [RoleRequestController::class, 'index']);
    Route::get('/my/role-requests', [RoleRequestController::class, 'myRequests']);
    Route::put('/role-requests/{id}', [RoleRequestController::class, 'update']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
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
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
