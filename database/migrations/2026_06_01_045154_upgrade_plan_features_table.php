<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plan_features', function (Blueprint $table) {
            if (!Schema::hasColumn('plan_features', 'feature_key')) {
                $table->string('feature_key')->after('pricing_plan_id')->default('');
            }
            if (!Schema::hasColumn('plan_features', 'feature_label')) {
                $table->string('feature_label')->after('feature_key')->default('');
            }
            if (!Schema::hasColumn('plan_features', 'feature_value')) {
                $table->string('feature_value')->after('feature_label')->default('');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plan_features', function (Blueprint $table) {
            $table->dropColumn(['feature_key', 'feature_label', 'feature_value']);
        });
    }
};
