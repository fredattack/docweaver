<?php

namespace LaravelArtifacts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtifactQualityGate extends Model
{
    protected $fillable = [
        'artifact_id',
        'rule_class',
        'enabled',
        'config',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'config' => 'json',
    ];

    // Relationships
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    // Scopes
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeDisabled($query)
    {
        return $query->where('enabled', false);
    }

    // Methods
    public function toggle(): void
    {
        $this->update(['enabled' => ! $this->enabled]);
    }
}
