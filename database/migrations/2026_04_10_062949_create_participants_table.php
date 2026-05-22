<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();

            $table->string('center_id');
            $table->string('local_participant_number')->nullable();
            $table->string('local_participant_id')->unique();
            $table->string('account_name');
            $table->string('preferred_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('birthdate')->nullable();

            $table->string('participant_status')->default('Active');
            $table->string('sponsorship_status')->nullable();
            $table->string('funding_type')->nullable();

            $table->string('program_type')->nullable();
            $table->string('age_group')->nullable();
            $table->string('household')->nullable();
            $table->string('correspondence_language')->nullable();
            $table->string('citizenship')->nullable();

            $table->string('fcp_id')->nullable();
            $table->string('cluster')->nullable();
            $table->string('fcp_name')->nullable();
            $table->string('partnership_facilitator')->nullable();
            $table->string('community_name')->nullable();
            $table->string('attending_location')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            $table->string('school_level')->nullable();
            $table->string('school_performance')->nullable();
            $table->string('religious_affiliation')->nullable();

            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->text('health_comments')->nullable();

            $table->string('photo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};