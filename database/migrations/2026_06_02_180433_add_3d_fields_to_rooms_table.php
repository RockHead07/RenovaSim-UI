<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('wall_color')->default('#f5f0eb')->after('layout_data');
            $table->string('floor_color')->default('#c4a882')->after('wall_color');
            $table->string('external_id')->nullable()->after('floor_color');
            $table->string('status')->default('saved')->after('external_id');
            $table->string('applied_template')->nullable()->after('status');
            $table->string('recommended_type')->nullable()->after('applied_template');
            $table->text('thumbnail')->nullable()->after('recommended_type');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'wall_color', 'floor_color', 'external_id',
                'status', 'applied_template', 'recommended_type', 'thumbnail',
            ]);
        });
    }
};
