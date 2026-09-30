<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HackathonController;
use App\Http\Controllers\Api\InvestorProfileController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - The Hack Hub
|--------------------------------------------------------------------------
| All routes are prefixed with /api automatically by Laravel.
| Public routes: register/login/forgot-password.
| Everything else requires a Sanctum bearer token (auth:sanctum),
| and role-specific routes are additionally protected by the 'role' middleware.
*/

// ---------- Public / Auth ----------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ---------- Shared (any logged in user) ----------
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::get('/hackathons', [HackathonController::class, 'index']);
    Route::get('/hackathons/{id}', [HackathonController::class, 'show']);
    Route::get('/participants/search', [ParticipantController::class, 'search']);

    // ---------- Organizer ----------
    Route::middleware('role:organizer')->prefix('organizer')->group(function () {
        Route::post('/hackathons', [HackathonController::class, 'store']);
        Route::put('/hackathons/{id}', [HackathonController::class, 'update']);
        Route::delete('/hackathons/{id}', [HackathonController::class, 'destroy']);
        Route::get('/analytics', [HackathonController::class, 'analytics']);

        Route::get('/hackathons/{hackathonId}/participants', [ParticipantController::class, 'index']);
        Route::put('/registrations/{id}/approve', [ParticipantController::class, 'approve']);
        Route::put('/registrations/{id}/reject', [ParticipantController::class, 'reject']);

        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);

        Route::get('/projects', [ProjectController::class, 'index']);
        Route::get('/projects/{id}', [ProjectController::class, 'show']);
        Route::put('/projects/{id}/status', [ProjectController::class, 'updateStatus']);
    });

    // ---------- Participant ----------
    Route::middleware('role:participant')->prefix('participant')->group(function () {
        Route::get('/profile', [ParticipantController::class, 'profile']);
        Route::put('/profile', [ParticipantController::class, 'updateProfile']);

        Route::post('/hackathons/{id}/register', [HackathonController::class, 'register']);
        Route::get('/announcements/feed', [AnnouncementController::class, 'feed']);

        Route::post('/teams', [TeamController::class, 'store']);
        Route::get('/teams/mine', [TeamController::class, 'myTeams']);
        Route::get('/teams/{id}', [TeamController::class, 'show']);
        Route::get('/teams/suggestions/{hackathonId}', [TeamController::class, 'suggestions']);
        Route::post('/teams/invite', [TeamController::class, 'invite']);
        Route::get('/teams/invites/mine', [TeamController::class, 'myInvites']);
        Route::put('/teams/invites/{memberId}', [TeamController::class, 'respondInvite']);
        Route::delete('/teams/{teamId}/leave', [TeamController::class, 'leave']);
        Route::delete('/teams/{teamId}/members/{userId}', [TeamController::class, 'removeMember']);

        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/history', [ProjectController::class, 'history']);

        Route::get('/offers/received', [OfferController::class, 'received']);
        Route::put('/offers/{id}/respond', [OfferController::class, 'respond']);
    });

    // ---------- Investor ----------
    Route::middleware('role:investor')->prefix('investor')->group(function () {
        Route::get('/dashboard', [OfferController::class, 'dashboard']);
        Route::get('/profile', [InvestorProfileController::class, 'show']);
        Route::put('/profile', [InvestorProfileController::class, 'update']);

        Route::get('/projects', [ProjectController::class, 'browse']);
        Route::get('/projects/{id}', [ProjectController::class, 'show']);
        Route::get('/teams/{id}', [TeamController::class, 'show']);

        Route::post('/offers', [OfferController::class, 'store']);
        Route::get('/offers/sent', [OfferController::class, 'sent']);

        Route::post('/saved/{projectId}', [OfferController::class, 'save']);
        Route::delete('/saved/{projectId}', [OfferController::class, 'unsave']);
        Route::get('/saved', [OfferController::class, 'savedList']);
    });
});
