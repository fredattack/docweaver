<?php

namespace LaravelArtifacts\Services\ValidationService\Rules;

use LaravelArtifacts\Enums\ValidationSeverity;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Services\ValidationService\Contracts\ValidationRule;
use LaravelArtifacts\Services\ValidationService\ValidationResult;

class CompletenessRule implements ValidationRule
{
    private array $requiredSections;
    private int $minLength;
    private float $minCompletenessScore;

    public function __construct(?array $config = null)
    {
        $this->requiredSections = $config['required_sections'] ?? config('artifacts.quality_gates.required_sections', []);
        $this->minLength = $config['min_length'] ?? 100;
        $this->minCompletenessScore = $config['min_completeness_score'] ?? config('artifacts.quality_gates.min_completeness_score', 70);
    }

    public function validate(Artifact $artifact): ValidationResult
    {
        $issues = [];
        $score = 0;
        $totalChecks = 3; // Nombre de vérifications

        // 1. Vérifier la longueur minimale
        $contentLength = strlen($artifact->content ?? '');
        if ($contentLength >= $this->minLength) {
            $score++;
        } else {
            $issues[] = "Contenu trop court ({$contentLength} caractères, minimum {$this->minLength})";
        }

        // 2. Vérifier les sections requises
        $missingSections = [];
        $content = strtolower($artifact->content ?? '');

        foreach ($this->requiredSections as $section) {
            $patterns = [
                "# {$section}",
                "## {$section}",
                "### {$section}",
            ];

            $found = false;
            foreach ($patterns as $pattern) {
                if (str_contains($content, strtolower($pattern))) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $missingSections[] = $section;
            }
        }

        if (empty($missingSections)) {
            $score++;
        } else {
            $issues[] = 'Sections manquantes: '.implode(', ', $missingSections);
        }

        // 3. Vérifier le score de complétude
        if ($artifact->completeness_score >= $this->minCompletenessScore) {
            $score++;
        } else {
            $issues[] = "Score de complétude insuffisant ({$artifact->completeness_score}%, minimum {$this->minCompletenessScore}%)";
        }

        // Calculer le score final
        $finalScore = ($score / $totalChecks) * 100;

        if (empty($issues)) {
            return ValidationResult::pass(
                message: 'L\'artefact est complet',
                details: [
                    'score' => $finalScore,
                    'content_length' => $contentLength,
                    'completeness_score' => $artifact->completeness_score,
                ]
            );
        }

        $severity = $finalScore < 50 ? ValidationSeverity::ERROR : ValidationSeverity::WARNING;

        return ValidationResult::fail(
            severity: $severity,
            message: 'L\'artefact n\'est pas complet',
            details: [
                'score' => $finalScore,
                'issues' => $issues,
                'content_length' => $contentLength,
            ],
            suggestedAction: 'Ajoutez les sections manquantes et développez le contenu'
        );
    }

    public function getName(): string
    {
        return 'CompletenessRule';
    }

    public function getDescription(): string
    {
        return 'Vérifie que l\'artefact contient toutes les sections requises et a un contenu suffisant';
    }

    public function getDefaultSeverity(): string
    {
        return ValidationSeverity::WARNING->value;
    }
}
