<x-artifacts::layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Artifacts -->
            <x-artifacts::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Artifacts</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </x-artifacts::card>

            <!-- Published -->
            <x-artifacts::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Publiés</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['published'] ?? 0 }}</p>
                    </div>
                </div>
            </x-artifacts::card>

            <!-- Draft -->
            <x-artifacts::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Brouillons</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['draft'] ?? 0 }}</p>
                    </div>
                </div>
            </x-artifacts::card>

            <!-- Quality Score -->
            <x-artifacts::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Qualité Moyenne</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['avg_quality'] ?? 0, 1) }}%</p>
                    </div>
                </div>
            </x-artifacts::card>
        </div>

        <!-- Recent Artifacts -->
        <x-artifacts::card title="Artifacts Récents">
            <x-slot name="actions">
                <x-artifacts::button href="{{ route('artifacts.create') }}" variant="primary" size="sm">
                    Créer un Artifact
                </x-artifacts::button>
            </x-slot>

            @if($recentArtifacts && $recentArtifacts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Titre
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Version
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Qualité
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Modifié
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentArtifacts as $artifact)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $artifact->title }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            {{ $artifact->type->label() }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-artifacts::badge :type="$artifact->status->value">
                                            {{ $artifact->status->label() }}
                                        </x-artifacts::badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $artifact->current_version }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm text-gray-900">
                                                {{ number_format($artifact->quality_score ?? 0, 1) }}%
                                            </div>
                                            <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $artifact->quality_score ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $artifact->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('artifacts.show', $artifact->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Voir
                                        </a>
                                        <a href="{{ route('artifacts.edit', $artifact->id) }}" class="text-gray-600 hover:text-gray-900">
                                            Éditer
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun artifact</h3>
                    <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier artifact.</p>
                    <div class="mt-6">
                        <x-artifacts::button href="{{ route('artifacts.create') }}" variant="primary">
                            Créer un Artifact
                        </x-artifacts::button>
                    </div>
                </div>
            @endif
        </x-artifacts::card>

        <!-- Activity Timeline (placeholder for future) -->
        <x-artifacts::card title="Activité Récente">
            <div class="text-center py-8 text-gray-500">
                <p>Aucune activité récente</p>
            </div>
        </x-artifacts::card>
    </div>
</x-artifacts::layouts.app>
