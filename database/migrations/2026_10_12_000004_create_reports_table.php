<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 30)->unique();
            $table->foreignId('waste_point_id')->nullable()->constrained('waste_points')->nullOnDelete();
            $table->string('reporter_name', 100);
            $table->string('reporter_phone', 30);
            $table->string('subdistrict', 100)->index();
            $table->string('village', 100);
            $table->text('address_detail');
            $table->text('description');
            $table->string('photo_path', 255);
            $table->string('resolution_photo', 255)->nullable();
            $table->string('status', 30)->default('menunggu')->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
