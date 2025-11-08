<?php

namespace LaravelArtifacts\Services\ValidationService\Contracts;

use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Services\ValidationService\ValidationResult;

interface ValidationRule
{
    /**
     * Valider l'artefact selon cette règle.
     */
    public function validate(Artifact $artifact): ValidationResult;

    /**
     * Obtenir le nom de la règle.
     */
    public function getName(): string;

    /**
     * Obtenir la description de la règle.
     */
    public function getDescription(): string;

    /**
     * Obtenir la sévérité par défaut de la règle.
     */
    public function getDefaultSeverity(): string;
}
