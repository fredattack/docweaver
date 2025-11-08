<?php

namespace LaravelArtifacts\Commands\Versions;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Enums\AiProvider;
use LaravelArtifacts\Enums\ChangeType;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactChange;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Results\CommandResult;

class CreateVersionCommand extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private string $content,
        private string $createdBy,
        private ?string $changeDescription = null,
        private bool $isAiGenerated = false,
        private ?string $aiProvider = null,
        private ?string $version = null,
        private ?array $metadata = null,
    ) {
    }

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'content' => 'required|string',
            'createdBy' => 'required|uuid|exists:users,id',
            'changeDescription' => 'nullable|string|max:500',
            'isAiGenerated' => 'boolean',
            'aiProvider' => 'nullable|in:openai,claude,gemini,local',
            'version' => 'nullable|string|regex:/^\d+\.\d+\.\d+$/',
            'metadata' => 'nullable|array',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'content' => $this->content,
            'createdBy' => $this->createdBy,
            'changeDescription' => $this->changeDescription,
            'isAiGenerated' => $this->isAiGenerated,
            'aiProvider' => $this->aiProvider,
            'version' => $this->version,
            'metadata' => $this->metadata,
        ];
    }

    protected function handle(): CommandResult
    {
        $artifact = Artifact::findOrFail($this->artifactId);

        // Obtenir la dernière version pour incrémenter
        $latestVersion = $artifact->versions()->first();
        $newVersionNumber = $this->version ?? $this->incrementVersion($latestVersion?->version ?? '0.0.0');

        // Créer la nouvelle version
        $version = ArtifactVersion::create([
            'artifact_id' => $artifact->id,
            'content' => $this->content,
            'version' => $newVersionNumber,
            'change_description' => $this->changeDescription ?? 'Nouvelle version',
            'is_ai_generated' => $this->isAiGenerated,
            'ai_provider_used' => $this->aiProvider ? AiProvider::from($this->aiProvider) : null,
            'metadata' => $this->metadata,
            'created_by' => $this->createdBy,
        ]);

        // Créer l'enregistrement de changement
        ArtifactChange::create([
            'artifact_id' => $artifact->id,
            'from_version_id' => $latestVersion?->id,
            'to_version_id' => $version->id,
            'change_type' => $this->isAiGenerated ? ChangeType::AI_GENERATION : ChangeType::MANUAL_EDIT,
            'description' => $this->changeDescription ?? 'Création de version',
            'details' => [
                'version_number' => $newVersionNumber,
                'is_ai_generated' => $this->isAiGenerated,
            ],
            'created_by' => $this->createdBy,
        ]);

        // Mettre à jour le contenu de l'artefact
        $artifact->update([
            'content' => $this->content,
            'updated_by' => $this->createdBy,
        ]);

        return CommandResult::success(
            data: [
                'version' => $version,
                'artifact' => $artifact->fresh(),
            ],
            message: 'Version créée avec succès',
            metadata: [
                'version_number' => $newVersionNumber,
                'artifact_id' => $artifact->id,
            ]
        );
    }

    private function incrementVersion(string $version): string
    {
        [$major, $minor, $patch] = explode('.', $version);

        return sprintf('%d.%d.%d', $major, $minor, (int) $patch + 1);
    }
}
