<?php

namespace LaravelArtifacts\Commands\Versions;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Enums\ChangeType;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactChange;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Results\CommandResult;

class RestoreVersionCommand extends BaseCommand
{
    public function __construct(
        private int $versionId,
        private string $restoredBy,
        private bool $createNewVersion = true,
    ) {
    }

    protected function rules(): array
    {
        return [
            'versionId' => 'required|integer|exists:artifact_versions,id',
            'restoredBy' => 'required|uuid|exists:users,id',
            'createNewVersion' => 'boolean',
        ];
    }

    protected function toArray(): array
    {
        return [
            'versionId' => $this->versionId,
            'restoredBy' => $this->restoredBy,
            'createNewVersion' => $this->createNewVersion,
        ];
    }

    protected function handle(): CommandResult
    {
        $versionToRestore = ArtifactVersion::findOrFail($this->versionId);
        $artifact = Artifact::findOrFail($versionToRestore->artifact_id);

        $currentVersion = $artifact->latestVersion();

        if ($this->createNewVersion) {
            // Créer une nouvelle version avec le contenu restauré
            $latestVersionNumber = $currentVersion?->version ?? '0.0.0';
            [$major, $minor, $patch] = explode('.', $latestVersionNumber);
            $newVersionNumber = sprintf('%d.%d.%d', $major, $minor, (int) $patch + 1);

            $newVersion = ArtifactVersion::create([
                'artifact_id' => $artifact->id,
                'content' => $versionToRestore->content,
                'version' => $newVersionNumber,
                'change_description' => sprintf(
                    'Restauration de la version %s',
                    $versionToRestore->version
                ),
                'is_ai_generated' => false,
                'metadata' => [
                    'restored_from_version_id' => $versionToRestore->id,
                    'restored_from_version' => $versionToRestore->version,
                ],
                'created_by' => $this->restoredBy,
            ]);

            // Créer l'enregistrement de changement
            ArtifactChange::create([
                'artifact_id' => $artifact->id,
                'from_version_id' => $currentVersion?->id,
                'to_version_id' => $newVersion->id,
                'change_type' => ChangeType::MANUAL_EDIT,
                'description' => sprintf('Restauration de la version %s', $versionToRestore->version),
                'details' => [
                    'restored_from' => $versionToRestore->version,
                    'action' => 'restore',
                ],
                'created_by' => $this->restoredBy,
            ]);

            $resultVersion = $newVersion;
            $message = 'Version restaurée avec création d\'une nouvelle version';
        } else {
            // Restaurer directement sans créer de nouvelle version
            $resultVersion = $versionToRestore;
            $message = 'Version restaurée sans créer de nouvelle version';
        }

        // Mettre à jour le contenu de l'artefact
        $artifact->update([
            'content' => $versionToRestore->content,
            'updated_by' => $this->restoredBy,
        ]);

        return CommandResult::success(
            data: [
                'artifact' => $artifact->fresh(['versions']),
                'restored_version' => $resultVersion,
                'original_version' => $versionToRestore,
            ],
            message: $message,
            metadata: [
                'artifact_id' => $artifact->id,
                'restored_from_version' => $versionToRestore->version,
                'new_version_created' => $this->createNewVersion,
            ]
        );
    }
}
