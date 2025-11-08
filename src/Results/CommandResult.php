<?php

namespace LaravelArtifacts\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class CommandResult implements Arrayable, Jsonable
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public array $errors = [],
        public string $message = '',
        public array $metadata = []
    ) {
    }

    public static function success(
        mixed $data = null,
        string $message = '',
        array $metadata = []
    ): self {
        return new self(
            success: true,
            data: $data,
            message: $message,
            metadata: $metadata
        );
    }

    public static function failed(
        array $errors = [],
        string $message = 'Operation failed',
        array $metadata = []
    ): self {
        return new self(
            success: false,
            errors: $errors,
            message: $message,
            metadata: $metadata
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailed(): bool
    {
        return ! $this->success;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'errors' => $this->errors,
            'message' => $this->message,
            'meta' => $this->metadata,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
