<?php

use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\RequisitionController as AdminRequisitionController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Client\RequisitionController;
use App\Http\Controllers\Client\ReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\Officer\EvaluationController as OfficerEvaluationController;
use App\Http\Controllers\Officer\QuotationController as OfficerQuotationController;
use App\Http\Controllers\Officer\RequisitionController as OfficerRequisitionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Vendor\AccreditationController;
use App\Http\Controllers\Vendor\RequestController as VendorRequestController;
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

// ---- Documents (any signed-in user; the policy decides) -------------------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
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

        // Reviewing the officer's bid evaluation, and leaving a note for the officer
        Route::post('/requisitions/{requisition}/decision', [ReviewController::class, 'decide'])->name('requisitions.decide');
        Route::post('/requisitions/{requisition}/reply', [ReviewController::class, 'reply'])->name('requisitions.reply');
    });

// ---- Vendor area ------------------------------------------------------
Route::middleware(['auth', 'verified', EnsureUserRole::class.':vendor'])
    ->prefix('vendor/accreditation')
    ->name('vendor.accreditation.')
    ->controller(AccreditationController::class)
    ->group(function () {
        Route::get('/', 'show')->name('show');
        Route::get('/company', 'company')->name('company');
        Route::put('/company', 'saveCompany')->name('company.save');
        Route::get('/capability', 'capability')->name('capability');
        Route::put('/capability', 'saveCapability')->name('capability.save');
        Route::get('/documents', 'documents')->name('documents');
        Route::post('/documents', 'upload')->name('documents.upload');
        Route::post('/submit', 'submit')->name('submit');
    });

// ---- Vendor: requests they were invited to quote on (read only) --------
Route::middleware(['auth', 'verified', EnsureUserRole::class.':vendor'])
    ->prefix('vendor')
    ->name('vendor.')
    ->group(function () {
        Route::get('/requests', [VendorRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{requisition}', [VendorRequestController::class, 'show'])->name('requests.show');
    });

// ---- Procurement officer area -----------------------------------------
Route::middleware(['auth', 'verified', EnsureUserRole::class.':procurement_officer'])
    ->prefix('officer')
    ->name('officer.')
    ->group(function () {
        Route::get('/requisitions', [OfficerRequisitionController::class, 'index'])->name('requisitions.index');
        Route::get('/requisitions/{requisition}', [OfficerRequisitionController::class, 'show'])->name('requisitions.show');
        Route::post('/requisitions/{requisition}/transition', [OfficerRequisitionController::class, 'transition'])->name('requisitions.transition');
        Route::post('/requisitions/{requisition}/reply', [OfficerRequisitionController::class, 'reply'])->name('requisitions.reply');
        Route::post('/requisitions/{requisition}/invitations', [OfficerRequisitionController::class, 'invite'])->name('requisitions.invite');
        Route::delete('/requisitions/{requisition}/invitations/{invitation}', [OfficerRequisitionController::class, 'uninvite'])->name('requisitions.uninvite');
        Route::post('/requisitions/{requisition}/purchase-order', [OfficerRequisitionController::class, 'issuePo'])->name('requisitions.po');

        Route::post('/requisitions/{requisition}/quotations', [OfficerQuotationController::class, 'store'])->name('quotations.store');
        Route::delete('/quotations/{quotation}', [OfficerQuotationController::class, 'destroy'])->name('quotations.destroy');

        Route::get('/requisitions/{requisition}/evaluation', [OfficerEvaluationController::class, 'edit'])->name('evaluation.edit');
        Route::post('/requisitions/{requisition}/evaluation', [OfficerEvaluationController::class, 'save'])->name('evaluation.save');
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

        Route::get('/vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
        Route::get('/vendors/{vendor}', [AdminVendorController::class, 'show'])->name('vendors.show');
        Route::post('/vendors/{vendor}/decision', [AdminVendorController::class, 'decide'])->name('vendors.decide');
        Route::post('/documents/{document}/review', [AdminVendorController::class, 'reviewDocument'])->name('documents.review');

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
