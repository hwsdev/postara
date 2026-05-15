<?php

use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DomainController;
use App\Http\Controllers\Api\V1\EmailController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Middleware\AuthenticateApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([AuthenticateApiKey::class])->group(function () {

    // ─── Emails ──────────────────────────────────────────────────────────────
    Route::post('/emails', [EmailController::class, 'send']);
    Route::get('/emails', [EmailController::class, 'index']);
    Route::get('/emails/{id}', [EmailController::class, 'show']);

    // ─── Domains ─────────────────────────────────────────────────────────────
    Route::get('/domains', [DomainController::class, 'index']);
    Route::post('/domains', [DomainController::class, 'store']);
    Route::post('/domains/{domain}/verify', [DomainController::class, 'verify']);
    Route::delete('/domains/{domain}', [DomainController::class, 'destroy']);

    // ─── Contacts ────────────────────────────────────────────────────────────
    Route::get('/contacts', [ContactController::class, 'index']);
    Route::post('/contacts', [ContactController::class, 'store']);
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy']);

    // ─── Webhooks ────────────────────────────────────────────────────────────
    Route::get('/webhooks', [WebhookController::class, 'index']);
    Route::post('/webhooks', [WebhookController::class, 'store']);
    Route::patch('/webhooks/{webhook}', [WebhookController::class, 'update']);
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy']);
    Route::post('/webhooks/{webhook}/rotate-secret', [WebhookController::class, 'rotateSecret']);
});
