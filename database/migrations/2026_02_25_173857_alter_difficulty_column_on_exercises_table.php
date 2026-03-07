<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if exercises table exists
        if (!Schema::hasTable('exercises')) {
            return;
        }

        // For SQLite, we need to check the driver
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            // PostgreSQL specific syntax
            DB::statement(<<<SQL
                ALTER TABLE exercises
                ALTER COLUMN difficulty TYPE varchar(255) USING difficulty::text
            SQL);
        } else {
            // For SQLite and other databases, use Schema Builder
            // SQLite doesn't support ALTER COLUMN, so we'll skip it if the column already exists as text
            if (Schema::hasColumn('exercises', 'difficulty')) {
                // Column exists, SQLite can't alter it easily, so we skip
                // In production, you'd need to recreate the table
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            // Revert back to text type
            DB::statement(<<<SQL
                ALTER TABLE exercises
                ALTER COLUMN difficulty TYPE text USING CASE 
                    WHEN difficulty = 'easy' THEN 'easy'
                    WHEN difficulty = 'medium' THEN 'medium'
                    WHEN difficulty = 'hard' THEN 'hard'
                    ELSE 'easy'
                END
            SQL);
        }
    }
};
