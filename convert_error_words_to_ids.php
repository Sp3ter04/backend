<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DictationMetric;
use App\Models\Word;
use Illuminate\Support\Facades\DB;

echo "=== Conversão de error_words (palavras → IDs) ===\n\n";

// Cache de palavras para melhor performance
$wordCache = [];

function getWordId(string $word, array &$cache): ?string
{
    // Normalizar: remover pontuação e converter para minúsculas
    $normalized = mb_strtolower(trim($word));
    $normalized = preg_replace('/[.,!?;:]+$/', '', $normalized);
    
    if (isset($cache[$normalized])) {
        return $cache[$normalized];
    }
    
    // Buscar palavra no banco (com e sem pontuação)
    $wordModel = Word::where('word', $word)
        ->orWhere('word', $normalized)
        ->first();
    
    if ($wordModel) {
        $cache[$normalized] = $wordModel->id;
        return $wordModel->id;
    }
    
    return null;
}

// Buscar todas as métricas com error_words
$metrics = DictationMetric::whereNotNull('error_words')->get();

echo "📊 Total de métricas encontradas: " . $metrics->count() . "\n\n";

$converted = 0;
$notFound = [];
$alreadyIds = 0;

foreach ($metrics as $metric) {
    $errorWords = $metric->error_words;
    
    if (!is_array($errorWords) || empty($errorWords)) {
        continue;
    }
    
    // Verificar se já são IDs (UUIDs)
    $firstItem = $errorWords[0];
    if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $firstItem)) {
        $alreadyIds++;
        echo "⏭️  Métrica {$metric->id}: já contém IDs\n";
        continue;
    }
    
    $wordIds = [];
    $missingWords = [];
    
    foreach ($errorWords as $word) {
        $wordId = getWordId($word, $wordCache);
        
        if ($wordId) {
            $wordIds[] = $wordId;
        } else {
            $missingWords[] = $word;
            if (!in_array($word, $notFound)) {
                $notFound[] = $word;
            }
        }
    }
    
    if (!empty($wordIds)) {
        // Atualizar com os IDs encontrados
        $metric->error_words = $wordIds;
        $metric->save();
        
        $converted++;
        echo "✅ Métrica {$metric->id}: convertidas " . count($wordIds) . " palavras";
        if (!empty($missingWords)) {
            echo " (⚠️  " . count($missingWords) . " não encontradas: " . implode(', ', $missingWords) . ")";
        }
        echo "\n";
    } elseif (!empty($missingWords)) {
        echo "❌ Métrica {$metric->id}: nenhuma palavra encontrada\n";
    }
}

echo "\n=== RESUMO ===\n";
echo "✅ Métricas convertidas: $converted\n";
echo "⏭️  Métricas já com IDs: $alreadyIds\n";
echo "⚠️  Palavras não encontradas: " . count($notFound) . "\n";

if (!empty($notFound)) {
    echo "\n📝 Palavras que não existem na tabela 'words':\n";
    foreach ($notFound as $word) {
        echo "   - \"$word\"\n";
    }
    
    echo "\n💡 Sugestão: Execute o script de importação de palavras ou crie-as manualmente.\n";
}

echo "\n✅ Conversão concluída!\n";
