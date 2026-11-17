<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('speakers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business');
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('kind', 20);
            $table->foreignId('business_field_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->string('location');
            $table->unsignedInteger('quota');
            $table->string('status', 20)->index();
            $table->json('materials')->nullable();
            $table->boolean('is_official')->default(false);
            $table->boolean('is_demo')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('program_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('attendance_code', 12)->nullable();
            $table->timestamps();
        });

        Schema::create('program_speaker', function (Blueprint $table) {
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speaker_id')->constrained()->cascadeOnDelete();
            $table->primary(['program_id', 'speaker_id']);
        });

        Schema::create('program_signers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedTinyInteger('sort')->default(1);
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_ip', 45)->nullable();
            $table->string('wallet_address', 42)->nullable();
            $table->text('wallet_signature')->nullable();
            $table->timestamps();
            $table->unique(['program_id', 'user_id']);
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->json('materials_opened')->nullable();
            $table->timestamps();
            $table->unique(['program_id', 'user_id']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['program_session_id', 'user_id']);
        });
    }

    public function down(): void
    {
        foreach (['attendances', 'enrollments', 'program_signers', 'program_speaker', 'program_sessions', 'programs', 'speakers'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
