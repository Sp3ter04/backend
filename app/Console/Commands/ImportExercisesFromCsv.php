<?php

namespace App\Console\Commands;

use App\Services\SupabaseClient;
use Illuminate\Console\Command;

class ImportExercisesFromCsv extends Command
{
    protected $signature = 'exercises:import-csv {file : Caminho para o arquivo CSV}';
    protected $description = 'Importar exercícios de um arquivo CSV para o Supabase';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("❌ Arquivo não encontrado: {$filePath}");
            return 1;
        }

        $this->info('📥 Lendo arquivo CSV...');
        
        // Ler o CSV
        $csv = array_map('str_getcsv', file($filePath));
        $headers = array_shift($csv); // Remover cabeçalho
        
        $this->info('📋 Encontradas ' . count($csv) . ' linhas no CSV');
        $this->newLine();

        $supabase = new SupabaseClient();
        $imported = 0;
        $errors = 0;

        foreach ($csv as $index => $row) {
            try {
                // Mapear colunas do CSV
                $exercise = [
                    'id' => $row[0],
                    'content' => trim($row[1]),
                    'sentence' => trim($row[1]), // Usar content como sentence também
                    'difficulty' => $row[2],
                    'created_at' => $row[3],
                    'audio_url_1' => !empty($row[4]) ? $row[4] : null,
                    'audio_url_2' => !empty($row[5]) ? $row[5] : null,
                    'number' => (int)$row[6],
                ];

                // Inserir no Supabase
                $result = $supabase->from('exercises')->insert($exercise);

                if ($result) {
                    $imported++;
                    $this->info("✅ [{$imported}] Exercício #{$exercise['number']}: {$exercise['content']}");
                } else {
                    $errors++;
                    $this->warn("⚠️  Erro ao importar exercício #{$exercise['number']}");
                }

            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ Erro na linha " . ($index + 2) . ": " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("🎉 Importação concluída!");
        $this->info("✅ {$imported} exercícios importados com sucesso");
        
        if ($errors > 0) {
            $this->warn("⚠️  {$errors} erros encontrados");
        }

        return 0;
    }
}
