<?php

namespace LaravelArtifacts\Enums;

enum ArtifactStatus: string
{
    case DRAFT = 'draft';
    case UNDER_REVIEW = 'under_review';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::UNDER_REVIEW => 'Under Review',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::UNDER_REVIEW => 'yellow',
            self::PUBLISHED => 'green',
            self::ARCHIVED => 'red',
        };
    }
}
