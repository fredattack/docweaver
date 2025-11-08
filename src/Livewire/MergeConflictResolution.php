<?php

namespace LaravelArtifacts\Livewire;

use Livewire\Component;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Commands\Quality\MergeArtifactCommand;

class MergeConflictResolution extends Component
{
    public string $artifactId;
    public string $newContent = '';
    public array $conflicts = [];
    public ?array $mergeResult = null;
    public bool $isMerging = false;
    public ?string $errorMessage = null;
    public string $changeDescription = '';

    public function mount(string $artifactId, string $newContent = '')
    {
        $this->artifactId = $artifactId;
        $this->newContent = $newContent;
    }

    public function performMerge()
    {
        if (empty($this->newContent)) {
            $this->errorMessage = 'Le nouveau contenu ne peut pas être vide';

            return;
        }

        $this->isMerging = true;
        $this->errorMessage = null;

        try {
            $command = new MergeArtifactCommand(
                artifactId: $this->artifactId,
                newContent: $this->newContent,
                userId: 'f47ac10b-58cc-4372-a567-0e02b2c3d479', // TODO: user réel
                isAiGenerated: false,
                changeDescription: $this->changeDescription
            );

            $result = $command->execute();

            if ($result->success) {
                $this->mergeResult = $result->data;
                $this->conflicts = $result->data['conflicts'] ?? [];

                if (empty($this->conflicts)) {
                    session()->flash('success', 'Fusion réussie sans conflits');
                } else {
                    session()->flash('warning', 'Fusion effectuée avec '.count($this->conflicts).' conflit(s)');
                }
            } else {
                $this->errorMessage = $result->message;
                $this->conflicts = $result->errors['conflicts'] ?? [];
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isMerging = false;
        }
    }

    public function acceptOriginal(int $conflictIndex)
    {
        if (isset($this->conflicts[$conflictIndex])) {
            // Résoudre le conflit en acceptant la version originale
            $this->resolveConflict($conflictIndex, 'original');
        }
    }

    public function acceptNew(int $conflictIndex)
    {
        if (isset($this->conflicts[$conflictIndex])) {
            // Résoudre le conflit en acceptant la nouvelle version
            $this->resolveConflict($conflictIndex, 'new');
        }
    }

    public function acceptBoth(int $conflictIndex)
    {
        if (isset($this->conflicts[$conflictIndex])) {
            // Résoudre le conflit en acceptant les deux versions
            $this->resolveConflict($conflictIndex, 'both');
        }
    }

    private function resolveConflict(int $index, string $resolution)
    {
        // Marquer le conflit comme résolu
        if (isset($this->conflicts[$index])) {
            $this->conflicts[$index]['resolved'] = true;
            $this->conflicts[$index]['resolution'] = $resolution;
        }
    }

    public function render()
    {
        return view('artifacts::livewire.merge-conflict-resolution');
    }
}
