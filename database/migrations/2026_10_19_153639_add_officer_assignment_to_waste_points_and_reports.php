<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('waste_points', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->string('resolution_photo', 255)->nullable()->after('assigned_to');
            $table->text('handling_notes')->nullable()->after('resolution_photo');
            $table->timestamp('handled_at')->nullable()->after('handling_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waste_points', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['assigned_to', 'resolution_photo', 'handling_notes', 'handled_at']);
        });
    }
};
