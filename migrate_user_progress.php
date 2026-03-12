<?php

/**
 * 🔄 Migração de User Progress
 * 
 * Migra dados de progresso dos alunos do CSV user_progress_rows.csv
 * para a tabela user_progress no PostgreSQL
 * 
 * Execute: php migrate_user_progress.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          🔄 Migração de User Progress                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// =============================================================================
// CONFIGURAÇÕES
// =============================================================================

$csvPath = '/Users/vitorclara/Downloads/user_progress_rows.csv';

if (!\file_exists($csvPath)) {
    $csvPath = __DIR__ . '/user_progress_rows.csv';
}

if (!\file_exists($csvPath)) {
    echo "❌ Arquivo CSV não encontrado!\n";
    echo "   Procurado em: $csvPath\n\n";
    exit(1);
}

echo "📁 Arquivo CSV: " . \basename($csvPath) . "\n\n";

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

// =============================================================================
// MIGRAÇÃO
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Migrando User Progress\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$userProgress = readCSV($csvPath);
$migratedCount = 0;
$skippedCount = 0;
$updatedCount = 0;

foreach ($userProgress as $progress) {
    $userId = $progress['user_id'];
    
    // Verificar se usuário existe
    $userExists = DB::table('users')->where('id', $userId)->exists();
    
    if (!$userExists) {
        echo "   ⚠️  Usuário não encontrado: $userId\n";
        $skippedCount++;
        continue;
    }
    
    // Verificar se já existe user_progress
    $existingProgress = DB::table('user_progress')->where('user_id', $userId)->first();
    
    // Preparar dados
    $activeDays = $progress['active_days'] ?? '[]';
    if ($activeDays && $activeDays !== '[]') {
        // Já está em formato JSON
        if (!\json_decode($activeDays)) {
            $activeDays = '[]';
        }
    } else {
        $activeDays = '[]';
    }
    
    $accuracyHistory = $progress['accuracy_history'] ?? '[]';
    if ($accuracyHistory && $accuracyHistory !== '[]') {
        // Já está em formato JSON
        if (!\json_decode($accuracyHistory)) {
            $accuracyHistory = '[]';
        }
    } else {
        $accuracyHistory = '[]';
    }
    
    $data = [
        'user_id' => $userId,
        'stars_total' => (int) ($progress['stars_total'] ?? 0),
        'level' => $progress['level'] ?? 'explorador',
        'active_days' => $activeDays,
        'evolution_count' => (int) ($progress['evolution_count'] ?? 0),
        'last_daily_bonus_date' => $progress['last_daily_bonus_date'] ?: null,
        'accuracy_history' => $accuracyHistory,
        'created_at' => $progress['created_at'] ?? \now(),
        'updated_at' => $progress['updated_at'] ?? \now(),
    ];
    
    try {
        if ($existingProgress) {
            // Atualizar
            DB::table('user_progress')
                ->where('user_id', $userId)
                ->update($data);
            
            echo "   ✅ Atualizado: $userId (Stars: {$data['stars_total']}, Level: {$data['level']})\n";
            $updatedCount++;
        } else {
            // Inserir
            DB::table('user_progress')->insert($data);
            
            echo "   ✅ Criado: $userId (Stars: {$data['stars_total']}, Level: {$data['level']})\n";
            $migratedCount++;
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Erro ao migrar $userId: " . $e->getMessage() . "\n";
        $skippedCount++;
    }
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Resumo\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "   ✅ Criados: $migratedCount\n";
echo "   🔄 Atualizados: $updatedCount\n";
echo "   ⏭️  Ignorados: $skippedCount\n";
echo "   📊 Total processado: " . \count($userProgress) . "\n\n";

// Estatísticas
$totalStars = DB::table('user_progress')->sum('stars_total');
$avgStars = DB::table('user_progress')->avg('stars_total');
$levelDistribution = DB::table('user_progress')
    ->select('level', DB::raw('COUNT(*) as count'))
    ->groupBy('level')
    ->get();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📈 Estatísticas Gerais\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "   ⭐ Total de Stars: " . \number_format($totalStars) . "\n";
echo "   📊 Média de Stars: " . \number_format($avgStars, 2) . "\n\n";

echo "   📊 Distribuição por Nível:\n";
foreach ($levelDistribution as $dist) {
    echo "      - {$dist->level}: {$dist->count} alunos\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  ✨ MIGRAÇÃO CONCLUÍDA                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
