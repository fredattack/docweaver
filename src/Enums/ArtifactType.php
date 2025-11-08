<?php

namespace LaravelArtifacts\Enums;

enum ArtifactType: string
{
    case DOCUMENTATION = 'documentation';
    case SPECIFICATION = 'specification';
    case GUIDE = 'guide';
    case API = 'api';
    case RESPONSE = 'response';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DOCUMENTATION => 'Documentation',
            self::SPECIFICATION => 'Specification',
            self::GUIDE => 'Guide',
            self::API => 'API',
            self::RESPONSE => 'Response',
            self::OTHER => 'Other',
        };
    }
}
