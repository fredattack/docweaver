<?php

namespace LaravelArtifacts\Commands;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LaravelArtifacts\Contracts\ArtifactCommand;
use LaravelArtifacts\Results\CommandResult;

abstract class BaseCommand implements ArtifactCommand
{
    protected array $validationErrors = [];

    /**
     * Get validation rules for the command.
     */
    abstract protected function rules(): array;

    /**
     * Execute the command logic.
     */
    abstract protected function handle(): CommandResult;

    /**
     * Validate the command payload.
     */
    public function validate(): bool
    {
        $validator = Validator::make($this->toArray(), $this->rules());

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors()->toArray();

            return false;
        }

        $this->validationErrors = [];

        return true;
    }

    /**
     * Execute the command.
     */
    public function execute(): CommandResult
    {
        if (! $this->validate()) {
            return CommandResult::failed(
                errors: $this->validationErrors,
                message: 'Validation failed'
            );
        }

        try {
            return $this->handle();
        } catch (ValidationException $e) {
            return CommandResult::failed(
                errors: $e->errors(),
                message: $e->getMessage()
            );
        } catch (\Exception $e) {
            return CommandResult::failed(
                errors: ['exception' => $e->getMessage()],
                message: 'Command execution failed'
            );
        }
    }

    /**
     * Get validation errors if any.
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Convert command to array for validation.
     */
    abstract protected function toArray(): array;
}
