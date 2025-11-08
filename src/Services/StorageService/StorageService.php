<?php

namespace LaravelArtifacts\Services\StorageService;

use Illuminate\Support\Facades\Storage;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactStorage;

class StorageService
{
    /**
     * Sauvegarder un artefact dans le stockage.
     */
    public function store(Artifact $artifact, ?string $driver = null): ArtifactStorage
    {
        $driver = $driver ?? config('artifacts.storage.default', 'local');
        $content = $artifact->content;
        $path = $this->generatePath($artifact);

        // Sauvegarder selon le driver
        match ($driver) {
            'local' => $this->storeLocal($path, $content),
            's3' => $this->storeS3($path, $content),
            'azure' => $this->storeAzure($path, $content),
            'database' => null, // Déjà dans la base de données
            default => throw new \InvalidArgumentException("Driver de stockage invalide: {$driver}"),
        };

        // Créer ou mettre à jour l'enregistrement de stockage
        return ArtifactStorage::updateOrCreate(
            [
                'artifact_id' => $artifact->id,
                'driver' => $driver,
            ],
            [
                'path' => $path,
                'location' => $this->getLocationConfig($driver),
                'metadata' => [
                    'size' => strlen($content),
                    'mime_type' => 'text/markdown',
                    'checksum' => md5($content),
                ],
                'synced_at' => now(),
            ]
        );
    }

    /**
     * Récupérer un artefact depuis le stockage.
     */
    public function retrieve(ArtifactStorage $storage): ?string
    {
        return match ($storage->driver) {
            'local' => $this->retrieveLocal($storage->path),
            's3' => $this->retrieveS3($storage->path),
            'azure' => $this->retrieveAzure($storage->path),
            'database' => $storage->artifact->content,
            default => null,
        };
    }

    /**
     * Supprimer un artefact du stockage.
     */
    public function delete(ArtifactStorage $storage): bool
    {
        $result = match ($storage->driver) {
            'local' => $this->deleteLocal($storage->path),
            's3' => $this->deleteS3($storage->path),
            'azure' => $this->deleteAzure($storage->path),
            'database' => true, // Ne rien faire pour database
            default => false,
        };

        if ($result) {
            $storage->delete();
        }

        return $result;
    }

    /**
     * Vérifier si un artefact existe dans le stockage.
     */
    public function exists(ArtifactStorage $storage): bool
    {
        return match ($storage->driver) {
            'local' => $this->existsLocal($storage->path),
            's3' => $this->existsS3($storage->path),
            'azure' => $this->existsAzure($storage->path),
            'database' => true,
            default => false,
        };
    }

    /**
     * Synchroniser un artefact avec le stockage.
     */
    public function sync(Artifact $artifact): array
    {
        $results = [];
        $storageRecords = $artifact->storage()->get();

        foreach ($storageRecords as $storage) {
            try {
                $this->store($artifact, $storage->driver);
                $results[$storage->driver] = ['success' => true, 'synced_at' => now()];
            } catch (\Exception $e) {
                $results[$storage->driver] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    // Méthodes privées pour chaque driver

    private function storeLocal(string $path, string $content): void
    {
        $basePath = config('artifacts.storage.drivers.local.path', 'artifacts');
        $fullPath = "{$basePath}/{$path}";

        Storage::disk('local')->put($fullPath, $content);
    }

    private function retrieveLocal(string $path): ?string
    {
        $basePath = config('artifacts.storage.drivers.local.path', 'artifacts');
        $fullPath = "{$basePath}/{$path}";

        return Storage::disk('local')->exists($fullPath)
            ? Storage::disk('local')->get($fullPath)
            : null;
    }

    private function deleteLocal(string $path): bool
    {
        $basePath = config('artifacts.storage.drivers.local.path', 'artifacts');
        $fullPath = "{$basePath}/{$path}";

        return Storage::disk('local')->delete($fullPath);
    }

    private function existsLocal(string $path): bool
    {
        $basePath = config('artifacts.storage.drivers.local.path', 'artifacts');
        $fullPath = "{$basePath}/{$path}";

        return Storage::disk('local')->exists($fullPath);
    }

    private function storeS3(string $path, string $content): void
    {
        $basePath = config('artifacts.storage.drivers.s3.path', 'artifacts');
        $fullPath = "{$basePath}/{$path}";

        Storage::disk('s3')->put($fullPath, $content);
    }

    private function retrieveS3(string $path): ?string
    {
        $basePath = config('artifacts.storage.drivers.s3.path', 'artifacts');
        $fullPath = "{$basePath}/{$path}";

        return Storage::disk('s3')->exists($fullPath)
            ? Storage::disk('s3')->get($fullPath)
            : null;
    }

    private function deleteS3(string $path): bool
    {
        $basePath = config('artifacts.storage.drivers.s3.path', 'artifacts');
        $fullPath = "{$basePath}/{$path}";

        return Storage::disk('s3')->delete($fullPath);
    }

    private function existsS3(string $path): bool
    {
        $basePath = config('artifacts.storage.drivers.s3.path', 'artifacts');
        $fullPath = "{$basePath}/{$path}";

        return Storage::disk('s3')->exists($fullPath);
    }

    private function storeAzure(string $path, string $content): void
    {
        // Implémentation Azure (nécessite un driver Laravel pour Azure)
        throw new \Exception('Azure storage not yet implemented');
    }

    private function retrieveAzure(string $path): ?string
    {
        throw new \Exception('Azure storage not yet implemented');
    }

    private function deleteAzure(string $path): bool
    {
        throw new \Exception('Azure storage not yet implemented');
    }

    private function existsAzure(string $path): bool
    {
        throw new \Exception('Azure storage not yet implemented');
    }

    private function generatePath(Artifact $artifact): string
    {
        $date = now()->format('Y/m/d');
        $filename = $artifact->slug.'.md';

        return "{$date}/{$filename}";
    }

    private function getLocationConfig(string $driver): array
    {
        return match ($driver) {
            's3' => [
                'bucket' => config('artifacts.storage.drivers.s3.bucket'),
                'region' => config('artifacts.storage.drivers.s3.region'),
            ],
            'azure' => [
                'container' => config('artifacts.storage.drivers.azure.container'),
                'account' => config('artifacts.storage.drivers.azure.account'),
            ],
            default => [],
        };
    }
}
