<?php

namespace LaravelArtifacts\Services\MergeService;

use LaravelArtifacts\Enums\ChangeType;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactChange;
use LaravelArtifacts\Models\ArtifactVersion;

class MergeService
{
    private float $semanticThreshold;
    private bool $preserveManualEdits;

    public function __construct()
    {
        $this->semanticThreshold = config('artifacts.merge.semantic_threshold', 0.85);
        $this->preserveManualEdits = config('artifacts.merge.preserve_manual_edits', true);
    }

    /**
     * Fusionner du nouveau contenu avec un artefact existant.
     */
    public function merge(
        Artifact $artifact,
        string $newContent,
        string $userId,
        bool $isAiGenerated = false,
        ?string $changeDescription = null
    ): MergeResult {
        $originalContent = $artifact->content ?? '';
        $latestVersion = $artifact->latestVersion();

        // Détecter les conflits
        $conflicts = $this->detectConflicts($originalContent, $newContent);

        if (! empty($conflicts) && $this->preserveManualEdits) {
            return MergeResult::conflicted(
                conflicts: $conflicts,
                message: 'Des conflits ont été détectés entre le contenu original et le nouveau contenu'
            );
        }

        // Effectuer la fusion
        $mergedContent = $this->performMerge($originalContent, $newContent, $conflicts);

        // Créer une nouvelle version
        $version = $this->createVersion($artifact, $mergedContent, $userId, $isAiGenerated, $changeDescription);

        // Créer l'enregistrement de changement
        $this->recordChange($artifact, $latestVersion, $version, $conflicts, $userId);

        // Mettre à jour l'artefact
        $artifact->update([
            'content' => $mergedContent,
            'updated_by' => $userId,
        ]);

        return MergeResult::success(
            mergedContent: $mergedContent,
            version: $version,
            conflicts: $conflicts,
            message: empty($conflicts) ? 'Fusion réussie sans conflit' : 'Fusion réussie avec résolution automatique'
        );
    }

