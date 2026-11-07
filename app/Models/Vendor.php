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
     * Workflow columns (accreditation_status, submitted_at, declared_*, reviewed_*,
     * rejection_reason, remarks) are NOT mass-assignable: they change only through
     * submitForReview() / accredit() / requestChanges() / reject().
     */
    protected $fillable = [
        'organization_id', 'representative_name', 'representative_title', 'mobile_number', 'landline',
        'vat_registered', 'bank_reference', 'years_in_industry', 'employees_count',
        'service_capacity', 'facilities_equipment', 'industries_served', 'products_services',
        'has_legal_disputes', 'legal_disputes_details', 'complies_hse', 'hse_details',
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
            'complies_hse' => 'boolean',
            'industries_served' => 'array',
            'years_in_industry' => 'integer',
            'employees_count' => 'integer',
            'submitted_at' => 'datetime',
            'declared_at' => 'datetime',
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

    /** The vendor sends a complete application for review. */
    public function submitForReview(string $declaredBy): void
    {
        $this->forceFill([
            'accreditation_status' => AccreditationStatus::UnderReview,
            'submitted_at' => now(),
            'declared_by' => $declaredBy,
            'declared_at' => now(),
            'remarks' => null,
            'rejection_reason' => null,
        ])->save();
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

    /** Send the application back to the vendor with a note about what to fix. */
    public function requestChanges(User $reviewer, string $note): void
    {
        $this->forceFill([
            'accreditation_status' => AccreditationStatus::Pending,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'remarks' => $note,
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

    /** Wording for badges: "pending" means two things, never submitted or sent back. */
    public function statusLabel(): string
    {
        return match ($this->accreditation_status) {
            AccreditationStatus::Pending => $this->submitted_at ? 'Changes requested' : 'Not submitted',
            AccreditationStatus::UnderReview => 'Under review',
            AccreditationStatus::Accredited => $this->isAccredited() ? 'Accredited' : 'Accreditation expired',
            AccreditationStatus::Rejected => 'Rejected',
            AccreditationStatus::Expired => 'Expired',
            AccreditationStatus::Suspended => 'Suspended',
        };
    }

    /** Badge color family: neutral, info, attention, success, danger. */
    public function statusTone(): string
    {
        return match ($this->accreditation_status) {
            AccreditationStatus::Pending => $this->submitted_at ? 'attention' : 'neutral',
            AccreditationStatus::UnderReview => 'info',
            AccreditationStatus::Accredited => $this->isAccredited() ? 'success' : 'danger',
            default => 'danger',
        };
    }

    public function getNameAttribute(): string
    {
        return $this->organization?->display_name ?? "Vendor #{$this->id}";
    }
}
