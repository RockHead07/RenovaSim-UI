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
            // Add after 'name'
            $table->string('description')->nullable()->after('name');
            $table->string('building_type')->nullable()->after('description');
            // building_type options: rumah, apartemen, ruko, kantor, lainnya

            $table->string('location')->nullable()->after('building_type');
            // location = city name, e.g. "jakarta"

            $table->integer('estimations_count')->default(0)->after('total_cost');
            // cached count of estimations in this project

            // Note: room_type and area_size are left as-is.
            // SQLite does not support column modification via ->change().
            // They are already effectively nullable at the application layer
            // since we handle null checks in the model.
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
