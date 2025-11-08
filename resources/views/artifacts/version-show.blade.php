<x-artifacts::layouts.app>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Version {{ $version->version }} - {{ $artifact->title }}
            </h2>
            <div class="flex space-x-2">
                <x-artifacts::button href="{{ route('artifacts.versions', $artifact->id) }}" variant="outline" size="sm">
                    Toutes les versions
                </x-artifacts::button>
                <x-artifacts::button href="{{ route('artifacts.show', $artifact->id) }}" variant="outline" size="sm">
                    Retour à l'artifact
                </x-artifacts::button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Version Info -->
        <x-artifacts::card title="Informations de Version">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Numéro de version</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $version->version }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Date de création</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $version->created_at->format('d/m/Y à H:i') }}</dd>
                </div>
                @if($version->change_description)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Description des modifications</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $version->change_description }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Générée par l'IA</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $version->is_ai_generated ? 'Oui' : 'Non' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Hash du contenu</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $version->content_hash ? substr($version->content_hash, 0, 16).'...' : 'N/A' }}</dd>
                </div>
                @if($version->metadata)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Métadonnées</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <pre class="bg-gray-50 p-3 rounded text-xs overflow-auto">{{ json_encode($version->metadata, JSON_PRETTY_PRINT) }}</pre>
                        </dd>
                    </div>
                @endif
            </dl>

            @if($version->version !== $artifact->current_version)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <form action="{{ route('artifacts.version.restore', [$artifact->id, $version->id]) }}" method="POST" class="inline">
                        @csrf
                        <x-artifacts::button type="submit" variant="primary" onclick="return confirm('Êtes-vous sûr de vouloir restaurer cette version ?')">
                            Restaurer cette version
                        </x-artifacts::button>
                    </form>
                </div>
            @else
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <x-artifacts::badge type="info" class="text-base px-4 py-2">
                        Ceci est la version actuelle
                    </x-artifacts::badge>
                </div>
            @endif
        </x-artifacts::card>

        <!-- Content -->
        <x-artifacts::card title="Contenu">
            <div class="prose max-w-none">
                @if($version->content)
                    {!! \Illuminate\Support\Str::markdown($version->content) !!}
                @else
                    <p class="text-gray-500 italic">Aucun contenu disponible</p>
                @endif
            </div>
        </x-artifacts::card>

        <!-- Raw Content (Optional) -->
        <x-artifacts::card title="Contenu Brut (Markdown)">
            <div class="bg-gray-50 p-4 rounded overflow-auto">
                <pre class="text-xs font-mono whitespace-pre-wrap">{{ $version->content }}</pre>
            </div>
        </x-artifacts::card>
    </div>
</x-artifacts::layouts.app>
