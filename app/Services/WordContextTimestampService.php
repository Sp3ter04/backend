<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\Word;
use Illuminate\Support\Facades\Log;

class WordContextTimestampService
{
    /**
     * Normaliza token para correspondencia com words.word.
     */
    public function normalizeToken(string $token): string
    {
        $token = mb_strtolower(trim($token));
        $token = preg_replace('/[^\p{L}\p{N}]/u', '', $token) ?? '';

        return $token;
    }

    /**
     * Sincroniza word_timestamps.in_context para as palavras de um exercicio.
     */
    public function syncFromExercise(Exercise $exercise): void
    {
        $contextsByWord = $this->extractContextsByWord($exercise);

        if (empty($contextsByWord)) {
            return;
        }

        foreach ($contextsByWord as $normalizedWord => $entries) {
            $word = Word::where('word', $normalizedWord)->first();

            if (!$word) {
                continue;
            }

            $current = $word->word_timestamps;
            $inContext = is_array($current) && isset($current['in_context']) && is_array($current['in_context'])
                ? $current['in_context']
                : [];

            // Remove entradas antigas deste exercicio para evitar duplicados no regenerate.
            $inContext = array_values(array_filter($inContext, function ($item) use ($exercise) {
                return ($item['exercise_id'] ?? null) !== $exercise->id;
            }));

            $word->update([
                'word_timestamps' => [
                    'in_context' => array_values(array_merge($inContext, $entries)),
                ],
            ]);
        }
    }

    /**
     * Reconstroi os contextos de palavras a partir de todos os exercicios.
     *
     * @return array{exercises:int,contexts:int,words:int}
     */
    public function rebuildFromAllExercises(bool $clearExisting = true): array
    {
        if ($clearExisting) {
            Word::query()->update(['word_timestamps' => null]);
        }

        $stats = [
            'exercises' => 0,
            'contexts' => 0,
            'words' => 0,
        ];

        $seenWords = [];

        Exercise::query()
            ->whereNotNull('word_timestamps')
            ->whereNotNull('sentence')
            ->where('sentence', '!=', '')
            ->cursor()
            ->each(function (Exercise $exercise) use (&$stats, &$seenWords) {
                $contextsByWord = $this->extractContextsByWord($exercise);

                if (empty($contextsByWord)) {
                    return;
                }

                foreach ($contextsByWord as $normalizedWord => $entries) {
                    $word = Word::where('word', $normalizedWord)->first();
                    if (!$word) {
                        continue;
                    }

                    $current = $word->word_timestamps;
                    $inContext = is_array($current) && isset($current['in_context']) && is_array($current['in_context'])
                        ? $current['in_context']
                        : [];

                    $word->update([
                        'word_timestamps' => [
                            'in_context' => array_values(array_merge($inContext, $entries)),
                        ],
                    ]);

                    $seenWords[$word->id] = true;
                    $stats['contexts'] += count($entries);
                }

                $stats['exercises']++;
            });

        $stats['words'] = count($seenWords);

        return $stats;
    }

