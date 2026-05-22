<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'admin_onboarded_at')) {
                $table->timestamp('admin_onboarded_at')->nullable()->after('approved_by');
            }
        });

        if (!Schema::hasTable('admin_user_supervisions')) {
            Schema::create('admin_user_supervisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('supervised_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['admin_user_id', 'supervised_user_id'], 'admin_supervision_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_user_supervisions');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'admin_onboarded_at')) {
                $table->dropColumn('admin_onboarded_at');
            }
        });
    }
};
