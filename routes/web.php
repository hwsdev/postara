<?php

use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\TemplateController;
use App\Http\Controllers\Dashboard\TrackingController;
use Illuminate\Support\Facades\Route;

// ─── Setup wizard (always accessible, cannot be skipped) ─────────────────────
Route::get('/setup', function () {
    if (\App\Models\Setting::isSetupComplete()) {
        return redirect()->route('dashboard');
    }
    return view('setup.wizard');
})->name('setup');

// ─── Auth ────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Tracking (public, signed) ───────────────────────────────────────────────
Route::get('/t/o/{email}', [TrackingController::class, 'open'])
    ->name('track.open')
    ->middleware('signed');

Route::get('/t/c/{email}', [TrackingController::class, 'click'])
    ->name('track.click')
    ->middleware('signed');

// ─── Dashboard ───────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Domains
    Route::get('/domains', fn () => view('dashboard.domains'))->name('domains.index');

    // API Keys
    Route::get('/api-keys', fn () => view('dashboard.api-keys'))->name('api-keys.index');

    // Contacts
    Route::get('/contacts', fn () => view('dashboard.contacts'))->name('contacts.index');
    Route::get('/contacts/lists', fn () => view('dashboard.contact-lists'))->name('contacts.lists');

    // Campaigns
    Route::get('/campaigns', fn () => view('dashboard.campaigns'))->name('campaigns.index');
    Route::get('/campaigns/create', fn () => view('dashboard.campaigns-create'))->name('campaigns.create');

    // Templates
    Route::get('/templates', fn () => view('dashboard.templates'))->name('templates.index');
    Route::get('/templates/create', [TemplateController::class, 'create'])->name('templates.create');
    Route::get('/templates/{id}/edit', [TemplateController::class, 'edit'])->name('templates.edit');

    // Webhooks
    Route::get('/webhooks', fn () => view('dashboard.webhooks'))->name('webhooks.index');

    // Suppression
    Route::get('/suppressions', fn () => view('dashboard.suppressions'))->name('suppressions.index');

    // Settings
    Route::get('/settings', fn () => view('dashboard.settings'))->name('settings.index');
});
