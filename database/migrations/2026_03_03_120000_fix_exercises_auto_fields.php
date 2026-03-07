<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Para PostgreSQL (Supabase)
        if (DB::getDriverName() === 'pgsql') {
            // Configurar default para UUID
            DB::statement('ALTER TABLE exercises ALTER COLUMN id SET DEFAULT gen_random_uuid()');
            
            // Configurar sequence para number
            DB::statement('CREATE SEQUENCE IF NOT EXISTS exercises_number_seq START WITH 13');
            DB::statement('ALTER TABLE exercises ALTER COLUMN number SET DEFAULT nextval(\'exercises_number_seq\')');
            DB::statement('ALTER TABLE exercises ALTER COLUMN number SET NOT NULL');
        }
        
        // Para SQLite (desenvolvimento local)
        if (DB::getDriverName() === 'sqlite') {
            // SQLite não suporta ALTER COLUMN DEFAULT de forma direta
            // Precisamos recriar a tabela
            DB::statement('
                CREATE TABLE exercises_new (
                    id TEXT PRIMARY KEY DEFAULT (lower(hex(randomblob(16)))),
                    sentence TEXT,
                    words_json TEXT,
                    difficulty INTEGER DEFAULT 1,
                    content TEXT,
                    number INTEGER NOT NULL,
                    audio_url_1 TEXT,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
            
            // Copiar dados existentes
            DB::statement('
                INSERT INTO exercises_new (id, sentence, words_json, difficulty, content, number, audio_url_1, created_at, updated_at)
                SELECT 
                    COALESCE(id, lower(hex(randomblob(16)))),
                    sentence,
                    words_json,
                    difficulty,
                    content,
                    COALESCE(number, (SELECT COALESCE(MAX(number), 0) + 1 FROM exercises)),
                    audio_url_1,
                    created_at,
                    updated_at
                FROM exercises
            ');
            
            // Substituir tabela antiga
            DB::statement('DROP TABLE exercises');
            DB::statement('ALTER TABLE exercises_new RENAME TO exercises');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE exercises ALTER COLUMN id DROP DEFAULT');
            DB::statement('ALTER TABLE exercises ALTER COLUMN number DROP DEFAULT');
            DB::statement('ALTER TABLE exercises ALTER COLUMN number DROP NOT NULL');
            DB::statement('DROP SEQUENCE IF EXISTS exercises_number_seq');
        }
    }
};
