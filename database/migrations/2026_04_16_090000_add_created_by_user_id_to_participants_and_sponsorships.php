<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->after('center_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('participant_sponsorships', function (Blueprint $table) {
            if (!Schema::hasColumn('participant_sponsorships', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->after('participant_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('participant_sponsorships', function (Blueprint $table) {
            if (Schema::hasColumn('participant_sponsorships', 'created_by_user_id')) {
                $table->dropConstrainedForeignId('created_by_user_id');
            }
        });

        Schema::table('participants', function (Blueprint $table) {
            if (Schema::hasColumn('participants', 'created_by_user_id')) {
                $table->dropConstrainedForeignId('created_by_user_id');
            }
        });
    }
};
