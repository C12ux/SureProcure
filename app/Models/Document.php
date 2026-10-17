<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use SoftDeletes;

    public const DISK = 'supabase';

    /**
     * `documentable_type` stores short aliases ('vendor', 'quotation', ...), not class
     * names. Register them with Relation::morphMap() in AppServiceProvider.
     * status / verified_* are changed through verify() / reject().
     */
    protected $fillable = [
        'documentable_type', 'documentable_id', 'type', 'original_name', 'storage_path',
        'mime_type', 'size_bytes', 'expires_at', 'uploaded_by',
    ];

    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'status' => DocumentStatus::class,
            'size_bytes' => 'integer',
            'verified_at' => 'datetime',
            'expires_at' => 'date',
        ];
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** Expiring signed link. Files stay private; never expose storage_path directly. */
    public function temporaryUrl(int $minutes = 10): string
    {
        return Storage::disk(self::DISK)->temporaryUrl($this->storage_path, now()->addMinutes($minutes));
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->endOfDay()->isPast() ?? false;
    }

    public function verify(User $by): void
    {
        $this->forceFill([
            'status' => DocumentStatus::Verified,
            'verified_by' => $by->id,
            'verified_at' => now(),
        ])->save();
    }

    public function reject(User $by): void
    {
        $this->forceFill([
            'status' => DocumentStatus::Rejected,
            'verified_by' => $by->id,
            'verified_at' => now(),
        ])->save();
    }
}
