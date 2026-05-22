<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('weight')->nullable()->change();
            $table->string('height')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->decimal('weight', 8, 2)->nullable()->change();
            $table->decimal('height', 8, 2)->nullable()->change();
        });
    }
};
