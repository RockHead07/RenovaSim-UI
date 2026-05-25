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
        Schema::create('estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // User-given label for this estimation
            $table->string('label')->nullable();
            // e.g. "Kamar Tidur", "Dapur", auto-generated if null

            // Mode used: 'wizard' | 'ai'
            $table->string('mode')->default('wizard');

            // Job type extracted/selected
            $table->string('job_type')->nullable();

            // Area in m²
            $table->decimal('area', 8, 2)->nullable();

            // Location used for this estimation
            $table->string('location')->nullable();

            // Quality: ekonomi | standar | premium
            $table->string('quality')->nullable();

            // Cost range from FastAPI
            $table->decimal('cost_min', 12, 2)->default(0);
            $table->decimal('cost_max', 12, 2)->default(0);
            $table->decimal('cost_display', 12, 2)->default(0);
            // cost_display = midpoint of min and max

            // Confidence from FastAPI
            $table->decimal('confidence_score', 3, 2)->default(0);
            $table->string('confidence_label')->nullable();
            // Tinggi | Sedang | Rendah

            // Full FastAPI response stored as JSON for refine/display
            $table->json('fastapi_response')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimations');
    }
};
