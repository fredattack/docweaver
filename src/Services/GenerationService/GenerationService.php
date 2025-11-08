<?php

namespace LaravelArtifacts\Services\GenerationService;

use LaravelArtifacts\Enums\AiProvider;
use LaravelArtifacts\Models\Artifact;

class GenerationService
{
    private string $defaultProvider;

    public function __construct()
    {
        $this->defaultProvider = config('artifacts.ai.default_provider', 'openai');
    }

    /**
     * Générer du contenu avec l'IA.
     */
    public function generate(
        string $prompt,
        ?string $provider = null,
        ?array $options = null
    ): GenerationResult {
        $provider = $provider ?? $this->defaultProvider;
        $providerEnum = AiProvider::from($provider);

        try {
            $content = match ($providerEnum) {
                AiProvider::OPENAI => $this->generateWithOpenAI($prompt, $options),
                AiProvider::CLAUDE => $this->generateWithClaude($prompt, $options),
                AiProvider::GEMINI => $this->generateWithGemini($prompt, $options),
                AiProvider::LOCAL => $this->generateLocal($prompt, $options),
            };

            return GenerationResult::success(
                content: $content,
                provider: $providerEnum,
                message: 'Contenu généré avec succès'
            );
        } catch (\Exception $e) {
            return GenerationResult::failed(
                error: $e->getMessage(),
                provider: $providerEnum
            );
        }
    }

    /**
     * Améliorer un contenu existant avec l'IA.
     */
    public function improve(
        string $content,
        string $instructions,
        ?string $provider = null
    ): GenerationResult {
        $prompt = $this->buildImprovementPrompt($content, $instructions);

        return $this->generate($prompt, $provider);
    }

    /**
     * Compléter un contenu existant.
     */
    public function complete(
        string $partialContent,
        ?string $context = null,
        ?string $provider = null
    ): GenerationResult {
        $prompt = $this->buildCompletionPrompt($partialContent, $context);

        return $this->generate($prompt, $provider);
    }

    /**
     * Générer des sections manquantes.
     */
    public function generateMissingSections(
        Artifact $artifact,
        array $missingSections,
        ?string $provider = null
    ): GenerationResult {
        $prompt = $this->buildMissingSectionsPrompt($artifact, $missingSections);

        return $this->generate($prompt, $provider);
    }

    /**
     * Générer avec OpenAI.
     */
    private function generateWithOpenAI(string $prompt, ?array $options = null): string
    {
        // Note: Nécessite l'installation de openai-php/client
        // Pour l'instant, retourne un placeholder

        $apiKey = config('artifacts.ai.providers.openai.api_key');

        if (empty($apiKey)) {
            throw new \Exception('Clé API OpenAI non configurée');
        }

        // TODO: Implémenter l'appel réel à l'API OpenAI
        // $client = OpenAI::client($apiKey);
        // $response = $client->chat()->create([...]);

        return "# Contenu généré par OpenAI\n\n[Placeholder - Implémentation OpenAI à venir]\n\nPrompt: {$prompt}";
    }

    /**
     * Générer avec Claude (Anthropic).
     */
    private function generateWithClaude(string $prompt, ?array $options = null): string
    {
        $apiKey = config('artifacts.ai.providers.claude.api_key');

        if (empty($apiKey)) {
            throw new \Exception('Clé API Claude non configurée');
        }

        // TODO: Implémenter l'appel réel à l'API Claude
        return "# Contenu généré par Claude\n\n[Placeholder - Implémentation Claude à venir]\n\nPrompt: {$prompt}";
    }

    /**
     * Générer avec Gemini (Google).
     */
    private function generateWithGemini(string $prompt, ?array $options = null): string
    {
        $apiKey = config('artifacts.ai.providers.gemini.api_key');

        if (empty($apiKey)) {
            throw new \Exception('Clé API Gemini non configurée');
        }

        // TODO: Implémenter l'appel réel à l'API Gemini
        return "# Contenu généré par Gemini\n\n[Placeholder - Implémentation Gemini à venir]\n\nPrompt: {$prompt}";
    }

    /**
     * Générer localement (modèle local ou template).
     */
    private function generateLocal(string $prompt, ?array $options = null): string
    {
        // Génération basique par template
        return $this->generateFromTemplate($prompt, $options);
    }

    /**
     * Générer à partir d'un template.
     */
    private function generateFromTemplate(string $prompt, ?array $options = null): string
    {
        $template = <<<'MARKDOWN'
# {title}

## Vue d'ensemble

{overview}

## Installation

```bash
# Installation via Composer
composer require package/name
```

## Utilisation

### Exemple de base

```php
// Exemple de code
$example = new Example();
$example->run();
```

## Configuration

Ajoutez la configuration dans votre fichier `config/app.php`.

## Documentation

Pour plus d'informations, consultez la documentation complète.

## FAQ

### Question fréquente ?

Réponse à la question.

MARKDOWN;

        // Remplacer les placeholders basiques
        $content = str_replace(
            ['{title}', '{overview}'],
            [
                $options['title'] ?? 'Nouveau Document',
                $prompt,
            ],
            $template
        );

        return $content;
    }

    /**
     * Construire le prompt d'amélioration.
     */
    private function buildImprovementPrompt(string $content, string $instructions): string
    {
        return <<<PROMPT
Améliore le contenu suivant selon ces instructions:

Instructions: {$instructions}

Contenu actuel:
{$content}

Retourne le contenu amélioré en conservant le format Markdown.
PROMPT;
    }

    /**
     * Construire le prompt de complétion.
     */
    private function buildCompletionPrompt(string $partialContent, ?string $context): string
    {
        $contextSection = $context ? "\nContexte: {$context}\n" : '';

        return <<<PROMPT
Complète le contenu suivant de manière cohérente:{$contextSection}

Contenu partiel:
{$partialContent}

Retourne le contenu complet en format Markdown.
PROMPT;
    }

    /**
     * Construire le prompt pour sections manquantes.
     */
    private function buildMissingSectionsPrompt(Artifact $artifact, array $missingSections): string
    {
        $sectionsStr = implode(', ', $missingSections);
        $existingContent = $artifact->content ?? '';

        return <<<PROMPT
Génère les sections manquantes suivantes pour ce document: {$sectionsStr}

Document existant:
{$existingContent}

Retourne uniquement les nouvelles sections en format Markdown, en restant cohérent avec le contenu existant.
PROMPT;
    }
}
