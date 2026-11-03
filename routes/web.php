<?php

use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\RequisitionController as AdminRequisitionController;
use App\Http\Controllers\Client\RequisitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationAcceptController;
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

// ---- Accepting an invitation (public link, rate limited) -----------------
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/invitations/{token}', [InvitationAcceptController::class, 'show'])->name('invitations.accept');
    Route::post('/invitations/{token}', [InvitationAcceptController::class, 'store'])->name('invitations.accept.store');
});

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

// ---- Admin area -------------------------------------------------------
Route::middleware(['auth', 'verified', EnsureUserRole::class.':admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/requisitions', [AdminRequisitionController::class, 'index'])->name('requisitions.index');
        Route::get('/requisitions/{requisition}', [AdminRequisitionController::class, 'show'])->name('requisitions.show');
        Route::post('/requisitions/{requisition}/assign', [AdminRequisitionController::class, 'assign'])->name('requisitions.assign');
        Route::post('/requisitions/{requisition}/transition', [AdminRequisitionController::class, 'transition'])->name('requisitions.transition');

        Route::get('/customers', AdminCustomerController::class)->name('customers.index');

        Route::get('/invitations', [AdminInvitationController::class, 'index'])->name('invitations.index');
        Route::get('/invitations/create', [AdminInvitationController::class, 'create'])->name('invitations.create');
        Route::post('/invitations', [AdminInvitationController::class, 'store'])->name('invitations.store');
        Route::post('/invitations/{invitation}/regenerate', [AdminInvitationController::class, 'regenerate'])->name('invitations.regenerate');
        Route::post('/invitations/{invitation}/revoke', [AdminInvitationController::class, 'revoke'])->name('invitations.revoke');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
