<?php

namespace LaravelArtifacts\Services\VersioningService;

use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactVersion;

class VersioningService
{
    /**
     * Obtenir toutes les versions d'un artefact.
     */
    public function getVersions(string $artifactId, int $limit = 50): \Illuminate\Support\Collection
    {
        return ArtifactVersion::where('artifact_id', $artifactId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir une version spécifique.
     */
    public function getVersion(int $versionId): ?ArtifactVersion
    {
        return ArtifactVersion::find($versionId);
    }

    /**
     * Obtenir la dernière version d'un artefact.
     */
    public function getLatestVersion(string $artifactId): ?ArtifactVersion
    {
        return ArtifactVersion::where('artifact_id', $artifactId)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Incrémenter un numéro de version sémantique.
     */
    public function incrementVersion(string $version, string $type = 'patch'): string
    {
        [$major, $minor, $patch] = explode('.', $version);

        return match ($type) {
            'major' => sprintf('%d.%d.%d', (int) $major + 1, 0, 0),
            'minor' => sprintf('%d.%d.%d', $major, (int) $minor + 1, 0),
            'patch' => sprintf('%d.%d.%d', $major, $minor, (int) $patch + 1),
            default => throw new \InvalidArgumentException("Type de version invalide: {$type}"),
        };
    }

    /**
     * Comparer deux versions sémantiques.
     *
     * @return int -1 si v1 < v2, 0 si v1 == v2, 1 si v1 > v2
     */
    public function compareVersions(string $version1, string $version2): int
    {
        return version_compare($version1, $version2);
    }

    /**
     * Vérifier si une version existe déjà pour un artefact.
     */
    public function versionExists(string $artifactId, string $version): bool
    {
        return ArtifactVersion::where('artifact_id', $artifactId)
            ->where('version', $version)
            ->exists();
    }

    /**
     * Obtenir les versions entre deux dates.
     */
    public function getVersionsBetween(
        string $artifactId,
        \DateTime $startDate,
        \DateTime $endDate
    ): \Illuminate\Support\Collection {
        return ArtifactVersion::where('artifact_id', $artifactId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Nettoyer les anciennes versions selon la politique de rétention.
     */
    public function cleanupOldVersions(string $artifactId): int
    {
        $maxVersions = config('artifacts.versioning.max_versions', 50);
        $retentionDays = config('artifacts.versioning.retention_days', 365);

        $versions = ArtifactVersion::where('artifact_id', $artifactId)
            ->orderByDesc('created_at')
            ->get();

        $deletedCount = 0;

        // Supprimer les versions excédentaires
        if ($versions->count() > $maxVersions) {
            $versionsToDelete = $versions->slice($maxVersions);

            foreach ($versionsToDelete as $version) {
                $version->delete();
                $deletedCount++;
            }
        }

        // Supprimer les versions plus anciennes que la période de rétention
        $cutoffDate = now()->subDays($retentionDays);
        $oldVersions = ArtifactVersion::where('artifact_id', $artifactId)
            ->where('created_at', '<', $cutoffDate)
            ->get();

        foreach ($oldVersions as $version) {
            // Garder toujours au moins une version
            if ($this->getVersions($artifactId)->count() > 1) {
                $version->delete();
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Obtenir les statistiques de versioning pour un artefact.
     */
    public function getVersionStatistics(string $artifactId): array
    {
        $versions = $this->getVersions($artifactId);

        $aiGeneratedCount = $versions->where('is_ai_generated', true)->count();
        $manualCount = $versions->where('is_ai_generated', false)->count();

        $providerStats = $versions->where('is_ai_generated', true)
            ->groupBy('ai_provider_used')
            ->map(fn ($group) => $group->count())
            ->toArray();

        return [
            'total_versions' => $versions->count(),
            'ai_generated' => $aiGeneratedCount,
            'manual' => $manualCount,
            'by_provider' => $providerStats,
            'latest_version' => $versions->first()?->version,
            'first_version' => $versions->last()?->version,
            'oldest_version_date' => $versions->last()?->created_at,
            'newest_version_date' => $versions->first()?->created_at,
        ];
    }

    /**
     * Vérifier si un contenu est dupliqué.
     */
    public function isDuplicateContent(string $artifactId, string $content): bool
    {
        $contentHash = hash('sha256', $content);

        return ArtifactVersion::where('artifact_id', $artifactId)
            ->where('content_hash', $contentHash)
            ->exists();
    }
}
