<?php

namespace App\Models;

use App\Enums\OrganizationType;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'legal_name', 'trade_name', 'tin', 'business_address', 'website',
        'org_type', 'year_established', 'phone', 'email',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
            'year_established' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot('org_role')
            ->withTimestamps();
    }

    /** Only vendor organizations have one. */
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function purchaseRequisitions(): HasMany
    {
        return $this->hasMany(PurchaseRequisition::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', SubscriptionStatus::Active->value)
            ->whereDate('end_date', '>=', today())
            ->exists();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->trade_name ?: $this->legal_name;
    }
}
