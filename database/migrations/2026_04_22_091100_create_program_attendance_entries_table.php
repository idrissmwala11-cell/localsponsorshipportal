<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_attendance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_attendance_session_id')->constrained('program_attendance_sessions')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->boolean('is_present')->default(false);
            $table->timestamps();

            $table->unique(['program_attendance_session_id', 'participant_id'], 'program_attendance_session_participant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_attendance_entries');
    }
};
