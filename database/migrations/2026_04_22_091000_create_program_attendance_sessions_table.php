<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('center_id')->nullable()->index();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('attendance_date')->index();
            $table->string('instructor_name')->nullable();
            $table->string('topic')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedInteger('present_count')->default(0);
            $table->unsignedInteger('absent_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_attendance_sessions');
    }
};
