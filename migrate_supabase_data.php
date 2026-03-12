<?php

/**
 * 🔄 Migração Completa de Dados do Supabase
 * 
 * Este script migra:
 * 1. Profissionais → users (role: profissional)
 * 2. Alunos (user_progress) → users (role: student)
 * 3. Relacionamentos aluno_profissionais → profissional_student
 * 4. Exercícios antigos → exercises (com mapeamento de IDs)
 * 5. Métricas de ditado → dictation_metrics (com IDs atualizados)
 * 
 * Execute: php migrate_supabase_data.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║     🔄 Migração de Dados do Supabase para PostgreSQL          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// =============================================================================
// CONFIGURAÇÕES
// =============================================================================

$csvPath = __DIR__;
$csvFiles = [
    'profissionais' => $csvPath . '/profissionais_rows.csv',
    'user_progress' => $csvPath . '/user_progress_rows.csv',
    'aluno_profissionais' => $csvPath . '/aluno_profissionais_rows.csv',
    'exercises' => $csvPath . '/exercises_rows.csv',
    'dictation_metrics' => $csvPath . '/dictation_metrics_rows.csv',
];

// Verificar se arquivos existem
echo "📁 Verificando arquivos CSV...\n";
foreach ($csvFiles as $name => $path) {
    if (\file_exists($path)) {
        echo "   ✅ $name: " . \basename($path) . "\n";
    } else {
        echo "   ❌ $name: ARQUIVO NÃO ENCONTRADO - $path\n";
        // Tentar Downloads
        $downloadPath = '/Users/vitorclara/Downloads/' . \basename($path);
        if (\file_exists($downloadPath)) {
            $csvFiles[$name] = $downloadPath;
            echo "      ✅ Encontrado em Downloads\n";
        }
    }
}
echo "\n";

// =============================================================================
// FUNÇÕES AUXILIARES
// =============================================================================

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

function isValidUuid($uuid) {
    return \preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1;
}

function generateSlug($text) {
    $text = \strtolower($text);
    $text = \preg_replace('/[áàâãäå]/u', 'a', $text);
    $text = \preg_replace('/[éèêë]/u', 'e', $text);
    $text = \preg_replace('/[íìîï]/u', 'i', $text);
    $text = \preg_replace('/[óòôõö]/u', 'o', $text);
    $text = \preg_replace('/[úùûü]/u', 'u', $text);
    $text = \preg_replace('/[ç]/u', 'c', $text);
    $text = \preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = \trim($text, '-');
    return $text;
}

// =============================================================================
// PASSO 1: MIGRAR PROFISSIONAIS → USERS
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📚 PASSO 1: Migrar Profissionais → users\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$profissionais = readCSV($csvFiles['profissionais']);
$profissionaisCount = 0;
$profissionaisSkipped = 0;

foreach ($profissionais as $prof) {
    $userId = $prof['id'];
    
    if (!isValidUuid($userId)) {
        echo "   ⚠️  ID inválido: $userId\n";
        $profissionaisSkipped++;
        continue;
    }
    
    // Verificar se já existe
    $exists = DB::table('users')->where('id', $userId)->exists();
    
    if ($exists) {
        echo "   ⏭️  Profissional já existe: {$prof['nome']} ({$prof['email']})\n";
        $profissionaisSkipped++;
        continue;
    }
    
    // Inserir
    try {
        DB::table('users')->insert([
            'id' => $userId,
            'name' => $prof['nome'],
            'email' => $prof['email'],
            'email_verified_at' => $prof['created_at'] ?? \now(),
            'role' => 'profissional',
            'school_id' => null, // Será associado depois se necessário
            'created_at' => $prof['created_at'] ?? \now(),
            'updated_at' => \now(),
        ]);
        
        echo "   ✅ Profissional criado: {$prof['nome']} ({$prof['email']})\n";
        $profissionaisCount++;
        
    } catch (\Exception $e) {
        echo "   ❌ Erro ao criar profissional {$prof['nome']}: " . $e->getMessage() . "\n";
        $profissionaisSkipped++;
    }
}

echo "\n📊 Resumo: $profissionaisCount criados, $profissionaisSkipped ignorados\n\n";

// =============================================================================
// PASSO 2: MIGRAR ALUNOS (USER_PROGRESS) → USERS
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "👨‍🎓 PASSO 2: Migrar Alunos → users\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$userProgress = readCSV($csvFiles['user_progress']);
$alunosCount = 0;
$alunosSkipped = 0;

foreach ($userProgress as $progress) {
    $userId = $progress['user_id'];
    
    if (!isValidUuid($userId)) {
        echo "   ⚠️  ID inválido: $userId\n";
        $alunosSkipped++;
        continue;
    }
    
    // Verificar se já existe
    $exists = DB::table('users')->where('id', $userId)->exists();
    
    if ($exists) {
        echo "   ⏭️  Aluno já existe: $userId\n";
        $alunosSkipped++;
        continue;
    }
    
    // Gerar email único baseado no ID (não temos email no CSV)
    $email = 'aluno-' . \substr($userId, 0, 8) . '@dyscovery.app';
    $name = 'Aluno ' . \substr($userId, 0, 8);
    
    // Inserir
    try {
        DB::table('users')->insert([
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'email_verified_at' => $progress['created_at'] ?? \now(),
            'role' => 'aluno',
            'school_id' => null,
            'created_at' => $progress['created_at'] ?? \now(),
            'updated_at' => $progress['updated_at'] ?? \now(),
        ]);
        
        echo "   ✅ Aluno criado: $name ($email)\n";
        $alunosCount++;
        
    } catch (\Exception $e) {
        echo "   ❌ Erro ao criar aluno $name: " . $e->getMessage() . "\n";
        $alunosSkipped++;
    }
}

echo "\n📊 Resumo: $alunosCount criados, $alunosSkipped ignorados\n\n";

// =============================================================================
// PASSO 3: MIGRAR RELACIONAMENTOS ALUNO_PROFISSIONAIS
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔗 PASSO 3: Migrar Relacionamentos → profissional_student\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$alunoProfissionais = readCSV($csvFiles['aluno_profissionais']);
$relacionamentosCount = 0;
$relacionamentosSkipped = 0;

foreach ($alunoProfissionais as $rel) {
    $alunoId = $rel['aluno_id'];
    $profissionalId = $rel['profissional_id'];
    
    if (!isValidUuid($alunoId) || !isValidUuid($profissionalId)) {
        echo "   ⚠️  ID inválido\n";
        $relacionamentosSkipped++;
        continue;
    }
    
    // Verificar se usuários existem
    $alunoExists = DB::table('users')->where('id', $alunoId)->exists();
    $profExists = DB::table('users')->where('id', $profissionalId)->exists();
    
    if (!$alunoExists || !$profExists) {
        echo "   ⚠️  Usuário não encontrado: aluno=$alunoId, prof=$profissionalId\n";
        $relacionamentosSkipped++;
        continue;
    }
    
    // Verificar se relacionamento já existe
    $exists = DB::table('profissional_student')
        ->where('student_id', $alunoId)
        ->where('profissional_id', $profissionalId)
        ->exists();
    
    if ($exists) {
        echo "   ⏭️  Relacionamento já existe\n";
        $relacionamentosSkipped++;
        continue;
    }
    
    // Inserir
    try {
        DB::table('profissional_student')->insert([
            'id' => Str::uuid()->toString(),
            'student_id' => $alunoId,
            'profissional_id' => $profissionalId,
            'created_at' => $rel['created_at'] ?? \now(),
            'updated_at' => \now(),
        ]);
        
        echo "   ✅ Relacionamento criado: aluno → profissional\n";
        $relacionamentosCount++;
        
    } catch (\Exception $e) {
        echo "   ❌ Erro: " . $e->getMessage() . "\n";
        $relacionamentosSkipped++;
    }
}

echo "\n📊 Resumo: $relacionamentosCount criados, $relacionamentosSkipped ignorados\n\n";

// =============================================================================
// PASSO 4: CRIAR MAPEAMENTO DE EXERCÍCIOS (ANTIGOS → NOVOS)
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎯 PASSO 4: Mapear Exercícios Antigos → Novos\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$exercisesOld = readCSV($csvFiles['exercises']);
$exercisesMap = []; // [old_id => new_id]
$exercisesNotFound = [];

// Buscar exercícios atuais no banco
$exercisesCurrent = DB::table('exercises')->get();

echo "📚 Exercícios no CSV antigo: " . \count($exercisesOld) . "\n";
echo "📚 Exercícios no banco atual: " . \count($exercisesCurrent) . "\n\n";

foreach ($exercisesOld as $oldEx) {
    $oldId = $oldEx['id'];
    $oldContent = \trim($oldEx['content']);
    
    // Buscar por conteúdo exato
    $match = $exercisesCurrent->first(function ($ex) use ($oldContent) {
        return \trim($ex->content) === $oldContent;
    });
    
    if ($match) {
        $exercisesMap[$oldId] = $match->id;
        echo "   ✅ Mapeado: '{$oldContent}' → {$match->id}\n";
    } else {
        $exercisesNotFound[] = $oldContent;
        echo "   ⚠️  NÃO encontrado: '{$oldContent}'\n";
    }
}

echo "\n📊 Resumo: " . \count($exercisesMap) . " mapeados, " . \count($exercisesNotFound) . " não encontrados\n\n";

if (\count($exercisesNotFound) > 0) {
    echo "⚠️  Exercícios não encontrados:\n";
    foreach ($exercisesNotFound as $content) {
        echo "   - $content\n";
    }
    echo "\n";
}

// Salvar mapeamento em arquivo JSON
$mapFile = __DIR__ . '/exercise_id_mapping.json';
\file_put_contents($mapFile, \json_encode($exercisesMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "💾 Mapeamento salvo em: exercise_id_mapping.json\n\n";

// =============================================================================
// PASSO 5: MIGRAR MÉTRICAS DE DITADO (COM IDS ATUALIZADOS)
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 PASSO 5: Migrar Métricas de Ditado\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$dictationMetrics = readCSV($csvFiles['dictation_metrics']);
$metricsCount = 0;
$metricsSkipped = 0;
$metricsExerciseNotMapped = 0;

foreach ($dictationMetrics as $metric) {
    $oldExerciseId = $metric['exercise_id'] ?? null;
    $studentId = $metric['student_id'] ?? null;
    
    if (!$oldExerciseId || !$studentId) {
        echo "   ⚠️  Dados incompletos\n";
        $metricsSkipped++;
        continue;
    }
    
    // Verificar se exercício foi mapeado
    if (!isset($exercisesMap[$oldExerciseId])) {
        echo "   ⚠️  Exercício não mapeado: $oldExerciseId\n";
        $metricsExerciseNotMapped++;
        continue;
    }
    
    $newExerciseId = $exercisesMap[$oldExerciseId];
    
    // Verificar se aluno existe
    $studentExists = DB::table('users')->where('id', $studentId)->exists();
    if (!$studentExists) {
        echo "   ⚠️  Aluno não encontrado: $studentId\n";
        $metricsSkipped++;
        continue;
    }
    
    // Preparar error_words (JSON)
    $errorWords = null;
    if (!empty($metric['error_words'])) {
        $errorWords = $metric['error_words'];
        // Se não for JSON válido, tentar corrigir
        if (!\json_decode($errorWords)) {
            $errorWords = \json_encode([]);
        }
    }
    
    // Inserir métrica
    try {
        DB::table('dictation_metrics')->insert([
            'id' => Str::uuid()->toString(),
            'student_id' => $studentId,
            'exercise_id' => $newExerciseId, // ID NOVO!
            'difficulty' => $metric['difficulty'] ?? 'easy',
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
        
        echo "   ✅ Métrica migrada: {$metric['difficulty']} - {$metric['accuracy_percent']}%\n";
        $metricsCount++;
        
    } catch (\Exception $e) {
        echo "   ❌ Erro: " . $e->getMessage() . "\n";
        $metricsSkipped++;
    }
}

echo "\n📊 Resumo:\n";
echo "   - Migradas: $metricsCount\n";
echo "   - Ignoradas: $metricsSkipped\n";
echo "   - Exercícios não mapeados: $metricsExerciseNotMapped\n\n";

// =============================================================================
// RESUMO FINAL
// =============================================================================

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                     ✨ MIGRAÇÃO CONCLUÍDA                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 Estatísticas:\n";
echo "   👨‍🏫 Profissionais: $profissionaisCount criados\n";
echo "   👨‍🎓 Alunos: $alunosCount criados\n";
echo "   🔗 Relacionamentos: $relacionamentosCount criados\n";
echo "   🎯 Exercícios mapeados: " . \count($exercisesMap) . "\n";
echo "   📊 Métricas migradas: $metricsCount\n\n";

echo "⚠️  IMPORTANTE:\n";
echo "   1. Alunos criados com emails temporários: aluno-xxxxx@dyscovery.app\n";
echo "   2. Mapeamento salvo em: exercise_id_mapping.json\n";
echo "   3. Revise os exercícios não encontrados e crie-os se necessário\n\n";

echo "🎉 Migração finalizada com sucesso!\n\n";
