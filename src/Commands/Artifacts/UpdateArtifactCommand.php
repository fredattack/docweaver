<?php

namespace LaravelArtifacts\Commands\Artifacts;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Enums\ArtifactType;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Results\CommandResult;

class UpdateArtifactCommand extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private ?string $title = null,
        private ?string $content = null,
        private ?string $type = null,
        private ?string $status = null,
        private ?array $metadata = null,
        private string $updatedBy,
    ) {
    }

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'type' => 'nullable|in:documentation,specification,guide,api,response,other',
            'status' => 'nullable|in:draft,under_review,published,archived',
            'metadata' => 'nullable|array',
            'updatedBy' => 'required|uuid|exists:users,id',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'updatedBy' => $this->updatedBy,
        ];
    }

    protected function handle(): CommandResult
    {
        $artifact = Artifact::findOrFail($this->artifactId);

        // Préparer les données de mise à jour
        $updateData = [
            'updated_by' => $this->updatedBy,
        ];

        if ($this->title !== null) {
            $updateData['title'] = $this->title;
        }

        if ($this->content !== null) {
            $updateData['content'] = $this->content;
        }

        if ($this->type !== null) {
            $updateData['type'] = ArtifactType::from($this->type);
        }

        if ($this->status !== null) {
            $updateData['status'] = ArtifactStatus::from($this->status);
        }

        if ($this->metadata !== null) {
            $updateData['metadata'] = array_merge(
                $artifact->metadata ?? [],
                $this->metadata
            );
        }

        // Mettre à jour l'artefact
        $artifact->update($updateData);

        return CommandResult::success(
            data: $artifact->fresh(['versions', 'creator', 'updater']),
            message: 'Artefact mis à jour avec succès',
            metadata: [
                'artifact_id' => $artifact->id,
                'updated_fields' => array_keys($updateData),
            ]
        );
    }
}