    /**
     * Détecter les conflits entre deux contenus.
     */
    public function detectConflicts(string $original, string $new): array
    {
        $originalLines = explode("\n", $original);
        $newLines = explode("\n", $new);

        $conflicts = [];
        $maxLines = max(count($originalLines), count($newLines));

        for ($i = 0; $i < $maxLines; $i++) {
            $originalLine = $originalLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';

            // Ignorer les lignes identiques
            if ($originalLine === $newLine) {
                continue;
            }

            // Ignorer les lignes vides des deux côtés
            if (trim($originalLine) === '' && trim($newLine) === '') {
                continue;
            }

            // Calculer la similarité sémantique
            $similarity = $this->calculateSimilarity($originalLine, $newLine);

            // Si la similarité est faible, c'est un conflit potentiel
            if ($similarity < $this->semanticThreshold) {
                $conflicts[] = [
                    'line' => $i + 1,
                    'original' => $originalLine,
                    'new' => $newLine,
                    'similarity' => round($similarity, 3),
                    'type' => $this->determineConflictType($originalLine, $newLine),
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Effectuer la fusion en résolvant les conflits.
     */
    private function performMerge(string $original, string $new, array $conflicts): string
    {
        if (empty($conflicts)) {
            // Fusion simple - privilégier le nouveau contenu
            return $this->mergeSimple($original, $new);
        }

        // Fusion intelligente avec résolution de conflits
        return $this->mergeIntelligent($original, $new, $conflicts);
    }

    /**
     * Fusion simple sans conflits.
     */
    private function mergeSimple(string $original, string $new): string
    {
        // Stratégie: combiner les sections uniques des deux contenus

        $originalSections = $this->extractSections($original);
        $newSections = $this->extractSections($new);

        $mergedSections = [];

        // Garder toutes les sections du nouveau contenu
        foreach ($newSections as $title => $content) {
            $mergedSections[$title] = $content;
        }

        // Ajouter les sections de l'original qui ne sont pas dans le nouveau
        foreach ($originalSections as $title => $content) {
            if (! isset($mergedSections[$title])) {
                $mergedSections[$title] = $content;
            }
        }

        // Reconstruire le contenu
        return $this->rebuildContent($mergedSections);
    }

    /**
     * Fusion intelligente avec résolution de conflits.
     */
    private function mergeIntelligent(string $original, string $new, array $conflicts): string
    {
        $originalLines = explode("\n", $original);
        $newLines = explode("\n", $new);
        $mergedLines = [];

        $conflictedLines = array_column($conflicts, 'line');

        for ($i = 0; $i < max(count($originalLines), count($newLines)); $i++) {
            $lineNumber = $i + 1;
            $originalLine = $originalLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';

            if (in_array($lineNumber, $conflictedLines)) {
                // Ligne en conflit - utiliser stratégie de résolution
                $mergedLines[] = $this->resolveConflict($originalLine, $newLine);
            } else {
                // Pas de conflit - privilégier le nouveau contenu
                $mergedLines[] = $newLine !== '' ? $newLine : $originalLine;
            }
        }

        return implode("\n", $mergedLines);
    }

    /**
     * Résoudre un conflit entre deux lignes.
     */
    private function resolveConflict(string $originalLine, string $newLine): string
    {
        // Si une ligne est vide, prendre l'autre
        if (trim($originalLine) === '') {
            return $newLine;
        }
        if (trim($newLine) === '') {
            return $originalLine;
        }

        // Si c'est un header, privilégier le plus détaillé
        if (str_starts_with($originalLine, '#') || str_starts_with($newLine, '#')) {
            return strlen($newLine) > strlen($originalLine) ? $newLine : $originalLine;
        }

        // Par défaut, privilégier le nouveau contenu
        return $newLine;
    }

    /**
     * Extraire les sections d'un contenu Markdown.
     */
    private function extractSections(string $content): array
    {
        $sections = [];
        $currentSection = 'intro';
        $currentContent = [];

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                // Sauvegarder la section précédente
                if (! empty($currentContent)) {
                    $sections[$currentSection] = implode("\n", $currentContent);
                }

                // Nouvelle section
                $currentSection = trim($matches[2]);
                $currentContent = [$line];
            } else {
                $currentContent[] = $line;
            }
        }

        // Sauvegarder la dernière section
        if (! empty($currentContent)) {
            $sections[$currentSection] = implode("\n", $currentContent);
        }

        return $sections;
    }

    /**
     * Reconstruire le contenu à partir des sections.
     */
    private function rebuildContent(array $sections): string
    {
        return implode("\n\n", array_values($sections));
    }

    /**
     * Calculer la similarité entre deux chaînes.
     */
    private function calculateSimilarity(string $str1, string $str2): float
    {
        if ($str1 === $str2) {
            return 1.0;
        }

        if (trim($str1) === '' || trim($str2) === '') {
            return 0.0;
        }

        // Utiliser la distance de Levenshtein normalisée
        $maxLength = max(strlen($str1), strlen($str2));
        $distance = levenshtein(substr($str1, 0, 255), substr($str2, 0, 255));

        return 1 - ($distance / $maxLength);
    }

    /**
     * Déterminer le type de conflit.
     */
    private function determineConflictType(string $original, string $new): string
    {
        if (trim($original) === '') {
            return 'addition';
        }
        if (trim($new) === '') {
            return 'deletion';
        }

        return 'modification';
    }

    /**
     * Créer une nouvelle version après fusion.
     */
    private function createVersion(
        Artifact $artifact,
        string $content,
        string $userId,
        bool $isAiGenerated,
        ?string $changeDescription
    ): ArtifactVersion {
        $latestVersion = $artifact->latestVersion();
        $newVersionNumber = $this->incrementVersion($latestVersion?->version ?? '0.0.0');

        return ArtifactVersion::create([
            'artifact_id' => $artifact->id,
            'content' => $content,
            'version' => $newVersionNumber,
            'change_description' => $changeDescription ?? 'Fusion de contenu',
            'is_ai_generated' => $isAiGenerated,
            'created_by' => $userId,
            'metadata' => [
                'merge_timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Enregistrer le changement de fusion.
     */
    private function recordChange(
        Artifact $artifact,
        ?ArtifactVersion $fromVersion,
        ArtifactVersion $toVersion,
        array $conflicts,
        string $userId
    ): void {
        ArtifactChange::create([
            'artifact_id' => $artifact->id,
            'from_version_id' => $fromVersion?->id,
            'to_version_id' => $toVersion->id,
            'change_type' => ChangeType::MERGE,
            'description' => 'Fusion de contenu',
            'details' => [
                'conflicts_count' => count($conflicts),
                'conflicts' => $conflicts,
                'merge_strategy' => empty($conflicts) ? 'simple' : 'intelligent',
            ],
            'created_by' => $userId,
        ]);
    }

    /**
     * Incrémenter un numéro de version.
     */
    private function incrementVersion(string $version): string
    {
        [$major, $minor, $patch] = explode('.', $version);

        return sprintf('%d.%d.%d', $major, $minor, (int) $patch + 1);
    }
}
