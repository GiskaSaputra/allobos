<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('status', 20);
            $table->string('name')->nullable();
            $table->json('result')->nullable();
            $table->string('error')->nullable();
            $table->timestamps();
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('batch')->index();
            $table->string('title');
            $table->text('why');
            $table->string('capital_range');
            $table->text('risk');
            $table->string('ipi_field')->nullable();
            $table->boolean('is_ai')->default(true);
            $table->boolean('is_custom')->default(false);
            $table->timestamp('chosen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('product')->nullable();
            $table->text('customers')->nullable();
            $table->text('channels')->nullable();
            $table->text('rivals')->nullable();
            $table->json('capital')->nullable();
            $table->json('risks')->nullable();
            $table->unsignedInteger('batch_units')->default(0);
            $table->unsignedInteger('selling_price')->default(0);
            $table->boolean('is_ai')->default(false);
            $table->timestamp('built_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_plan_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('kind', 10);
            $table->unsignedInteger('amount');
            $table->timestamps();
        });

        Schema::create('plan_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week')->default(1);
            $table->string('title');
            $table->string('origin', 20);
            $table->timestamp('done_at')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['plan_steps', 'cost_items', 'business_plans', 'opportunities', 'product_checks'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
