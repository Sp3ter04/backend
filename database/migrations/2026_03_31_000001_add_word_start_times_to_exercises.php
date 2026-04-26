<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exercises') && !Schema::hasColumn('exercises', 'word_start_times')) {
            Schema::table('exercises', function (Blueprint $table) {
                $table->jsonb('word_start_times')->nullable()->after('word_timestamps');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exercises', 'word_start_times')) {
            Schema::table('exercises', function (Blueprint $table) {
                $table->dropColumn('word_start_times');
            });
        }
    }
};
