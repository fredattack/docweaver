<?php

namespace LaravelArtifacts\Commands\Versions;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Results\CommandResult;

class CompareVersionsCommand extends BaseCommand
{
    public function __construct(
        private int $fromVersionId,
        private int $toVersionId,
    ) {
    }

    protected function rules(): array
    {
        return [
            'fromVersionId' => 'required|integer|exists:artifact_versions,id',
            'toVersionId' => 'required|integer|exists:artifact_versions,id|different:fromVersionId',
        ];
    }

    protected function toArray(): array
    {
        return [
            'fromVersionId' => $this->fromVersionId,
            'toVersionId' => $this->toVersionId,
        ];
    }

    protected function handle(): CommandResult
    {
        $fromVersion = ArtifactVersion::findOrFail($this->fromVersionId);
        $toVersion = ArtifactVersion::findOrFail($this->toVersionId);

        // Vérifier que les versions appartiennent au même artefact
        if ($fromVersion->artifact_id !== $toVersion->artifact_id) {
            return CommandResult::failed(
                errors: ['versions' => ['Les versions doivent appartenir au même artefact']],
                message: 'Comparaison impossible'
            );
        }

        // Calculer les différences
        $diff = $this->calculateDiff($fromVersion->content, $toVersion->content);

        return CommandResult::success(
            data: [
                'from_version' => [
                    'id' => $fromVersion->id,
                    'version' => $fromVersion->version,
                    'created_at' => $fromVersion->created_at,
                ],
                'to_version' => [
                    'id' => $toVersion->id,
                    'version' => $toVersion->version,
                    'created_at' => $toVersion->created_at,
                ],
                'diff' => $diff,
                'statistics' => [
                    'lines_added' => $diff['lines_added'],
                    'lines_removed' => $diff['lines_removed'],
                    'lines_changed' => $diff['lines_changed'],
                    'total_changes' => $diff['lines_added'] + $diff['lines_removed'],
                ],
            ],
            message: 'Comparaison effectuée avec succès'
        );
    }

    private function calculateDiff(string $from, string $to): array
    {
        $fromLines = explode("\n", $from);
        $toLines = explode("\n", $to);

        $linesAdded = 0;
        $linesRemoved = 0;
        $linesChanged = 0;
        $changes = [];

        // Algorithme de diff simple (pour une vraie implémentation, utiliser une bibliothèque)
        $maxLines = max(count($fromLines), count($toLines));

        for ($i = 0; $i < $maxLines; $i++) {
            $fromLine = $fromLines[$i] ?? null;
            $toLine = $toLines[$i] ?? null;

            if ($fromLine === null && $toLine !== null) {
                $linesAdded++;
                $changes[] = [
                    'type' => 'added',
                    'line' => $i + 1,
                    'content' => $toLine,
                ];
            } elseif ($fromLine !== null && $toLine === null) {
                $linesRemoved++;
                $changes[] = [
                    'type' => 'removed',
                    'line' => $i + 1,
                    'content' => $fromLine,
                ];
            } elseif ($fromLine !== $toLine) {
                $linesChanged++;
                $changes[] = [
                    'type' => 'modified',
                    'line' => $i + 1,
                    'from' => $fromLine,
                    'to' => $toLine,
                ];
            }
        }

        return [
            'lines_added' => $linesAdded,
            'lines_removed' => $linesRemoved,
            'lines_changed' => $linesChanged,
            'changes' => $changes,
        ];
    }
}
