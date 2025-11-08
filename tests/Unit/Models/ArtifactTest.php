<?php

namespace LaravelArtifacts\Tests\Unit\Models;

use LaravelArtifacts\Enums\ArtifactStatus;
use LaravelArtifacts\Enums\ArtifactType;
use LaravelArtifacts\Models\Artifact;
use LaravelArtifacts\Models\ArtifactChange;
use LaravelArtifacts\Models\ArtifactQualityGate;
use LaravelArtifacts\Models\ArtifactStorage;
use LaravelArtifacts\Models\ArtifactValidation;
use LaravelArtifacts\Models\ArtifactVersion;
use LaravelArtifacts\Models\User;
use LaravelArtifacts\Tests\TestCase;

class ArtifactTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_an_artifact(): void
    {
        $artifact = Artifact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $this->assertInstanceOf(Artifact::class, $artifact);
        $this->assertDatabaseHas('artifacts', [
            'id' => $artifact->id,
            'title' => $artifact->title,
        ]);
    }

    /** @test */
    public function it_auto_generates_slug_from_title(): void
    {
        $artifact = Artifact::factory()->create([
            'title' => 'My Test Artifact',
            'slug' => '',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals('my-test-artifact', $artifact->slug);
    }

    /** @test */
    public function it_casts_type_to_enum(): void
    {
        $artifact = Artifact::factory()->create([
            'type' => ArtifactType::DOCUMENTATION,
            'created_by' => $this->user->id,
        ]);

        $this->assertInstanceOf(ArtifactType::class, $artifact->type);
        $this->assertEquals(ArtifactType::DOCUMENTATION, $artifact->type);
    }

    /** @test */
    public function it_casts_status_to_enum(): void
    {
        $artifact = Artifact::factory()->create([
            'status' => ArtifactStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        $this->assertInstanceOf(ArtifactStatus::class, $artifact->status);
        $this->assertEquals(ArtifactStatus::DRAFT, $artifact->status);
    }

    /** @test */
    public function it_has_versions_relationship(): void
    {
        $artifact = Artifact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $version = ArtifactVersion::factory()->create([
            'artifact_id' => $artifact->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($artifact->versions->contains($version));
    }

    /** @test */
    public function it_has_changes_relationship(): void
    {
        $artifact = Artifact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $version = ArtifactVersion::factory()->create([
            'artifact_id' => $artifact->id,
            'created_by' => $this->user->id,
        ]);

        $change = ArtifactChange::factory()->create([
            'artifact_id' => $artifact->id,
            'to_version_id' => $version->id,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($artifact->changes->contains($change));
    }

    /** @test */
    public function it_has_validations_relationship(): void
    {
        $artifact = Artifact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $version = ArtifactVersion::factory()->create([
            'artifact_id' => $artifact->id,
            'created_by' => $this->user->id,
        ]);

        $validation = ArtifactValidation::factory()->create([
            'artifact_id' => $artifact->id,
            'version_id' => $version->id,
        ]);

        $this->assertTrue($artifact->validations->contains($validation));
    }

    /** @test */
    public function it_has_quality_gates_relationship(): void
    {
        $artifact = Artifact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $qualityGate = ArtifactQualityGate::factory()->create([
            'artifact_id' => $artifact->id,
        ]);

        $this->assertTrue($artifact->qualityGates->contains($qualityGate));
    }

    /** @test */
    public function it_has_storage_relationship(): void
    {
        $artifact = Artifact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $storage = ArtifactStorage::factory()->create([
            'artifact_id' => $artifact->id,
        ]);

        $this->assertEquals($storage->id, $artifact->storage->id);
    }

    /** @test */
    public function it_can_publish_artifact(): void
    {
        $artifact = Artifact::factory()->create([
            'status' => ArtifactStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        $artifact->publish();

        $this->assertEquals(ArtifactStatus::PUBLISHED, $artifact->fresh()->status);
    }

    /** @test */
    public function it_can_archive_artifact(): void
    {
        $artifact = Artifact::factory()->create([
            'status' => ArtifactStatus::PUBLISHED,
            'created_by' => $this->user->id,
        ]);

        $artifact->archive();

        $this->assertEquals(ArtifactStatus::ARCHIVED, $artifact->fresh()->status);
    }

    /** @test */
    public function it_can_send_for_review(): void
    {
        $artifact = Artifact::factory()->create([
            'status' => ArtifactStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        $artifact->sendForReview();

        $this->assertEquals(ArtifactStatus::UNDER_REVIEW, $artifact->fresh()->status);
    }

    /** @test */
    public function it_scopes_published_artifacts(): void
    {
        Artifact::factory()->create([
            'status' => ArtifactStatus::PUBLISHED,
            'created_by' => $this->user->id,
        ]);

        Artifact::factory()->create([
            'status' => ArtifactStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        $published = Artifact::published()->get();

        $this->assertCount(1, $published);
        $this->assertEquals(ArtifactStatus::PUBLISHED, $published->first()->status);
    }

    /** @test */
    public function it_scopes_by_type(): void
    {
        Artifact::factory()->create([
            'type' => ArtifactType::DOCUMENTATION,
            'created_by' => $this->user->id,
        ]);

        Artifact::factory()->create([
            'type' => ArtifactType::GUIDE,
            'created_by' => $this->user->id,
        ]);

        $docs = Artifact::byType(ArtifactType::DOCUMENTATION)->get();

        $this->assertCount(1, $docs);
        $this->assertEquals(ArtifactType::DOCUMENTATION, $docs->first()->type);
    }

    /** @test */
    public function it_soft_deletes(): void
    {
        $artifact = Artifact::factory()->create([
            'created_by' => $this->user->id,
        ]);

        $artifact->delete();

        $this->assertSoftDeleted('artifacts', ['id' => $artifact->id]);
    }
}
