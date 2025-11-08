<?php

namespace LaravelArtifacts\Services\ValidationService;

use Illuminate\Support\Collection;
use LaravelArtifacts\Enums\ValidationSeverity;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactQualityGate;
use LaravelArtifacts\Models\ArtifactValidation;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Services\ValidationService\Contracts\ValidationRule;

class ValidationService
{
    /**
     * Valider un artefact avec toutes les règles actives.
     */
    public function validate(Artifact $artifact, ?ArtifactVersion $version = null): Collection
    {
        $rules = $this->getActiveRules($artifact);
        $results = collect();

        foreach ($rules as $ruleClass => $config) {
            $rule = $this->instantiateRule($ruleClass, $config);
            $result = $rule->validate($artifact);

            // Enregistrer le résultat
            $this->recordValidation($artifact, $version, $rule, $result);

            $results->push([
                'rule' => $rule->getName(),
                'result' => $result,
            ]);
        }

        // Mettre à jour le score de qualité de l'artefact
        $this->updateQualityScore($artifact, $results);

        return $results;
    }

    /**
     * Valider une règle spécifique.
     */
    public function validateRule(Artifact $artifact, string $ruleClass, ?array $config = null): ValidationResult
    {
        $rule = $this->instantiateRule($ruleClass, $config);

        return $rule->validate($artifact);
    }

    /**
     * Obtenir les règles actives pour un artefact.
     */
    public function getActiveRules(Artifact $artifact): array
    {
        // Récupérer les portes de qualité configurées pour cet artefact
        $customGates = ArtifactQualityGate::where('artifact_id', $artifact->id)
            ->where('enabled', true)
            ->get()
            ->mapWithKeys(fn ($gate) => [$gate->rule_class => $gate->config ?? []])
            ->toArray();

        // Si des règles personnalisées existent, les utiliser
        if (! empty($customGates)) {
            return $customGates;
        }

        // Sinon, utiliser les règles par défaut de la configuration
        $defaultRules = config('artifacts.quality_gates.rules', []);

        return array_fill_keys($defaultRules, []);
    }

    /**
     * Vérifier si un artefact passe toutes les validations.
     */
    public function passes(Artifact $artifact): bool
    {
        $results = $this->validate($artifact);

        foreach ($results as $result) {
            if (! $result['result']->passed && $result['result']->severity === ValidationSeverity::ERROR) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtenir les validations échouées pour un artefact.
     */
    public function getFailedValidations(Artifact $artifact, ?ArtifactVersion $version = null): Collection
    {
        $query = ArtifactValidation::where('artifact_id', $artifact->id)
            ->where('passed', false);

        if ($version) {
            $query->where('version_id', $version->id);
        }

        return $query->get();
    }

    /**
     * Obtenir les erreurs bloquantes.
     */
    public function getBlockingErrors(Artifact $artifact, ?ArtifactVersion $version = null): Collection
    {
        return $this->getFailedValidations($artifact, $version)
            ->where('severity', ValidationSeverity::ERROR);
    }

    /**
     * Activer une règle pour un artefact.
     */
    public function enableRule(Artifact $artifact, string $ruleClass, ?array $config = null): ArtifactQualityGate
    {
        return ArtifactQualityGate::updateOrCreate(
            [
                'artifact_id' => $artifact->id,
                'rule_class' => $ruleClass,
            ],
            [
                'enabled' => true,
                'config' => $config,
            ]
        );
    }

    /**
     * Désactiver une règle pour un artefact.
     */
    public function disableRule(Artifact $artifact, string $ruleClass): void
    {
        ArtifactQualityGate::where('artifact_id', $artifact->id)
            ->where('rule_class', $ruleClass)
            ->update(['enabled' => false]);
    }

    /**
     * Instancier une règle de validation.
     */
    private function instantiateRule(string $ruleClass, ?array $config = null): ValidationRule
    {
        if (! class_exists($ruleClass)) {
            throw new \InvalidArgumentException("Règle de validation introuvable: {$ruleClass}");
        }

        $rule = new $ruleClass($config);

        if (! $rule instanceof ValidationRule) {
            throw new \InvalidArgumentException("La classe {$ruleClass} doit implémenter ValidationRule");
        }

        return $rule;
    }

    /**
     * Enregistrer le résultat de validation dans la base de données.
     */
    private function recordValidation(
        Artifact $artifact,
        ?ArtifactVersion $version,
        ValidationRule $rule,
        ValidationResult $result
    ): void {
        ArtifactValidation::create([
            'artifact_id' => $artifact->id,
            'version_id' => $version?->id ?? $artifact->latestVersion()?->id,
            'rule_class' => get_class($rule),
            'passed' => $result->passed,
            'severity' => $result->severity->value,
            'message' => $result->message,
            'details' => $result->details,
            'suggested_action' => $result->suggestedAction,
        ]);
    }

    /**
     * Mettre à jour le score de qualité de l'artefact.
     */
    private function updateQualityScore(Artifact $artifact, Collection $results): void
    {
        if ($results->isEmpty()) {
            return;
        }

        $passedCount = $results->where('result.passed', true)->count();
        $totalCount = $results->count();

        $qualityScore = ($passedCount / $totalCount) * 100;

        $artifact->update([
            'quality_score' => round($qualityScore, 2),
        ]);
    }
}
