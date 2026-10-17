<?php

namespace App\Models;

use App\Enums\OfficerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficerProfile extends Model
{
    use SoftDeletes;

    /** accreditation_status / reviewed_* are set by reviewers via forceFill(). */
    protected $fillable = [
        'user_id', 'first_name', 'middle_name', 'surname', 'current_position',
        'current_company', 'business_address', 'linkedin_url', 'years_experience',
        'highest_education', 'certifications', 'specializations', 'industries_handled',
        'top_categories', 'avg_annual_spend_band', 'tools_systems', 'skills_assessment',
        'services_offered', 'ethics_signed_at', 'remarks',
    ];

    protected $attributes = [
        'accreditation_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'accreditation_status' => OfficerStatus::class,
            'years_experience' => 'integer',
            'certifications' => 'array',
            'specializations' => 'array',
            'industries_handled' => 'array',
            'top_categories' => 'array',
            'tools_systems' => 'array',
            'skills_assessment' => 'array',
            'services_offered' => 'array',
            'ethics_signed_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->surname}");
    }

    public function isApproved(): bool
    {
        return $this->accreditation_status === OfficerStatus::Approved;
    }
}
