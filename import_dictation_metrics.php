<?php

/**
 * 📊 Importar Dictation Metrics do Supabase
 * 
 * Importa métricas de ditados dos CSVs do Supabase
 * 
 * Execute: php import_dictation_metrics.php /path/to/dictation_metrics_rows.csv
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║        📊 Importar Dictation Metrics do Supabase             ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Verificar se o arquivo CSV foi fornecido
if ($argc < 2) {
    echo "❌ Erro: Por favor forneça o caminho do arquivo CSV\n";
    echo "   Uso: php import_dictation_metrics.php /path/to/dictation_metrics_rows.csv\n\n";
    exit(1);
}

$csvFile = $argv[1];

if (!file_exists($csvFile)) {
    echo "❌ Erro: Arquivo não encontrado: $csvFile\n\n";
    exit(1);
}

echo "📁 Lendo arquivo: $csvFile\n\n";

// Ler CSV
$file = fopen($csvFile, 'r');
$headers = fgetcsv($file);

$metricsImportadas = 0;
$metricsAtualizadas = 0;
$metricsIgnoradas = 0;
$erros = [];

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Importando Métricas de Ditados\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

while (($row = fgetcsv($file)) !== false) {
    $data = array_combine($headers, $row);
    
    // Verificar se student_id existe
    $studentExists = DB::table('users')->where('id', $data['student_id'])->exists();
    if (!$studentExists) {
        $erros[] = "Aluno não encontrado: {$data['student_id']}";
        $metricsIgnoradas++;
        continue;
    }
    
    // Verificar se exercise_id existe
    $exerciseExists = DB::table('exercises')->where('id', $data['exercise_id'])->exists();
    if (!$exerciseExists) {
        $erros[] = "Exercício não encontrado: {$data['exercise_id']}";
        $metricsIgnoradas++;
        continue;
    }
    
    // Verificar se métrica já existe
    $exists = DB::table('dictation_metrics')->where('id', $data['id'])->exists();
    
    try {
        // Processar error_words (JSON)
        $errorWords = $data['error_words'];
        if (empty($errorWords) || $errorWords === '[]') {
            $errorWords = null;
        }
        
        $metricData = [
            'id' => $data['id'],
            'student_id' => $data['student_id'],
            'exercise_id' => $data['exercise_id'],
            'difficulty' => $data['difficulty'],
            'correct_count' => (int) $data['correct_count'],
            'error_count' => (int) $data['error_count'],
            'missing_count' => (int) $data['missing_count'],
            'extra_count' => (int) $data['extra_count'],
            'accuracy_percent' => (float) $data['accuracy_percent'],
            'letter_omission_count' => (int) $data['letter_omission_count'],
            'letter_insertion_count' => (int) $data['letter_insertion_count'],
            'letter_substitution_count' => (int) $data['letter_substitution_count'],
            'transposition_count' => (float) $data['transposition_count'],
            'split_join_count' => (int) $data['split_join_count'],
            'punctuation_error_count' => (int) $data['punctuation_error_count'],
            'capitalization_error_count' => (int) $data['capitalization_error_count'],
            'error_words' => $errorWords,
            'resolution' => !empty($data['resolution']) ? $data['resolution'] : null,
            'created_at' => $data['created_at'],
            'updated_at' => now(),
        ];
        
        if ($exists) {
            DB::table('dictation_metrics')
                ->where('id', $data['id'])
                ->update($metricData);
            
            echo "   🔄 Atualizada: {$data['id']}\n";
            $metricsAtualizadas++;
        } else {
            DB::table('dictation_metrics')->insert($metricData);
            
            echo "   ✅ Criada: {$data['id']}\n";
            $metricsImportadas++;
        }
    } catch (\Exception $e) {
        $erros[] = "Erro ao importar {$data['id']}: " . $e->getMessage();
        $metricsIgnoradas++;
    }
}

fclose($file);

echo "\n📊 Métricas: $metricsImportadas criadas, $metricsAtualizadas atualizadas, $metricsIgnoradas ignoradas\n";

if (!empty($erros)) {
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "⚠️  Erros encontrados (" . count($erros) . ")\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    foreach (array_slice($erros, 0, 10) as $erro) {
        echo "   ❌ $erro\n";
    }
    
    if (count($erros) > 10) {
        echo "   ... e mais " . (count($erros) - 10) . " erros\n";
    }
    echo "\n";
}

// =============================================================================
// RESUMO FINAL
// =============================================================================

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  ✨ IMPORTAÇÃO CONCLUÍDA                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$totalMetrics = DB::table('dictation_metrics')->count();
$avgAccuracy = DB::table('dictation_metrics')->avg('accuracy_percent');

echo "📊 Total de métricas: $totalMetrics\n";
echo "📈 Precisão média: " . number_format($avgAccuracy, 2) . "%\n\n";
