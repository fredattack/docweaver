<?php

namespace LaravelArtifacts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LaravelArtifacts\Enums\AiProvider;

class ArtifactVersion extends Model
{
    protected $fillable = [
        'artifact_id',
        'content',
        'content_hash',
        'change_description',
        'version',
        'is_ai_generated',
        'ai_provider_used',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'ai_provider_used' => AiProvider::class,
        'metadata' => 'json',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($version) {
            if (empty($version->content_hash)) {
                $version->content_hash = hash('sha256', $version->content);
            }
        });
    }

    // Relationships
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    public function changesFrom(): HasMany
    {
        return $this->hasMany(ArtifactChange::class, 'from_version_id');
    }

    public function changesTo(): HasMany
    {
        return $this->hasMany(ArtifactChange::class, 'to_version_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(ArtifactValidation::class, 'version_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    // Methods
    public function isDuplicate(): bool
    {
        return static::where('artifact_id', $this->artifact_id)
            ->where('content_hash', $this->content_hash)
            ->where('id', '!=', $this->id)
            ->exists();
    }
}
