<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_attendance_sessions', function (Blueprint $table) {
            $table->string('attendance_type')->default('program')->after('created_by_user_id');
        });

        DB::table('program_attendance_sessions')
            ->whereNull('attendance_type')
            ->orWhere('attendance_type', '')
            ->update(['attendance_type' => 'program']);
    }

    public function down(): void
    {
        Schema::table('program_attendance_sessions', function (Blueprint $table) {
            $table->dropColumn('attendance_type');
        });
    }
};
