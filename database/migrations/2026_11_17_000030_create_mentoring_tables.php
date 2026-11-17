<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boss_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->text('business_desc');
            $table->foreignId('business_field_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('years_running')->default(0);
            $table->text('track_record')->nullable();
            $table->string('trace_url')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 20)->index();
            $table->json('checklist')->nullable();
            $table->string('reject_reason')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('boss_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boss_profile_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at')->index();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('mode', 12);
            $table->timestamps();
        });

        Schema::create('mentoring_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boss_slot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boss_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('topic');
            $table->foreignId('consult_note_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->index();
            $table->string('meeting_link')->nullable();
            $table->string('address')->nullable();
            $table->string('reason')->nullable();
            $table->text('boss_notes')->nullable();
            $table->json('boss_steps')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('rating_comment')->nullable();
            $table->timestamp('reminded_day_at')->nullable();
            $table->timestamp('reminded_hour_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mentoring_sessions');
        Schema::dropIfExists('boss_slots');
        Schema::dropIfExists('boss_profiles');
    }
};
