<?php

namespace LaravelArtifacts\Services\MergeService;

use LaravelArtifacts\Models\ArtifactVersion;

class MergeResult
{
    public function __construct(
        public bool $success,
        public ?string $mergedContent = null,
        public ?ArtifactVersion $version = null,
        public array $conflicts = [],
        public string $message = '',
    ) {
    }

    public static function success(
        string $mergedContent,
        ArtifactVersion $version,
        array $conflicts = [],
        string $message = 'Fusion réussie'
    ): self {
        return new self(
            success: true,
            mergedContent: $mergedContent,
            version: $version,
            conflicts: $conflicts,
            message: $message
        );
    }

    public static function conflicted(
        array $conflicts,
        string $message = 'Conflits détectés'
    ): self {
        return new self(
            success: false,
            conflicts: $conflicts,
            message: $message
        );
    }

    public function hasConflicts(): bool
    {
        return ! empty($this->conflicts);
    }

    public function getConflictCount(): int
    {
        return count($this->conflicts);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'merged_content' => $this->mergedContent,
            'version' => $this->version?->toArray(),
            'conflicts' => $this->conflicts,
            'conflict_count' => $this->getConflictCount(),
            'message' => $this->message,
        ];
    }
}
