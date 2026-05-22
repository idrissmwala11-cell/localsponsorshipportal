<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_attendance_sessions', function (Blueprint $table) {
            $table->json('activity_photo_paths')->nullable()->after('activity_photo_caption');
            $table->json('activity_photo_captions')->nullable()->after('activity_photo_paths');
        });
    }

    public function down(): void
    {
        Schema::table('program_attendance_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'activity_photo_paths',
                'activity_photo_captions',
            ]);
        });
    }
};
