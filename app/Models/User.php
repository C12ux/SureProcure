<?php

namespace App\Models;

use App\Enums\OrgRole;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * `role` and `is_active` are deliberately NOT mass-assignable so nobody can
     * register themselves as admin. Set them explicitly (forceFill / forceCreate).
     */
    protected $fillable = ['name', 'email', 'password', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // ---- Relationships --------------------------------------------------

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot('org_role')
            ->withTimestamps();
    }

    public function officerProfile(): HasOne
    {
        return $this->hasOne(OfficerProfile::class);
    }

    public function requestedRequisitions(): HasMany
    {
        return $this->hasMany(PurchaseRequisition::class, 'requested_by');
    }

    public function assignedRequisitions(): HasMany
    {
        return $this->hasMany(PurchaseRequisition::class, 'assigned_officer_id');
    }

    // ---- Role helpers ---------------------------------------------------

    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isOfficer(): bool
    {
        return $this->role === UserRole::ProcurementOfficer;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::Client;
    }

    public function isVendor(): bool
    {
        return $this->role === UserRole::Vendor;
    }

    /** The user's role inside a given organization, or null if not a member. */
    public function orgRole(Organization $organization): ?OrgRole
    {
        return $this->organizations()
            ->where('organizations.id', $organization->id)
            ->first()?->pivot->org_role;
    }

    public function belongsToOrganization(Organization $organization): bool
    {
        return $this->orgRole($organization) !== null;
    }
}
