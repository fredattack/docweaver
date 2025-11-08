<?php

namespace LaravelArtifacts\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtifactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->when($request->boolean('include_content', false), $this->content),
            'content_preview' => $this->when(
                ! $request->boolean('include_content', false),
                fn () => substr($this->content ?? '', 0, 200).'...'
            ),
            'type' => [
                'value' => $this->type->value,
                'label' => $this->type->label(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'quality_score' => $this->quality_score,
            'completeness_score' => $this->completeness_score,
            'ai_provider_used' => $this->ai_provider_used?->value,
            'metadata' => $this->metadata,

            // Relations
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return $this->updater ? [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ] : null;
            }),
            'versions' => $this->whenLoaded('versions', function () {
                return ArtifactVersionResource::collection($this->versions);
            }),
            'versions_count' => $this->whenCounted('versions'),
            'latest_version' => $this->when(
                $this->relationLoaded('versions'),
                fn () => $this->latestVersion()?->version
            ),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
