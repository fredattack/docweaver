<div>
    <!-- Header avec bouton de validation -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Résultats de Validation</h3>
        <button
            wire:click="runValidation"
            wire:loading.attr="disabled"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
            <span wire:loading.remove wire:target="runValidation">Lancer la Validation</span>
            <span wire:loading wire:target="runValidation">Validation en cours...</span>
        </button>
    </div>

    <!-- Message d'erreur -->
    @if($errorMessage)
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ $errorMessage }}</span>
        </div>
    @endif

    <!-- Message de succès -->
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Résultats de validation -->
    @if(count($validationResults) > 0)
        <div class="space-y-3">
            @foreach($validationResults as $result)
                <div class="flex items-start space-x-3 p-4 rounded-lg {{ $result['passed'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <!-- Icon -->
                    <div class="flex-shrink-0 mt-0.5">
                        @if($result['passed'])
                            <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium {{ $result['passed'] ? 'text-green-800' : 'text-red-800' }}">
                                {{ $result['rule_name'] }}
                            </p>
                            @if($result['severity'])
                                <x-artifacts::badge :type="$result['severity']">
                                    {{ strtoupper($result['severity']) }}
                                </x-artifacts::badge>
                            @endif
                        </div>

                        <p class="mt-1 text-sm {{ $result['passed'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $result['message'] }}
                        </p>

                        @if($result['details'])
                            <div class="mt-2 text-xs {{ $result['passed'] ? 'text-green-600' : 'text-red-600' }}">
                                @if(is_array($result['details']))
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($result['details'] as $key => $value)
                                            <li>{{ is_numeric($key) ? $value : "$key: $value" }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>{{ $result['details'] }}</p>
                                @endif
                            </div>
                        @endif

                        <p class="mt-2 text-xs text-gray-500">
                            {{ $result['created_at'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Statistiques -->
        <div class="mt-6 grid grid-cols-3 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ count($validationResults) }}</p>
                <p class="text-sm text-gray-500">Total</p>
            </div>
            <div class="bg-white border border-green-200 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ collect($validationResults)->where('passed', true)->count() }}</p>
                <p class="text-sm text-gray-500">Réussies</p>
            </div>
            <div class="bg-white border border-red-200 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-red-600">{{ collect($validationResults)->where('passed', false)->count() }}</p>
                <p class="text-sm text-gray-500">Échouées</p>
            </div>
        </div>
    @else
        <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune validation</h3>
            <p class="mt-1 text-sm text-gray-500">Lancez une validation pour voir les résultats.</p>
            <div class="mt-6">
                <button
                    wire:click="runValidation"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Lancer la Validation
                </button>
            </div>
        </div>
    @endif

    <!-- Loading overlay -->
    <div wire:loading wire:target="runValidation" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-lg font-medium text-gray-900">Validation en cours...</span>
        </div>
    </div>
</div>
