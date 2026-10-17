<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrVendorInvitation extends Model
{
    // This table has invited_at / responded_at instead of created_at / updated_at.
    public $timestamps = false;

    protected $fillable = ['purchase_requisition_id', 'vendor_id', 'invited_by', 'status', 'responded_at'];

    protected $attributes = ['status' => 'invited'];

    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'invited_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
