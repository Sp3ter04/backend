<?php

namespace App\Console\Commands;

use App\Services\SupabaseClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncSupabaseExercises extends Command
{
    protected $signature = 'supabase:sync-exercises';
    protected $description = 'Sincronizar exercícios do Supabase para SQLite local';

    public function handle()
    {
        $this->info('Sincronizando exercícios do Supabase...');

        try {
            // Buscar do Supabase
            $supabase = new SupabaseClient();
            $exercises = $supabase->from('exercises')
                ->select('*')
                ->get();

            $this->info('Encontrados ' . count($exercises) . ' exercícios no Supabase');

            // Limpar tabela local
            DB::table('exercises')->truncate();
            $this->info('Tabela local limpa');

            // Inserir no SQLite
            foreach ($exercises as $exercise) {
                DB::table('exercises')->insert([
                    'id' => $exercise['id'],
                    'sentence' => $exercise['sentence'] ?? $exercise['content'] ?? '',
                    'words_json' => $exercise['words_json'],
                    'difficulty' => $exercise['difficulty'],
                    'content' => $exercise['content'] ?? $exercise['sentence'] ?? '',
                    'number' => $exercise['number'] ?? null,
                    'audio_url_1' => $exercise['audio_url_1'] ?? null,
                    'audio_url_2' => $exercise['audio_url_2'] ?? null,
                    'created_at' => $exercise['created_at'] ?? now(),
                    'updated_at' => $exercise['updated_at'] ?? now(),
                ]);
            }

            $this->info('✅ Sincronização concluída! ' . count($exercises) . ' exercícios sincronizados.');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao sincronizar: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
