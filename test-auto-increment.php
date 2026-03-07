<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Exercise;
use App\Enums\DictationDifficulty;

echo "\n========================================\n";
echo "Teste: Auto-incremento do campo 'number'\n";
echo "========================================\n\n";

// 1. Verificar último número
$lastNumber = Exercise::max('number') ?? 0;
echo "📊 Último número existente: {$lastNumber}\n";
echo "📊 Próximo número esperado: " . ($lastNumber + 1) . "\n\n";

// 2. Criar exercício de teste (SEM especificar 'number')
echo "🧪 Criando exercício de teste...\n";

try {
    $exercise = Exercise::create([
        'sentence' => 'Este é um exercício de teste para auto-incremento.',
        'difficulty' => DictationDifficulty::EASY->value,
        'created_by' => 'admin@gmail.com',
        // NOTE: NÃO estamos passando 'number' - deve ser auto-incrementado!
    ]);
    
    echo "✅ Exercício criado com sucesso!\n\n";
    echo "📊 ID: {$exercise->id}\n";
    echo "📊 Number: {$exercise->number}\n";
    echo "📊 Sentence: {$exercise->sentence}\n";
    echo "📊 Created by: {$exercise->created_by}\n\n";
    
    if ($exercise->number === $lastNumber + 1) {
        echo "✅ AUTO-INCREMENTO FUNCIONOU CORRETAMENTE!\n";
        echo "   Esperado: " . ($lastNumber + 1) . " | Obtido: {$exercise->number}\n\n";
    } else {
        echo "❌ ERRO: Auto-incremento não funcionou!\n";
        echo "   Esperado: " . ($lastNumber + 1) . " | Obtido: {$exercise->number}\n\n";
    }
    
    // 3. Limpar teste (deletar exercício criado)
    echo "🧹 Limpando teste (deletando exercício)...\n";
    $exercise->delete();
    echo "✅ Exercício de teste deletado!\n\n";
    
} catch (Exception $e) {
    echo "❌ ERRO ao criar exercício: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "========================================\n";
echo "✅ Teste concluído!\n";
echo "========================================\n\n";

echo "📋 RESUMO:\n";
echo "   - Campo 'number' é auto-incrementado automaticamente\n";
echo "   - Baseado no último número existente (max + 1)\n";
echo "   - Não é necessário (nem possível) editar manualmente\n";
echo "   - Visível na tabela de exercícios (coluna '#')\n\n";
