<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('created_by_user_id');
            $table->string('center_id')->index();
            $table->text('treatment')->nullable();
            $table->date('treatment_date')->nullable()->index();
            $table->text('tested_diseases')->nullable();
            $table->string('illness_type')->nullable();
            $table->string('treatment_location')->nullable();
            $table->decimal('treatment_cost', 12, 2)->nullable();
            $table->text('health_comments')->nullable();
            $table->timestamps();

            $table->index(['center_id', 'created_by_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_treatments');
    }
};
