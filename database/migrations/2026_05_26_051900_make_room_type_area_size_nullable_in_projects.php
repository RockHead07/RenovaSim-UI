<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQLite does not support ALTER COLUMN, so we recreate the projects table
     * to make room_type and area_size nullable (they were NOT NULL from the original
     * migration but the controller never provides values for them).
     */
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::create('projects_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('building_type')->nullable();
            $table->string('location')->nullable();
            $table->string('room_type')->nullable();
            $table->decimal('area_size', 8, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->integer('estimations_count')->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        DB::statement('
            INSERT INTO projects_new
                (id, user_id, name, description, building_type, location,
                 room_type, area_size, total_cost, estimations_count, status,
                 created_at, updated_at)
            SELECT
                id, user_id, name, description, building_type, location,
                room_type, area_size, total_cost, estimations_count, status,
                created_at, updated_at
            FROM projects
        ');

        Schema::drop('projects');
        DB::statement('ALTER TABLE projects_new RENAME TO projects');

        DB::statement('PRAGMA foreign_keys = ON');
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
