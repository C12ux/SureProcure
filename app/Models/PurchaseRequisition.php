<?php

namespace App\Models;

use App\Enums\PrPriority;
use App\Enums\PrStatus;
use App\Enums\UserRole;
use App\Models\Concerns\HasSequentialNumber;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseRequisition extends Model
{
    use HasSequentialNumber, SoftDeletes;

    /**
     * pr_number is generated automatically and `status` is NOT mass-assignable:
     * move a PR through its workflow with transitionTo() so the rules are
     * enforced and a pr_status_history row is written.
     */
    protected $fillable = [
        'organization_id', 'requested_by', 'assigned_officer_id', 'category_id',
        'title', 'description', 'needed_by_date', 'budget', 'currency', 'priority',
    ];

    protected $attributes = [
        'status' => 'draft',
        'priority' => 'normal',
        'currency' => 'PHP',
    ];

    protected function casts(): array
    {
        return [
            'status' => PrStatus::class,
            'priority' => PrPriority::class,
            'needed_by_date' => 'date',
            'budget' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $pr) {
            if (empty($pr->pr_number)) {
                $pr->pr_number = static::nextNumber('pr_number', 'PR');
            }
        });

        // First history row: the PR's initial status.
        static::created(function (self $pr) {
            $pr->statusHistory()->create([
                'from_status' => null,
                'to_status' => $pr->status,
                'changed_by' => $pr->requested_by,
                'note' => 'Created',
            ]);
        });
    }

    // ---- Relationships --------------------------------------------------

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_officer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(PrStatusHistory::class)->orderBy('id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(PrVendorInvitation::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function bidEvaluation(): HasOne
    {
        return $this->hasOne(BidEvaluation::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    // ---- Workflow -------------------------------------------------------

    /**
     * @throws DomainException when the move is not allowed by PrStatus::allowedTransitions()
     */
    public function transitionTo(PrStatus $to, ?User $by = null, ?string $note = null): static
    {
        $from = $this->status;

        if (! $from->canTransitionTo($to)) {
            throw new DomainException(
                "PR {$this->pr_number} cannot move from '{$from->value}' to '{$to->value}'."
            );
        }

        DB::transaction(function () use ($from, $to, $by, $note) {
            $this->forceFill(['status' => $to]);

            if ($to === PrStatus::Submitted) {
                $this->submitted_at = now();
            }

            $this->save();

            $this->statusHistory()->create([
                'from_status' => $from,
                'to_status' => $to,
                'changed_by' => $by?->id,
                'note' => $note,
            ]);
        });

        return $this;
    }

    // ---- Tenant isolation -----------------------------------------------

    /**
     * Restrict a query to the PRs a user may see:
     *  admin   -> all
     *  officer -> PRs assigned to them
     *  client  -> PRs of organizations they belong to
     *  vendor  -> only PRs their vendor org was invited to quote on
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $orgIds = $user->organizations()->select('organizations.id');

        return match ($user->role) {
            UserRole::Admin => $query,
            UserRole::ProcurementOfficer => $query->where('assigned_officer_id', $user->id),
            UserRole::Client => $query->whereIn('organization_id', $orgIds),
            UserRole::Vendor => $query->whereHas('invitations', function (Builder $q) use ($orgIds) {
                $q->whereIn(
                    'vendor_id',
                    Vendor::query()->whereIn('organization_id', $orgIds)->select('id')
                );
            }),
        };
    }

    public function scopeWithStatus(Builder $query, PrStatus ...$statuses): Builder
    {
        return $query->whereIn('status', array_map(fn (PrStatus $s) => $s->value, $statuses));
    }

    // ---- Helpers --------------------------------------------------------

    public function getEstimatedTotalAttribute(): float
    {
        return (float) $this->items->sum(fn (PurchaseRequisitionItem $i) => $i->line_total);
    }
}
