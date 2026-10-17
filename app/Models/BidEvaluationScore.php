<?php

namespace App\Models;

use App\Enums\CriterionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BidEvaluationScore extends Model
{
    protected $fillable = [
        'bid_evaluation_id', 'quotation_id', 'criterion_type',
        'criterion', 'weight', 'score', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'criterion_type' => CriterionType::class,
            'weight' => 'decimal:2',
            'score' => 'decimal:2',
        ];
    }

    public function bidEvaluation(): BelongsTo
    {
        return $this->belongsTo(BidEvaluation::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