    /**
     * @return array<string,array<int,array{exercise_id:string,sentence:string,startTime:float,duration:float|null}>>
     */
    protected function extractContextsByWord(Exercise $exercise): array
    {
        $tokens = $exercise->word_timestamps;

        if (!is_array($tokens) || empty($tokens)) {
            return [];
        }

        $audioDuration = $this->getAudioDurationSeconds($exercise->audio_url_1);
        $startTimes = $this->buildTokenStartTimes($tokens, $audioDuration);
        $contextsByWord = [];

        foreach ($tokens as $index => $tokenInfo) {
            $type = (string) ($tokenInfo['type'] ?? 'word');
            if ($type !== 'word') {
                continue;
            }

            $token = (string) ($tokenInfo['token'] ?? '');
            $normalized = $this->normalizeToken($token);
            if ($normalized === '') {
                continue;
            }

            $start = $startTimes[$index] ?? null;
            if (!is_numeric($start)) {
                continue;
            }

            $start = (float) $start;
            $nextStart = $this->findNextStartTimeFromArray($startTimes, $index + 1);

            $duration = null;
            if ($nextStart !== null) {
                $duration = max(0.0, $nextStart - $start);
            } elseif ($audioDuration !== null) {
                $duration = max(0.0, $audioDuration - $start);
            }

            $contextsByWord[$normalized][] = [
                'exercise_id' => (string) $exercise->id,
                'sentence' => (string) $exercise->sentence,
                'startTime' => $start,
                'duration' => $duration,
            ];

            // Para tokens hifenizados (ex.: "deu-me", "diz-se"), também emitir
            // entradas para cada sub-palavra, com tempos proporcionais ao comprimento.
            if (mb_strpos($token, '-') !== false) {
                $pieces = preg_split('/-+/u', $token, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if (count($pieces) > 1) {
                    $totalLen = 0;
                    foreach ($pieces as $p) { $totalLen += max(1, mb_strlen($p)); }
                    $totalDuration = $duration ?? 0.0;
                    $offset = 0.0;
                    foreach ($pieces as $piece) {
                        $pieceNorm = $this->normalizeToken($piece);
                        $pieceLen = max(1, mb_strlen($piece));
                        $pieceDuration = $totalDuration > 0 ? $totalDuration * ($pieceLen / $totalLen) : null;

                        if ($pieceNorm !== '' && $pieceNorm !== $normalized) {
                            $contextsByWord[$pieceNorm][] = [
                                'exercise_id' => (string) $exercise->id,
                                'sentence' => (string) $exercise->sentence,
                                'startTime' => $start + $offset,
                                'duration' => $pieceDuration,
                            ];
                        }
                        if ($totalDuration > 0) { $offset += $totalDuration * ($pieceLen / $totalLen); }
                    }
                }
            }
        }

        return $contextsByWord;
    }

    /**
     * @param array<int,float|null> $startTimes
     */
    protected function findNextStartTimeFromArray(array $startTimes, int $startIndex): ?float
    {
        for ($i = $startIndex; $i < count($startTimes); $i++) {
            $candidate = $startTimes[$i] ?? null;

            if (is_numeric($candidate)) {
                return (float) $candidate;
            }
        }

        return null;
    }

    /**
     * Preenche startTime em falta de forma monotónica usando duração do áudio.
     *
     * @param array<int,mixed> $tokens
     * @return array<int,float|null>
     */
    protected function buildTokenStartTimes(array $tokens, ?float $audioDuration): array
    {
        $count = count($tokens);
        if ($count === 0) {
            return [];
        }

        $startTimes = [];
        for ($i = 0; $i < $count; $i++) {
            $candidate = $tokens[$i]['startTime'] ?? null;
            $startTimes[$i] = is_numeric($candidate) ? (float) $candidate : null;
        }

        // Garantir referência inicial.
        if (!is_numeric($startTimes[0] ?? null)) {
            $startTimes[0] = 0.0;
        }

        $step = 0.3;
        if (is_numeric($audioDuration) && $audioDuration > 0) {
            $step = max(0.05, (float) $audioDuration / max(1, $count));
        }

        // Interpolar segmentos entre âncoras conhecidas.
        $knownIndexes = [];
        foreach ($startTimes as $idx => $value) {
            if (is_numeric($value)) {
                $knownIndexes[] = $idx;
            }
        }

        sort($knownIndexes);

        for ($k = 0; $k < count($knownIndexes) - 1; $k++) {
            $left = $knownIndexes[$k];
            $right = $knownIndexes[$k + 1];

            if ($right <= $left + 1) {
                continue;
            }

            $leftValue = (float) $startTimes[$left];
            $rightValue = (float) $startTimes[$right];
            $segmentStep = ($rightValue - $leftValue) / ($right - $left);

            for ($i = $left + 1; $i < $right; $i++) {
                $startTimes[$i] = $leftValue + ($segmentStep * ($i - $left));
            }
        }

        // Preencher para a direita da última âncora.
        $lastKnown = null;
        for ($i = 0; $i < $count; $i++) {
            if (is_numeric($startTimes[$i])) {
                $lastKnown = (float) $startTimes[$i];
                continue;
            }

            if ($lastKnown === null) {
                $startTimes[$i] = 0.0;
                $lastKnown = 0.0;
                continue;
            }

            $lastKnown += $step;
            $startTimes[$i] = $lastKnown;
        }

        // Clamp opcional pela duração total para evitar overshoot.
        if (is_numeric($audioDuration) && $audioDuration > 0) {
            $max = (float) $audioDuration;
            for ($i = 0; $i < $count; $i++) {
                if (is_numeric($startTimes[$i])) {
                    $startTimes[$i] = min((float) $startTimes[$i], $max);
                }
            }
        }

        return $startTimes;
    }

    protected function getAudioDurationSeconds(?string $audioPath): ?float
    {
        if (empty($audioPath)) {
            return null;
        }

        $absolutePath = storage_path('app/public/' . ltrim($audioPath, '/'));
        if (!is_file($absolutePath)) {
            return null;
        }

        if (!function_exists('shell_exec')) {
            return null;
        }

        try {
            $ffprobe = trim((string) shell_exec('which ffprobe'));
            if ($ffprobe === '') {
                return null;
            }

            $cmd = sprintf(
                '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>&1',
                escapeshellarg($ffprobe),
                escapeshellarg($absolutePath)
            );

            $output = trim((string) shell_exec($cmd));

            return is_numeric($output) ? (float) $output : null;
        } catch (\Throwable $e) {
            Log::warning('Failed to read audio duration: ' . $e->getMessage());
            return null;
        }
    }
}
