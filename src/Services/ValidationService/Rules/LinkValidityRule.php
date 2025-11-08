<?php

namespace LaravelArtifacts\Services\ValidationService\Rules;

use LaravelArtifacts\Enums\ValidationSeverity;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Services\ValidationService\Contracts\ValidationRule;
use LaravelArtifacts\Services\ValidationService\ValidationResult;

class LinkValidityRule implements ValidationRule
{
    private bool $checkExternalLinks;

    public function __construct(?array $config = null)
    {
        $this->checkExternalLinks = $config['check_external_links'] ?? false;
    }

    public function validate(Artifact $artifact): ValidationResult
    {
        $content = $artifact->content ?? '';
        $issues = [];
        $warnings = [];

        // Extraire tous les liens Markdown [text](url)
        preg_match_all('/\[([^\]]+)\]\(([^\)]+)\)/', $content, $markdownLinks);

        // Extraire tous les liens HTML <a href="url">
        preg_match_all('/<a\s+href=["\']([^"\']+)["\']/', $content, $htmlLinks);

        $allLinks = array_merge($markdownLinks[2] ?? [], $htmlLinks[1] ?? []);

        if (empty($allLinks)) {
            return ValidationResult::pass(
                message: 'Aucun lien à valider',
                details: ['link_count' => 0]
            );
        }

        $validLinks = 0;
        $invalidLinks = 0;
        $internalLinks = 0;
        $externalLinks = 0;

        foreach ($allLinks as $link) {
            // Vérifier les liens internes (ancres)
            if (str_starts_with($link, '#')) {
                $internalLinks++;
                $anchor = substr($link, 1);

                // Vérifier si l'ancre existe dans le contenu
                if (! $this->anchorExists($content, $anchor)) {
                    $warnings[] = "Ancre manquante: {$link}";
                    $invalidLinks++;
                } else {
                    $validLinks++;
                }
                continue;
            }

            // Vérifier les liens relatifs
            if (str_starts_with($link, '/') || str_starts_with($link, './') || str_starts_with($link, '../')) {
                $internalLinks++;
                $warnings[] = "Lien relatif non vérifiable: {$link}";
                continue;
            }

            // Liens externes
            if (str_starts_with($link, 'http://') || str_starts_with($link, 'https://')) {
                $externalLinks++;

                // Vérifier la validité de l'URL
                if (! filter_var($link, FILTER_VALIDATE_URL)) {
                    $issues[] = "URL invalide: {$link}";
                    $invalidLinks++;
                } else {
                    $validLinks++;

                    // Optionnel: Vérifier que le lien est accessible
                    if ($this->checkExternalLinks) {
                        // Note: En production, utilisez une queue pour ne pas bloquer
                        // if (!$this->isLinkAccessible($link)) {
                        //     $warnings[] = "Lien inaccessible: {$link}";
                        // }
                    }
                }
                continue;
            }

            // Autres liens (mailto:, tel:, etc.)
            if (str_contains($link, ':')) {
                $validLinks++;
            } else {
                $warnings[] = "Type de lien inconnu: {$link}";
            }
        }

        $totalLinks = count($allLinks);
        $validityRatio = $totalLinks > 0 ? ($validLinks / $totalLinks) * 100 : 100;

        $details = [
            'total_links' => $totalLinks,
            'valid_links' => $validLinks,
            'invalid_links' => $invalidLinks,
            'internal_links' => $internalLinks,
            'external_links' => $externalLinks,
            'validity_ratio' => round($validityRatio, 2),
        ];

        if (empty($issues) && empty($warnings)) {
            return ValidationResult::pass(
                message: "Tous les liens sont valides ({$totalLinks} liens vérifiés)",
                details: $details
            );
        }

        if (empty($issues)) {
            return ValidationResult::warning(
                message: "Certains liens nécessitent votre attention ({$totalLinks} liens vérifiés)",
                details: array_merge($details, ['warnings' => $warnings]),
                suggestedAction: 'Vérifiez les liens signalés'
            );
        }

        return ValidationResult::error(
            message: "Des liens invalides ont été détectés ({$invalidLinks} sur {$totalLinks})",
            details: array_merge($details, ['issues' => $issues, 'warnings' => $warnings]),
            suggestedAction: 'Corrigez ou supprimez les liens invalides'
        );
    }

    private function anchorExists(string $content, string $anchor): bool
    {
        // Rechercher les headers qui correspondent à l'ancre
        preg_match_all('/^#{1,6}\s+(.+)$/m', $content, $headers);

        foreach ($headers[1] ?? [] as $header) {
            $headerSlug = strtolower(trim($header));
            $headerSlug = preg_replace('/[^a-z0-9\s-]/', '', $headerSlug);
            $headerSlug = preg_replace('/\s+/', '-', $headerSlug);

            if ($headerSlug === $anchor) {
                return true;
            }
        }

        // Rechercher les ancres HTML explicites
        if (preg_match('/<[^>]+id=["\']'.preg_quote($anchor, '/').'["\']/', $content)) {
            return true;
        }

        return false;
    }

    public function getName(): string
    {
        return 'LinkValidityRule';
    }

    public function getDescription(): string
    {
        return 'Vérifie la validité des liens (internes et externes)';
    }

    public function getDefaultSeverity(): string
    {
        return ValidationSeverity::WARNING->value;
    }
}
