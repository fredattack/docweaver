<?php

namespace LaravelArtifacts\Commands\Artifacts;

use Illuminate\Support\Str;
use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Enums\ArtifactType;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Results\CommandResult;

class CreateArtifactCommand extends BaseCommand
{
    public function __construct(
        private string $title,
        private string $content,
        private string $type,
        private string $createdBy,
        private ?string $slug = null,
        private ?array $metadata = null,
    ) {
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:documentation,specification,guide,api,response,other',
            'createdBy' => 'required|uuid|exists:users,id',
            'slug' => 'nullable|string|max:255|unique:artifacts,slug',
            'metadata' => 'nullable|array',
        ];
    }

    protected function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'createdBy' => $this->createdBy,
            'slug' => $this->slug,
            'metadata' => $this->metadata,
        ];
    }

    protected function handle(): CommandResult
    {
        // Créer l'artefact
        $artifact = Artifact::create([
            'title' => $this->title,
            'slug' => $this->slug ?? Str::slug($this->title),
            'content' => $this->content,
            'type' => ArtifactType::from($this->type),
            'status' => ArtifactStatus::DRAFT,
            'created_by' => $this->createdBy,
            'metadata' => $this->metadata,
        ]);

        // Créer la première version
        $version = ArtifactVersion::create([
            'artifact_id' => $artifact->id,
            'content' => $this->content,
            'version' => '1.0.0',
            'change_description' => 'Version initiale',
            'is_ai_generated' => false,
            'created_by' => $this->createdBy,
        ]);

        return CommandResult::success(
            data: [
                'artifact' => $artifact->fresh(['versions', 'creator']),
                'version' => $version,
            ],
            message: 'Artefact créé avec succès',
            metadata: [
                'artifact_id' => $artifact->id,
                'version_id' => $version->id,
            ]
        );
    }
}
