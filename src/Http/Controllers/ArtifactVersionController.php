<?php

namespace LaravelArtifacts\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelArtifacts\Commands\Versions\CompareVersionsCommand;
use LaravelArtifacts\Commands\Versions\CreateVersionCommand;
use LaravelArtifacts\Commands\Versions\RestoreVersionCommand;
use LaravelArtifacts\Http\Resources\ArtifactVersionResource;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Services\VersioningService\VersioningService;

class ArtifactVersionController extends Controller
{
    public function __construct(
        private VersioningService $versioningService
    ) {
    }

    /**
     * Lister toutes les versions d'un artefact.
     */
    public function index(Artifact $artifact): JsonResponse
    {
        $versions = $artifact->versions()->paginate(20);

        return response()->json([
            'data' => ArtifactVersionResource::collection($versions->items()),
            'meta' => [
                'current_page' => $versions->currentPage(),
                'total' => $versions->total(),
                'per_page' => $versions->perPage(),
                'statistics' => $this->versioningService->getVersionStatistics($artifact->id),
            ],
        ]);
    }

    /**
     * Créer une nouvelle version.
     */
    public function store(Request $request, Artifact $artifact): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'change_description' => 'nullable|string|max:500',
            'is_ai_generated' => 'boolean',
            'ai_provider' => 'nullable|in:openai,claude,gemini,local',
            'version' => 'nullable|string|regex:/^\d+\.\d+\.\d+$/',
        ]);

        $command = new CreateVersionCommand(
            artifactId: $artifact->id,
            content: $request->input('content'),
            createdBy: auth()->id() ?? $request->input('created_by'),
            changeDescription: $request->input('change_description'),
            isAiGenerated: $request->boolean('is_ai_generated', false),
            aiProvider: $request->input('ai_provider'),
            version: $request->input('version'),
            metadata: $request->input('metadata'),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => new ArtifactVersionResource($result->data['version']),
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }

    /**
     * Afficher une version spécifique.
     */
    public function show(Artifact $artifact, ArtifactVersion $version): JsonResponse
    {
        // Vérifier que la version appartient à l'artefact
        if ($version->artifact_id !== $artifact->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cette version n\'appartient pas à cet artefact',
            ], 404);
        }

        return response()->json([
            'data' => new ArtifactVersionResource($version),
        ]);
    }

    /**
     * Restaurer une version.
     */
    public function restore(Request $request, Artifact $artifact, ArtifactVersion $version): JsonResponse
    {
        if ($version->artifact_id !== $artifact->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cette version n\'appartient pas à cet artefact',
            ], 404);
        }

        $command = new RestoreVersionCommand(
            versionId: $version->id,
            restoredBy: auth()->id() ?? $request->input('restored_by'),
            createNewVersion: $request->boolean('create_new_version', true),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => $result->data,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }

    /**
     * Comparer deux versions.
     */
    public function compare(Request $request, Artifact $artifact): JsonResponse
    {
        $request->validate([
            'from_version_id' => 'required|integer|exists:artifact_versions,id',
            'to_version_id' => 'required|integer|exists:artifact_versions,id|different:from_version_id',
        ]);

        $command = new CompareVersionsCommand(
            fromVersionId: $request->input('from_version_id'),
            toVersionId: $request->input('to_version_id'),
        );

        $result = $command->execute();

        if ($result->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $result->message,
                'data' => $result->data,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result->message,
            'errors' => $result->errors,
        ], 422);
    }
}
