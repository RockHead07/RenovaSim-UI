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
        Schema::table('estimations', function (Blueprint $table) {
            $table->string('cost_display')->nullable()->change();
            $table->string('job_type')->nullable()->change();
            $table->string('location')->nullable()->change();
            $table->string('quality')->nullable()->change();
            $table->string('label')->nullable()->change();
            $table->string('mode')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
