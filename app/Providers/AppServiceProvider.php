<?php

namespace App\Providers;

use App\Models\OfficerProfile;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Quotation;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'vendor'               => Vendor::class,
            'officer_profile'      => OfficerProfile::class,
            'purchase_requisition' => PurchaseRequisition::class,
            'quotation'            => Quotation::class,
            'purchase_order'       => PurchaseOrder::class,
            'payment'              => Payment::class,
        ]);
    }
}