<x-artifacts::layouts.app>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Versions - {{ $artifact->title }}
            </h2>
            <x-artifacts::button href="{{ route('artifacts.show', $artifact->id) }}" variant="outline" size="sm">
                Retour à l'artifact
            </x-artifacts::button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Version Comparison Component -->
        <livewire:version-comparison :artifact-id="$artifact->id" />

        <!-- All Versions List -->
        <x-artifacts::card title="Toutes les Versions">
            @if($artifact->versions && $artifact->versions->count() > 0)
                <div class="space-y-4">
                    @foreach($artifact->versions as $version)
                        <div class="flex items-start space-x-4 pb-4 border-b border-gray-200 last:border-b-0">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-800">v{{ $version->version }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        Version {{ $version->version }}
                                        @if($version->version === $artifact->current_version)
                                            <x-artifacts::badge type="info">Actuelle</x-artifacts::badge>
                                        @endif
                                        @if($version->is_ai_generated)
                                            <x-artifacts::badge type="info">IA</x-artifacts::badge>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $version->created_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>
                                @if($version->change_description)
                                    <p class="mt-1 text-sm text-gray-500">{{ $version->change_description }}</p>
                                @endif

                                <div class="mt-2 text-xs text-gray-400">
                                    {{ strlen($version->content) }} caractères
                                    @if($version->content_hash)
                                        - Hash: {{ substr($version->content_hash, 0, 8) }}...
                                    @endif
                                </div>

                                <div class="mt-3 flex space-x-3">
                                    <a href="{{ route('artifacts.version.show', [$artifact->id, $version->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                        Voir le contenu
                                    </a>
                                    @if($version->version !== $artifact->current_version)
                                        <form action="{{ route('artifacts.version.restore', [$artifact->id, $version->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm text-gray-600 hover:text-gray-900" onclick="return confirm('Êtes-vous sûr de vouloir restaurer cette version ?')">
                                                Restaurer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">Aucune version disponible</p>
            @endif
        </x-artifacts::card>
    </div>
</x-artifacts::layouts.app>
