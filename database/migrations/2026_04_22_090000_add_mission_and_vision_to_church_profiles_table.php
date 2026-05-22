<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('church_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('church_profiles', 'mission')) {
                $table->text('mission')->nullable()->after('historical_background');
            }

            if (!Schema::hasColumn('church_profiles', 'vision')) {
                $table->text('vision')->nullable()->after('mission');
            }
        });
    }

    public function down(): void
    {
        Schema::table('church_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('church_profiles', 'vision')) {
                $table->dropColumn('vision');
            }

            if (Schema::hasColumn('church_profiles', 'mission')) {
                $table->dropColumn('mission');
            }
        });
    }
};
