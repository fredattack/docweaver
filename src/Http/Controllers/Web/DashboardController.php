<?php

namespace LaravelArtifacts\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelArtifacts\Models\Artifact;

class DashboardController extends Controller
{
    /**
     * Afficher le dashboard principal.
     */
    public function index()
    {
        $stats = [
            'total' => Artifact::count(),
            'published' => Artifact::where('status', 'published')->count(),
            'draft' => Artifact::where('status', 'draft')->count(),
            'avg_quality' => Artifact::avg('quality_score') ?? 0,
        ];

        $recentArtifacts = Artifact::with(['versions'])
            ->latest()
            ->limit(10)
            ->get();

        return view('artifacts::dashboard', compact('stats', 'recentArtifacts'));
    }
}
