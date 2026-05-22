<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('center_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('center_notifications', 'sent_by_user_id')) {
                $table->foreignId('sent_by_user_id')->nullable()->after('participant_id')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('center_notifications', 'is_manual')) {
                $table->boolean('is_manual')->default(true)->after('meta');
            }
        });
    }

    public function down(): void
    {
        Schema::table('center_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('center_notifications', 'sent_by_user_id')) {
                $table->dropConstrainedForeignId('sent_by_user_id');
            }

            if (Schema::hasColumn('center_notifications', 'is_manual')) {
                $table->dropColumn('is_manual');
            }
        });
    }
};
