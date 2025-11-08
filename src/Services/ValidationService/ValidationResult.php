<?php

namespace LaravelArtifacts\Services\ValidationService;

use LaravelArtifacts\Enums\ValidationSeverity;

class ValidationResult
{
    public function __construct(
        public bool $passed,
        public ValidationSeverity $severity,
        public string $message,
        public array $details = [],
        public ?string $suggestedAction = null,
    ) {
    }

    public static function pass(string $message, array $details = []): self
    {
        return new self(
            passed: true,
            severity: ValidationSeverity::INFO,
            message: $message,
            details: $details,
        );
    }

    public static function fail(
        ValidationSeverity $severity,
        string $message,
        array $details = [],
        ?string $suggestedAction = null
    ): self {
        return new self(
            passed: false,
            severity: $severity,
            message: $message,
            details: $details,
            suggestedAction: $suggestedAction,
        );
    }

    public static function error(string $message, array $details = [], ?string $suggestedAction = null): self
    {
        return self::fail(ValidationSeverity::ERROR, $message, $details, $suggestedAction);
    }

    public static function warning(string $message, array $details = [], ?string $suggestedAction = null): self
    {
        return self::fail(ValidationSeverity::WARNING, $message, $details, $suggestedAction);
    }

    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'severity' => $this->severity->value,
            'message' => $this->message,
            'details' => $this->details,
            'suggested_action' => $this->suggestedAction,
        ];
    }
}
