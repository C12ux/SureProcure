<?php

namespace App\Models;

use App\Enums\BidEvaluationStatus;
use App\Enums\CriterionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class BidEvaluation extends Model
{
    protected $fillable = [
        'purchase_requisition_id', 'recommended_quotation_id', 'recommendation',
        'status', 'prepared_by', 'submitted_at', 'decided_by', 'decided_at', 'decision_remarks',
    ];

    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return [
            'status' => BidEvaluationStatus::class,
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function recommendedQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'recommended_quotation_id');
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(BidEvaluationScore::class);
    }

    /**
     * Weighted score per quotation, best first:
     * [quotation_id => ['technical' => 82.5, 'commercial' => 90.0, 'overall' => 85.8]]
     * Each figure is sum(weight * score) / sum(weight) over that criterion group.
     */
    public function totalsByQuotation(): Collection
    {
        $weighted = function (Collection $rows): ?float {
            $weights = $rows->sum(fn ($r) => (float) $r->weight);

            return $weights > 0
                ? round($rows->sum(fn ($r) => (float) $r->weight * (float) $r->score) / $weights, 2)
                : null;
        };

        return $this->scores()->get()
            ->groupBy('quotation_id')
            ->map(fn (Collection $rows) => [
                'technical' => $weighted($rows->filter(fn ($r) => $r->criterion_type === CriterionType::Technical)),
                'commercial' => $weighted($rows->filter(fn ($r) => $r->criterion_type === CriterionType::Commercial)),
                'overall' => $weighted($rows),
            ])
            ->sortByDesc('overall');
    }
}
