<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifact_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('artifact_id')->constrained('artifacts')->cascadeOnDelete();
            $table->foreignId('version_id')->constrained('artifact_versions');
            $table->string('rule_class');
            $table->boolean('passed');
            $table->string('severity');
            $table->text('message');
            $table->json('details')->nullable();
            $table->text('suggested_action')->nullable();
            $table->timestamps();

            $table->index('artifact_id');
            $table->index('version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifact_validations');
    }
};
