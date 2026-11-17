<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // 0 berarti kelas gratis.
            $table->unsignedInteger('price')->default(0)->after('quota');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->string('order_id', 40)->unique();
            $table->string('provider', 20)->default('sumopod');
            $table->string('provider_payment_id', 64)->nullable()->index();
            $table->unsignedInteger('amount');
            $table->unsignedInteger('fee')->nullable();
            $table->string('status', 20)->index();
            $table->string('payment_url', 500)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('last_event')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::table('programs', fn (Blueprint $table) => $table->dropColumn('price'));
    }
};
