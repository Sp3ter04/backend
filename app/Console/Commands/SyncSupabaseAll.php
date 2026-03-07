<?php

namespace App\Console\Commands;

use App\Services\SupabaseClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncSupabaseAll extends Command
{
    protected $signature = 'supabase:sync-all {--table= : Sincronizar apenas uma tabela específica}';
    protected $description = 'Sincronizar todas as tabelas do Supabase para SQLite local';

    protected array $tables = [
        'users' => [
            'id', 'name', 'email', 'email_verified_at', 'password', 
            'remember_token', 'created_at', 'updated_at'
        ],
        'schools' => [
            'id', 'name', 'address', 'city', 'postal_code', 
            'phone', 'email', 'created_at', 'updated_at'
        ],
        'exercises' => [
            'id', 'sentence', 'words_json', 'difficulty', 'content', 
            'number', 'audio_url_1', 'audio_url_2', 'created_at', 'updated_at'
        ],
        'words' => [
            'id', 'text', 'difficulty', 'audio_url', 
            'created_at', 'updated_at'
        ],
        'syllables' => [
            'id', 'text', 'audio_url', 
            'created_at', 'updated_at'
        ],
        'exercise_words' => [
            'id', 'exercise_id', 'word_id', 'word_order', 
            'created_at', 'updated_at'
        ],
        'word_syllables' => [
            'id', 'word_id', 'syllable_id', 'syllable_order', 
            'created_at', 'updated_at'
        ],
        'profissional_student' => [
            'id', 'profissional_id', 'student_id', 
            'created_at', 'updated_at'
        ],
        'dictation_metrics' => [
            'id', 'student_id', 'user_id', 'exercise_id', 'completed_at', 
            'time_taken', 'accuracy', 'created_at', 'updated_at'
        ],
    ];

    public function handle()
    {
        $specificTable = $this->option('table');
        
        if ($specificTable) {
            if (!isset($this->tables[$specificTable])) {
                $this->error("❌ Tabela '{$specificTable}' não encontrada!");
                $this->info("Tabelas disponíveis: " . implode(', ', array_keys($this->tables)));
                return 1;
            }
            
            $tables = [$specificTable => $this->tables[$specificTable]];
        } else {
            $tables = $this->tables;
        }

        $this->info('🔄 Iniciando sincronização do Supabase...');
        $this->newLine();

        $totalSynced = 0;
        $supabase = new SupabaseClient();

        foreach ($tables as $table => $columns) {
            try {
                $this->info("📋 Sincronizando tabela: {$table}");

                // Verificar se a tabela existe no SQLite
                if (!Schema::hasTable($table)) {
                    $this->warn("  ⚠️  Tabela '{$table}' não existe no SQLite. Pulando...");
                    continue;
                }

                // Buscar do Supabase
                $records = $supabase->from($table)
                    ->select('*')
                    ->get();

                $count = count($records);
                $this->info("  📥 Encontrados {$count} registros no Supabase");

                if ($count === 0) {
                    $this->warn("  ⚠️  Nenhum registro encontrado. Pulando...");
                    continue;
                }

                // Limpar tabela local
                DB::table($table)->truncate();
                $this->info("  🗑️  Tabela local limpa");

                // Inserir registros
                $inserted = 0;
                foreach ($records as $record) {
                    try {
                        // Filtrar apenas as colunas que existem na tabela
                        $data = [];
                        foreach ($columns as $column) {
                            if (isset($record[$column])) {
                                $data[$column] = $record[$column];
                            }
                        }

                        // Garantir que campos de timestamp sejam válidos
                        if (isset($data['created_at']) && empty($data['created_at'])) {
                            $data['created_at'] = now();
                        }
                        if (isset($data['updated_at']) && empty($data['updated_at'])) {
                            $data['updated_at'] = now();
                        }

                        DB::table($table)->insert($data);
                        $inserted++;
                    } catch (\Exception $e) {
                        $this->warn("  ⚠️  Erro ao inserir registro: " . $e->getMessage());
                    }
                }

                $this->info("  ✅ {$inserted} registros sincronizados");
                $totalSynced += $inserted;

            } catch (\Exception $e) {
                $this->error("  ❌ Erro ao sincronizar tabela '{$table}': " . $e->getMessage());
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("🎉 Sincronização concluída!");
        $this->info("📊 Total de registros sincronizados: {$totalSynced}");
        
        return 0;
    }
}
