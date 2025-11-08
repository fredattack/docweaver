<x-artifacts::layouts.app>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Validation - {{ $artifact->title }}
            </h2>
            <x-artifacts::button href="{{ route('artifacts.show', $artifact->id) }}" variant="outline" size="sm">
                Retour à l'artifact
            </x-artifacts::button>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Artifact Info -->
        <x-artifacts::card title="Informations de l'Artifact">
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Titre</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $artifact->title }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Version</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $artifact->current_version }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Score de Qualité</dt>
                    <dd class="mt-1">
                        <div class="flex items-center">
                            <span class="text-sm font-medium text-gray-900 mr-2">{{ number_format($artifact->quality_score ?? 0, 1) }}%</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-xs">
                                <div class="h-2 rounded-full {{ $artifact->quality_score >= 80 ? 'bg-green-600' : ($artifact->quality_score >= 60 ? 'bg-yellow-600' : 'bg-red-600') }}" style="width: {{ $artifact->quality_score ?? 0 }}%"></div>
                            </div>
                        </div>
                    </dd>
                </div>
            </dl>
        </x-artifacts::card>

        <!-- Validation Results Component -->
        <x-artifacts::card>
            <livewire:validation-results :artifact-id="$artifact->id" />
        </x-artifacts::card>

        <!-- Quality Recommendations -->
        <x-artifacts::card title="Recommandations de Qualité">
            <div class="prose max-w-none text-sm">
                <h4>Pour améliorer la qualité de votre artifact:</h4>
                <ul>
                    <li>Assurez-vous que toutes les sections requises sont présentes</li>
                    <li>Vérifiez que les liens internes et externes sont valides</li>
                    <li>Incluez des exemples de code clairs et bien formatés</li>
                    <li>Maintenez la cohérence entre le titre et le contenu</li>
                    <li>Utilisez une hiérarchie de titres correcte (H1, H2, H3...)</li>
                </ul>
            </div>
        </x-artifacts::card>
    </div>
</x-artifacts::layouts.app>
