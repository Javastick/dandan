<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criterion_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criterion_source_id')->constrained('criteria')->cascadeOnDelete();
            $table->foreignId('criterion_target_id')->constrained('criteria')->cascadeOnDelete();
            $table->decimal('value', 8, 4);
            $table->timestamps();

            $table->unique(['criterion_source_id', 'criterion_target_id'], 'crit_comp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criterion_comparisons');
    }
};
