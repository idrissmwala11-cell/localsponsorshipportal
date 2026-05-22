<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_attendance_sessions', function (Blueprint $table) {
            $table->string('activity_name')->nullable()->after('attendance_date');
            $table->string('activity_photo_path')->nullable()->after('activity_name');
            $table->string('activity_photo_caption')->nullable()->after('activity_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('program_attendance_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'activity_name',
                'activity_photo_path',
                'activity_photo_caption',
            ]);
        });
    }
};
