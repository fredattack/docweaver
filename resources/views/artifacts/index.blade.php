<x-artifacts::layouts.app>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mes Artifacts
            </h2>
            <x-artifacts::button href="{{ route('artifacts.create') }}" variant="primary">
                Créer un Artifact
            </x-artifacts::button>
        </div>
    </x-slot>

    <x-artifacts::card>
        <!-- Filters -->
        <div class="mb-6 flex flex-wrap gap-4">
            <div>
                <label for="filter-type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="filter-type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Tous les types</option>
                    <option value="documentation">Documentation</option>
                    <option value="tutorial">Tutoriel</option>
                    <option value="guide">Guide</option>
                    <option value="reference">Référence</option>
                    <option value="faq">FAQ</option>
                    <option value="changelog">Changelog</option>
                </select>
            </div>

            <div>
                <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select id="filter-status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="draft">Brouillon</option>
                    <option value="review">En révision</option>
                    <option value="published">Publié</option>
                    <option value="archived">Archivé</option>
                </select>
            </div>

            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Rechercher</label>
                <input type="text" id="search" placeholder="Rechercher par titre, tags..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <!-- Artifacts Table -->
        @if($artifacts && $artifacts->count() > 0)
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
                        @foreach($artifacts as $artifact)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $artifact->title }}
                                        </div>
                                        @if($artifact->description)
                                            <div class="text-sm text-gray-500 truncate max-w-md">
                                                {{ Str::limit($artifact->description, 80) }}
                                            </div>
                                        @endif
                                        @if($artifact->tags && count($artifact->tags) > 0)
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach(array_slice($artifact->tags, 0, 3) as $tag)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $tag }}
                                                    </span>
                                                @endforeach
                                                @if(count($artifact->tags) > 3)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-gray-500">
                                                        +{{ count($artifact->tags) - 3 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
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
                                        <div class="text-sm text-gray-900 mr-2">
                                            {{ number_format($artifact->quality_score ?? 0, 1) }}%
                                        </div>
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $artifact->quality_score >= 80 ? 'bg-green-600' : ($artifact->quality_score >= 60 ? 'bg-yellow-600' : 'bg-red-600') }}" style="width: {{ $artifact->quality_score ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $artifact->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('artifacts.show', $artifact->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Voir
                                        </a>
                                        <a href="{{ route('artifacts.edit', $artifact->id) }}" class="text-gray-600 hover:text-gray-900">
                                            Éditer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $artifacts->links() }}
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
</x-artifacts::layouts.app>
