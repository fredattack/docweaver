# Laravel Artifacts

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-artifacts/laravel-artifacts.svg?style=flat-square)](https://packagist.org/packages/laravel-artifacts/laravel-artifacts)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-artifacts/laravel-artifacts.svg?style=flat-square)](https://packagist.org/packages/laravel-artifacts/laravel-artifacts)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**Laravel Artifacts** is an intelligent artifact management system for Laravel applications, featuring AI-powered content generation, smart merging algorithms, and comprehensive quality validation.

## Features

- **Intelligent Content Merging**: AI-powered merge algorithm that preserves context and detects conflicts
- **Quality Gates Framework**: Validate content quality before publication
- **Version Control**: Complete version tracking with change history
- **Multi-Provider AI Support**: OpenAI, Claude (Anthropic), and Gemini (Google)
- **Flexible Storage**: Local, S3, Azure, or database storage options
- **Command Pattern Architecture**: Clean, testable command-based operations
- **Comprehensive Testing**: 85%+ test coverage with PHPUnit

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+

## Installation

Install the package via Composer:

```bash
composer require laravel-artifacts/laravel-artifacts
```

### Publish Configuration

Publish the package configuration file:

```bash
php artisan vendor:publish --tag="artifacts-config"
```

### Run Migrations

Publish and run the database migrations:

```bash
php artisan vendor:publish --tag="artifacts-migrations"
php artisan migrate
```

## Quick Start

### 1. Create an Artifact

```php
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Enums\ArtifactType;

$artifact = Artifact::create([
    'title' => 'API Documentation',
    'content' => '# API Overview...',
    'type' => ArtifactType::DOCUMENTATION,
    'created_by' => auth()->id(),
]);
```

### 2. Create a Version

```php
use LaravelArtifacts\Models\ArtifactVersion;

$version = ArtifactVersion::create([
    'artifact_id' => $artifact->id,
    'content' => $updatedContent,
    'version' => '1.1.0',
    'change_description' => 'Added new endpoints',
    'created_by' => auth()->id(),
]);
```

### 3. Publish an Artifact

```php
$artifact->publish();
```

## Configuration

The package configuration file is located at `config/artifacts.php`. Here are the key configuration options:

### AI Providers

```php
'ai' => [
    'default_provider' => env('ARTIFACTS_AI_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('ARTIFACTS_OPENAI_MODEL', 'gpt-4-turbo'),
        ],
        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ARTIFACTS_CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
        ],
    ],
],
```

### Storage Options

```php
'storage' => [
    'default' => env('ARTIFACTS_STORAGE', 'local'),

    'drivers' => [
        'local' => [
            'path' => env('ARTIFACTS_LOCAL_PATH', 'artifacts'),
        ],
        's3' => [
            'bucket' => env('ARTIFACTS_S3_BUCKET'),
            'region' => env('ARTIFACTS_S3_REGION', 'us-east-1'),
        ],
    ],
],
```

### Quality Gates

```php
'quality_gates' => [
    'enabled' => env('ARTIFACTS_QUALITY_GATES_ENABLED', true),
    'min_completeness_score' => env('ARTIFACTS_MIN_COMPLETENESS', 70),
    'min_quality_score' => env('ARTIFACTS_MIN_QUALITY', 80),
],
```

## Environment Variables

Add these variables to your `.env` file:

```env
# AI Provider Configuration
ARTIFACTS_AI_PROVIDER=openai
OPENAI_API_KEY=your-openai-api-key
ANTHROPIC_API_KEY=your-anthropic-api-key

# Storage Configuration
ARTIFACTS_STORAGE=local
ARTIFACTS_LOCAL_PATH=artifacts

# Quality Gates
ARTIFACTS_QUALITY_GATES_ENABLED=true
ARTIFACTS_MIN_COMPLETENESS=70
ARTIFACTS_MIN_QUALITY=80

# Merge Configuration
ARTIFACTS_MERGE_THRESHOLD=0.85
ARTIFACTS_PRESERVE_MANUAL=true
```

## Models

### Artifact

The main artifact model with support for different types and statuses.

**Attributes:**
- `title` - Artifact title
- `slug` - URL-friendly slug (auto-generated)
- `content` - Artifact content
- `type` - Type (documentation, specification, guide, api, response)
- `status` - Status (draft, under_review, published, archived)
- `quality_score` - Quality score (0-100)
- `completeness_score` - Completeness score (0-100)

**Relationships:**
- `versions()` - All versions
- `changes()` - All changes
- `validations()` - All validations
- `qualityGates()` - Quality gate configurations
- `storage()` - Storage information

**Methods:**
- `publish()` - Publish the artifact
- `archive()` - Archive the artifact
- `sendForReview()` - Send for review

### ArtifactVersion

Immutable version history for artifacts.

**Attributes:**
- `content` - Full content snapshot
- `content_hash` - SHA256 hash for deduplication
- `version` - Semantic version (e.g., "1.0.0")
- `is_ai_generated` - Whether generated by AI
- `ai_provider_used` - AI provider used

### ArtifactChange

Audit trail for all changes.

**Attributes:**
- `change_type` - Type of change (creation, merge, manual_edit, etc.)
- `description` - Change description
- `details` - JSON details (conflicts, lines added/removed)

## Command Pattern

The package uses a command pattern for operations:

```php
use LaravelArtifacts\Commands\YourCommand;

$command = new YourCommand([
    'artifact_id' => $artifact->id,
    'data' => [...],
]);

$result = $command->execute();

if ($result->isSuccess()) {
    // Handle success
    $data = $result->data;
} else {
    // Handle failure
    $errors = $result->errors;
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

Format code:

```bash
composer format
```

## Roadmap

### Phase 1 (Weeks 1-12) - CURRENT
- ✅ Core infrastructure and data model
- ✅ Command pattern implementation
- ⏳ Merge algorithm and quality gates
- ⏳ Dashboard MVP
- ⏳ Closed beta launch

### Phase 2 (Months 4-6)
- Team collaboration features
- Advanced analytics
- GitHub/Slack integrations
- Multi-language support

### Phase 3 (Months 7-9)
- Enterprise features
- Custom workflows
- Advanced AI features
- NotebookLM integration

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email team@laravel-artifacts.com instead of using the issue tracker.

## Credits

- [Laravel Artifacts Team](https://github.com/laravel-artifacts)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- [Documentation](https://docs.laravel-artifacts.com)
- [Issue Tracker](https://github.com/laravel-artifacts/laravel-artifacts/issues)
- [Discussions](https://github.com/laravel-artifacts/laravel-artifacts/discussions)

---

**Laravel Artifacts** - Intelligent artifact management for Laravel.
