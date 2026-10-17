<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quotation extends Model
{
    protected $fillable = [
        'purchase_requisition_id', 'vendor_id', 'total_amount', 'currency',
        'lead_time_days', 'valid_until', 'payment_terms', 'delivery_terms',
        'notes', 'status', 'submitted_by',
    ];

    protected $attributes = ['status' => 'received', 'currency' => 'PHP'];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'total_amount' => 'decimal:2',
            'lead_time_days' => 'integer',
            'valid_until' => 'date',
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

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(BidEvaluationScore::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function isExpired(): bool
    {
        return $this->valid_until?->endOfDay()->isPast() ?? false;
    }
}
