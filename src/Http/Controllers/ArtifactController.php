<?php

namespace LaravelArtifacts\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use LaravelArtifacts\Commands\Artifacts\ArchiveArtifactCommand;
use LaravelArtifacts\Commands\Artifacts\CreateArtifactCommand;
use LaravelArtifacts\Commands\Artifacts\DeleteArtifactCommand;
use LaravelArtifacts\Commands\Artifacts\PublishArtifactCommand;
use LaravelArtifacts\Commands\Artifacts\UpdateArtifactCommand;
use LaravelArtifacts\Http\Requests\CreateArtifactRequest;
use LaravelArtifacts\Http\Requests\UpdateArtifactRequest;
use LaravelArtifacts\Http\Resources\ArtifactResource;
use LaravelArtifacts\Models\Artifact;

class ArtifactController extends Controller
{
    /**
     * Lister tous les artefacts.
     */
    public function index(): JsonResponse
    {
        $artifacts = Artifact::with(['creator', 'versions'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => ArtifactResource::collection($artifacts->items()),
            'meta' => [
                'current_page' => $artifacts->currentPage(),
                'total' => $artifacts->total(),
                'per_page' => $artifacts->perPage(),
                'last_page' => $artifacts->lastPage(),
            ],
        ]);
    }

    /**
     * Créer un nouvel artefact.
     */
    public function store(CreateArtifactRequest $request): JsonResponse
    {
        $command = new CreateArtifactCommand(
            title: $request->input('title'),
            content: $request->input('content'),
            type: $request->input('type'),
            createdBy: auth()->id() ?? $request->input('created_by'),
            slug: $request->input('slug'),
            metadata: $request->input('metadata'),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => new ArtifactResource($result->data['artifact']),
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }

    /**
     * Afficher un artefact spécifique.
     */
    public function show(Artifact $artifact): JsonResponse
    {
        $artifact->load(['versions', 'creator', 'updater', 'changes']);

        return response()->json([
            'data' => new ArtifactResource($artifact),
        ]);
    }

    /**
     * Mettre à jour un artefact.
     */
    public function update(UpdateArtifactRequest $request, Artifact $artifact): JsonResponse
    {
        $command = new UpdateArtifactCommand(
            artifactId: $artifact->id,
            title: $request->input('title'),
            content: $request->input('content'),
            type: $request->input('type'),
            status: $request->input('status'),
            metadata: $request->input('metadata'),
            updatedBy: auth()->id() ?? $request->input('updated_by'),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => new ArtifactResource($result->data),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }

    /**
     * Supprimer un artefact.
     */
    public function destroy(Artifact $artifact): JsonResponse
    {
        $command = new DeleteArtifactCommand(
            artifactId: $artifact->id,
            forceDelete: request()->boolean('force', false),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->message,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }

    /**
     * Publier un artefact.
     */
    public function publish(Artifact $artifact): JsonResponse
    {
        $command = new PublishArtifactCommand(
            artifactId: $artifact->id,
            publishedBy: auth()->id() ?? request()->input('published_by'),
            skipQualityGates: request()->boolean('skip_quality_gates', false),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => new ArtifactResource($result->data),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }

    /**
     * Archiver un artefact.
     */
    public function archive(Artifact $artifact): JsonResponse
    {
        $command = new ArchiveArtifactCommand(
            artifactId: $artifact->id,
            archivedBy: auth()->id() ?? request()->input('archived_by'),
            reason: request()->input('reason'),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => new ArtifactResource($result->data),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }

    /**
     * Dupliquer un artefact.
     */
    public function duplicate(Artifact $artifact): JsonResponse
    {
        $command = new CreateArtifactCommand(
            title: request()->input('title', $artifact->title.' (Copie)'),
            content: $artifact->content,
            type: $artifact->type->value,
            createdBy: auth()->id() ?? request()->input('created_by'),
            metadata: array_merge($artifact->metadata ?? [], [
                'duplicated_from' => $artifact->id,
                'duplicated_at' => now()->toIso8601String(),
            ]),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => 'Artefact dupliqué avec succès',
                'data' => new ArtifactResource($result->data['artifact']),
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }
}
