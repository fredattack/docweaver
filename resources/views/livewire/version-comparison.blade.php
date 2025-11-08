<div>
    <!-- Version Selectors -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Comparer les Versions</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Version 1 -->
            <div>
                <label for="version1" class="block text-sm font-medium text-gray-700 mb-2">
                    Version 1
                </label>
                <select
                    wire:model.live="version1Id"
                    id="version1"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Sélectionner une version</option>
                    @foreach($versions as $version)
                        <option value="{{ $version['id'] }}">
                            {{ $version['version'] }} - {{ $version['created_at'] }}
                            @if($version['change_description'])
                                ({{ Str::limit($version['change_description'], 30) }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Version 2 -->
            <div>
                <label for="version2" class="block text-sm font-medium text-gray-700 mb-2">
                    Version 2
                </label>
                <select
                    wire:model.live="version2Id"
                    id="version2"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Sélectionner une version</option>
                    @foreach($versions as $version)
                        <option value="{{ $version['id'] }}">
                            {{ $version['version'] }} - {{ $version['created_at'] }}
                            @if($version['change_description'])
                                ({{ Str::limit($version['change_description'], 30) }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4">
            <button
                wire:click="compareVersions"
                wire:loading.attr="disabled"
                :disabled="!version1Id || !version2Id"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                <span wire:loading.remove wire:target="compareVersions">Comparer</span>
                <span wire:loading wire:target="compareVersions">Comparaison...</span>
            </button>
        </div>

        <!-- Message d'erreur -->
        @if($errorMessage)
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $errorMessage }}</span>
            </div>
        @endif
    </div>

    <!-- Comparison Results -->
    @if($comparisonResult)
        <div class="space-y-6">
            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Statistiques</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $comparisonResult['statistics']['total_lines'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Lignes totales</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $comparisonResult['statistics']['added_lines'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Ajoutées</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $comparisonResult['statistics']['removed_lines'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Supprimées</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $comparisonResult['statistics']['changed_lines'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Modifiées</p>
                    </div>
                </div>
            </div>

            <!-- Diff View -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900">Différences</h4>
                </div>
                <div class="p-6">
                    @if(isset($comparisonResult['diff']) && count($comparisonResult['diff']) > 0)
                        <div class="font-mono text-sm space-y-0 border border-gray-300 rounded overflow-hidden">
                            @foreach($comparisonResult['diff'] as $line)
                                @php
                                    $bgClass = 'bg-white';
                                    $textClass = 'text-gray-900';
                                    $symbol = ' ';

                                    if (isset($line['type'])) {
                                        switch($line['type']) {
                                            case 'added':
                                                $bgClass = 'bg-green-50';
                                                $textClass = 'text-green-900';
                                                $symbol = '+';
                                                break;
                                            case 'removed':
                                                $bgClass = 'bg-red-50';
                                                $textClass = 'text-red-900';
                                                $symbol = '-';
                                                break;
                                            case 'changed':
                                                $bgClass = 'bg-blue-50';
                                                $textClass = 'text-blue-900';
                                                $symbol = '~';
                                                break;
                                        }
                                    }
                                @endphp

                                <div class="{{ $bgClass }} {{ $textClass }} px-4 py-1 border-b border-gray-200 last:border-b-0">
                                    <span class="inline-block w-8 text-gray-400 select-none">{{ $line['line'] ?? '' }}</span>
                                    <span class="inline-block w-4 font-bold">{{ $symbol }}</span>
                                    <span>{{ $line['content'] ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">Aucune différence détectée</p>
                    @endif
                </div>
            </div>

            <!-- Side-by-side view (optional) -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900">Vue Côte à Côte</h4>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Version 1 -->
                        <div>
                            <h5 class="text-sm font-medium text-gray-700 mb-2">
                                Version {{ $comparisonResult['version1']['version'] ?? '' }}
                            </h5>
                            <div class="border border-gray-300 rounded p-4 bg-gray-50 overflow-auto" style="max-height: 400px;">
                                <pre class="text-xs whitespace-pre-wrap">{{ $comparisonResult['version1']['content'] ?? '' }}</pre>
                            </div>
                        </div>

                        <!-- Version 2 -->
                        <div>
                            <h5 class="text-sm font-medium text-gray-700 mb-2">
                                Version {{ $comparisonResult['version2']['version'] ?? '' }}
                            </h5>
                            <div class="border border-gray-300 rounded p-4 bg-gray-50 overflow-auto" style="max-height: 400px;">
                                <pre class="text-xs whitespace-pre-wrap">{{ $comparisonResult['version2']['content'] ?? '' }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Loading overlay -->
    <div wire:loading wire:target="compareVersions" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-lg font-medium text-gray-900">Comparaison en cours...</span>
        </div>
    </div>
</div>
