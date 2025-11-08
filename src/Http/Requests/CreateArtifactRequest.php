<?php

namespace LaravelArtifacts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LaravelArtifacts\Enums\ArtifactType;

class CreateArtifactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adapter selon votre logique d'autorisation
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:'.implode(',', array_column(ArtifactType::cases(), 'value')),
            'slug' => 'nullable|string|max:255|unique:artifacts,slug',
            'metadata' => 'nullable|array',
            'created_by' => 'sometimes|required|uuid|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est requis',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères',
            'content.required' => 'Le contenu est requis',
            'type.required' => 'Le type est requis',
            'type.in' => 'Le type doit être: '.implode(', ', array_column(ArtifactType::cases(), 'value')),
            'slug.unique' => 'Ce slug est déjà utilisé',
        ];
    }
}
