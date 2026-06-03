<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // e.g. 'create_project', 'delete_project', 'create_estimation',
            //       'delete_estimation', 'create_room', 'delete_room'
            $table->string('event');

            // Human-readable description, e.g. "membuat project estimasi \"Rumah Socrates\""
            $table->string('description');

            // Optional reference name for the subject
            $table->string('subject_name')->nullable();

            // 'New' | 'Done' | 'Deleted'
            $table->string('status')->default('Done');

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
