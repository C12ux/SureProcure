# SureProcure Eloquent models

Copy `app/` into your project root (it will overwrite app/Models/User.php).

## One manual step: app/Providers/AppServiceProvider.php

```php
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\{Vendor, OfficerProfile, PurchaseRequisition, Quotation, PurchaseOrder, Payment};

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
```
