<?php

/**
 * Analyze and curate canonical word timings from words.word_timestamps.
 *
 * Usage:
 *   php analyze_word_timings.php
 *   php analyze_word_timings.php --force
 *   php analyze_word_timings.php --threshold=0.05
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\Word;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$options = getopt('', ['force', 'threshold::', 'help']);

if (isset($options['help'])) {
    echo "\n";
    echo "Analyze Word Timings\n";
    echo "====================\n\n";
    echo "Usage:\n";
    echo "  php analyze_word_timings.php [--force] [--threshold=0.05]\n\n";
    echo "Options:\n";
    echo "  --force           Reanalisar palavras que ja tem canonical_timing\n";
    echo "  --threshold=0.05 Diferenca minima (segundos) para considerar timings diferentes\n";
    echo "  --help            Mostrar esta ajuda\n\n";
    exit(0);
}

$force = isset($options['force']);
$threshold = isset($options['threshold']) ? (float) $options['threshold'] : 0.05;
if ($threshold <= 0) {
    $threshold = 0.05;
}

echo "\n";
echo "ANALISE DE TIMINGS DE PALAVRAS\n";
echo "==============================\n\n";
echo "Threshold diferenca: {$threshold}s\n";
echo "Force: " . ($force ? 'Sim' : 'Nao') . "\n\n";

$words = Word::query()
    ->whereNotNull('word_timestamps')
    ->orderBy('word')
    ->get();

$stats = [
    'total_words_with_timestamps' => $words->count(),
    'total_analisadas' => 0,
    'total_com_diferencas' => 0,
    'decisoes_tomadas' => 0,
    'decisoes_media' => 0,
    'decisoes_automaticas' => 0,
    'saltadas' => 0,
    'ignoradas_sem_contexto' => 0,
    'ignoradas_sem_duracao' => 0,
    'ja_tinham_canonical' => 0,
];

$wordsDifferent = [];
$singleContextWords = [];

foreach ($words as $word) {
    $data = $word->word_timestamps;
    if (!is_array($data)) {
        $stats['ignoradas_sem_contexto']++;
        continue;
    }

    $contexts = $data['in_context'] ?? null;
    if (!is_array($contexts) || count($contexts) === 0) {
        $stats['ignoradas_sem_contexto']++;
        continue;
    }

    if (!$force && isset($data['canonical_timing'])) {
        $stats['ja_tinham_canonical']++;
        continue;
    }

    $stats['total_analisadas']++;

    if (count($contexts) === 1) {
        $singleContextWords[] = $word;
        continue;
    }

    $durations = [];
    foreach ($contexts as $context) {
        $duration = $context['duration'] ?? null;
        if (is_numeric($duration)) {
            $durations[] = (float) $duration;
        }
    }

    if (count($durations) < 2) {
        $stats['ignoradas_sem_duracao']++;
        continue;
    }

    $delta = max($durations) - min($durations);
    if ($delta > $threshold) {
        $wordsDifferent[] = [
            'word' => $word,
            'delta' => $delta,
        ];
    }
}

$stats['total_com_diferencas'] = count($wordsDifferent);

if (!empty($singleContextWords)) {
    echo "A definir canonical_timing automaticamente para " . count($singleContextWords) . " palavra(s) com 1 contexto...\n";

    foreach ($singleContextWords as $word) {
        $data = is_array($word->word_timestamps) ? $word->word_timestamps : [];
        $contexts = $data['in_context'] ?? [];
        $first = $contexts[0] ?? null;

        if (!is_array($first)) {
            continue;
        }

        $duration = is_numeric($first['duration'] ?? null) ? (float) $first['duration'] : null;
        $source = (string) ($first['exercise_id'] ?? 'single_context');

        $data['canonical_timing'] = [
            'duration' => $duration,
            'source' => $source,
        ];

        $word->word_timestamps = $data;
        $word->save();

        $stats['decisoes_tomadas']++;
        $stats['decisoes_automaticas']++;
    }

    echo "Concluido.\n\n";
}

if (empty($wordsDifferent)) {
    echo "Nenhuma palavra com timings significativamente diferentes encontrada.\n\n";
    printSummary($stats);
    exit(0);
}

$totalDifferent = count($wordsDifferent);

foreach ($wordsDifferent as $index => $item) {
    /** @var Word $word */
    $word = $item['word'];
    $data = is_array($word->word_timestamps) ? $word->word_timestamps : [];
    $contexts = $data['in_context'] ?? [];

    $wordNumber = $index + 1;
    echo "Palavra {$wordNumber} de {$totalDifferent} com timings diferentes\n";
    echo str_repeat('=', 32) . "\n";
    echo "Palavra: \"{$word->word}\"  (" . count($contexts) . " contextos)\n";
    echo "Delta duration: " . number_format((float) $item['delta'], 3) . "s\n";
    echo str_repeat('=', 32) . "\n";

    $validChoices = [];
    foreach ($contexts as $i => $context) {
        $choice = $i + 1;
        $sentence = (string) ($context['sentence'] ?? '(sem frase)');
        $startTime = formatSeconds($context['startTime'] ?? null);
        $duration = formatSeconds($context['duration'] ?? null);

        echo "[{$choice}] Frase: \"{$sentence}\"\n";
        echo "    startTime: {$startTime}  duration: {$duration}\n\n";

        $validChoices[] = (string) $choice;
    }

    echo "Qual timing usar como referencia?\n";
    echo '[' . implode('/', $validChoices) . '/m] (m = media, enter = saltar): ';

    $answer = trim((string) fgets(STDIN));

    if ($answer === '') {
        $stats['saltadas']++;
        echo "Saltado.\n\n";
        continue;
    }

    if ($answer === 'm') {
        $durations = [];
        foreach ($contexts as $context) {
            if (is_numeric($context['duration'] ?? null)) {
                $durations[] = (float) $context['duration'];
            }
        }

        if (empty($durations)) {
            $stats['saltadas']++;
            echo "Sem duracoes validas para media. Saltado.\n\n";
            continue;
        }

        $average = array_sum($durations) / count($durations);
        $data['canonical_timing'] = [
            'duration' => round($average, 3),
            'source' => 'average',
        ];

        $word->word_timestamps = $data;
        $word->save();

        $stats['decisoes_tomadas']++;
        $stats['decisoes_media']++;

        echo "Guardado canonical_timing com media: " . number_format($average, 3) . "s\n\n";
        continue;
    }

    if (!in_array($answer, $validChoices, true)) {
        $stats['saltadas']++;
        echo "Opcao invalida. Saltado.\n\n";
        continue;
    }

    $selectedIndex = ((int) $answer) - 1;
    $selected = $contexts[$selectedIndex] ?? null;

    if (!is_array($selected)) {
        $stats['saltadas']++;
        echo "Contexto invalido. Saltado.\n\n";
        continue;
    }

    $selectedDuration = is_numeric($selected['duration'] ?? null)
        ? round((float) $selected['duration'], 3)
        : null;

    $selectedSource = (string) ($selected['exercise_id'] ?? 'manual');

    $data['canonical_timing'] = [
        'duration' => $selectedDuration,
        'source' => $selectedSource,
    ];

    $word->word_timestamps = $data;
    $word->save();

    $stats['decisoes_tomadas']++;

    echo "Guardado canonical_timing do contexto [{$answer}].\n\n";
}

printSummary($stats);

function formatSeconds($value): string
{
    if (!is_numeric($value)) {
        return 'null';
    }

    return number_format((float) $value, 3) . 's';
}

function printSummary(array $stats): void
{
    echo "\n";
    echo "RESUMO\n";
    echo "======\n";
    echo "Total words com word_timestamps: {$stats['total_words_with_timestamps']}\n";
    echo "Total de palavras analisadas: {$stats['total_analisadas']}\n";
    echo "Total com timings diferentes: {$stats['total_com_diferencas']}\n";
    echo "Decisoes tomadas: {$stats['decisoes_tomadas']}\n";
    echo "  - Automaticas (1 contexto): {$stats['decisoes_automaticas']}\n";
    echo "  - Media: {$stats['decisoes_media']}\n";
    echo "Saltadas: {$stats['saltadas']}\n";
    echo "Ja tinham canonical (sem force): {$stats['ja_tinham_canonical']}\n";
    echo "Ignoradas sem contexto: {$stats['ignoradas_sem_contexto']}\n";
    echo "Ignoradas sem duracoes suficientes: {$stats['ignoradas_sem_duracao']}\n";
    echo "\n";
}
