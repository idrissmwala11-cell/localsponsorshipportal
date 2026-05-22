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
                'physical_address' => fn () => $table->text('physical_address')->nullable(),
                'house_number' => fn () => $table->string('house_number')->nullable(),
                'region_city_street' => fn () => $table->string('region_city_street')->nullable(),
                'parent_guardian_name' => fn () => $table->string('parent_guardian_name')->nullable(),
                'parent_guardian_occupation' => fn () => $table->string('parent_guardian_occupation')->nullable(),
                'parent_guardian_phone' => fn () => $table->string('parent_guardian_phone')->nullable(),
                'household_name' => fn () => $table->string('household_name')->nullable(),
                'school_name' => fn () => $table->string('school_name')->nullable(),
                'current_class' => fn () => $table->string('current_class')->nullable(),
                'education_stage' => fn () => $table->string('education_stage')->nullable(),
                'education_grade' => fn () => $table->string('education_grade')->nullable(),
                'primary_score' => fn () => $table->decimal('primary_score', 5, 2)->nullable(),
                'o_level_score' => fn () => $table->decimal('o_level_score', 5, 2)->nullable(),
                'a_level_score' => fn () => $table->decimal('a_level_score', 5, 2)->nullable(),
                'college_score' => fn () => $table->decimal('college_score', 5, 2)->nullable(),
                'university_gpa' => fn () => $table->decimal('university_gpa', 4, 2)->nullable(),
                'is_in_school' => fn () => $table->boolean('is_in_school')->default(true),
                'not_in_school_reason' => fn () => $table->text('not_in_school_reason')->nullable(),
                'hobbies' => fn () => $table->text('hobbies')->nullable(),
                'vision_for_tomorrow' => fn () => $table->text('vision_for_tomorrow')->nullable(),
                'planned_exit_type' => fn () => $table->string('planned_exit_type')->nullable(),
                'planned_exit_reason' => fn () => $table->text('planned_exit_reason')->nullable(),
                'unplanned_exit_lessons' => fn () => $table->text('unplanned_exit_lessons')->nullable(),
                'treatment_date' => fn () => $table->date('treatment_date')->nullable(),
                'tested_diseases' => fn () => $table->text('tested_diseases')->nullable(),
                'illness_type' => fn () => $table->string('illness_type')->nullable(),
                'treatment_location' => fn () => $table->string('treatment_location')->nullable(),
                'treatment_cost' => fn () => $table->decimal('treatment_cost', 10, 2)->nullable(),
                'general_assessment_social' => fn () => $table->text('general_assessment_social')->nullable(),
                'general_assessment_physical' => fn () => $table->text('general_assessment_physical')->nullable(),
                'general_assessment_emotional' => fn () => $table->text('general_assessment_emotional')->nullable(),
                'general_assessment_spiritual' => fn () => $table->text('general_assessment_spiritual')->nullable(),
                'baptism_status' => fn () => $table->string('baptism_status')->nullable(),
            ];

            foreach ($columns as $column => $callback) {
                if (!Schema::hasColumn('participants', $column)) {
                    $callback();
                }
            }
        });

        Schema::table('participant_sponsorships', function (Blueprint $table) {
            $columns = [
                'sponsor_name' => fn () => $table->string('sponsor_name')->nullable(),
                'sponsor_type' => fn () => $table->string('sponsor_type')->nullable(),
                'sponsorship_type' => fn () => $table->string('sponsorship_type')->nullable(),
                'sponsor_physical_address' => fn () => $table->text('sponsor_physical_address')->nullable(),
                'sponsor_contact' => fn () => $table->string('sponsor_contact')->nullable(),
            ];

            foreach ($columns as $column => $callback) {
                if (!Schema::hasColumn('participant_sponsorships', $column)) {
                    $callback();
                }
            }
        });

        Schema::table('participants', function (Blueprint $table) {
            $columns = [
                'sponsor_type' => fn () => $table->string('sponsor_type')->nullable(),
                'sponsorship_type' => fn () => $table->string('sponsorship_type')->nullable(),
                'sponsor_physical_address' => fn () => $table->text('sponsor_physical_address')->nullable(),
                'sponsor_contact' => fn () => $table->string('sponsor_contact')->nullable(),
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
                'physical_address',
                'house_number',
                'region_city_street',
                'parent_guardian_name',
                'parent_guardian_occupation',
                'parent_guardian_phone',
                'household_name',
                'school_name',
                'current_class',
                'education_stage',
                'education_grade',
                'primary_score',
                'o_level_score',
                'a_level_score',
                'college_score',
                'university_gpa',
                'is_in_school',
                'not_in_school_reason',
                'hobbies',
                'vision_for_tomorrow',
                'planned_exit_type',
                'planned_exit_reason',
                'unplanned_exit_lessons',
                'treatment_date',
                'tested_diseases',
                'illness_type',
                'treatment_location',
                'treatment_cost',
                'general_assessment_social',
                'general_assessment_physical',
                'general_assessment_emotional',
                'general_assessment_spiritual',
                'baptism_status',
                'sponsor_type',
                'sponsorship_type',
                'sponsor_physical_address',
                'sponsor_contact',
            ] as $column) {
                if (Schema::hasColumn('participants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('participant_sponsorships', function (Blueprint $table) {
            foreach ([
                'sponsor_name',
                'sponsor_type',
                'sponsorship_type',
                'sponsor_physical_address',
                'sponsor_contact',
            ] as $column) {
                if (Schema::hasColumn('participant_sponsorships', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
