<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'sponsored_by')) {
                $table->string('sponsored_by')->nullable();
            }

            if (!Schema::hasColumn('participants', 'sponsorship_start_date')) {
                $table->date('sponsorship_start_date')->nullable();
            }

            if (!Schema::hasColumn('participants', 'sponsorship_category')) {
                $table->string('sponsorship_category')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            foreach (['sponsored_by', 'sponsorship_start_date', 'sponsorship_category'] as $column) {
                if (Schema::hasColumn('participants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
