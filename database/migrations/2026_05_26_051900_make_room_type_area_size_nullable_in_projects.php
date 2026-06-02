<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * For PostgreSQL/Supabase, columns are already nullable, so skip this.
     * This migration was for SQLite table recreation.
     */
    public function up(): void
    {
        // PostgreSQL supports ALTER COLUMN and table already has nullable columns
        // No action needed
    }

    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::create('projects_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('room_type');
            $table->decimal('area_size', 8, 2);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->string('description')->nullable();
            $table->string('building_type')->nullable();
            $table->string('location')->nullable();
            $table->integer('estimations_count')->default(0);
            $table->timestamps();
        });

        DB::statement('
            INSERT INTO projects_old
                (id, user_id, name, room_type, area_size, total_cost,
                 status, description, building_type, location,
                 estimations_count, created_at, updated_at)
            SELECT
                id, user_id, name, room_type, area_size, total_cost,
                status, description, building_type, location,
                estimations_count, created_at, updated_at
            FROM projects
        ');

        Schema::drop('projects');
        DB::statement('ALTER TABLE projects_old RENAME TO projects');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
