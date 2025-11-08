<?php

namespace LaravelArtifacts\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelArtifacts\Commands\Artifacts\CreateArtifactCommand;
use LaravelArtifacts\Commands\Artifacts\UpdateArtifactCommand;
use LaravelArtifacts\Commands\Artifacts\DeleteArtifactCommand;
use LaravelArtifacts\Commands\Artifacts\PublishArtifactCommand;
use LaravelArtifacts\Commands\Artifacts\ArchiveArtifactCommand;
use LaravelArtifacts\Commands\Versions\CreateVersionCommand;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Services\VersioningService\VersioningService;

class ArtifactWebController extends Controller
{
    public function __construct(
        private VersioningService $versioningService
    ) {
    }

    /**
     * Liste tous les artifacts.
     */
    public function index(Request $request)
    {
        $query = Artifact::query()->with(['versions']);

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%")
                    ->orWhereJsonContains('tags', $request->search);
            });
        }

        $artifacts = $query->latest()->paginate(20);

        return view('artifacts::artifacts.index', compact('artifacts'));
    }

    /**
     * Affiche le formulaire de création.
     */
    public function create()
    {
        return view('artifacts::artifacts.create');
    }

    /**
     * Enregistre un nouvel artifact.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:documentation,tutorial,guide,reference,faq,changelog',
            'content' => 'required|string',
            'tags' => 'nullable|string',
            'metadata' => 'nullable|json',
        ]);

        // Convertir les tags en tableau
        if (! empty($validated['tags'])) {
            $validated['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // Convertir metadata en tableau
        if (! empty($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }

        // TODO: Remplacer par le vrai user_id depuis l'authentification
        $validated['user_id'] = 'f47ac10b-58cc-4372-a567-0e02b2c3d479'; // UUID placeholder

        $command = new CreateArtifactCommand(...$validated);
        $result = $command->execute();

        if ($result->success) {
            return redirect()
                ->route('artifacts.show', $result->data['artifact']->id)
                ->with('success', 'Artifact créé avec succès');
        }

        return back()
            ->withInput()
            ->with('error', $result->message);
    }

    /**
     * Affiche un artifact.
     */
    public function show(string $id)
    {
        $artifact = Artifact::with(['versions', 'validations'])
            ->findOrFail($id);

        return view('artifacts::artifacts.show', compact('artifact'));
    }

    /**
     * Affiche le formulaire d'édition.
     */
    public function edit(string $id)
    {
        $artifact = Artifact::findOrFail($id);

        // Calculer les prochaines versions possibles
        $nextVersions = [
            'patch' => $this->versioningService->incrementVersion($artifact->current_version, 'patch'),
            'minor' => $this->versioningService->incrementVersion($artifact->current_version, 'minor'),
            'major' => $this->versioningService->incrementVersion($artifact->current_version, 'major'),
        ];

        return view('artifacts::artifacts.edit', compact('artifact', 'nextVersions'));
    }

    /**
     * Met à jour un artifact.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:documentation,tutorial,guide,reference,faq,changelog',
            'content' => 'required|string',
            'tags' => 'nullable|string',
            'metadata' => 'nullable|json',
            'change_description' => 'nullable|string|max:500',
            'version_type' => 'nullable|in:patch,minor,major',
        ]);

        $artifact = Artifact::findOrFail($id);

        // Préparer les données pour la mise à jour
        $updateData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'content' => $validated['content'],
        ];

        // Convertir les tags en tableau
        if (! empty($validated['tags'])) {
            $updateData['tags'] = array_map('trim', explode(',', $validated['tags']));
        }

        // Convertir metadata en tableau
        if (! empty($validated['metadata'])) {
            $updateData['metadata'] = json_decode($validated['metadata'], true);
        }

        $command = new UpdateArtifactCommand(
            artifactId: $id,
            data: $updateData
        );

        $result = $command->execute();

        if (! $result->success) {
            return back()
                ->withInput()
                ->with('error', $result->message);
        }

        // Créer une nouvelle version si le contenu a changé
        if ($artifact->content !== $validated['content']) {
            $versionCommand = new CreateVersionCommand(
                artifactId: $id,
                userId: 'f47ac10b-58cc-4372-a567-0e02b2c3d479', // TODO: user réel
                versionType: $validated['version_type'] ?? 'patch',
                changeDescription: $validated['change_description'] ?? 'Mise à jour du contenu',
                isAiGenerated: false
            );

            $versionCommand->execute();
        }

        return redirect()
            ->route('artifacts.show', $id)
            ->with('success', 'Artifact mis à jour avec succès');
    }

    /**
     * Supprime un artifact.
     */
    public function destroy(string $id)
    {
        $command = new DeleteArtifactCommand(
            artifactId: $id,
            userId: 'f47ac10b-58cc-4372-a567-0e02b2c3d479', // TODO: user réel
            hardDelete: false
        );

        $result = $command->execute();

        if ($result->success) {
            return redirect()
                ->route('artifacts.index')
                ->with('success', 'Artifact supprimé avec succès');
        }

        return back()->with('error', $result->message);
    }

    /**
     * Publie un artifact.
     */
    public function publish(string $id)
    {
        $command = new PublishArtifactCommand(
            artifactId: $id,
            userId: 'f47ac10b-58cc-4372-a567-0e02b2c3d479', // TODO: user réel
            skipValidation: false
        );

        $result = $command->execute();

        if ($result->success) {
            return back()->with('success', 'Artifact publié avec succès');
        }

        return back()->with('error', $result->message);
    }

    /**
     * Archive un artifact.
     */
    public function archive(string $id)
    {
        $command = new ArchiveArtifactCommand(
            artifactId: $id,
            userId: 'f47ac10b-58cc-4372-a567-0e02b2c3d479', // TODO: user réel
            reason: 'Archivé via l\'interface web'
        );

        $result = $command->execute();

        if ($result->success) {
            return back()->with('success', 'Artifact archivé avec succès');
        }

        return back()->with('error', $result->message);
    }

    /**
     * Affiche toutes les versions d'un artifact.
     */
    public function versions(string $id)
    {
        $artifact = Artifact::with(['versions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('artifacts::artifacts.versions', compact('artifact'));
    }

    /**
     * Affiche une version spécifique.
     */
    public function showVersion(string $artifactId, string $versionId)
    {
        $artifact = Artifact::findOrFail($artifactId);
        $version = ArtifactVersion::where('artifact_id', $artifactId)
            ->where('id', $versionId)
            ->firstOrFail();

        return view('artifacts::artifacts.version-show', compact('artifact', 'version'));
    }

    /**
     * Restaure une version.
     */
    public function restoreVersion(string $artifactId, string $versionId)
    {
        // TODO: Implémenter la restauration de version
        return back()->with('success', 'Version restaurée avec succès');
    }

    /**
     * Affiche la page de validation.
     */
    public function validate(string $id)
    {
        $artifact = Artifact::with(['validations'])->findOrFail($id);

        return view('artifacts::artifacts.validate', compact('artifact'));
    }
}
