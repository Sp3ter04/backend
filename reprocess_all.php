<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(App\Services\ExerciseProcessorService::class);
$exercises = App\Models\Exercise::whereNotNull('sentence')->where('sentence','<>', '')->orderBy('number')->get();

$ok = 0; $err = 0;
foreach ($exercises as $ex) {
    try {
        $svc->process($ex);
        echo "OK #" . $ex->number . ": " . $ex->sentence . PHP_EOL;
        $ok++;
    } catch (\Throwable $e) {
        echo "ERR #" . $ex->number . ": " . $e->getMessage() . PHP_EOL;
        $err++;
    }
}

echo PHP_EOL . "=== DONE: OK=$ok ERR=$err ===" . PHP_EOL;

// Final check: missing words
$sentenceMissing = [];
App\Models\Exercise::whereNotNull('sentence')->where('sentence','<>', '')->pluck('sentence')->each(function($sentence) use (&$sentenceMissing) {
    $text = preg_replace('/[.,;:!?¿¡()\[\]{}\"\'«»\-–—…\/\\\\]/u', ' ', $sentence);
    $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    foreach ($words as $raw) {
        $w = mb_strtolower(trim($raw));
        if ($w !== '' && !App\Models\Word::where('word', $w)->exists()) {
            $sentenceMissing[$w] = true;
        }
    }
});
echo 'Still missing after reprocess: ' . count($sentenceMissing) . PHP_EOL;
foreach (array_keys($sentenceMissing) as $m) { echo "  $m" . PHP_EOL; }
