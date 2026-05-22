<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('center_notification_reads')) {
            Schema::create('center_notification_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('center_notification_id')->constrained('center_notifications')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('read_at')->nullable();

                $table->unique(['center_notification_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('center_notification_reads');
    }
};
