<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove old fields first if they exist
        Schema::table('participants', function (Blueprint $table) {
            if (Schema::hasColumn('participants', 'funding_type')) {
                $table->dropColumn('funding_type');
            }

            if (Schema::hasColumn('participants', 'program_type')) {
                $table->dropColumn('program_type');
            }
        });

        // Add new fields one by one only if missing
        Schema::table('participants', function (Blueprint $table) {
            // C. FCP Association
            if (!Schema::hasColumn('participants', 'cluster')) {
                $table->string('cluster')->nullable();
            }

            if (!Schema::hasColumn('participants', 'fcp_name')) {
                $table->string('fcp_name')->nullable();
            }

            if (!Schema::hasColumn('participants', 'partnership_facilitator')) {
                $table->string('partnership_facilitator')->nullable();
            }

            if (!Schema::hasColumn('participants', 'national_office_community_name')) {
                $table->string('national_office_community_name')->nullable();
            }

            if (!Schema::hasColumn('participants', 'attending_location')) {
                $table->string('attending_location')->nullable();
            }

            // D. Significant Dates
            if (!Schema::hasColumn('participants', 'planned_completion_date')) {
                $table->date('planned_completion_date')->nullable();
            }

            if (!Schema::hasColumn('participants', 'transition_date')) {
                $table->date('transition_date')->nullable();
            }

            // E. Address Information
            if (!Schema::hasColumn('participants', 'address')) {
                $table->text('address')->nullable();
            }

            if (!Schema::hasColumn('participants', 'gps_location')) {
                $table->string('gps_location')->nullable();
            }

            // F. Participant Favorites
            if (!Schema::hasColumn('participants', 'things_i_like')) {
                $table->text('things_i_like')->nullable();
            }

            if (!Schema::hasColumn('participants', 'favorite_activities')) {
                $table->text('favorite_activities')->nullable();
            }

            if (!Schema::hasColumn('participants', 'household_duties')) {
                $table->text('household_duties')->nullable();
            }

            if (!Schema::hasColumn('participants', 'favorite_subjects')) {
                $table->text('favorite_subjects')->nullable();
            }

            // G. Education Information
            if (!Schema::hasColumn('participants', 'country')) {
                $table->string('country')->nullable();
            }

            if (!Schema::hasColumn('participants', 'grade_level')) {
                $table->string('grade_level')->nullable();
            }

            if (!Schema::hasColumn('participants', 'school_performance')) {
                $table->string('school_performance')->nullable();
            }

            if (!Schema::hasColumn('participants', 'course_of_study')) {
                $table->string('course_of_study')->nullable();
            }

            if (!Schema::hasColumn('participants', 'vocational_training')) {
                $table->string('vocational_training')->nullable();
            }

            // H. Spiritual Information
            if (!Schema::hasColumn('participants', 'religious_affiliation')) {
                $table->string('religious_affiliation')->nullable();
            }

            if (!Schema::hasColumn('participants', 'bible_distributed_date')) {
                $table->date('bible_distributed_date')->nullable();
            }

            if (!Schema::hasColumn('participants', 'faith_confession_date')) {
                $table->date('faith_confession_date')->nullable();
            }

            if (!Schema::hasColumn('participants', 'christian_activities')) {
                $table->text('christian_activities')->nullable();
            }

            // I. Medical Information
            if (!Schema::hasColumn('participants', 'weight')) {
                $table->string('weight')->nullable();
            }

            if (!Schema::hasColumn('participants', 'height')) {
                $table->string('height')->nullable();
            }

            if (!Schema::hasColumn('participants', 'disabilities')) {
                $table->text('disabilities')->nullable();
            }

            if (!Schema::hasColumn('participants', 'chronic_illnesses')) {
                $table->text('chronic_illnesses')->nullable();
            }

            if (!Schema::hasColumn('participants', 'treatment')) {
                $table->text('treatment')->nullable();
            }

            if (!Schema::hasColumn('participants', 'health_comments')) {
                $table->text('health_comments')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $columnsToDrop = [
                'cluster',
                'fcp_name',
                'partnership_facilitator',
                'national_office_community_name',
                'attending_location',
                'planned_completion_date',
                'transition_date',
                'address',
                'gps_location',
                'things_i_like',
                'favorite_activities',
                'household_duties',
                'favorite_subjects',
                'country',
                'grade_level',
                'school_performance',
                'course_of_study',
                'vocational_training',
                'religious_affiliation',
                'bible_distributed_date',
                'faith_confession_date',
                'christian_activities',
                'weight',
                'height',
                'disabilities',
                'chronic_illnesses',
                'treatment',
                'health_comments',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('participants', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (!Schema::hasColumn('participants', 'funding_type')) {
                $table->string('funding_type')->nullable();
            }

            if (!Schema::hasColumn('participants', 'program_type')) {
                $table->string('program_type')->nullable();
            }
        });
    }
};