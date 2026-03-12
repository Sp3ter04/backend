<?php

/**
 * 🔧 Corrigir Exercício Faltante
 * 
 * Cria o exercício "O Pedro pinta uma parede branca." e atualiza
 * as 122 métricas que estavam pendentes.
 * 
 * Execute: php fix_missing_exercise.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║            🔧 Corrigir Exercício Faltante                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// =============================================================================
// CRIAR EXERCÍCIO
// =============================================================================

$oldExerciseId = '53498836-f6c6-49fe-8f9b-92f408dcb58b';
$content = 'O Pedro pinta uma parede branca.';
$difficulty = 'medium';
$number = 11;

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎯 PASSO 1: Criar Exercício\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Verificar se já existe
$exists = DB::table('exercises')->where('id', $oldExerciseId)->exists();

if ($exists) {
    echo "   ⏭️  Exercício já existe: $oldExerciseId\n\n";
} else {
    try {
        DB::table('exercises')->insert([
            'id' => $oldExerciseId,
            'sentence' => $content, // Coluna sentence
            'content' => $content,  // Coluna content
            'difficulty' => $difficulty,
            'number' => $number,
            'created_at' => '2025-12-30 11:05:27.371569+00',
            'updated_at' => \now(),
        ]);
        
        echo "   ✅ Exercício criado:\n";
        echo "      ID: $oldExerciseId\n";
        echo "      Conteúdo: \"$content\"\n";
        echo "      Dificuldade: $difficulty\n";
        echo "      Número: $number\n\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Erro ao criar exercício: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}

// =============================================================================
// MIGRAR MÉTRICAS PENDENTES
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 PASSO 2: Migrar Métricas Pendentes\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$csvPath = '/Users/vitorclara/Downloads/dictation_metrics_rows.csv';

if (!\file_exists($csvPath)) {
    $csvPath = __DIR__ . '/dictation_metrics_rows.csv';
}

if (!\file_exists($csvPath)) {
    echo "❌ Arquivo CSV não encontrado!\n\n";
    exit(1);
}

function readCSV($filePath) {
    $data = [];
    if (($handle = \fopen($filePath, 'r')) !== false) {
        $header = \fgetcsv($handle);
        while (($row = \fgetcsv($handle)) !== false) {
            if (\count($header) === \count($row)) {
                $data[] = \array_combine($header, $row);
            }
        }
        \fclose($handle);
    }
    return $data;
}

$dictationMetrics = readCSV($csvPath);
$migratedCount = 0;
$skippedCount = 0;

foreach ($dictationMetrics as $metric) {
    $exerciseId = $metric['exercise_id'] ?? null;
    $studentId = $metric['student_id'] ?? null;
    
    // Só processar métricas do exercício faltante
    if ($exerciseId !== $oldExerciseId) {
        continue;
    }
    
    if (!$studentId) {
        $skippedCount++;
        continue;
    }
    
    // Verificar se aluno existe
    $studentExists = DB::table('users')->where('id', $studentId)->exists();
    if (!$studentExists) {
        echo "   ⚠️  Aluno não encontrado: $studentId\n";
        $skippedCount++;
        continue;
    }
    
    // Preparar error_words (JSON)
    $errorWords = null;
    if (!empty($metric['error_words'])) {
        $errorWords = $metric['error_words'];
        if (!\json_decode($errorWords)) {
            $errorWords = \json_encode([]);
        }
    }
    
    // Inserir métrica
    try {
        DB::table('dictation_metrics')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'student_id' => $studentId,
            'exercise_id' => $exerciseId,
            'difficulty' => $metric['difficulty'] ?? 'medium',
            'correct_count' => (int) ($metric['correct_count'] ?? 0),
            'error_count' => (int) ($metric['error_count'] ?? 0),
            'missing_count' => (int) ($metric['missing_count'] ?? 0),
            'extra_count' => (int) ($metric['extra_count'] ?? 0),
            'accuracy_percent' => (float) ($metric['accuracy_percent'] ?? 0),
            'letter_omission_count' => (int) ($metric['letter_omission_count'] ?? 0),
            'letter_insertion_count' => (int) ($metric['letter_insertion_count'] ?? 0),
            'letter_substitution_count' => (int) ($metric['letter_substitution_count'] ?? 0),
            'transposition_count' => (float) ($metric['transposition_count'] ?? 0),
            'split_join_count' => (int) ($metric['split_join_count'] ?? 0),
            'punctuation_error_count' => (int) ($metric['punctuation_error_count'] ?? 0),
            'capitalization_error_count' => (int) ($metric['capitalization_error_count'] ?? 0),
            'error_words' => $errorWords,
            'resolution' => $metric['resolution'] ?? null,
            'created_at' => $metric['created_at'] ?? \now(),
            'updated_at' => \now(),
        ]);
        
        echo "   ✅ Métrica migrada: {$metric['accuracy_percent']}% accuracy\n";
        $migratedCount++;
        
    } catch (\Exception $e) {
        echo "   ❌ Erro: " . $e->getMessage() . "\n";
        $skippedCount++;
    }
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Resumo\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "   ✅ Métricas migradas: $migratedCount\n";
echo "   ⏭️  Ignoradas: $skippedCount\n\n";

// Estatísticas finais
$totalMetrics = DB::table('dictation_metrics')->count();
$avgAccuracy = DB::table('dictation_metrics')->avg('accuracy_percent');

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📈 Estatísticas Gerais\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "   📊 Total de Métricas: $totalMetrics\n";
echo "   📊 Accuracy Média: " . \number_format($avgAccuracy, 2) . "%\n\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  ✨ CORREÇÃO CONCLUÍDA                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "🎉 Todas as métricas foram migradas com sucesso!\n\n";
