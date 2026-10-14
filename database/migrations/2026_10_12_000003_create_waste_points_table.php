<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_points', function (Blueprint $table) {
            $table->id();
            $table->string('point_code', 30)->unique();
            $table->string('name', 150)->nullable();
            $table->string('subdistrict', 100)->index();
            $table->string('village', 100);
            $table->text('address_detail');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 30)->default('terverifikasi')->index();
            $table->decimal('total_score', 8, 4)->default(0.0000)->index();
            $table->unsignedInteger('rank')->nullable()->index();
            $table->json('weights_snapshot')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_points');
    }
};
