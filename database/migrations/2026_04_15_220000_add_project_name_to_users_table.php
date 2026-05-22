<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'project_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('project_name')->default('compassion')->after('job_title');
            });
        }

        if (Schema::hasColumn('users', 'project_name')) {
            DB::table('users')->whereNull('project_name')->update([
                'project_name' => 'compassion',
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'project_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('project_name');
            });
        }
    }
};
