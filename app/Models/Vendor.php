<?php

namespace App\Models;

use App\Enums\AccreditationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    /**
     * accreditation_status / reviewed_* / rejection_reason are NOT mass-assignable;
     * change them through accredit() / reject() / forceFill().
     */
    protected $fillable = [
        'organization_id', 'vat_registered', 'bank_reference', 'employees_count',
        'service_capacity', 'facilities_equipment', 'industries_served',
        'products_services', 'has_legal_disputes', 'legal_disputes_details', 'remarks',
    ];

    protected $attributes = [
        'accreditation_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'accreditation_status' => AccreditationStatus::class,
            'vat_registered' => 'boolean',
            'has_legal_disputes' => 'boolean',
            'industries_served' => 'array',
            'employees_count' => 'integer',
            'accredited_at' => 'datetime',
            'accreditation_expires_at' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    // ---- Relationships --------------------------------------------------

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'vendor_categories');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(VendorClient::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(VendorCertification::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(PrVendorInvitation::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // ---- Accreditation --------------------------------------------------

    /** Accredited AND not past its expiry date. */
    public function scopeAccredited(Builder $query): Builder
    {
        return $query
            ->where('accreditation_status', AccreditationStatus::Accredited->value)
            ->where(function (Builder $q) {
                $q->whereNull('accreditation_expires_at')
                    ->orWhereDate('accreditation_expires_at', '>=', today());
            });
    }

    public function isAccredited(): bool
    {
        return $this->accreditation_status === AccreditationStatus::Accredited
            && ($this->accreditation_expires_at === null
                || $this->accreditation_expires_at->copy()->endOfDay()->isFuture());
    }

    /** Default validity is 12 months to match the annual subscription; change if needed. */
    public function accredit(User $reviewer, ?Carbon $expiresAt = null): void
    {
        $this->forceFill([
            'accreditation_status' => AccreditationStatus::Accredited,
            'accredited_at' => now(),
            'accreditation_expires_at' => ($expiresAt ?? now()->addYear())->toDateString(),
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ])->save();
    }

    public function reject(User $reviewer, string $reason): void
    {
        $this->forceFill([
            'accreditation_status' => AccreditationStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ])->save();
    }

    public function getNameAttribute(): string
    {
        return $this->organization?->display_name ?? "Vendor #{$this->id}";
    }
}
