<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('youtube_id', 20);
            $table->string('thumbnail_url')->nullable();
            $table->foreignId('business_field_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->text('source_notes')->nullable();
            $table->text('summary')->nullable();
            $table->json('lessons')->nullable();
            $table->boolean('summary_is_ai')->default(false);
            $table->string('summary_state', 20)->default('kosong');
            $table->string('status', 20)->index();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('podcast_listens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('podcast_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('listened_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['podcast_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podcast_listens');
        Schema::dropIfExists('podcasts');
    }
};
