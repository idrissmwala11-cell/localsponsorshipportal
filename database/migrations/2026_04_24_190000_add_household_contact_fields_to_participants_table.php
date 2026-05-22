<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'household_phone')) {
                $table->string('household_phone')->nullable()->after('household_name');
            }

            if (!Schema::hasColumn('participants', 'household_relationship')) {
                $table->string('household_relationship')->nullable()->after('household_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (Schema::hasColumn('participants', 'household_relationship')) {
                $table->dropColumn('household_relationship');
            }

            if (Schema::hasColumn('participants', 'household_phone')) {
                $table->dropColumn('household_phone');
            }
        });
    }
};
