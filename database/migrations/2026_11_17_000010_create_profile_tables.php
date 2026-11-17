<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('potential');
        });

        Schema::create('business_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('kind', 20);
            $table->string('keywords')->default('');
            $table->unsignedSmallInteger('sort')->default(0);
        });

        Schema::create('resident_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_status', 10)->nullable();
            $table->string('existing_business')->nullable();
            $table->json('interests')->nullable();
            $table->string('capital_range', 20)->nullable();
            $table->string('free_time', 20)->nullable();
            $table->text('skills')->nullable();
            $table->unsignedTinyInteger('step')->default(1);
            $table->timestamp('onboarded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resident_profiles');
        Schema::dropIfExists('business_fields');
        Schema::dropIfExists('districts');
    }
};
