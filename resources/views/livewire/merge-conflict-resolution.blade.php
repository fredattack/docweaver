<div>
    <!-- Merge Form -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Fusionner un Nouveau Contenu</h3>

        <div class="space-y-4">
            <!-- New Content -->
            <div>
                <label for="newContent" class="block text-sm font-medium text-gray-700 mb-2">
                    Nouveau Contenu (Markdown)
                </label>
                <textarea
                    wire:model="newContent"
                    id="newContent"
                    rows="10"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                    placeholder="Collez le nouveau contenu à fusionner..."></textarea>
            </div>

            <!-- Change Description -->
            <div>
                <label for="changeDescription" class="block text-sm font-medium text-gray-700 mb-2">
                    Description des modifications
                </label>
                <input
                    wire:model="changeDescription"
                    type="text"
                    id="changeDescription"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Décrivez brièvement les modifications...">
            </div>

            <!-- Merge Button -->
            <div>
                <button
                    wire:click="performMerge"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                    <span wire:loading.remove wire:target="performMerge">Fusionner</span>
                    <span wire:loading wire:target="performMerge">Fusion en cours...</span>
                </button>
            </div>

            <!-- Error Message -->
            @if($errorMessage)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ $errorMessage }}</span>
                </div>
            @endif

            <!-- Success/Warning Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Conflicts Resolution -->
    @if(count($conflicts) > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h4 class="text-lg font-semibold text-red-900">
                        {{ count($conflicts) }} Conflit(s) Détecté(s)
                    </h4>
                </div>
                <p class="mt-1 text-sm text-red-700">
                    Veuillez résoudre les conflits ci-dessous pour finaliser la fusion.
                </p>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($conflicts as $index => $conflict)
                    <div class="p-6 {{ isset($conflict['resolved']) && $conflict['resolved'] ? 'bg-green-50' : '' }}">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h5 class="text-sm font-semibold text-gray-900">
                                    Conflit #{{ $index + 1 }}
                                    @if(isset($conflict['line']))
                                        <span class="text-gray-500 font-normal">- Ligne {{ $conflict['line'] }}</span>
                                    @endif
                                </h5>
                                @if(isset($conflict['type']))
                                    <x-artifacts::badge :type="$conflict['type']">
                                        {{ ucfirst($conflict['type']) }}
                                    </x-artifacts::badge>
                                @endif
                            </div>

                            @if(isset($conflict['resolved']) && $conflict['resolved'])
                                <x-artifacts::badge type="success">
                                    Résolu ({{ $conflict['resolution'] }})
                                </x-artifacts::badge>
                            @endif
                        </div>

                        <!-- Conflict Content -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <!-- Original -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Version Originale</label>
                                <div class="bg-red-50 border border-red-200 rounded p-3">
                                    <pre class="text-xs whitespace-pre-wrap text-red-900">{{ $conflict['original'] ?? 'N/A' }}</pre>
                                </div>
                            </div>

                            <!-- New -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-2">Nouvelle Version</label>
                                <div class="bg-green-50 border border-green-200 rounded p-3">
                                    <pre class="text-xs whitespace-pre-wrap text-green-900">{{ $conflict['new'] ?? 'N/A' }}</pre>
                                </div>
                            </div>
                        </div>

                        <!-- Resolution Buttons -->
                        @if(!isset($conflict['resolved']) || !$conflict['resolved'])
                            <div class="flex space-x-2">
                                <button
                                    wire:click="acceptOriginal({{ $index }})"
                                    class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded hover:bg-red-200">
                                    Accepter Original
                                </button>
                                <button
                                    wire:click="acceptNew({{ $index }})"
                                    class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded hover:bg-green-200">
                                    Accepter Nouveau
                                </button>
                                <button
                                    wire:click="acceptBoth({{ $index }})"
                                    class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                                    Accepter Les Deux
                                </button>
                            </div>
                        @endif

                        <!-- Similarity Score -->
                        @if(isset($conflict['similarity']))
                            <div class="mt-3 text-xs text-gray-500">
                                Similarité: {{ number_format($conflict['similarity'] * 100, 1) }}%
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Global Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        {{ collect($conflicts)->where('resolved', true)->count() }} sur {{ count($conflicts) }} conflit(s) résolu(s)
                    </div>
                    <button
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
                        :disabled="{{ collect($conflicts)->where('resolved', true)->count() !== count($conflicts) }}">
                        Finaliser la Fusion
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Merge Result Preview -->
    @if($mergeResult && isset($mergeResult['merged_content']))
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Contenu Fusionné</h4>
            <div class="prose max-w-none">
                {!! \Illuminate\Support\Str::markdown($mergeResult['merged_content']) !!}
            </div>
        </div>
    @endif

    <!-- Loading overlay -->
    <div wire:loading wire:target="performMerge" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-lg font-medium text-gray-900">Fusion en cours...</span>
        </div>
    </div>
</div>
