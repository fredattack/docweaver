<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use LaravelArtifacts\Enums\AiProvider;
use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Enums\ArtifactType;
use LaravelArtifacts\Models\Artifact;

class ArtifactFactory extends Factory
{
    protected $model = Artifact::class;

    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(5, true),
            'type' => fake()->randomElement(ArtifactType::cases()),
            'status' => ArtifactStatus::DRAFT,
            'quality_score' => fake()->randomFloat(2, 0, 100),
            'completeness_score' => fake()->randomFloat(2, 0, 100),
            'ai_provider_used' => fake()->optional()->randomElement(AiProvider::cases()),
            'metadata' => [
                'tags' => fake()->words(3),
                'author_notes' => fake()->sentence(),
            ],
            'created_by' => null, // Will be set in tests
            'updated_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArtifactStatus::PUBLISHED,
            'quality_score' => fake()->randomFloat(2, 80, 100),
            'completeness_score' => fake()->randomFloat(2, 85, 100),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArtifactStatus::DRAFT,
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArtifactStatus::UNDER_REVIEW,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ArtifactStatus::ARCHIVED,
        ]);
    }

    public function documentation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ArtifactType::DOCUMENTATION,
        ]);
    }

    public function specification(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ArtifactType::SPECIFICATION,
        ]);
    }

    public function guide(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ArtifactType::GUIDE,
        ]);
    }
}
