<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profile', [UserController::class, 'createProfile']);
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::get('/users', [UserController::class, 'listUsers']);
    Route::post('/request-to-mentor', [UserController::class, 'requestToMentor']);
    Route::get('/get-all-mentor-requests', [AdminController::class, 'getAllMentorRequests']);
    Route::get('/organization-requests', [AdminController::class, 'getOrganizationRequests']);
    Route::post('/approve-mentor', [AdminController::class, 'approveMentor']);
    Route::get('/users/{id}', [UserController::class, 'getUserById']);
    Route::post('/approve-organization-request', [AdminController::class, 'approveOrganizationRequest']);
    Route::post('/profile/upload', [UserController::class, 'uploadProfilePicture']);
    Route::get('/get-organizations', [OrganizationController::class, 'getOrganizations']);
    Route::post('/upload-post', [PostController::class, 'uploadPost']);
    Route::get('/get-mentors', [AdminController::class, 'getMentors']);
    Route::get('/get-organizations', [AdminController::class, 'getOrganizations']);
    Route::get('/get-all-posts', [PostController::class, 'getAllPosts']);
    Route::post('/comment', [CommentController::class,'addComment']);
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::get('/posts/{id}', [PostController::class, 'getUserPosts']);
    Route::delete('/posts/{id}', [PostController::class, 'deletePost']);
    
   
});
