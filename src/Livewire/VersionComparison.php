<?php

namespace LaravelArtifacts\Livewire;

use Livewire\Component;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Commands\Versions\CompareVersionsCommand;

class VersionComparison extends Component
{
    public string $artifactId;
    public ?string $version1Id = null;
    public ?string $version2Id = null;
    public array $versions = [];
    public ?array $comparisonResult = null;
    public bool $isComparing = false;
    public ?string $errorMessage = null;

    public function mount(string $artifactId, ?string $version1Id = null, ?string $version2Id = null)
    {
        $this->artifactId = $artifactId;
        $this->version1Id = $version1Id;
        $this->version2Id = $version2Id;
        $this->loadVersions();

        // Auto-compare si les deux versions sont fournies
        if ($this->version1Id && $this->version2Id) {
            $this->compareVersions();
        }
    }

    public function loadVersions()
    {
        $artifact = Artifact::with('versions')->find($this->artifactId);

        if ($artifact) {
            $this->versions = $artifact->versions->map(function ($version) {
                return [
                    'id' => $version->id,
                    'version' => $version->version,
                    'created_at' => $version->created_at->format('d/m/Y H:i'),
                    'change_description' => $version->change_description,
                ];
            })->toArray();
        }
    }

    public function compareVersions()
    {
        if (! $this->version1Id || ! $this->version2Id) {
            $this->errorMessage = 'Veuillez sélectionner deux versions à comparer';

            return;
        }

        if ($this->version1Id === $this->version2Id) {
            $this->errorMessage = 'Veuillez sélectionner deux versions différentes';

            return;
        }

        $this->isComparing = true;
        $this->errorMessage = null;

        try {
            $command = new CompareVersionsCommand(
                artifactId: $this->artifactId,
                version1Id: (int) $this->version1Id,
                version2Id: (int) $this->version2Id
            );

            $result = $command->execute();

            if ($result->success) {
                $this->comparisonResult = $result->data;
            } else {
                $this->errorMessage = $result->message;
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isComparing = false;
        }
    }

    public function render()
    {
        return view('artifacts::livewire.version-comparison');
    }
}
