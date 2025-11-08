<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('type')->default('documentation');
            $table->string('status')->default('draft');
            $table->float('quality_score')->default(0);
            $table->float('completeness_score')->default(0);
            $table->string('ai_provider_used')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignUuid('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifacts');
    }
};
