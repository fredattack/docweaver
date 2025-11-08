<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelArtifacts\Enums\ChangeType;
use LaravelArtifacts\Models\ArtifactChange;

class ArtifactChangeFactory extends Factory
{
    protected $model = ArtifactChange::class;

    public function definition(): array
    {
        return [
            'artifact_id' => null,
            'from_version_id' => null,
            'to_version_id' => null,
            'change_type' => fake()->randomElement(ChangeType::cases()),
            'description' => fake()->sentence(),
            'details' => [
                'lines_added' => fake()->numberBetween(1, 100),
                'lines_removed' => fake()->numberBetween(0, 50),
                'conflicts' => [],
            ],
            'created_by' => null,
        ];
    }
}
