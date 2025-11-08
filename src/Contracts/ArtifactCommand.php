<?php

namespace LaravelArtifacts\Contracts;

use LaravelArtifacts\Results\CommandResult;

interface ArtifactCommand
{
    /**
     * Validate the command payload.
     */
    public function validate(): bool;

    /**
     * Execute the command.
     */
    public function execute(): CommandResult;

    /**
     * Get validation errors if any.
     */
    public function getValidationErrors(): array;
}
