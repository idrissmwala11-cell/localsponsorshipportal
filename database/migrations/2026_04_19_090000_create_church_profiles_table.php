<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('church_profiles')) {
            Schema::create('church_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('center_id')->nullable()->index();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('church_name')->nullable();
                $table->longText('historical_background')->nullable();
                $table->json('photo_paths')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('church_profiles');
    }
};
