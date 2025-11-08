<?php

namespace LaravelArtifacts\Livewire;

use Livewire\Component;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Commands\Quality\ValidateArtifactCommand;

class ValidationResults extends Component
{
    public string $artifactId;
    public array $validationResults = [];
    public bool $isValidating = false;
    public ?string $errorMessage = null;

    public function mount(string $artifactId)
    {
        $this->artifactId = $artifactId;
        $this->loadValidationResults();
    }

    public function loadValidationResults()
    {
        $artifact = Artifact::with('validations')->find($this->artifactId);

        if ($artifact && $artifact->validations) {
            $this->validationResults = $artifact->validations->map(function ($validation) {
                return [
                    'rule_name' => $validation->rule_name,
                    'passed' => $validation->passed,
                    'message' => $validation->message,
                    'severity' => $validation->severity?->value,
                    'details' => $validation->details,
                    'created_at' => $validation->created_at->diffForHumans(),
                ];
            })->toArray();
        }
    }

    public function runValidation()
    {
        $this->isValidating = true;
        $this->errorMessage = null;

        try {
            $command = new ValidateArtifactCommand(
                artifactId: $this->artifactId
            );

            $result = $command->execute();

            if ($result->success) {
                $this->loadValidationResults();
                session()->flash('success', 'Validation terminée avec succès');
            } else {
                $this->errorMessage = $result->message;
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isValidating = false;
        }
    }

    public function render()
    {
        return view('artifacts::livewire.validation-results');
    }
}
