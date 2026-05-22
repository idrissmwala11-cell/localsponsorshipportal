<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('center_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('center_notifications', 'target_user_id')) {
                $table->foreignId('target_user_id')->nullable()->after('sent_by_user_id')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('center_notifications', 'sent_to_all_users')) {
                $table->boolean('sent_to_all_users')->default(false)->after('is_manual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('center_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('center_notifications', 'target_user_id')) {
                $table->dropConstrainedForeignId('target_user_id');
            }

            if (Schema::hasColumn('center_notifications', 'sent_to_all_users')) {
                $table->dropColumn('sent_to_all_users');
            }
        });
    }
};
