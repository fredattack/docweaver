# Laravel Artifacts

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-artifacts/laravel-artifacts.svg?style=flat-square)](https://packagist.org/packages/laravel-artifacts/laravel-artifacts)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-artifacts/laravel-artifacts.svg?style=flat-square)](https://packagist.org/packages/laravel-artifacts/laravel-artifacts)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Laravel Artifacts** est un système intelligent de gestion d'artefacts pour applications Laravel, avec génération de contenu par IA, algorithmes de fusion intelligents et validation de qualité complète.

## Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
  - [Via Composer (Production)](#via-composer-production)
  - [Installation Locale (Développement)](#installation-locale-développement)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Modèles](#modèles)
- [Tests](#tests)
- [Développement](#développement)
- [Contribution](#contribution)

## Fonctionnalités

- ✨ **Fusion Intelligente de Contenu** : Algorithme de fusion IA qui préserve le contexte et détecte les conflits
- 🛡️ **Framework de Portes de Qualité** : Validation de la qualité du contenu avant publication
- 📚 **Contrôle de Version** : Suivi complet des versions avec historique des changements
- 🤖 **Support Multi-Fournisseurs IA** : OpenAI, Claude (Anthropic) et Gemini (Google)
- 💾 **Stockage Flexible** : Options de stockage local, S3, Azure ou base de données
- ⚡ **Architecture Command Pattern** : Opérations basées sur des commandes propres et testables
- ✅ **Tests Complets** : Couverture de tests >85% avec PHPUnit

## Prérequis

- PHP 8.2 ou supérieur
- Laravel 11.0 ou supérieur
- MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+

## Installation

### Via Composer (Production)

Pour installer le package dans votre application Laravel :

```bash
composer require laravel-artifacts/laravel-artifacts
```

### Installation Locale (Développement)

Pour développer et tester le package localement, consultez le [Guide de Développement Local](DEVELOPMENT.md).

## Configuration

### 1. Publier la Configuration

Publiez le fichier de configuration du package :

```bash
php artisan vendor:publish --tag="artifacts-config"
```

Cela créera le fichier `config/artifacts.php`.

### 2. Publier et Exécuter les Migrations

Publiez les migrations du package :

```bash
php artisan vendor:publish --tag="artifacts-migrations"
```

Exécutez les migrations :

```bash
php artisan migrate
```

Cela créera 6 tables dans votre base de données :
- `artifacts` - Table principale des artefacts
- `artifact_versions` - Historique des versions
- `artifact_changes` - Journal d'audit des changements
- `artifact_validations` - Enregistrements de validation qualité
- `artifact_quality_gates` - Configuration des règles de qualité
- `artifact_storage` - Abstraction du stockage

### 3. Variables d'Environnement

Ajoutez ces variables à votre fichier `.env` :

```env
# Configuration du Fournisseur IA
ARTIFACTS_AI_PROVIDER=openai
OPENAI_API_KEY=votre-clé-api-openai
ANTHROPIC_API_KEY=votre-clé-api-anthropic
GEMINI_API_KEY=votre-clé-api-gemini

# Configuration du Stockage
ARTIFACTS_STORAGE=local
ARTIFACTS_LOCAL_PATH=artifacts

# Portes de Qualité
ARTIFACTS_QUALITY_GATES_ENABLED=true
ARTIFACTS_MIN_COMPLETENESS=70
ARTIFACTS_MIN_QUALITY=80

# Configuration de Fusion
ARTIFACTS_MERGE_THRESHOLD=0.85
ARTIFACTS_PRESERVE_MANUAL=true
ARTIFACTS_TRACK_CHANGES=true

# Contrôle de Version
ARTIFACTS_VERSIONING=true
ARTIFACTS_MAX_VERSIONS=50
ARTIFACTS_RETENTION_DAYS=365

# Performance & Cache
ARTIFACTS_CACHE_ENABLED=true
ARTIFACTS_CACHE_TTL=3600
```

### 4. Configuration du Stockage S3 (Optionnel)

Si vous utilisez Amazon S3 pour le stockage :

```env
ARTIFACTS_STORAGE=s3
ARTIFACTS_S3_BUCKET=votre-bucket-s3
ARTIFACTS_S3_REGION=eu-west-1
ARTIFACTS_S3_PATH=artifacts
```

### 5. Configuration Azure (Optionnel)

Si vous utilisez Azure Blob Storage :

```env
ARTIFACTS_STORAGE=azure
ARTIFACTS_AZURE_CONTAINER=artifacts
ARTIFACTS_AZURE_ACCOUNT=votre-compte-storage
```

## Utilisation

### Guide de Démarrage Rapide

#### 1. Créer un Artefact

```php
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Enums\ArtifactType;
use LaravelArtifacts\Enums\ArtifactStatus;

$artifact = Artifact::create([
    'title' => 'Documentation API',
    'content' => '# Vue d\'ensemble de l\'API...',
    'type' => ArtifactType::DOCUMENTATION,
    'status' => ArtifactStatus::DRAFT,
    'created_by' => auth()->id(),
]);
```

#### 2. Créer une Version

```php
use LaravelArtifacts\Models\ArtifactVersion;

$version = ArtifactVersion::create([
    'artifact_id' => $artifact->id,
    'content' => $contenuMisAJour,
    'version' => '1.1.0',
    'change_description' => 'Ajout de nouveaux endpoints',
    'is_ai_generated' => false,
    'created_by' => auth()->id(),
]);
```

#### 3. Gérer les Statuts

```php
// Envoyer pour révision
$artifact->sendForReview();

// Publier l'artefact
$artifact->publish();

// Archiver l'artefact
$artifact->archive();
```

#### 4. Récupérer des Artefacts

```php
// Tous les artefacts publiés
$published = Artifact::published()->get();

// Artefacts par type
$documentation = Artifact::byType(ArtifactType::DOCUMENTATION)->get();

// Artefacts récents
$recent = Artifact::recent()->limit(10)->get();

// Avec relations
$artifact = Artifact::with(['versions', 'changes', 'validations'])->find($id);
```

#### 5. Travailler avec les Versions

```php
// Obtenir la dernière version
$latestVersion = $artifact->latestVersion();

// Obtenir toutes les versions
$versions = $artifact->versions;

// Vérifier si une version est dupliquée
$isDuplicate = $version->isDuplicate();
```

#### 6. Validation Qualité

```php
use LaravelArtifacts\Models\ArtifactValidation;
use LaravelArtifacts\Enums\ValidationSeverity;

// Créer une validation
$validation = ArtifactValidation::create([
    'artifact_id' => $artifact->id,
    'version_id' => $version->id,
    'rule_class' => 'LaravelArtifacts\\Rules\\CompletenessRule',
    'passed' => true,
    'severity' => ValidationSeverity::INFO,
    'message' => 'L\'artefact est complet',
]);

// Récupérer les validations échouées
$failedValidations = $artifact->validations()->failed()->get();

// Récupérer les erreurs
$errors = $artifact->validations()->errors()->get();
```

#### 7. Gestion du Stockage

```php
use LaravelArtifacts\Models\ArtifactStorage;

// Créer un enregistrement de stockage
$storage = ArtifactStorage::create([
    'artifact_id' => $artifact->id,
    'driver' => 'local',
    'path' => 'artifacts/my-artifact.md',
    'metadata' => [
        'size' => 12345,
        'mime_type' => 'text/markdown',
    ],
]);

// Marquer comme synchronisé
$storage->markSynced();

// Vérifier si nécessite synchronisation
if ($storage->needsSync()) {
    // Synchroniser...
}
```

### Utilisation Avancée

#### Pattern de Commande

Le package utilise un pattern de commande pour les opérations :

```php
use LaravelArtifacts\Commands\BaseCommand;
use LaravelArtifacts\Results\CommandResult;

class MonCommandePersonnalisee extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private array $data
    ) {}

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'data' => 'required|array',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'data' => $this->data,
        ];
    }

    protected function handle(): CommandResult
    {
        // Votre logique métier ici
        $artifact = Artifact::find($this->artifactId);

        // Effectuer des opérations...

        return CommandResult::success(
            data: $artifact,
            message: 'Opération réussie'
        );
    }
}

// Utilisation
$command = new MonCommandePersonnalisee($artifactId, $data);
$result = $command->execute();

if ($result->isSuccess()) {
    $artifact = $result->data;
    // Traiter le succès
} else {
    $errors = $result->errors;
    // Traiter les erreurs
}
```

## Modèles

### Artifact

Le modèle principal d'artefact avec support pour différents types et statuts.

**Attributs Principaux :**
- `id` (UUID) - Identifiant unique
- `title` (string) - Titre de l'artefact
- `slug` (string) - Slug URL (auto-généré)
- `content` (longText) - Contenu de l'artefact
- `type` (enum) - Type : documentation, specification, guide, api, response, other
- `status` (enum) - Statut : draft, under_review, published, archived
- `quality_score` (float) - Score de qualité (0-100)
- `completeness_score` (float) - Score de complétude (0-100)
- `ai_provider_used` (enum) - Fournisseur IA utilisé
- `metadata` (json) - Métadonnées personnalisées

**Relations :**
- `versions()` - Toutes les versions (HasMany)
- `changes()` - Tous les changements (HasMany)
- `validations()` - Toutes les validations (HasMany)
- `qualityGates()` - Portes de qualité configurées (HasMany)
- `storage()` - Information de stockage (HasOne)
- `creator()` - Créateur (BelongsTo User)
- `updater()` - Dernier modificateur (BelongsTo User)

**Méthodes Disponibles :**
- `publish()` - Publier l'artefact
- `archive()` - Archiver l'artefact
- `sendForReview()` - Envoyer pour révision
- `latestVersion()` - Obtenir la dernière version

**Scopes :**
- `published()` - Artefacts publiés
- `draft()` - Artefacts en brouillon
- `byType(ArtifactType $type)` - Par type
- `recent()` - Triés par date décroissante

### ArtifactVersion

Historique de versions immuable pour les artefacts.

**Attributs :**
- `content` (longText) - Snapshot complet du contenu
- `content_hash` (string) - Hash SHA256 pour déduplication
- `version` (string) - Version sémantique (ex: "1.0.0")
- `is_ai_generated` (boolean) - Généré par IA
- `ai_provider_used` (enum) - Fournisseur IA utilisé
- `change_description` (text) - Description du changement
- `metadata` (json) - Métadonnées

**Méthodes :**
- `isDuplicate()` - Vérifie si c'est un duplicata

### ArtifactChange

Journal d'audit pour tous les changements.

**Attributs :**
- `change_type` (enum) - Type : creation, merge, manual_edit, ai_generation, validation
- `description` (text) - Description du changement
- `details` (json) - Détails (conflits, lignes ajoutées/supprimées)
- `from_version_id` - Version source
- `to_version_id` - Version cible

### ArtifactValidation

Enregistrements de vérification qualité.

**Attributs :**
- `rule_class` (string) - Classe de la règle de validation
- `passed` (boolean) - Validation réussie ou non
- `severity` (enum) - Sévérité : error, warning, info
- `message` (text) - Message de validation
- `details` (json) - Détails spécifiques
- `suggested_action` (text) - Action suggérée

**Scopes :**
- `failed()` - Validations échouées
- `passed()` - Validations réussies
- `errors()` - Erreurs seulement
- `warnings()` - Avertissements seulement

### ArtifactQualityGate

Configuration des portes de qualité.

**Attributs :**
- `rule_class` (string) - Classe de la règle
- `enabled` (boolean) - Activé ou non
- `config` (json) - Configuration de la règle

**Méthodes :**
- `toggle()` - Basculer l'état activé/désactivé

### ArtifactStorage

Abstraction de stockage multi-drivers.

**Attributs :**
- `driver` (string) - Driver : local, s3, azure, database
- `path` (string) - Chemin du fichier
- `location` (json) - Informations de localisation
- `synced_at` (timestamp) - Date de dernière synchronisation

**Méthodes :**
- `markSynced()` - Marquer comme synchronisé
- `needsSync()` - Vérifier si synchronisation nécessaire

## Tests

### Exécuter les Tests

```bash
# Tous les tests
composer test

# Tests avec couverture
composer test-coverage

# Analyse statique
composer analyse

# Formatage du code
composer format
```

### Écrire des Tests

```php
use LaravelArtifacts\Tests\TestCase;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\User;

class MonTest extends TestCase
{
    /** @test */
    public function it_can_create_an_artifact(): void
    {
        $user = User::factory()->create();

        $artifact = Artifact::factory()->create([
            'title' => 'Test Artifact',
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('artifacts', [
            'title' => 'Test Artifact',
        ]);
    }
}
```

## Développement

Pour développer ce package localement et le tester dans une application Laravel, consultez le [Guide de Développement Local](DEVELOPMENT.md).

## Configuration Avancée

### Personnalisation des Fournisseurs IA

Modifiez `config/artifacts.php` :

```php
'ai' => [
    'default_provider' => 'openai',

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => 'gpt-4-turbo',
            'temperature' => 0.3,
            'max_tokens' => 2000,
        ],
        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => 'claude-3-5-sonnet-20241022',
            'temperature' => 0.3,
            'max_tokens' => 2000,
        ],
    ],
],
```

### Configuration des Portes de Qualité

```php
'quality_gates' => [
    'enabled' => true,
    'strict_mode' => false, // Bloque si échec en mode strict

    'rules' => [
        // Ajoutez vos classes de règles ici
    ],

    'min_completeness_score' => 70,
    'min_quality_score' => 80,
],
```

### Politique de Rétention des Versions

```php
'versioning' => [
    'enabled' => true,
    'auto_increment' => true,
    'max_versions' => 50, // Maximum de versions à conserver
    'retention_days' => 365, // Jours avant suppression
],
```

## Feuille de Route

### Phase 1 (Semaines 1-12) - EN COURS
- ✅ Infrastructure de base et modèle de données
- ✅ Implémentation du pattern de commande
- ⏳ Algorithme de fusion et portes de qualité
- ⏳ Dashboard MVP
- ⏳ Lancement bêta fermée

### Phase 2 (Mois 4-6)
- Fonctionnalités de collaboration en équipe
- Analyses avancées
- Intégrations GitHub/Slack
- Support multi-langues

### Phase 3 (Mois 7-9)
- Fonctionnalités entreprise
- Workflows personnalisés
- Fonctionnalités IA avancées
- Intégration NotebookLM

## Contribution

Les contributions sont les bienvenues ! Consultez [CONTRIBUTING.md](CONTRIBUTING.md) pour plus de détails.

### Directives de Contribution

1. Forkez le dépôt
2. Créez une branche de fonctionnalité (`git checkout -b feature/ma-fonctionnalite`)
3. Commitez vos changements (`git commit -am 'Ajout de ma fonctionnalité'`)
4. Poussez vers la branche (`git push origin feature/ma-fonctionnalite`)
5. Ouvrez une Pull Request

## Sécurité

Si vous découvrez des problèmes de sécurité, veuillez envoyer un email à team@laravel-artifacts.com au lieu d'utiliser le tracker de problèmes.

## Crédits

- [Laravel Artifacts Team](https://github.com/laravel-artifacts)
- [Tous les Contributeurs](../../contributors)

## Licence

Licence MIT. Consultez le [fichier de licence](LICENSE.md) pour plus d'informations.

## Support

- [Documentation](https://docs.laravel-artifacts.com)
- [Tracker de Problèmes](https://github.com/laravel-artifacts/laravel-artifacts/issues)
- [Discussions](https://github.com/laravel-artifacts/laravel-artifacts/discussions)

---

**Laravel Artifacts** - Gestion intelligente d'artefacts pour Laravel.
