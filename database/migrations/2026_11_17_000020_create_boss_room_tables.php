<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boss_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic', 160);
            $table->string('mode', 10)->default('suara');
            $table->string('status', 20)->index();
            $table->unsignedSmallInteger('turns_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('boss_turns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boss_session_id')->constrained()->cascadeOnDelete();
            $table->string('role', 10);
            $table->text('content');
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('consult_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boss_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('problem');
            $table->json('advice');
            $table->json('next_steps');
            $table->json('modules');
            $table->boolean('is_ai')->default(true);
            $table->timestamp('steps_added_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consult_notes');
        Schema::dropIfExists('boss_turns');
        Schema::dropIfExists('boss_sessions');
    }
};
