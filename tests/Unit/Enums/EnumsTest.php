<?php

namespace LaravelArtifacts\Tests\Unit\Enums;

use LaravelArtifacts\Enums\AiProvider;
use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Enums\ArtifactType;
use LaravelArtifacts\Enums\ChangeType;
use LaravelArtifacts\Enums\ValidationSeverity;
use LaravelArtifacts\Tests\TestCase;

class EnumsTest extends TestCase
{
    /** @test */
    public function artifact_type_has_correct_labels(): void
    {
        $this->assertEquals('Documentation', ArtifactType::DOCUMENTATION->label());
        $this->assertEquals('Specification', ArtifactType::SPECIFICATION->label());
        $this->assertEquals('Guide', ArtifactType::GUIDE->label());
        $this->assertEquals('API', ArtifactType::API->label());
    }

    /** @test */
    public function artifact_status_has_correct_labels(): void
    {
        $this->assertEquals('Draft', ArtifactStatus::DRAFT->label());
        $this->assertEquals('Under Review', ArtifactStatus::UNDER_REVIEW->label());
        $this->assertEquals('Published', ArtifactStatus::PUBLISHED->label());
        $this->assertEquals('Archived', ArtifactStatus::ARCHIVED->label());
    }

    /** @test */
    public function artifact_status_has_correct_colors(): void
    {
        $this->assertEquals('gray', ArtifactStatus::DRAFT->color());
        $this->assertEquals('yellow', ArtifactStatus::UNDER_REVIEW->color());
        $this->assertEquals('green', ArtifactStatus::PUBLISHED->color());
        $this->assertEquals('red', ArtifactStatus::ARCHIVED->color());
    }

    /** @test */
    public function ai_provider_has_correct_labels(): void
    {
        $this->assertEquals('OpenAI', AiProvider::OPENAI->label());
        $this->assertEquals('Claude', AiProvider::CLAUDE->label());
        $this->assertEquals('Gemini', AiProvider::GEMINI->label());
        $this->assertEquals('Local', AiProvider::LOCAL->label());
    }

    /** @test */
    public function validation_severity_has_correct_labels(): void
    {
        $this->assertEquals('Error', ValidationSeverity::ERROR->label());
        $this->assertEquals('Warning', ValidationSeverity::WARNING->label());
        $this->assertEquals('Info', ValidationSeverity::INFO->label());
    }

    /** @test */
    public function validation_severity_has_correct_colors(): void
    {
        $this->assertEquals('red', ValidationSeverity::ERROR->color());
        $this->assertEquals('yellow', ValidationSeverity::WARNING->color());
        $this->assertEquals('blue', ValidationSeverity::INFO->color());
    }

    /** @test */
    public function validation_severity_should_block_correctly(): void
    {
        $this->assertTrue(ValidationSeverity::ERROR->shouldBlock());
        $this->assertFalse(ValidationSeverity::WARNING->shouldBlock());
        $this->assertFalse(ValidationSeverity::INFO->shouldBlock());
    }

    /** @test */
    public function change_type_has_correct_labels(): void
    {
        $this->assertEquals('Created', ChangeType::CREATION->label());
        $this->assertEquals('Merged', ChangeType::MERGE->label());
        $this->assertEquals('Manually Edited', ChangeType::MANUAL_EDIT->label());
        $this->assertEquals('AI Generated', ChangeType::AI_GENERATION->label());
    }
}
