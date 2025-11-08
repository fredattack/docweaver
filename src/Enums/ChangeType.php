<?php

namespace LaravelArtifacts\Enums;

enum ChangeType: string
{
    case CREATION = 'creation';
    case MERGE = 'merge';
    case MANUAL_EDIT = 'manual_edit';
    case AI_GENERATION = 'ai_generation';
    case VALIDATION = 'validation';
    case PUBLICATION = 'publication';
    case ARCHIVAL = 'archival';

    public function label(): string
    {
        return match ($this) {
            self::CREATION => 'Created',
            self::MERGE => 'Merged',
            self::MANUAL_EDIT => 'Manually Edited',
            self::AI_GENERATION => 'AI Generated',
            self::VALIDATION => 'Validated',
            self::PUBLICATION => 'Published',
            self::ARCHIVAL => 'Archived',
        };
    }
}
