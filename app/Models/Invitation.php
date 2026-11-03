<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * An invitation to join as a vendor or procurement officer.
 * Created only through issue(); the plain token exists only in the link
 * returned to the admin, the database keeps its SHA-256 hash.
 */
class Invitation extends Model
{
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** @return array{0: self, 1: string} the invitation and its plain token */
    public static function issue(string $email, UserRole $role, ?string $companyName, User $by, int $days = 14): array
    {
        $token = Str::random(48);

        $invitation = static::forceCreate([
            'email' => $email,
            'role' => $role,
            'company_name' => $companyName,
            'token_hash' => hash('sha256', $token),
            'invited_by' => $by->id,
            'expires_at' => now()->addDays($days),
        ]);

        return [$invitation, $token];
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token_hash', hash('sha256', $token))->first();
    }

    /** Replace the link with a fresh one (the old link stops working). Returns the new plain token. */
    public function regenerate(int $days = 14): string
    {
        $token = Str::random(48);

        $this->forceFill([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays($days),
        ])->save();

        return $token;
    }

    public function acceptUrl(string $token): string
    {
        return route('invitations.accept', ['token' => $token]);
    }

    /** pending, accepted, revoked or expired */
    public function status(): string
    {
        return match (true) {
            $this->accepted_at !== null => 'accepted',
            $this->revoked_at !== null => 'revoked',
            $this->expires_at->isPast() => 'expired',
            default => 'pending',
        };
    }

    public function isPending(): bool
    {
        return $this->status() === 'pending';
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')->whereNull('revoked_at')->where('expires_at', '>', now());
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
