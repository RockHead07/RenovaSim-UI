<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'pricing_plan_id')) {
                $table->foreignId('pricing_plan_id')
                    ->nullable()
                    ->after('plan')
                    ->constrained('pricing_plans')
                    ->nullOnDelete();

                $table->index('pricing_plan_id', 'users_pricing_plan_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pricing_plan_id')) {
                $table->dropIndex('users_pricing_plan_id_index');
                $table->dropConstrainedForeignId('pricing_plan_id');
            }
        });
    }
};

