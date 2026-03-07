<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Exercise;

echo "\n========================================\n";
echo "Reindexação de Exercícios\n";
echo "========================================\n\n";

// 1. Contar exercícios
$totalExercises = Exercise::count();
echo "📊 Total de exercícios: {$totalExercises}\n\n";

// 2. Verificar estado atual
$withNumber = Exercise::whereNotNull('number')->count();
$withoutNumber = Exercise::whereNull('number')->count();
$maxNumber = Exercise::max('number') ?? 0;

echo "📊 Estado atual:\n";
echo "   - Com número: {$withNumber}\n";
echo "   - Sem número: {$withoutNumber}\n";
echo "   - Número máximo: {$maxNumber}\n\n";

echo "========================================\n";
echo "🔄 Iniciando reindexação...\n";
echo "========================================\n\n";

// 3. Buscar todos os exercícios ordenados por created_at (mais antigos primeiro)
$exercises = Exercise::orderBy('created_at', 'asc')->get();

$counter = 1;
$updated = 0;

foreach ($exercises as $exercise) {
    $oldNumber = $exercise->number;
    
    // Atualizar número
    $exercise->number = $counter;
    $exercise->save();
    
    echo sprintf(
        "✅ Exercício ID: %s | Número: %s → %s | Frase: %s\n",
        substr($exercise->id, 0, 8),
        $oldNumber ?? 'NULL',
        $counter,
        mb_substr($exercise->sentence, 0, 50)
    );
    
    $counter++;
    $updated++;
}

echo "\n========================================\n";
echo "✅ Reindexação concluída!\n";
echo "========================================\n\n";

echo "📊 RESUMO:\n";
echo "   - Total de exercícios: {$totalExercises}\n";
echo "   - Exercícios atualizados: {$updated}\n";
echo "   - Nova numeração: 1 a {$updated}\n\n";

// 4. Validar
$newMax = Exercise::max('number');
$nullCount = Exercise::whereNull('number')->count();

echo "📊 VALIDAÇÃO:\n";
echo "   - Número máximo atual: {$newMax}\n";
echo "   - Exercícios sem número: {$nullCount}\n";

if ($nullCount === 0 && $newMax === $totalExercises) {
    echo "\n✅ SUCESSO! Todos os exercícios foram reindexados corretamente!\n";
} else {
    echo "\n⚠️  ATENÇÃO: Pode haver algum problema na reindexação.\n";
}

echo "\n========================================\n\n";
