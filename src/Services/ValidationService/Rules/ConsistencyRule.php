<?php

namespace LaravelArtifacts\Services\ValidationService\Rules;

use LaravelArtifacts\Enums\ValidationSeverity;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Services\ValidationService\Contracts\ValidationRule;
use LaravelArtifacts\Services\ValidationService\ValidationResult;

class ConsistencyRule implements ValidationRule
{
    public function validate(Artifact $artifact): ValidationResult
    {
        $issues = [];
        $warnings = [];

        $content = $artifact->content ?? '';
        $title = $artifact->title ?? '';

        // 1. Vérifier la cohérence titre/contenu
        if (! empty($title) && ! empty($content)) {
            $titleWords = array_filter(explode(' ', strtolower($title)));
            $contentLower = strtolower($content);

            $titleWordsInContent = 0;
            foreach ($titleWords as $word) {
                if (strlen($word) > 3 && str_contains($contentLower, $word)) {
                    $titleWordsInContent++;
                }
            }

            $titleMatchRatio = count($titleWords) > 0
                ? ($titleWordsInContent / count($titleWords)) * 100
                : 0;

            if ($titleMatchRatio < 30) {
                $warnings[] = "Le titre et le contenu semblent peu cohérents (ratio: {$titleMatchRatio}%)";
            }
        }

        // 2. Vérifier les headers Markdown
        preg_match_all('/^(#{1,6})\s+(.+)$/m', $content, $headers);
        if (! empty($headers[0])) {
            $headerLevels = array_map(fn ($h) => strlen($h), $headers[1]);

            // Vérifier la hiérarchie des headers
            $previousLevel = 0;
            foreach ($headerLevels as $index => $level) {
                if ($previousLevel > 0 && $level > $previousLevel + 1) {
                    $warnings[] = "Hiérarchie de headers incohérente à la ligne avec '{$headers[2][$index]}'";
                }
                $previousLevel = $level;
            }
        }

        // 3. Vérifier les listes
        preg_match_all('/^[\*\-\+]\s+/m', $content, $lists);
        if (! empty($lists[0])) {
            // Les listes sont présentes, vérifier qu'elles sont cohérentes
            $listCount = count($lists[0]);
            if ($listCount < 2) {
                $warnings[] = 'Liste unique trouvée, envisagez de regrouper ou développer';
            }
        }

        // 4. Vérifier les métadonnées vs contenu
        if (! empty($artifact->metadata)) {
            if (isset($artifact->metadata['tags'])) {
                $tags = $artifact->metadata['tags'];
                foreach ($tags as $tag) {
                    if (! str_contains($contentLower, strtolower($tag))) {
                        $warnings[] = "Le tag '{$tag}' n'apparaît pas dans le contenu";
                    }
                }
            }
        }

        // Résultat
        if (empty($issues) && empty($warnings)) {
            return ValidationResult::pass(
                message: 'L\'artefact est cohérent',
                details: ['checks_passed' => 4]
            );
        }

        if (empty($issues)) {
            return ValidationResult::warning(
                message: 'L\'artefact présente quelques incohérences mineures',
                details: ['warnings' => $warnings],
                suggestedAction: 'Vérifiez les avertissements et corrigez si nécessaire'
            );
        }

        return ValidationResult::error(
            message: 'L\'artefact présente des incohérences majeures',
            details: ['issues' => $issues, 'warnings' => $warnings],
            suggestedAction: 'Corrigez les problèmes de cohérence avant publication'
        );
    }

    public function getName(): string
    {
        return 'ConsistencyRule';
    }

    public function getDescription(): string
    {
        return 'Vérifie la cohérence interne du contenu (titre, headers, métadonnées)';
    }

    public function getDefaultSeverity(): string
    {
        return ValidationSeverity::WARNING->value;
    }
}
