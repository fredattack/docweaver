<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use LaravelArtifacts\Models\ArtifactStorage;

class ArtifactStorageFactory extends Factory
{
    protected $model = ArtifactStorage::class;

    public function definition(): array
    {
        $driver = fake()->randomElement(['local', 's3', 'azure', 'database']);

        return [
            'artifact_id' => null,
            'driver' => $driver,
            'path' => fake()->filePath(),
            'location' => $this->getLocationForDriver($driver),
            'metadata' => [
                'size' => fake()->numberBetween(1000, 100000),
                'mime_type' => 'text/markdown',
            ],
            'synced_at' => fake()->optional()->dateTime(),
        ];
    }

    protected function getLocationForDriver(string $driver): array
    {
        return match ($driver) {
            's3' => [
                'bucket' => 'artifacts-bucket',
                'region' => 'us-east-1',
            ],
            'azure' => [
                'container' => 'artifacts',
                'account' => 'storageaccount',
            ],
            default => [],
        };
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'driver' => 'local',
            'location' => [],
        ]);
    }

    public function s3(): static
    {
        return $this->state(fn (array $attributes) => [
            'driver' => 's3',
            'location' => [
                'bucket' => 'artifacts-bucket',
                'region' => 'us-east-1',
            ],
        ]);
    }

    public function synced(): static
    {
        return $this->state(fn (array $attributes) => [
            'synced_at' => now(),
        ]);
    }

    public function unsynced(): static
    {
        return $this->state(fn (array $attributes) => [
            'synced_at' => null,
        ]);
    }
}
