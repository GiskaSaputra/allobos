<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_field_id')->nullable()->constrained()->nullOnDelete();
            $table->string('whatsapp', 20);
            $table->string('cover_path')->nullable();
            $table->string('status', 20)->index();
            $table->string('reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->unsignedInteger('price');
            $table->string('unit', 30)->default('bungkus');
            $table->text('description')->nullable();
            $table->boolean('in_stock')->default(true);
            $table->boolean('hidden')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->foreignId('product_check_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shop_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 10);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['shop_id', 'kind']);
        });

        Schema::create('shop_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->text('reason');
            $table->string('ip', 45)->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['shop_reports', 'shop_visits', 'products', 'shops'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
