<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_sponsorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->string('funding_type')->nullable();
            $table->string('sponsorship_status')->nullable();
            $table->string('sponsored_by')->nullable();
            $table->date('sponsorship_start_date')->nullable();
            $table->string('sponsorship_category')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_sponsorships');
    }
};