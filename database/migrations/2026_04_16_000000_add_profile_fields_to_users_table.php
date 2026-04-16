<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('username');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'account_status')) {
                $table->string('account_status')->default('active')->after('role');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->nullable()->after('account_status');
            }
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language')->nullable()->after('timezone');
            }
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()->after('language');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [
                'first_name',
                'last_name',
                'phone',
                'avatar_path',
                'account_status',
                'timezone',
                'language',
                'job_title',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

