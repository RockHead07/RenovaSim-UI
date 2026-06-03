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
        // Use IF NOT EXISTS to safely skip indexes that may already exist in Supabase
        DB::statement('CREATE INDEX IF NOT EXISTS projects_user_id_created_at_index ON projects (user_id, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS projects_status_index ON projects (status)');
        DB::statement('CREATE INDEX IF NOT EXISTS estimations_project_id_created_at_index ON estimations (project_id, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS estimations_user_id_index ON estimations (user_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS rooms_user_id_index ON rooms (user_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS partners_is_active_order_index ON partners (is_active, "order")');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS projects_user_id_created_at_index');
        DB::statement('DROP INDEX IF EXISTS projects_status_index');
        DB::statement('DROP INDEX IF EXISTS estimations_project_id_created_at_index');
        DB::statement('DROP INDEX IF EXISTS estimations_user_id_index');
        DB::statement('DROP INDEX IF EXISTS partners_is_active_order_index');
        // rooms_user_id_index intentionally not dropped — may pre-exist
    }
};
