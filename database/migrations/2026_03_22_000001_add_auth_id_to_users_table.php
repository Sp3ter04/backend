<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'auth_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('auth_id')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'auth_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['auth_id']);
            $table->dropColumn('auth_id');
        });
    }
};
