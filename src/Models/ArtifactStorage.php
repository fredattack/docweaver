<?php

namespace LaravelArtifacts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtifactStorage extends Model
{
    protected $table = 'artifact_storage';

    protected $fillable = [
        'artifact_id',
        'driver',
        'path',
        'location',
        'metadata',
        'synced_at',
    ];

    protected $casts = [
        'location' => 'json',
        'metadata' => 'json',
        'synced_at' => 'datetime',
    ];

    // Relationships
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    // Scopes
    public function scopeByDriver($query, string $driver)
    {
        return $query->where('driver', $driver);
    }

    public function scopeSynced($query)
    {
        return $query->whereNotNull('synced_at');
    }

    public function scopeUnsynced($query)
    {
        return $query->whereNull('synced_at');
    }

    // Methods
    public function markSynced(): void
    {
        $this->update(['synced_at' => now()]);
    }

    public function needsSync(): bool
    {
        if (! $this->synced_at) {
            return true;
        }

        return $this->artifact->updated_at > $this->synced_at;
    }
}
