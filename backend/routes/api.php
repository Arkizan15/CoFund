<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword']);
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword']);
Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware(['signed']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/email/resend', [AuthController::class, 'resend']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
