<?php

namespace LaravelArtifacts\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use LaravelArtifacts\Enums\AiProvider;
use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Enums\ArtifactType;

class Artifact extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'type',
        'status',
        'quality_score',
        'completeness_score',
        'ai_provider_used',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type' => ArtifactType::class,
        'status' => ArtifactStatus::class,
        'ai_provider_used' => AiProvider::class,
        'metadata' => 'json',
        'quality_score' => 'float',
        'completeness_score' => 'float',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($artifact) {
            if (empty($artifact->slug)) {
                $artifact->slug = Str::slug($artifact->title);
            }
        });
    }

    // Relationships
    public function versions(): HasMany
    {
        return $this->hasMany(ArtifactVersion::class)->orderByDesc('created_at');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(ArtifactChange::class)->orderByDesc('created_at');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(ArtifactValidation::class);
    }

    public function qualityGates(): HasMany
    {
        return $this->hasMany(ArtifactQualityGate::class);
    }

    public function storage(): HasOne
    {
        return $this->hasOne(ArtifactStorage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updated_by');
    }

    // Accessors
    public function latestVersion(): ?ArtifactVersion
    {
        return $this->versions()->first();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', ArtifactStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ArtifactStatus::DRAFT);
    }

    public function scopeByType($query, ArtifactType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->latest();
    }

    // Methods
    public function publish(): void
    {
        $this->update(['status' => ArtifactStatus::PUBLISHED]);
    }

    public function archive(): void
    {
        $this->update(['status' => ArtifactStatus::ARCHIVED]);
    }

    public function sendForReview(): void
    {
        $this->update(['status' => ArtifactStatus::UNDER_REVIEW]);
    }
}
