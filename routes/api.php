<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PromptController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\CardController;

/*
|--------------------------------------------------------------------------
| API Routes - AnonBox
|--------------------------------------------------------------------------
*/

// ========================================
// ROUTES PUBLIQUES (Pas d'authentification)
// ========================================

/**
 * USERS - Gestion des utilisateurs
 */
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{handle}', [UserController::class, 'show']);
Route::post('/users/verify-token', [UserController::class, 'verifyToken']);

/**
 * PROMPTS - Gestion des questions
 */
Route::get('/prompts/{id}', [PromptController::class, 'show']);
Route::get('/users/{handle}/prompts', [PromptController::class, 'index']);
Route::post('/prompts/{id}/share', [PromptController::class, 'incrementShare']);

/**
 * MESSAGES - Envoi de messages anonymes
 */
Route::post('/messages', [MessageController::class, 'store']);

// Téléchargement d'image
Route::get('/cards/download/{filename}', [CardController::class, 'download'])
    ->name('cards.download');

// ========================================
// ROUTES PRIVÉES (Nécessitent X-Private-Token header)
// ========================================

Route::middleware(['verify.private.token'])->group(function () {

    Route::get('/me', [UserController::class, 'me']);

    /**
     * INBOX - Gestion de la boîte de réception
     */
    Route::get('/inbox', [MessageController::class, 'inbox']);
    Route::patch('/messages/{id}/read', [MessageController::class, 'markAsRead']);
    Route::post('/messages/{id}/respond', [MessageController::class, 'respond']);
    Route::delete('/messages/{id}', [MessageController::class, 'destroy']);
    Route::post('/messages/{id}/share', [MessageController::class, 'markAsShared']);

    /**
     * PROMPTS - Création/gestion de questions (privé)
     */
    Route::post('/prompts', [PromptController::class, 'store']);
    Route::delete('/prompts/{id}', [PromptController::class, 'destroy']);

    /**
     * ACCOUNT - Gestion du compte
     */
    Route::post('/users/regenerate-token', [UserController::class, 'regenerateToken']);

    /**
     * CARDS - Génération d'images
     */
    Route::post('/cards/generate', [CardController::class, 'generate']);
    // Note: cleanup devrait être protégé ou déplacé dans une CRON task
    Route::delete('/cards/cleanup', [CardController::class, 'cleanup']);
});

// ========================================
// HEALTH CHECK
// ========================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'version' => '1.0.0'
    ]);
});
