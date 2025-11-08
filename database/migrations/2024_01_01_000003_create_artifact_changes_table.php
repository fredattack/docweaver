<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifact_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('artifact_id')->constrained('artifacts')->cascadeOnDelete();
            $table->foreignId('from_version_id')->nullable()->constrained('artifact_versions');
            $table->foreignId('to_version_id')->constrained('artifact_versions');
            $table->string('change_type');
            $table->text('description');
            $table->json('details')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();

            $table->index('artifact_id');
            $table->index('change_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifact_changes');
    }
};
