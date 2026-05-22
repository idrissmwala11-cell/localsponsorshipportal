<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (!Schema::hasColumn('participants', 'secondary_english_score')) {
                $table->decimal('secondary_english_score', 5, 2)->nullable()->after('primary_social_studies_score');
            }
            if (!Schema::hasColumn('participants', 'secondary_mathematics_score')) {
                $table->decimal('secondary_mathematics_score', 5, 2)->nullable()->after('secondary_english_score');
            }
            if (!Schema::hasColumn('participants', 'secondary_biology_score')) {
                $table->decimal('secondary_biology_score', 5, 2)->nullable()->after('secondary_mathematics_score');
            }
            if (!Schema::hasColumn('participants', 'secondary_chemistry_score')) {
                $table->decimal('secondary_chemistry_score', 5, 2)->nullable()->after('secondary_biology_score');
            }
            if (!Schema::hasColumn('participants', 'secondary_physics_score')) {
                $table->decimal('secondary_physics_score', 5, 2)->nullable()->after('secondary_chemistry_score');
            }
            if (!Schema::hasColumn('participants', 'secondary_average_score')) {
                $table->decimal('secondary_average_score', 5, 2)->nullable()->after('secondary_physics_score');
            }
            if (!Schema::hasColumn('participants', 'university_semester_one_gpa')) {
                $table->decimal('university_semester_one_gpa', 4, 2)->nullable()->after('secondary_average_score');
            }
            if (!Schema::hasColumn('participants', 'university_semester_two_gpa')) {
                $table->decimal('university_semester_two_gpa', 4, 2)->nullable()->after('university_semester_one_gpa');
            }
            if (!Schema::hasColumn('participants', 'university_semester_three_gpa')) {
                $table->decimal('university_semester_three_gpa', 4, 2)->nullable()->after('university_semester_two_gpa');
            }
            if (!Schema::hasColumn('participants', 'university_semester_four_gpa')) {
                $table->decimal('university_semester_four_gpa', 4, 2)->nullable()->after('university_semester_three_gpa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $columns = [
                'secondary_english_score',
                'secondary_mathematics_score',
                'secondary_biology_score',
                'secondary_chemistry_score',
                'secondary_physics_score',
                'secondary_average_score',
                'university_semester_one_gpa',
                'university_semester_two_gpa',
                'university_semester_three_gpa',
                'university_semester_four_gpa',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('participants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
