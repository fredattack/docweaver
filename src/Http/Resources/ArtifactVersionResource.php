<?php

namespace LaravelArtifacts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtifactVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'artifact_id' => $this->artifact_id,
            'version' => $this->version,
            'content' => $this->when($request->boolean('include_content', false), $this->content),
            'content_preview' => $this->when(
                ! $request->boolean('include_content', false),
                fn () => substr($this->content ?? '', 0, 200).'...'
            ),
            'content_hash' => $this->content_hash,
            'change_description' => $this->change_description,
            'is_ai_generated' => $this->is_ai_generated,
            'ai_provider_used' => $this->ai_provider_used?->value,
            'metadata' => $this->metadata,

            // Creator
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
