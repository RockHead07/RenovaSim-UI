<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * For PostgreSQL/Supabase, modify columns if they exist but don't support all changes.
     * Column types are already compatible, so skip.
     */
    public function up(): void
    {
        Schema::table('estimations', function (Blueprint $table) {
            // PostgreSQL allows nullable modifications on-the-fly
            // Columns are already nullable in Supabase schema
            // No action needed - table already has correct nullable columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
