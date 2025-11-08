# Guide de Développement Local - Laravel Artifacts

Ce guide explique comment développer et tester le package **Laravel Artifacts** localement dans une application Laravel réelle, permettant de faire des modifications facilement et de voir les changements en temps réel.

## Table des matières

- [Préparation de l'Environnement](#préparation-de-lenvironnement)
- [Méthode 1 : Utiliser un Repository Local avec Composer](#méthode-1--utiliser-un-repository-local-avec-composer)
- [Méthode 2 : Lien Symbolique (Symlink)](#méthode-2--lien-symbolique-symlink)
- [Développement du Package](#développement-du-package)
- [Tests](#tests)
- [Workflow de Développement](#workflow-de-développement)
- [Dépannage](#dépannage)

---

## Préparation de l'Environnement

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- Git
- Une application Laravel 11+ (pour tester le package)

### Structure des Répertoires Recommandée

```
~/projects/
├── laravel-artifacts/        # Le package (ce dépôt)
└── mon-app-test/             # Application Laravel de test
```

---

## Méthode 1 : Utiliser un Repository Local avec Composer

Cette méthode est **recommandée** car elle utilise Composer de manière standard et permet une gestion propre des dépendances.

### Étape 1 : Cloner le Package

```bash
# Naviguez vers votre dossier de projets
cd ~/projects

# Clonez le dépôt du package
git clone https://github.com/laravel-artifacts/laravel-artifacts.git
cd laravel-artifacts

# Installez les dépendances du package
composer install
```

### Étape 2 : Créer une Application Laravel de Test

```bash
# Retournez au dossier de projets
cd ~/projects

# Créez une nouvelle application Laravel
composer create-project laravel/laravel mon-app-test
cd mon-app-test
```

### Étape 3 : Configurer le Repository Local dans Composer

Modifiez le fichier `composer.json` de votre application de test :

```json
{
    "name": "mon-organisation/mon-app-test",
    "type": "project",
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel-artifacts/laravel-artifacts": "dev-main"
    },
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-artifacts",
            "options": {
                "symlink": true
            }
        }
    ],
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

**Explications :**
- `repositories` : Indique à Composer où trouver le package localement
- `type: "path"` : Utilise un chemin local au lieu de Packagist
- `url: "../laravel-artifacts"` : Chemin relatif vers le package
- `symlink: true` : Crée un lien symbolique (les changements sont visibles immédiatement)
- `dev-main` : Utilise la branche `main` du package

### Étape 4 : Installer le Package Localement

```bash
# Depuis le dossier de votre application de test
composer require laravel-artifacts/laravel-artifacts:@dev

# OU si déjà dans composer.json
composer update laravel-artifacts/laravel-artifacts
```

Vous devriez voir un message confirmant que le package a été lié via symlink :

```
  - Installing laravel-artifacts/laravel-artifacts (dev-main): Symlinking from ../laravel-artifacts
```

### Étape 5 : Publier et Configurer

```bash
# Publier les migrations
php artisan vendor:publish --tag="artifacts-migrations"

# Publier la configuration
php artisan vendor:publish --tag="artifacts-config"

# Configurer la base de données dans .env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Créer la base de données SQLite
touch database/database.sqlite

# Exécuter les migrations
php artisan migrate
```

### Étape 6 : Ajouter les Variables d'Environnement

Ajoutez dans votre fichier `.env` :

```env
# Laravel Artifacts Configuration
ARTIFACTS_AI_PROVIDER=openai
ARTIFACTS_STORAGE=local
ARTIFACTS_LOCAL_PATH=artifacts
ARTIFACTS_QUALITY_GATES_ENABLED=true
ARTIFACTS_MIN_COMPLETENESS=70
ARTIFACTS_MIN_QUALITY=80
```

### Étape 7 : Tester le Package

Créez un test rapide dans `routes/web.php` :

```php
<?php

use Illuminate\Support\Facades\Route;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Enums\ArtifactType;
use LaravelArtifacts\Enums\ArtifactStatus;

Route::get('/test-artifacts', function () {
    // Créer un utilisateur de test
    $user = \App\Models\User::factory()->create();

    // Créer un artefact
    $artifact = Artifact::create([
        'title' => 'Documentation de Test',
        'content' => '# Ceci est un test\n\nContenu de test.',
        'type' => ArtifactType::DOCUMENTATION,
        'status' => ArtifactStatus::DRAFT,
        'created_by' => $user->id,
    ]);

    // Publier l'artefact
    $artifact->publish();

    return response()->json([
        'success' => true,
        'artifact' => $artifact,
        'status' => $artifact->status->label(),
    ]);
});
```

Testez dans votre navigateur ou avec curl :

```bash
php artisan serve

# Dans un autre terminal
curl http://localhost:8000/test-artifacts
```

---

## Méthode 2 : Lien Symbolique (Symlink)

Cette méthode est plus manuelle mais peut être utile dans certains cas.

### Étape 1 : Cloner et Installer

```bash
cd ~/projects
git clone https://github.com/laravel-artifacts/laravel-artifacts.git
cd laravel-artifacts
composer install
```

### Étape 2 : Créer un Lien Symbolique

```bash
cd ~/projects/mon-app-test/vendor

# Créer le dossier laravel-artifacts s'il n'existe pas
mkdir -p laravel-artifacts

# Créer le lien symbolique
ln -s ~/projects/laravel-artifacts laravel-artifacts/laravel-artifacts
```

### Étape 3 : Configurer l'Autoload

Dans `composer.json` de l'application de test, ajoutez :

```json
{
    "autoload": {
        "psr-4": {
            "LaravelArtifacts\\": "vendor/laravel-artifacts/laravel-artifacts/src/"
        }
    }
}
```

### Étape 4 : Regénérer l'Autoload

```bash
composer dump-autoload
```

### Étape 5 : Enregistrer le Service Provider

Dans `config/app.php` (Laravel < 11) ou `bootstrap/providers.php` (Laravel 11+) :

```php
return [
    // ...
    'providers' => [
        // ...
        LaravelArtifacts\LaravelArtifactsServiceProvider::class,
    ],
];
```

---

## Développement du Package

### Workflow de Modification

1. **Faites vos modifications dans le package** (`~/projects/laravel-artifacts/`)

```bash
cd ~/projects/laravel-artifacts

# Exemple : Modifier un modèle
nano src/Models/Artifact.php
```

2. **Les changements sont immédiatement disponibles** dans l'application de test (grâce au symlink)

3. **Testez dans l'application de test** :

```bash
cd ~/projects/mon-app-test
php artisan tinker

>>> use LaravelArtifacts\Models\Artifact;
>>> Artifact::count();
```

4. **Exécutez les tests du package** :

```bash
cd ~/projects/laravel-artifacts
composer test
```

### Ajouter une Nouvelle Fonctionnalité

#### Exemple : Ajouter une Nouvelle Commande

1. **Créez la commande** dans `src/Commands/` :

```php
<?php

namespace LaravelArtifacts\Commands;

use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Results\CommandResult;

class DuplicateArtifactCommand extends BaseCommand
{
    public function __construct(
        private string $artifactId,
        private string $newTitle
    ) {}

    protected function rules(): array
    {
        return [
            'artifactId' => 'required|uuid|exists:artifacts,id',
            'newTitle' => 'required|string|max:255',
        ];
    }

    protected function toArray(): array
    {
        return [
            'artifactId' => $this->artifactId,
            'newTitle' => $this->newTitle,
        ];
    }

    protected function handle(): CommandResult
    {
        $original = Artifact::findOrFail($this->artifactId);

        $duplicate = Artifact::create([
            'title' => $this->newTitle,
            'content' => $original->content,
            'type' => $original->type,
            'status' => $original->status,
            'created_by' => $original->created_by,
            'metadata' => $original->metadata,
        ]);

        return CommandResult::success(
            data: $duplicate,
            message: 'Artefact dupliqué avec succès'
        );
    }
}
```

2. **Testez immédiatement dans votre app de test** :

```php
// routes/web.php
use LaravelArtifacts\Commands\DuplicateArtifactCommand;

Route::get('/duplicate-test', function () {
    $command = new DuplicateArtifactCommand(
        artifactId: 'votre-uuid-ici',
        newTitle: 'Copie de mon artefact'
    );

    $result = $command->execute();

    return response()->json($result->toArray());
});
```

3. **Écrivez un test** dans `tests/Unit/Commands/` :

```php
<?php

namespace LaravelArtifacts\Tests\Unit\Commands;

use LaravelArtifacts\Commands\DuplicateArtifactCommand;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\User;
use LaravelArtifacts\Tests\TestCase;

class DuplicateArtifactCommandTest extends TestCase
{
    /** @test */
    public function it_can_duplicate_an_artifact(): void
    {
        $user = User::factory()->create();
        $original = Artifact::factory()->create(['created_by' => $user->id]);

        $command = new DuplicateArtifactCommand(
            artifactId: $original->id,
            newTitle: 'Duplicated Artifact'
        );

        $result = $command->execute();

        $this->assertTrue($result->isSuccess());
        $this->assertCount(2, Artifact::all());
    }
}
```

4. **Exécutez le test** :

```bash
cd ~/projects/laravel-artifacts
composer test
```

### Ajouter une Migration

1. **Créez la migration** dans `database/migrations/` :

```bash
cd ~/projects/laravel-artifacts
```

Créez le fichier `database/migrations/2024_01_02_000001_add_author_to_artifacts_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artifacts', function (Blueprint $table) {
            $table->string('author')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('artifacts', function (Blueprint $table) {
            $table->dropColumn('author');
        });
    }
};
```

2. **Testez dans l'application** :

```bash
cd ~/projects/mon-app-test

# Re-publier les migrations
php artisan vendor:publish --tag="artifacts-migrations" --force

# Exécuter la migration
php artisan migrate
```

---

## Tests

### Exécuter les Tests du Package

```bash
cd ~/projects/laravel-artifacts

# Tous les tests
composer test

# Tests avec couverture
composer test-coverage

# Tests spécifiques
vendor/bin/phpunit tests/Unit/Models/ArtifactTest.php

# Test unique
vendor/bin/phpunit --filter it_can_create_an_artifact
```

### Analyse Statique

```bash
# PHPStan
composer analyse

# Laravel Pint (formatage)
composer format

# Vérifier sans modifier
vendor/bin/pint --test
```

### Tester dans l'Application

Créez un controller de test :

```bash
cd ~/projects/mon-app-test
php artisan make:controller ArtifactTestController
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Enums\ArtifactType;

class ArtifactTestController extends Controller
{
    public function index()
    {
        return Artifact::with(['versions', 'creator'])->paginate(15);
    }

    public function store(Request $request)
    {
        $artifact = Artifact::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => ArtifactType::from($request->type),
            'created_by' => auth()->id(),
        ]);

        return response()->json($artifact, 201);
    }

    public function publish($id)
    {
        $artifact = Artifact::findOrFail($id);
        $artifact->publish();

        return response()->json([
            'message' => 'Artifact published',
            'artifact' => $artifact,
        ]);
    }
}
```

---

## Workflow de Développement

### Cycle de Développement Recommandé

```bash
# 1. Créer une branche de fonctionnalité
cd ~/projects/laravel-artifacts
git checkout -b feature/ma-nouvelle-fonctionnalite

# 2. Faire vos modifications
# Éditer les fichiers nécessaires...

# 3. Tester dans le package
composer test

# 4. Tester dans l'application réelle
cd ~/projects/mon-app-test
php artisan serve
# Tester manuellement ou avec Postman/curl

# 5. Vérifier la qualité du code
cd ~/projects/laravel-artifacts
composer format
composer analyse

# 6. Committer vos changements
git add .
git commit -m "feat: Ajout de ma nouvelle fonctionnalité"

# 7. Pousser vers le dépôt distant
git push origin feature/ma-nouvelle-fonctionnalite
```

### Mettre à Jour les Dépendances

#### Dans le Package

```bash
cd ~/projects/laravel-artifacts
composer update
```

#### Dans l'Application de Test

```bash
cd ~/projects/mon-app-test
composer update laravel-artifacts/laravel-artifacts
```

### Debugger avec Tinker

```bash
cd ~/projects/mon-app-test
php artisan tinker

>>> use LaravelArtifacts\Models\Artifact;
>>> use LaravelArtifacts\Enums\ArtifactType;
>>>
>>> // Créer un artefact
>>> $artifact = Artifact::factory()->create(['created_by' => 1]);
>>>
>>> // Inspecter
>>> $artifact->toArray();
>>> $artifact->versions;
>>>
>>> // Tester des méthodes
>>> $artifact->publish();
>>> $artifact->status->label();
```

### Utiliser Laravel Debugbar

Installez dans l'application de test pour un meilleur debugging :

```bash
cd ~/projects/mon-app-test
composer require barryvdh/laravel-debugbar --dev
```

Cela affichera automatiquement :
- Requêtes SQL exécutées
- Temps d'exécution
- Variables de session
- Routes

---

## Dépannage

### Problème : Les Changements ne Sont Pas Visibles

**Solution :**

```bash
cd ~/projects/mon-app-test

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Régénérer l'autoload
composer dump-autoload
```

### Problème : Erreur "Class Not Found"

**Vérifiez :**

1. Le symlink est bien créé :
```bash
ls -la vendor/laravel-artifacts/laravel-artifacts
```

2. Le namespace est correct dans votre code

3. L'autoload est à jour :
```bash
composer dump-autoload
```

### Problème : Migrations Déjà Exécutées

**Réinitialiser la base de données :**

```bash
cd ~/projects/mon-app-test

# Option 1 : Fresh (supprime et recrée)
php artisan migrate:fresh

# Option 2 : Rollback puis migrate
php artisan migrate:rollback
php artisan migrate
```

### Problème : Conflits de Version Composer

**Solutions :**

```bash
# Supprimer le cache Composer
rm -rf ~/.composer/cache

# Supprimer vendor et réinstaller
cd ~/projects/mon-app-test
rm -rf vendor composer.lock
composer install
```

### Problème : Tests Échouent

**Vérifications :**

1. Base de données de test configurée :
```bash
cd ~/projects/laravel-artifacts
cat phpunit.xml
```

2. Dépendances à jour :
```bash
composer install
```

3. Cache PHPUnit :
```bash
rm -rf .phpunit.cache
composer test
```

---

## Astuces et Bonnes Pratiques

### 1. Utiliser des Factories pour les Tests

```php
// Dans votre app de test
$artifacts = Artifact::factory()->count(10)->create([
    'created_by' => auth()->id(),
]);
```

### 2. Utiliser Database Transactions dans les Tests

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonTest extends TestCase
{
    use RefreshDatabase;

    // Les données sont automatiquement rollback après chaque test
}
```

### 3. Créer des Seeders de Test

```bash
cd ~/projects/mon-app-test
php artisan make:seeder ArtifactsSeeder
```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Enums\ArtifactType;
use App\Models\User;

class ArtifactsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        Artifact::factory()->count(20)->create([
            'created_by' => $user->id,
            'type' => ArtifactType::DOCUMENTATION,
        ]);
    }
}
```

### 4. Utiliser Git Worktrees pour Plusieurs Branches

```bash
cd ~/projects/laravel-artifacts

# Créer un worktree pour une fonctionnalité
git worktree add ../laravel-artifacts-feature-x feature/x

# Vous avez maintenant deux dossiers :
# ~/projects/laravel-artifacts (main)
# ~/projects/laravel-artifacts-feature-x (feature/x)
```

### 5. Script de Réinitialisation Rapide

Créez `reset-dev.sh` dans votre application de test :

```bash
#!/bin/bash

echo "🔄 Réinitialisation de l'environnement de développement..."

# Vider les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Recréer la base de données
php artisan migrate:fresh --seed

# Régénérer l'autoload
composer dump-autoload

echo "✅ Environnement réinitialisé avec succès!"
```

Rendez-le exécutable :

```bash
chmod +x reset-dev.sh
./reset-dev.sh
```

---

## Ressources Supplémentaires

- [Documentation Laravel](https://laravel.com/docs)
- [Développement de Packages Laravel](https://laravelpackage.com)
- [Orchestra Testbench](https://github.com/orchestral/testbench)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

---

## Questions Fréquentes

### Q : Puis-je utiliser plusieurs versions du package simultanément ?

**R :** Oui, en utilisant Git Worktrees ou en clonant le dépôt dans différents dossiers et en ajustant le chemin dans `composer.json`.

### Q : Comment tester avec différentes versions de Laravel ?

**R :** Créez plusieurs applications de test avec différentes versions de Laravel.

### Q : Les modifications sont-elles persistées automatiquement ?

**R :** Oui, grâce au symlink. Chaque modification dans le package est immédiatement visible dans l'application de test.

### Q : Comment partager mon environnement de développement ?

**R :** Documentez votre setup dans un README et utilisez Docker/Laravel Sail pour un environnement reproductible.

---

**Bon développement ! 🚀**
