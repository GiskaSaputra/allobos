<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->unsignedInteger('seed');
            $table->string('status', 20)->index();
            $table->unsignedTinyInteger('day')->default(1);
            $table->string('phase', 20);
            $table->json('state');
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('ending', 20)->nullable();
            $table->json('debrief')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_game_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day');
            $table->json('decisions');
            $table->json('event');
            $table->json('result');
            $table->timestamps();
            $table->unique(['shop_game_id', 'day']);
        });

        Schema::create('shop_event_bank', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day')->nullable();
            $table->string('kind', 20)->nullable();
            $table->string('title');
            $table->text('story');
            $table->decimal('demand', 4, 2)->default(1);
            $table->decimal('cost', 4, 2)->default(1);
            $table->integer('cash')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_event_bank');
        Schema::dropIfExists('shop_days');
        Schema::dropIfExists('shop_games');
    }
};
