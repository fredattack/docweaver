<?php

namespace LaravelArtifacts\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

class MarkdownEditor extends Component
{
    public string $content = '';
    public bool $showPreview = true;
    public bool $splitView = true;
    public string $artifactId = '';

    public function mount(string $initialContent = '', string $artifactId = '')
    {
        $this->content = $initialContent;
        $this->artifactId = $artifactId;
    }

    public function togglePreview()
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function toggleSplitView()
    {
        $this->splitView = ! $this->splitView;
        if ($this->splitView) {
            $this->showPreview = true;
        }
    }

    public function getRenderedContentProperty()
    {
        return Str::markdown($this->content);
    }

    public function render()
    {
        return view('artifacts::livewire.markdown-editor');
    }
}
