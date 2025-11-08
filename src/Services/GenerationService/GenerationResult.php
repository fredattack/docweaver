<?php

namespace LaravelArtifacts\Services\GenerationService;

use LaravelArtifacts\Enums\AiProvider;

class GenerationResult
{
    public function __construct(
        public bool $success,
        public ?string $content = null,
        public ?AiProvider $provider = null,
        public ?string $error = null,
        public string $message = '',
        public array $metadata = [],
    ) {
    }

    public static function success(
        string $content,
        AiProvider $provider,
        string $message = 'Génération réussie',
        array $metadata = []
    ): self {
        return new self(
            success: true,
            content: $content,
            provider: $provider,
            message: $message,
            metadata: $metadata
        );
    }

    public static function failed(
        string $error,
        ?AiProvider $provider = null,
        string $message = 'Échec de la génération'
    ): self {
        return new self(
            success: false,
            error: $error,
            provider: $provider,
            message: $message
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'content' => $this->content,
            'provider' => $this->provider?->value,
            'error' => $this->error,
            'message' => $this->message,
            'metadata' => $this->metadata,
        ];
    }
}
