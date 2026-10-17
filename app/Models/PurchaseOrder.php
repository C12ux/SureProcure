<?php

namespace App\Models;

use App\Enums\PoStatus;
use App\Models\Concerns\HasSequentialNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PurchaseOrder extends Model
{
    use HasSequentialNumber;

    protected $fillable = [
        'purchase_requisition_id', 'quotation_id', 'vendor_id',
        'amount', 'currency', 'status', 'issued_at',
    ];

    protected $attributes = ['status' => 'issued', 'currency' => 'PHP'];

    protected function casts(): array
    {
        return [
            'status' => PoStatus::class,
            'amount' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $po) {
            if (empty($po->po_number)) {
                $po->po_number = static::nextNumber('po_number', 'PO');
            }
        });
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
