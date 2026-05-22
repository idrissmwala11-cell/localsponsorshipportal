<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'cluster_name')) {
                $table->string('cluster_name')->nullable()->after('center_id');
            }
        });

        if (!Schema::hasTable('admin_cluster_assignments')) {
            Schema::create('admin_cluster_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('cluster_name');
                $table->timestamps();

                $table->unique(['admin_user_id', 'cluster_name'], 'admin_cluster_assignment_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_cluster_assignments');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'cluster_name')) {
                $table->dropColumn('cluster_name');
            }
        });
    }
};
