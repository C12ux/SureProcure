<?php

namespace App\Models;

use App\Enums\PlanAudience;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['code', 'name', 'audience', 'price', 'currency', 'duration_months', 'is_active'];

    protected function casts(): array
    {
        return [
            'audience' => PlanAudience::class,
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFor(Builder $query, PlanAudience $audience): Builder
    {
        return $query->where('audience', $audience->value);
    }
}
