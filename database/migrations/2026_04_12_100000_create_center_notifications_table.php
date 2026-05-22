<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('center_notifications')) {
            Schema::create('center_notifications', function (Blueprint $table) {
                $table->id();
                $table->string('center_id')->index();
                $table->foreignId('participant_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type')->index();
                $table->string('title');
                $table->text('message');
                $table->string('event_key')->unique();
                $table->date('due_date')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('center_notifications');
    }
};
