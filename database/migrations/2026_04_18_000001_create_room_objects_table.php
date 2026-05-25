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
        Schema::create('room_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('type'); // 'bed', 'chair', 'table', etc.
            $table->json('position')->default(json_encode([0, 0, 0]));
            $table->json('rotation')->default(json_encode([0, 0, 0]));
            $table->json('scale')->default(json_encode([1, 1, 1]));
            $table->decimal('confidence', 3, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_objects');
    }
};
