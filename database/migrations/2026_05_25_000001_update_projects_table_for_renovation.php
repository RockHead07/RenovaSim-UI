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
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'description')) {
                $table->string('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('projects', 'building_type')) {
                $table->string('building_type')->nullable()->after('description');
            }
            if (!Schema::hasColumn('projects', 'location')) {
                $table->string('location')->nullable()->after('building_type');
            }
            if (!Schema::hasColumn('projects', 'estimations_count')) {
                $table->integer('estimations_count')->default(0)->after('total_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['description', 'building_type', 'location', 'estimations_count']);
        });
    }
};
