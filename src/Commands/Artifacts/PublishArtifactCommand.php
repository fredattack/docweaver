<?php

namespace LaravelArtifacts\Commands\Artifacts;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Results\CommandResult;

class PublishArtifactCommand extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private string $publishedBy,
        private bool $skipQualityGates = false,
    ) {
    }

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'publishedBy' => 'required|uuid|exists:users,id',
            'skipQualityGates' => 'boolean',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'publishedBy' => $this->publishedBy,
            'skipQualityGates' => $this->skipQualityGates,
        ];
    }

    protected function handle(): CommandResult
    {
        $artifact = Artifact::findOrFail($this->artifactId);

        // Vérifier si déjà publié
        if ($artifact->status === ArtifactStatus::PUBLISHED) {
            return CommandResult::failed(
                errors: ['status' => ['L\'artefact est déjà publié']],
                message: 'Publication impossible'
            );
        }

        // Vérifier les portes de qualité (si activées)
        if (! $this->skipQualityGates && config('artifacts.quality_gates.enabled')) {
            $failedValidations = $artifact->validations()
                ->where('passed', false)
                ->where('severity', 'error')
                ->get();

            if ($failedValidations->isNotEmpty()) {
                return CommandResult::failed(
                    errors: [
                        'quality_gates' => 'L\'artefact ne passe pas les contrôles de qualité',
                        'failed_validations' => $failedValidations->pluck('message')->toArray(),
                    ],
                    message: 'Publication bloquée par les portes de qualité'
                );
            }
        }

        // Publier l'artefact
        $artifact->update([
            'status' => ArtifactStatus::PUBLISHED,
            'updated_by' => $this->publishedBy,
        ]);

        return CommandResult::success(
            data: $artifact->fresh(['versions', 'creator', 'updater']),
            message: 'Artefact publié avec succès',
            metadata: [
                'artifact_id' => $artifact->id,
                'published_at' => now(),
                'quality_gates_skipped' => $this->skipQualityGates,
            ]
        );
    }
}
