<?php

namespace LaravelArtifacts\Enums;

enum ValidationSeverity: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';

    public function label(): string
    {
        return match ($this) {
            self::ERROR => 'Error',
            self::WARNING => 'Warning',
            self::INFO => 'Info',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ERROR => 'red',
            self::WARNING => 'yellow',
            self::INFO => 'blue',
        };
    }

    public function shouldBlock(): bool
    {
        return $this === self::ERROR;
    }
}
