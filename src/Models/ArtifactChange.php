<?php

namespace LaravelArtifacts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelArtifacts\Enums\ChangeType;

class ArtifactChange extends Model
{
    protected $fillable = [
        'artifact_id',
        'from_version_id',
        'to_version_id',
        'change_type',
        'description',
        'details',
        'created_by',
    ];

    protected $casts = [
        'change_type' => ChangeType::class,
        'details' => 'json',
    ];

    // Relationships
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    public function fromVersion(): BelongsTo
    {
        return $this->belongsTo(ArtifactVersion::class, 'from_version_id');
    }

    public function toVersion(): BelongsTo
    {
        return $this->belongsTo(ArtifactVersion::class, 'to_version_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    // Scopes
    public function scopeByType($query, ChangeType $type)
    {
        return $query->where('change_type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->latest();
    }
}
