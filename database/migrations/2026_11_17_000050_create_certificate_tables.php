<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chain_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10);
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('chain_name', 60);
            $table->string('tx_hash', 66)->nullable();
            $table->unsignedBigInteger('block_number')->nullable();
            $table->string('status', 20);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->index();
            $table->string('recipient_name');
            $table->date('issued_on')->nullable();
            $table->string('hash', 66)->nullable()->index();
            $table->string('pdf_path')->nullable();
            $table->foreignId('chain_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contract_address', 42)->nullable();
            $table->boolean('show_full_name')->default(true);
            $table->boolean('show_on_shop')->default(false);
            $table->string('reason')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoke_transaction_id')->nullable()->constrained('chain_transactions')->nullOnDelete();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
            $table->unique(['program_id', 'user_id']);
        });

        Schema::create('signature_specimens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_specimens');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('chain_transactions');
    }
};
