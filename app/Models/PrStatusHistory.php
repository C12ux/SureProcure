<?php

namespace App\Models;

use App\Enums\PrStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrStatusHistory extends Model
{
    protected $table = 'pr_status_history';

    // The table only has created_at.
    public const UPDATED_AT = null;

    protected $fillable = ['purchase_requisition_id', 'from_status', 'to_status', 'changed_by', 'note'];

    protected function casts(): array
    {
        return [
            'from_status' => PrStatus::class,
            'to_status' => PrStatus::class,
        ];
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
