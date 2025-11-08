<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifact_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('artifact_id')->constrained('artifacts')->cascadeOnDelete();
            $table->longText('content');
            $table->string('content_hash');
            $table->text('change_description')->nullable();
            $table->string('version')->default('1.0.0');
            $table->boolean('is_ai_generated')->default(false);
            $table->string('ai_provider_used')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();

            $table->index('artifact_id');
            $table->unique(['artifact_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifact_versions');
    }
};
