<?php

namespace LaravelArtifacts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Enums\ArtifactType;

class UpdateArtifactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adapter selon votre logique d'autorisation
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'type' => 'sometimes|in:'.implode(',', array_column(ArtifactType::cases(), 'value')),
            'status' => 'sometimes|in:'.implode(',', array_column(ArtifactStatus::cases(), 'value')),
            'metadata' => 'sometimes|array',
            'updated_by' => 'sometimes|required|uuid|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères',
            'type.in' => 'Le type doit être: '.implode(', ', array_column(ArtifactType::cases(), 'value')),
            'status.in' => 'Le statut doit être: '.implode(', ', array_column(ArtifactStatus::cases(), 'value')),
        ];
    }
}
