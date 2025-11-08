<x-artifacts::layouts.app>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $artifact->title }}
            </h2>
            <div class="flex space-x-2">
                @if($artifact->status->value === 'draft')
                    <form action="{{ route('artifacts.publish', $artifact->id) }}" method="POST" class="inline">
                        @csrf
                        <x-artifacts::button type="submit" variant="success" size="sm">
                            Publier
                        </x-artifacts::button>
                    </form>
                @endif
                <x-artifacts::button href="{{ route('artifacts.edit', $artifact->id) }}" variant="primary" size="sm">
                    Éditer
                </x-artifacts::button>
                <form action="{{ route('artifacts.destroy', $artifact->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet artifact ?')">
                    @csrf
                    @method('DELETE')
                    <x-artifacts::button type="submit" variant="danger" size="sm">
                        Supprimer
                    </x-artifacts::button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Metadata Card -->
        <x-artifacts::card title="Informations">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $artifact->type->label() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Statut</dt>
                    <dd class="mt-1">
                        <x-artifacts::badge :type="$artifact->status->value">
                            {{ $artifact->status->label() }}
                        </x-artifacts::badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Version actuelle</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $artifact->current_version }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Score de qualité</dt>
                    <dd class="mt-1">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-900 mr-2">{{ number_format($artifact->quality_score ?? 0, 1) }}%</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $artifact->quality_score >= 80 ? 'bg-green-600' : ($artifact->quality_score >= 60 ? 'bg-yellow-600' : 'bg-red-600') }}" style="width: {{ $artifact->quality_score ?? 0 }}%"></div>
                            </div>
                        </div>
                    </dd>
                </div>
                @if($artifact->description)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $artifact->description }}</dd>
                    </div>
                @endif
                @if($artifact->tags && count($artifact->tags) > 0)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Tags</dt>
                        <dd class="mt-1 flex flex-wrap gap-2">
                            @foreach($artifact->tags as $tag)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">Créé le</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $artifact->created_at->format('d/m/Y à H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Modifié le</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $artifact->updated_at->format('d/m/Y à H:i') }}</dd>
                </div>
            </dl>
        </x-artifacts::card>

        <!-- Content Preview -->
        <x-artifacts::card title="Contenu">
            <x-slot name="actions">
                <x-artifacts::button href="{{ route('artifacts.versions', $artifact->id) }}" variant="outline" size="sm">
                    Voir les versions
                </x-artifacts::button>
                <x-artifacts::button href="{{ route('artifacts.validate', $artifact->id) }}" variant="outline" size="sm">
                    Valider
                </x-artifacts::button>
            </x-slot>

            <div class="prose max-w-none">
                @if($artifact->content)
                    {!! \Illuminate\Support\Str::markdown($artifact->content) !!}
                @else
                    <p class="text-gray-500 italic">Aucun contenu disponible</p>
                @endif
            </div>
        </x-artifacts::card>

        <!-- Versions -->
        @if($artifact->versions && $artifact->versions->count() > 0)
            <x-artifacts::card title="Versions récentes">
                <x-slot name="actions">
                    <x-artifacts::button href="{{ route('artifacts.versions', $artifact->id) }}" variant="outline" size="sm">
                        Voir toutes les versions
                    </x-artifacts::button>
                </x-slot>

                <div class="space-y-4">
                    @foreach($artifact->versions->take(5) as $version)
                        <div class="flex items-start space-x-4 pb-4 border-b border-gray-200 last:border-b-0">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-indigo-800">{{ $version->version }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        Version {{ $version->version }}
                                        @if($version->version === $artifact->current_version)
                                            <x-artifacts::badge type="info">Actuelle</x-artifacts::badge>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $version->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @if($version->change_description)
                                    <p class="mt-1 text-sm text-gray-500">{{ $version->change_description }}</p>
                                @endif
                                <div class="mt-2 flex space-x-3">
                                    <a href="{{ route('artifacts.version.show', [$artifact->id, $version->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                        Voir
                                    </a>
                                    @if($version->version !== $artifact->current_version)
                                        <form action="{{ route('artifacts.version.restore', [$artifact->id, $version->id]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                                Restaurer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-artifacts::card>
        @endif

        <!-- Validation Results -->
        @if($artifact->validations && $artifact->validations->count() > 0)
            <x-artifacts::card title="Résultats de validation">
                <div class="space-y-3">
                    @foreach($artifact->validations->take(5) as $validation)
                        <div class="flex items-start space-x-3 p-3 rounded-lg {{ $validation->passed ? 'bg-green-50' : 'bg-red-50' }}">
                            <div class="flex-shrink-0">
                                @if($validation->passed)
                                    <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium {{ $validation->passed ? 'text-green-800' : 'text-red-800' }}">
                                    {{ $validation->rule_name }}
                                </p>
                                <p class="text-sm {{ $validation->passed ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $validation->message }}
                                </p>
                                @if($validation->severity)
                                    <x-artifacts::badge :type="$validation->severity->value" class="mt-1">
                                        {{ strtoupper($validation->severity->value) }}
                                    </x-artifacts::badge>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-artifacts::card>
        @endif
    </div>
</x-artifacts::layouts.app>
