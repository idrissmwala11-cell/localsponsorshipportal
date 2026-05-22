<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'caregiver_name')) {
                $table->string('caregiver_name')->nullable()->after('parent_guardian_phone');
            }

            if (!Schema::hasColumn('participants', 'father_status')) {
                $table->string('father_status')->nullable()->after('caregiver_name');
            }

            if (!Schema::hasColumn('participants', 'mother_status')) {
                $table->string('mother_status')->nullable()->after('father_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (Schema::hasColumn('participants', 'mother_status')) {
                $table->dropColumn('mother_status');
            }

            if (Schema::hasColumn('participants', 'father_status')) {
                $table->dropColumn('father_status');
            }

            if (Schema::hasColumn('participants', 'caregiver_name')) {
                $table->dropColumn('caregiver_name');
            }
        });
    }
};
