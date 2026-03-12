<?php

/**
 * 📊 Importar Exercícios e Métricas do Supabase com Mapeamento de IDs
 * 
 * 1. Importa exercícios novos (que ainda não existem)
 * 2. Cria mapeamento de IDs antigos -> novos
 * 3. Importa métricas com IDs corretos
 * 
 * Execute: php import_exercises_and_metrics.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     📊 Importar Exercícios e Métricas do Supabase            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// =============================================================================
// PASSO 1: IMPORTAR EXERCÍCIOS
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📝 PASSO 1: Importando Exercícios\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$exercisesCsv = __DIR__ . '/Downloads/exercises_rows.csv';

if (!file_exists($exercisesCsv)) {
    echo "❌ Arquivo exercises_rows.csv não encontrado em: $exercisesCsv\n";
    echo "   Por favor, copie o arquivo para a pasta Downloads do projeto\n\n";
    exit(1);
}

$file = fopen($exercisesCsv, 'r');
$headers = fgetcsv($file);

$exercisesImportados = 0;
$exercisesExistentes = 0;
$idMapping = []; // old_id => new_id

while (($row = fgetcsv($file)) !== false) {
    $data = array_combine($headers, $row);
    
    $oldId = $data['id'];
    $sentence = trim($data['content']); // 'content' no CSV é 'sentence' no banco
    
    // Verificar se exercício já existe (por sentence)
    $existing = DB::table('exercises')->where('sentence', $sentence)->first();
    
    if ($existing) {
        echo "   ⏭️  Já existe: " . substr($sentence, 0, 50) . "...\n";
        $idMapping[$oldId] = $existing->id;
        $exercisesExistentes++;
    } else {
        try {
            $newId = Str::uuid()->toString();
            
            DB::table('exercises')->insert([
                'id' => $newId,
                'sentence' => $sentence,
                'difficulty' => $data['difficulty'],
                'number' => (int) $data['number'],
                'created_at' => $data['created_at'],
                'updated_at' => now(),
            ]);
            
            echo "   ✅ Criado: " . substr($sentence, 0, 50) . "...\n";
            $idMapping[$oldId] = $newId;
            $exercisesImportados++;
        } catch (\Exception $e) {
            echo "   ❌ Erro: " . $e->getMessage() . "\n";
        }
    }
}

fclose($file);

echo "\n📊 Exercícios: $exercisesImportados criados, $exercisesExistentes já existiam\n";
echo "🗺️  Mapeamento criado para " . count($idMapping) . " exercícios\n\n";

// Salvar mapeamento em arquivo JSON
file_put_contents(__DIR__ . '/exercise_id_mapping_full.json', json_encode($idMapping, JSON_PRETTY_PRINT));
echo "💾 Mapeamento salvo em: exercise_id_mapping_full.json\n\n";

// =============================================================================
// PASSO 2: IMPORTAR MÉTRICAS COM IDs MAPEADOS
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 PASSO 2: Importando Métricas de Ditados\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$metricsCsv = __DIR__ . '/Downloads/dictation_metrics_rows.csv';

if (!file_exists($metricsCsv)) {
    echo "❌ Arquivo dictation_metrics_rows.csv não encontrado em: $metricsCsv\n";
    echo "   Por favor, copie o arquivo para a pasta Downloads do projeto\n\n";
    exit(1);
}

$file = fopen($metricsCsv, 'r');
$headers = fgetcsv($file);

$metricsImportadas = 0;
$metricsIgnoradas = 0;
$erros = [];

while (($row = fgetcsv($file)) !== false) {
    $data = array_combine($headers, $row);
    
    $oldExerciseId = $data['exercise_id'];
    
    // Verificar se temos mapeamento para este exercício
    if (!isset($idMapping[$oldExerciseId])) {
        $erros[] = "Exercício não mapeado: $oldExerciseId";
        $metricsIgnoradas++;
        continue;
    }
    
    $newExerciseId = $idMapping[$oldExerciseId];
    
    // Verificar se student_id existe
    $studentExists = DB::table('users')->where('id', $data['student_id'])->exists();
    if (!$studentExists) {
        $erros[] = "Aluno não encontrado: {$data['student_id']}";
        $metricsIgnoradas++;
        continue;
    }
    
    // Verificar se métrica já existe
    $exists = DB::table('dictation_metrics')
        ->where('student_id', $data['student_id'])
        ->where('exercise_id', $newExerciseId)
        ->where('created_at', $data['created_at'])
        ->exists();
    
    if ($exists) {
        $metricsIgnoradas++;
        continue;
    }
    
    try {
        // Processar error_words (JSON)
        $errorWords = $data['error_words'];
        if (empty($errorWords) || $errorWords === '[]') {
            $errorWords = null;
        }
        
        DB::table('dictation_metrics')->insert([
            'id' => Str::uuid()->toString(),
            'student_id' => $data['student_id'],
            'exercise_id' => $newExerciseId, // ID MAPEADO
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
        ]);
        
        $metricsImportadas++;
        
        if ($metricsImportadas % 50 == 0) {
            echo "   ✅ Importadas: $metricsImportadas métricas...\n";
        }
    } catch (\Exception $e) {
        $erros[] = "Erro ao importar métrica: " . $e->getMessage();
        $metricsIgnoradas++;
    }
}

fclose($file);

echo "\n📊 Métricas: $metricsImportadas criadas, $metricsIgnoradas ignoradas\n";

if (!empty($erros)) {
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "⚠️  Erros encontrados (" . count($erros) . ")\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    $uniqueErrors = array_unique($erros);
    foreach (array_slice($uniqueErrors, 0, 10) as $erro) {
        echo "   ❌ $erro\n";
    }
    
    if (count($uniqueErrors) > 10) {
        echo "   ... e mais " . (count($uniqueErrors) - 10) . " erros\n";
    }
    echo "\n";
}

// =============================================================================
// RESUMO FINAL
// =============================================================================

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  ✨ IMPORTAÇÃO CONCLUÍDA                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$totalExercises = DB::table('exercises')->count();
$totalMetrics = DB::table('dictation_metrics')->count();
$avgAccuracy = DB::table('dictation_metrics')->avg('accuracy_percent');

echo "📝 Total de exercícios: $totalExercises\n";
echo "📊 Total de métricas: $totalMetrics\n";
echo "📈 Precisão média: " . number_format($avgAccuracy, 2) . "%\n\n";
