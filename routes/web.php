<?php

use App\Http\Controllers\Client\RequisitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureUserRole;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Every signed-in user lands here; the controller picks the right dashboard for their role.
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ---- Customer area ----------------------------------------------------
Route::middleware(['auth', 'verified', EnsureUserRole::class.':client'])
    ->controller(RequisitionController::class)
    ->group(function () {
        // Fixed paths first, so "export" and "create" are not read as a {requisition} id.
        Route::get('/requisitions/export', 'export')->name('requisitions.export');
        Route::get('/requisitions/create', 'create')->name('requisitions.create');

        Route::get('/requisitions', 'index')->name('requisitions.index');
        Route::post('/requisitions', 'store')->name('requisitions.store');
        Route::get('/requisitions/{requisition}', 'show')->name('requisitions.show');
        Route::get('/requisitions/{requisition}/edit', 'edit')->name('requisitions.edit');
        Route::put('/requisitions/{requisition}', 'update')->name('requisitions.update');
        Route::post('/requisitions/{requisition}/submit', 'submit')->name('requisitions.submit');
        Route::post('/requisitions/{requisition}/cancel', 'cancel')->name('requisitions.cancel');
        Route::delete('/requisitions/{requisition}', 'destroy')->name('requisitions.destroy');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
