<x-artifacts::layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Créer un Artifact
        </h2>
    </x-slot>

    <x-artifacts::card>
        <form action="{{ route('artifacts.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Titre <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title') }}"
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
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
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
                    <option value="">Sélectionner un type</option>
                    <option value="documentation" {{ old('type') === 'documentation' ? 'selected' : '' }}>Documentation</option>
                    <option value="tutorial" {{ old('type') === 'tutorial' ? 'selected' : '' }}>Tutoriel</option>
                    <option value="guide" {{ old('type') === 'guide' ? 'selected' : '' }}>Guide</option>
                    <option value="reference" {{ old('type') === 'reference' ? 'selected' : '' }}>Référence</option>
                    <option value="faq" {{ old('type') === 'faq' ? 'selected' : '' }}>FAQ</option>
                    <option value="changelog" {{ old('type') === 'changelog' ? 'selected' : '' }}>Changelog</option>
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
                              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono @error('content') border-red-500 @enderror"
                              placeholder="# Titre de votre document&#x0A;&#x0A;Votre contenu en Markdown...">{{ old('content') }}</textarea>
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
                       value="{{ old('tags') }}"
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
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono @error('metadata') border-red-500 @enderror"
                          placeholder='{"author": "John Doe", "version": "1.0"}'>{{ old('metadata') }}</textarea>
                @error('metadata')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">
                    Format JSON optionnel pour des métadonnées supplémentaires.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <x-artifacts::button href="{{ route('artifacts.index') }}" variant="outline">
                    Annuler
                </x-artifacts::button>
                <x-artifacts::button type="submit" variant="primary">
                    Créer l'Artifact
                </x-artifacts::button>
            </div>
        </form>
    </x-artifacts::card>
</x-artifacts::layouts.app>
