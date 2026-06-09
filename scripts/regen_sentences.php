<?php
/**
 * Standalone script to regenerate all exercise sentence audios
 * Usage from project root: php scripts/regen_sentences.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Exercise;
use App\Services\SimplePausedAudioService;

$service = app(SimplePausedAudioService::class);
$exercises = Exercise::whereNotNull('sentence')->where('sentence', '!=', '')->orderBy('number')->get();
$total = $exercises->count();

echo "Total exercises: {$total}\n";

$success = 0;
$skipped = 0;
$failed  = 0;

foreach ($exercises as $i => $exercise) {
    $n = $i + 1;
    $sentence = $exercise->sentence;
    $short = mb_substr($sentence, 0, 50);
    echo "[{$n}/{$total}] #{$exercise->number}: {$short}";

    try {
        if ($service->audioExists($sentence, true, 0.9, $exercise->number)) {
            echo " — skipped (exists)\n";
            $skipped++;
            continue;
        }

        $result = $service->generateSentenceAudioWithTimestamps(
            $sentence,
            'pt-PT',
            true,
            0.9,
            $exercise->number,
            false
        );

        if ($result && !empty($result['path'])) {
            $exercise->audio_url_1 = $result['path'];
            if (!empty($result['word_timestamps'])) {
                $exercise->word_timestamps = $result['word_timestamps'];
            }
            $exercise->save();
            echo " — OK\n";
            $success++;
        } else {
            echo " — FAILED\n";
            $failed++;
        }
    } catch (\Throwable $e) {
        echo " — ERROR: " . $e->getMessage() . "\n";
        $failed++;
    }

    usleep(150_000); // 150ms anti rate-limit
}

echo "\n=== RESULT ===\n";
echo "Success: {$success}\n";
echo "Skipped: {$skipped}\n";
echo "Failed:  {$failed}\n";
