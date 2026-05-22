<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('program_type');
            $table->timestamp('photo_updated_at')->nullable()->after('photo');
            $table->date('next_photo_update_due_at')->nullable()->after('photo_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn([
                'photo',
                'photo_updated_at',
                'next_photo_update_due_at',
            ]);
        });
    }
};