<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_sessions', function (Blueprint $table) {
            $table->timestamp('reminded_at')->nullable()->after('attendance_code');
        });
    }

    public function down(): void
    {
        Schema::table('program_sessions', fn (Blueprint $table) => $table->dropColumn('reminded_at'));
    }
};
