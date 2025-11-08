<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelArtifacts\Models\ArtifactQualityGate;

class ArtifactQualityGateFactory extends Factory
{
    protected $model = ArtifactQualityGate::class;

    public function definition(): array
    {
        return [
            'artifact_id' => null,
            'rule_class' => 'LaravelArtifacts\\Services\\ValidationService\\Rules\\'.fake()->word().'Rule',
            'enabled' => fake()->boolean(80),
            'config' => [
                'threshold' => fake()->numberBetween(70, 90),
                'strict' => fake()->boolean(),
            ],
        ];
    }

    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => false,
        ]);
    }
}
