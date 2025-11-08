<?php

namespace LaravelArtifacts\Commands\Quality;

use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Results\CommandResult;
use LaravelArtifacts\Services\MergeService\MergeService;

class MergeArtifactCommand extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private string $newContent,
        private string $userId,
        private bool $isAiGenerated = false,
        private ?string $changeDescription = null,
        private MergeService $mergeService = new MergeService(),
    ) {
    }

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'newContent' => 'required|string',
            'userId' => 'required|uuid|exists:users,id',
            'isAiGenerated' => 'boolean',
            'changeDescription' => 'nullable|string|max:500',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'newContent' => $this->newContent,
            'userId' => $this->userId,
            'isAiGenerated' => $this->isAiGenerated,
            'changeDescription' => $this->changeDescription,
        ];
    }

    protected function handle(): CommandResult
    {
        $artifact = Artifact::findOrFail($this->artifactId);

        $mergeResult = $this->mergeService->merge(
            artifact: $artifact,
            newContent: $this->newContent,
            userId: $this->userId,
            isAiGenerated: $this->isAiGenerated,
            changeDescription: $this->changeDescription
        );

        if (! $mergeResult->success) {
            return CommandResult::failed(
                errors: [
                    'merge' => $mergeResult->message,
                    'conflicts' => $mergeResult->conflicts,
                ],
                message: 'La fusion a échoué en raison de conflits'
            );
        }

        return CommandResult::success(
            data: [
                'artifact' => $artifact->fresh(['versions', 'changes']),
                'version' => $mergeResult->version,
                'merged_content' => $mergeResult->mergedContent,
                'conflicts' => $mergeResult->conflicts,
                'conflict_count' => $mergeResult->getConflictCount(),
            ],
            message: $mergeResult->message,
            metadata: [
                'has_conflicts' => $mergeResult->hasConflicts(),
                'auto_resolved' => $mergeResult->success && $mergeResult->hasConflicts(),
            ]
        );
    }
}
