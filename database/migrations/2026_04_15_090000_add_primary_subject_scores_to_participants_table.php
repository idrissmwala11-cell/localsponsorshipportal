<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $columns = [
                'primary_kiswahili_score' => fn () => $table->decimal('primary_kiswahili_score', 5, 2)->nullable(),
                'primary_english_score' => fn () => $table->decimal('primary_english_score', 5, 2)->nullable(),
                'primary_mathematics_score' => fn () => $table->decimal('primary_mathematics_score', 5, 2)->nullable(),
                'primary_science_score' => fn () => $table->decimal('primary_science_score', 5, 2)->nullable(),
                'primary_social_studies_score' => fn () => $table->decimal('primary_social_studies_score', 5, 2)->nullable(),
            ];

            foreach ($columns as $column => $callback) {
                if (!Schema::hasColumn('participants', $column)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            foreach ([
                'primary_kiswahili_score',
                'primary_english_score',
                'primary_mathematics_score',
                'primary_science_score',
                'primary_social_studies_score',
            ] as $column) {
                if (Schema::hasColumn('participants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
