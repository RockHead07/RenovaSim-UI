<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->string('feature_key')->after('pricing_plan_id')->default('');
            $table->string('feature_label')->after('feature_key')->default('');
            $table->string('feature_value')->after('feature_label')->default('');
        });
    }

    public function down(): void
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->dropColumn(['feature_key', 'feature_label', 'feature_value']);
        });
    }
};
