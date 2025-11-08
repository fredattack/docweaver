<?php

namespace LaravelArtifacts\Services\ValidationService\Rules;

use LaravelArtifacts\Enums\ValidationSeverity;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Services\ValidationService\Contracts\ValidationRule;
use LaravelArtifacts\Services\ValidationService\ValidationResult;

class CodeExamplesRule implements ValidationRule
{
    private int $minExamples;
    private bool $requireLanguageTag;

    public function __construct(?array $config = null)
    {
        $this->minExamples = $config['min_examples'] ?? 1;
        $this->requireLanguageTag = $config['require_language_tag'] ?? true;
    }

    public function validate(Artifact $artifact): ValidationResult
    {
        $content = $artifact->content ?? '';
        $issues = [];
        $warnings = [];

        // Extraire les blocs de code (```...```)
        preg_match_all('/```([a-z]*)\n(.*?)\n```/s', $content, $codeBlocks);

        $totalBlocks = count($codeBlocks[0]);
        $blocksWithLanguage = 0;
        $blocksWithoutLanguage = 0;
        $emptyBlocks = 0;
        $languages = [];

        foreach ($codeBlocks[1] as $index => $language) {
            $code = trim($codeBlocks[2][$index]);

            // Vérifier si le bloc est vide
            if (empty($code)) {
                $emptyBlocks++;
                $warnings[] = "Bloc de code vide trouvé";
                continue;
            }

            // Vérifier si le langage est spécifié
            if (! empty($language)) {
                $blocksWithLanguage++;
                $languages[$language] = ($languages[$language] ?? 0) + 1;
            } else {
                $blocksWithoutLanguage++;
                if ($this->requireLanguageTag) {
                    $warnings[] = "Bloc de code sans langage spécifié";
                }
            }

            // Vérifier la longueur minimale du code
            if (strlen($code) < 10) {
                $warnings[] = "Bloc de code très court (moins de 10 caractères)";
            }
        }

        // Extraire les code inline (`code`)
        preg_match_all('/`([^`]+)`/', $content, $inlineCode);
        $inlineCodeCount = count($inlineCode[0]);

        // Calculer les statistiques
        $totalExamples = $totalBlocks + $inlineCodeCount;

        $details = [
            'total_code_blocks' => $totalBlocks,
            'blocks_with_language' => $blocksWithLanguage,
            'blocks_without_language' => $blocksWithoutLanguage,
            'empty_blocks' => $emptyBlocks,
            'inline_code_count' => $inlineCodeCount,
            'total_examples' => $totalExamples,
            'languages' => $languages,
        ];

        // Vérifier le nombre minimum d'exemples
        if ($totalBlocks < $this->minExamples) {
            $issues[] = "Nombre insuffisant d'exemples de code ({$totalBlocks}, minimum {$this->minExamples})";
        }

        // Vérifier les blocs vides
        if ($emptyBlocks > 0) {
            $issues[] = "{$emptyBlocks} bloc(s) de code vide(s) détecté(s)";
        }

        // Résultat
        if (empty($issues) && empty($warnings)) {
            return ValidationResult::pass(
                message: "Les exemples de code sont de bonne qualité ({$totalBlocks} blocs, {$inlineCodeCount} inline)",
                details: $details
            );
        }

        if (empty($issues)) {
            return ValidationResult::warning(
                message: "Les exemples de code peuvent être améliorés",
                details: array_merge($details, ['warnings' => $warnings]),
                suggestedAction: 'Ajoutez les tags de langage et vérifiez les blocs vides'
            );
        }

        $severity = $totalBlocks === 0 ? ValidationSeverity::ERROR : ValidationSeverity::WARNING;

        return ValidationResult::fail(
            severity: $severity,
            message: 'Les exemples de code présentent des problèmes',
            details: array_merge($details, ['issues' => $issues, 'warnings' => $warnings]),
            suggestedAction: 'Ajoutez des exemples de code et corrigez les problèmes détectés'
        );
    }

    public function getName(): string
    {
        return 'CodeExamplesRule';
    }

    public function getDescription(): string
    {
        return 'Vérifie la qualité et la présence d\'exemples de code';
    }

    public function getDefaultSeverity(): string
    {
        return ValidationSeverity::WARNING->value;
    }
}
