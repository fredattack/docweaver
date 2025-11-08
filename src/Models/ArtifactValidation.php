<?php

namespace LaravelArtifacts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelArtifacts\Enums\ValidationSeverity;

class ArtifactValidation extends Model
{
    protected $fillable = [
        'artifact_id',
        'version_id',
        'rule_class',
        'passed',
        'severity',
        'message',
        'details',
        'suggested_action',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'severity' => ValidationSeverity::class,
        'details' => 'json',
    ];

    // Relationships
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(Artifact::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ArtifactVersion::class, 'version_id');
    }

    // Scopes
    public function scopeFailed($query)
    {
        return $query->where('passed', false);
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }

    public function scopeBySeverity($query, ValidationSeverity $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeErrors($query)
    {
        return $query->where('severity', ValidationSeverity::ERROR);
    }

    public function scopeWarnings($query)
    {
        return $query->where('severity', ValidationSeverity::WARNING);
    }
}
