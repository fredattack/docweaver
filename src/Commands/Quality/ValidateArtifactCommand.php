<?php

namespace LaravelArtifacts\Commands\Quality;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Results\CommandResult;
use LaravelArtifacts\Services\ValidationService\ValidationService;

class ValidateArtifactCommand extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private ?int $versionId = null,
        private ?array $specificRules = null,
        private ValidationService $validationService = new ValidationService(),
    ) {
    }

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'versionId' => 'nullable|integer|exists:artifact_versions,id',
            'specificRules' => 'nullable|array',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'versionId' => $this->versionId,
            'specificRules' => $this->specificRules,
        ];
    }

    protected function handle(): CommandResult
    {
        $artifact = Artifact::findOrFail($this->artifactId);
        $version = $this->versionId ? ArtifactVersion::findOrFail($this->versionId) : null;

        // Si des règles spécifiques sont demandées
        if ($this->specificRules) {
            $results = collect();
            foreach ($this->specificRules as $ruleClass) {
                $result = $this->validationService->validateRule($artifact, $ruleClass);
                $results->push([
                    'rule' => $ruleClass,
                    'result' => $result,
                ]);
            }
        } else {
            // Valider avec toutes les règles actives
            $results = $this->validationService->validate($artifact, $version);
        }

        // Analyser les résultats
        $passed = $results->where('result.passed', true);
        $failed = $results->where('result.passed', false);
        $errors = $failed->where('result.severity.value', 'error');
        $warnings = $failed->where('result.severity.value', 'warning');

        $allPassed = $failed->isEmpty();

        return CommandResult::success(
            data: [
                'artifact' => $artifact->fresh(['validations']),
                'validation_summary' => [
                    'total_rules' => $results->count(),
                    'passed' => $passed->count(),
                    'failed' => $failed->count(),
                    'errors' => $errors->count(),
                    'warnings' => $warnings->count(),
                    'quality_score' => $artifact->fresh()->quality_score,
                ],
                'results' => $results->map(fn ($r) => [
                    'rule' => $r['rule'],
                    'passed' => $r['result']->passed,
                    'severity' => $r['result']->severity->value,
                    'message' => $r['result']->message,
                    'details' => $r['result']->details,
                    'suggested_action' => $r['result']->suggestedAction,
                ])->toArray(),
                'blocking_errors' => $errors->map(fn ($r) => [
                    'rule' => $r['rule'],
                    'message' => $r['result']->message,
                    'suggested_action' => $r['result']->suggestedAction,
                ])->values()->toArray(),
            ],
            message: $allPassed
                ? 'Toutes les validations ont réussi'
                : sprintf(
                    '%d validations échouées (%d erreurs, %d avertissements)',
                    $failed->count(),
                    $errors->count(),
                    $warnings->count()
                ),
            metadata: [
                'can_publish' => $errors->isEmpty(),
                'all_passed' => $allPassed,
            ]
        );
    }
}
