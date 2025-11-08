<x-artifacts::layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Éditer: {{ $artifact->title }}
        </h2>
    </x-slot>

    <x-artifacts::card>
        <form action="{{ route('artifacts.update', $artifact->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Titre <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $artifact->title) }}"
                       required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea name="description"
                          id="description"
                          rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description', $artifact->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">
                    Type <span class="text-red-500">*</span>
                </label>
                <select name="type"
                        id="type"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('type') border-red-500 @enderror">
                    <option value="documentation" {{ old('type', $artifact->type->value) === 'documentation' ? 'selected' : '' }}>Documentation</option>
                    <option value="tutorial" {{ old('type', $artifact->type->value) === 'tutorial' ? 'selected' : '' }}>Tutoriel</option>
                    <option value="guide" {{ old('type', $artifact->type->value) === 'guide' ? 'selected' : '' }}>Guide</option>
                    <option value="reference" {{ old('type', $artifact->type->value) === 'reference' ? 'selected' : '' }}>Référence</option>
                    <option value="faq" {{ old('type', $artifact->type->value) === 'faq' ? 'selected' : '' }}>FAQ</option>
                    <option value="changelog" {{ old('type', $artifact->type->value) === 'changelog' ? 'selected' : '' }}>Changelog</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">
                    Contenu (Markdown) <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <textarea name="content"
                              id="content"
                              rows="15"
                              required
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono @error('content') border-red-500 @enderror">{{ old('content', $artifact->content) }}</textarea>
                </div>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    Utilisez le format Markdown pour formater votre contenu.
                </p>
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-sm font-medium text-gray-700">
                    Tags
                </label>
                <input type="text"
                       name="tags"
                       id="tags"
                       value="{{ old('tags', is_array($artifact->tags) ? implode(', ', $artifact->tags) : '') }}"
                       placeholder="laravel, documentation, api"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('tags') border-red-500 @enderror">
                @error('tags')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    Séparez les tags par des virgules.
                </p>
            </div>

            <!-- Metadata -->
            <div>
                <label for="metadata" class="block text-sm font-medium text-gray-700">
                    Métadonnées (JSON)
                </label>
                <textarea name="metadata"
                          id="metadata"
                          rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono @error('metadata') border-red-500 @enderror">{{ old('metadata', is_array($artifact->metadata) ? json_encode($artifact->metadata, JSON_PRETTY_PRINT) : '') }}</textarea>
                @error('metadata')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    Format JSON optionnel pour des métadonnées supplémentaires.
                </p>
            </div>

            <!-- Change Description -->
            <div>
                <label for="change_description" class="block text-sm font-medium text-gray-700">
                    Description des modifications
                </label>
                <textarea name="change_description"
                          id="change_description"
                          rows="2"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('change_description') border-red-500 @enderror"
                          placeholder="Décrivez brièvement les modifications apportées...">{{ old('change_description') }}</textarea>
                @error('change_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    Cette description sera ajoutée à l'historique des versions.
                </p>
            </div>

            <!-- Version Type -->
            <div>
                <label for="version_type" class="block text-sm font-medium text-gray-700">
                    Type de version
                </label>
                <select name="version_type"
                        id="version_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="patch" {{ old('version_type') === 'patch' ? 'selected' : '' }}>Patch ({{ $artifact->current_version }} → {{ $nextVersions['patch'] ?? '' }})</option>
                    <option value="minor" {{ old('version_type') === 'minor' ? 'selected' : '' }}>Minor ({{ $artifact->current_version }} → {{ $nextVersions['minor'] ?? '' }})</option>
                    <option value="major" {{ old('version_type') === 'major' ? 'selected' : '' }}>Major ({{ $artifact->current_version }} → {{ $nextVersions['major'] ?? '' }})</option>
                </select>
                <p class="mt-2 text-sm text-gray-500">
                    Version actuelle: <strong>{{ $artifact->current_version }}</strong>
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <x-artifacts::button href="{{ route('artifacts.show', $artifact->id) }}" variant="outline">
                    Annuler
                </x-artifacts::button>
                <x-artifacts::button type="submit" variant="primary">
                    Enregistrer les modifications
                </x-artifacts::button>
            </div>
        </form>
    </x-artifacts::card>
</x-artifacts::layouts.app>
