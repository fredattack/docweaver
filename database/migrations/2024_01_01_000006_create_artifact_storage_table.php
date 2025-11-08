<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifact_storage', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('artifact_id')->constrained('artifacts')->cascadeOnDelete();
            $table->string('driver');
            $table->string('path');
            $table->json('location')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['artifact_id', 'driver']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifact_storage');
    }
};
