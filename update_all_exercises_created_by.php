<?php

/**
 * Script para atualizar TODOS os exercícios com created_by = 'admin@gmail.com'
 * 
 * Uso: php update_all_exercises_created_by.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Exercise;
use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "Atualizando created_by de TODOS os exercícios\n";
echo "========================================\n\n";

try {
    // Contar total antes
    $totalExercises = Exercise::count();
    echo "📊 Total de exercícios no banco: {$totalExercises}\n";
    
    // Contar quantos têm created_by diferente de admin@gmail.com
    $toUpdate = Exercise::where(function($query) {
        $query->whereNull('created_by')
              ->orWhere('created_by', '!=', 'admin@gmail.com');
    })->count();
    
    echo "🔄 Exercícios a serem atualizados: {$toUpdate}\n\n";
    
    if ($toUpdate === 0) {
        echo "✅ Todos os exercícios já têm created_by = 'admin@gmail.com'\n\n";
        exit(0);
    }
    
    // Confirmação
    echo "⚠️  Isso vai atualizar {$toUpdate} exercícios!\n";
    echo "Deseja continuar? (digite 'sim' para confirmar): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($line) !== 'sim') {
        echo "\n❌ Operação cancelada pelo usuário.\n\n";
        exit(0);
    }
    
    echo "\n🔄 Atualizando...\n";
    
    // Executar update em massa (mais rápido)
    $updated = DB::table('exercises')->update([
        'created_by' => 'admin@gmail.com',
        'updated_at' => now(),
    ]);
    
    echo "✅ Atualização concluída!\n\n";
    
    // Verificação final
    $adminExercises = Exercise::where('created_by', 'admin@gmail.com')->count();
    $nullExercises = Exercise::whereNull('created_by')->count();
    
    echo "========================================\n";
    echo "📊 RESULTADO FINAL\n";
    echo "========================================\n";
    echo "Total de exercícios: {$totalExercises}\n";
    echo "Com created_by = 'admin@gmail.com': {$adminExercises}\n";
    echo "Com created_by = NULL: {$nullExercises}\n";
    echo "Outros: " . ($totalExercises - $adminExercises - $nullExercises) . "\n";
    echo "========================================\n\n";
    
    if ($nullExercises === 0 && $adminExercises === $totalExercises) {
        echo "✅ SUCESSO! Todos os exercícios agora têm created_by = 'admin@gmail.com'\n\n";
    } else {
        echo "⚠️  Atenção: Alguns exercícios podem não ter sido atualizados.\n\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n\n";
    exit(1);
}

echo "✅ Script finalizado!\n\n";
exit(0);
