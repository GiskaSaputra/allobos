<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('batches_per_month')->default(8)->after('batch_units');
        });
    }

    public function down(): void
    {
        Schema::table('business_plans', fn (Blueprint $table) => $table->dropColumn('batches_per_month'));
    }
};
