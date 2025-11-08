<?php

namespace LaravelArtifacts\Commands\Artifacts;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Results\CommandResult;

class ArchiveArtifactCommand extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private string $archivedBy,
        private ?string $reason = null,
    ) {
    }

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'archivedBy' => 'required|uuid|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'archivedBy' => $this->archivedBy,
            'reason' => $this->reason,
        ];
    }

    protected function handle(): CommandResult
    {
        $artifact = Artifact::findOrFail($this->artifactId);

        // Vérifier si déjà archivé
        if ($artifact->status === ArtifactStatus::ARCHIVED) {
            return CommandResult::failed(
                errors: ['status' => ['L\'artefact est déjà archivé']],
                message: 'Archivage impossible'
            );
        }

        // Mettre à jour les métadonnées avec la raison d'archivage
        $metadata = $artifact->metadata ?? [];
        if ($this->reason) {
            $metadata['archive_reason'] = $this->reason;
            $metadata['archived_at'] = now()->toIso8601String();
        }

        // Archiver l'artefact
        $artifact->update([
            'status' => ArtifactStatus::ARCHIVED,
            'updated_by' => $this->archivedBy,
            'metadata' => $metadata,
        ]);

        return CommandResult::success(
            data: $artifact->fresh(['versions', 'creator', 'updater']),
            message: 'Artefact archivé avec succès',
            metadata: [
                'artifact_id' => $artifact->id,
                'archived_at' => now(),
                'reason' => $this->reason,
            ]
        );
    }
}
