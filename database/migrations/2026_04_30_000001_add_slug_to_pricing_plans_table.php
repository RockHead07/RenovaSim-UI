<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            if (! Schema::hasColumn('pricing_plans', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });

        // Backfill slugs safely (idempotent).
        // - Use slugified name
        // - Ensure uniqueness by appending "-{n}" on conflicts
        // - Keep existing slug if already set
        DB::transaction(function (): void {
            $plans = DB::table('pricing_plans')
                ->select(['id', 'name', 'slug'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $existing = DB::table('pricing_plans')
                ->whereNotNull('slug')
                ->pluck('slug')
                ->map(fn ($s) => (string) $s)
                ->all();

            $used = array_fill_keys($existing, true);

            foreach ($plans as $plan) {
                if (is_string($plan->slug) && trim($plan->slug) !== '') {
                    $used[$plan->slug] = true;
                    continue;
                }

                $base = Str::slug((string) $plan->name);
                $base = $base !== '' ? $base : 'plan';

                $candidate = $base;
                $i = 2;
                while (isset($used[$candidate])) {
                    $candidate = "{$base}-{$i}";
                    $i++;
                }

                DB::table('pricing_plans')
                    ->where('id', $plan->id)
                    ->update(['slug' => $candidate]);

                $used[$candidate] = true;
            }
        });

        Schema::table('pricing_plans', function (Blueprint $table) {
            // Add constraint after backfill so it won't fail on existing data.
            $table->unique('slug', 'pricing_plans_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            if (Schema::hasColumn('pricing_plans', 'slug')) {
                $table->dropUnique('pricing_plans_slug_unique');
                $table->dropColumn('slug');
            }
        });
    }
};

