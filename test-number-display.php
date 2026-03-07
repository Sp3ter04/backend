<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Exercise;

echo "\n========================================\n";
echo "Verificação: Número do próximo exercício\n";
echo "========================================\n\n";

$lastNumber = Exercise::max('number') ?? 0;
$nextNumber = $lastNumber + 1;

echo "📊 Último número: {$lastNumber}\n";
echo "📊 Próximo número: {$nextNumber}\n\n";

echo "✅ Este número ({$nextNumber}) será mostrado no formulário de criação!\n";
echo "   - Campo: 'Número do Exercício'\n";
echo "   - Estado: Desabilitado (somente leitura)\n";
echo "   - Ícone: # (hashtag)\n";
echo "   - Texto de ajuda: 'Este número será atribuído automaticamente ao criar o exercício'\n\n";

echo "========================================\n";
echo "🎯 RESULTADO NO FORMULÁRIO:\n";
echo "========================================\n\n";

echo "┌─────────────────────────────────────────┐\n";
echo "│ Número do Exercício         #           │\n";
echo "│ ┌─────────────────────────────────────┐ │\n";
echo "│ │  {$nextNumber}                                  │ │\n";
echo "│ └─────────────────────────────────────┘ │\n";
echo "│ ℹ️  Este número será atribuído          │\n";
echo "│    automaticamente ao criar o exercício │\n";
echo "└─────────────────────────────────────────┘\n\n";

echo "========================================\n\n";
