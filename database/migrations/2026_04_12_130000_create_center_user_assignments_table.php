<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('center_user_assignments')) {
            return;
        }

        Schema::create('center_user_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('center_id');
            $table->timestamps();

            $table->unique(['user_id', 'center_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_user_assignments');
    }
};
