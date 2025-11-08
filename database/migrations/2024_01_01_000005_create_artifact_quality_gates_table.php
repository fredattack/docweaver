<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artifact_quality_gates', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('artifact_id')->constrained('artifacts')->cascadeOnDelete();
            $table->string('rule_class');
            $table->boolean('enabled')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['artifact_id', 'rule_class']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artifact_quality_gates');
    }
};
