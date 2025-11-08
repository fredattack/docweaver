<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelArtifacts\Enums\ValidationSeverity;
use LaravelArtifacts\Models\ArtifactValidation;

class ArtifactValidationFactory extends Factory
{
    protected $model = ArtifactValidation::class;

    public function definition(): array
    {
        $passed = fake()->boolean(70);

        return [
            'artifact_id' => null,
            'version_id' => null,
            'rule_class' => 'LaravelArtifacts\\Services\\ValidationService\\Rules\\'.fake()->word().'Rule',
            'passed' => $passed,
            'severity' => $passed ? ValidationSeverity::INFO : fake()->randomElement([ValidationSeverity::ERROR, ValidationSeverity::WARNING]),
            'message' => $passed ? 'Validation passed' : fake()->sentence(),
            'details' => [
                'checked_at' => now()->toIso8601String(),
            ],
            'suggested_action' => $passed ? null : fake()->sentence(),
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'passed' => true,
            'severity' => ValidationSeverity::INFO,
            'suggested_action' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'passed' => false,
            'severity' => fake()->randomElement([ValidationSeverity::ERROR, ValidationSeverity::WARNING]),
            'suggested_action' => fake()->sentence(),
        ]);
    }
}
