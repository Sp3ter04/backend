<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exercises') && !Schema::hasColumn('exercises', 'word_timestamps')) {
            Schema::table('exercises', function (Blueprint $table) {
                $table->jsonb('word_timestamps')->nullable()->after('words_json');
            });
        }

        if (Schema::hasTable('words') && !Schema::hasColumn('words', 'word_timestamps')) {
            Schema::table('words', function (Blueprint $table) {
                $table->jsonb('word_timestamps')->nullable()->after('audio_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exercises') && Schema::hasColumn('exercises', 'word_timestamps')) {
            Schema::table('exercises', function (Blueprint $table) {
                $table->dropColumn('word_timestamps');
            });
        }

        if (Schema::hasTable('words') && Schema::hasColumn('words', 'word_timestamps')) {
            Schema::table('words', function (Blueprint $table) {
                $table->dropColumn('word_timestamps');
            });
        }
    }
};
