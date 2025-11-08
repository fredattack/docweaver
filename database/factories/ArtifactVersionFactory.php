<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelArtifacts\Enums\AiProvider;
use LaravelArtifacts\Models\ArtifactVersion;

class ArtifactVersionFactory extends Factory
{
    protected $model = ArtifactVersion::class;

    public function definition(): array
    {
        $content = fake()->paragraphs(5, true);

        return [
            'artifact_id' => null, // Will be set in tests
            'content' => $content,
            'content_hash' => hash('sha256', $content),
            'change_description' => fake()->sentence(),
            'version' => '1.0.0',
            'is_ai_generated' => fake()->boolean(),
            'ai_provider_used' => fake()->optional()->randomElement(AiProvider::cases()),
            'metadata' => [
                'generation_time' => fake()->randomFloat(2, 0.5, 5.0),
                'tokens_used' => fake()->numberBetween(100, 2000),
            ],
            'created_by' => null, // Will be set in tests
        ];
    }

    public function aiGenerated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_ai_generated' => true,
            'ai_provider_used' => fake()->randomElement(AiProvider::cases()),
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_ai_generated' => false,
            'ai_provider_used' => null,
        ]);
    }

    public function version(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => $version,
        ]);
    }
}
