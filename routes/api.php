<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-verification-code', [AuthController::class, 'sendVerificationCode']);
Route::post('/verify-email', [VerificationController::class, 'verifyEmail']);
Route::post('/resend-email-otp', [AuthController::class, 'resendEmailOtp']);
Route::post('/check-email-exists', [AuthController::class, 'checkEmailExists']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
