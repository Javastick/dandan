<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_point_id')->constrained('waste_points')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('raw_value');
            $table->decimal('normalized_score', 6, 4);
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['waste_point_id', 'criterion_id'], 'pt_assess_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_assessments');
    }
};
