<div class="bg-white rounded-lg shadow">
    <!-- Toolbar -->
    <div class="border-b border-gray-200 px-4 py-3 flex items-center justify-between bg-gray-50 rounded-t-lg">
        <div class="flex items-center space-x-4">
            <h3 class="text-sm font-medium text-gray-900">Éditeur Markdown</h3>

            <!-- Markdown Helpers -->
            <div class="flex items-center space-x-2">
                <button type="button"
                        class="px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 rounded"
                        onclick="insertMarkdown('**', '**', 'texte en gras')"
                        title="Gras">
                    <strong>B</strong>
                </button>
                <button type="button"
                        class="px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 rounded italic"
                        onclick="insertMarkdown('*', '*', 'texte en italique')"
                        title="Italique">
                    I
                </button>
                <button type="button"
                        class="px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 rounded"
                        onclick="insertMarkdown('[', '](url)', 'lien')"
                        title="Lien">
                    🔗
                </button>
                <button type="button"
                        class="px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 rounded"
                        onclick="insertMarkdown('```\n', '\n```', 'code')"
                        title="Bloc de code">
                    &lt;/&gt;
                </button>
                <button type="button"
                        class="px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 rounded"
                        onclick="insertMarkdown('\n## ', '', 'Titre de section')"
                        title="Titre">
                    H
                </button>
            </div>
        </div>

        <!-- View Toggles -->
        <div class="flex items-center space-x-2">
            <button type="button"
                    wire:click="toggleSplitView"
                    class="px-3 py-1 text-xs font-medium rounded {{ $splitView ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-200' }}">
                Split
            </button>
            <button type="button"
                    wire:click="togglePreview"
                    class="px-3 py-1 text-xs font-medium rounded {{ $showPreview ? 'bg-indigo-100 text-indigo-700' : 'text-gray-700 hover:bg-gray-200' }}">
                Prévisualisation
            </button>
        </div>
    </div>

    <!-- Editor Area -->
    <div class="grid {{ $splitView ? 'grid-cols-2' : 'grid-cols-1' }} divide-x divide-gray-200">
        <!-- Editor -->
        <div class="p-4">
            <textarea
                wire:model.live.debounce.500ms="content"
                id="markdown-content"
                rows="20"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                placeholder="# Titre de votre document&#x0A;&#x0A;Votre contenu en Markdown..."></textarea>

            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                <span>{{ strlen($content) }} caractères</span>
                <a href="https://www.markdownguide.org/basic-syntax/" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                    Guide Markdown
                </a>
            </div>
        </div>

        <!-- Preview -->
        @if($showPreview)
            <div class="p-4 bg-gray-50 overflow-y-auto" style="max-height: 600px;">
                <div class="prose max-w-none">
                    @if($content)
                        {!! $this->renderedContent !!}
                    @else
                        <p class="text-gray-400 italic">La prévisualisation apparaîtra ici...</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function insertMarkdown(before, after, placeholder) {
    const textarea = document.getElementById('markdown-content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    const text = selectedText || placeholder;

    const newText = before + text + after;
    const newValue = textarea.value.substring(0, start) + newText + textarea.value.substring(end);

    textarea.value = newValue;
    textarea.focus();

    // Déclenchement de l'événement pour Livewire
    textarea.dispatchEvent(new Event('input', { bubbles: true }));

    // Sélectionner le texte inséré
    const newStart = start + before.length;
    const newEnd = newStart + text.length;
    textarea.setSelectionRange(newStart, newEnd);
}
</script>
@endpush
