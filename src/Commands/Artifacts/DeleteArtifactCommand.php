<?php

namespace LaravelArtifacts\Commands\Artifacts;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Results\CommandResult;

class DeleteArtifactCommand extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private bool $forceDelete = false,
    ) {
    }

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'forceDelete' => 'boolean',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'forceDelete' => $this->forceDelete,
        ];
    }

    protected function handle(): CommandResult
    {
        $artifact = Artifact::withTrashed()->findOrFail($this->artifactId);

        if ($this->forceDelete) {
            // Suppression définitive
            $artifact->forceDelete();
            $message = 'Artefact supprimé définitivement';
        } else {
            // Soft delete
            $artifact->delete();
            $message = 'Artefact archivé (soft delete)';
        }

        return CommandResult::success(
            data: [
                'artifact_id' => $this->artifactId,
                'force_deleted' => $this->forceDelete,
            ],
            message: $message,
            metadata: [
                'deleted_at' => now(),
                'type' => $this->forceDelete ? 'force' : 'soft',
            ]
        );
    }
}
