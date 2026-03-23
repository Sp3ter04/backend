<?php

/**
 * Regenerate All Word Audio with Speed Control
 * 
 * Run: php regenerate_all_words.php --speed=0.85 --force
 */

require __DIR__.'/vendor/autoload.php';

use App\Models\Word;
use App\Services\AudioService;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Configuration
$config = [
    'speed' => 0.85,
    'lang' => 'pt-PT',
    'limit' => null,
    'force' => false,
];

// Parse arguments
$options = getopt('', ['speed::', 'lang::', 'limit::', 'force', 'help']);

if (isset($options['help'])) {
    echo "
🎵 Regenerate Word Audio with Speed Control

Usage: php regenerate_all_words.php [options]

Options:
  --speed=0.85      Audio speed (0.5-1.5, default: 0.85)
  --lang=pt-PT      Language code
  --limit=10        Process only N words
  --force           Force regenerate existing
  --help            Show help

Examples:
  php regenerate_all_words.php --speed=0.85 --force
  php regenerate_all_words.php --limit=10

";
    exit(0);
}

if (isset($options['speed'])) $config['speed'] = (float)$options['speed'];
if (isset($options['lang'])) $config['lang'] = $options['lang'];
if (isset($options['limit'])) $config['limit'] = (int)$options['limit'];
if (isset($options['force'])) $config['force'] = true;

echo "🎵 REGENERATE WORD AUDIO\n";
echo "========================\n\n";
echo "📋 Config: Speed {$config['speed']}x, Lang {$config['lang']}\n\n";

// Get words
$query = Word::whereNotNull('word')->where('word', '!=', '');
if ($config['limit']) $query->limit($config['limit']);
$words = $query->get();
$total = $words->count();

if ($total === 0) {
    echo "⚠️ No words found\n";
    exit(0);
}

echo "📊 Found {$total} words\n\n";

$stats = ['success' => 0, 'skipped' => 0, 'failed' => 0];
$startTime = microtime(true);

foreach ($words as $i => $word) {
    $num = $i + 1;
    $text = $word->word;
    echo "[{$num}/{$total}] {$text}\n";
    
    try {
        if (!$config['force'] && !empty($word->audio_url)) {
            echo "         ⏭️  Skipped\n";
            $stats['skipped']++;
            continue;
        }

        $audioResult = AudioService::generateAndSaveWithTimestamps(
            $text,
            $config['lang'],
            'words',
            null,
            $config['speed']
        );

        if (!$audioResult || empty($audioResult['path'])) {
            echo "         ❌ TTS failed\n";
            $stats['failed']++;
            continue;
        }

        $word->audio_url = $audioResult['path'];
        $word->save();

        echo "         ✅ {$audioResult['path']}\n";
        $stats['success']++;
        
    } catch (\Exception $e) {
        echo "         ❌ Error: {$e->getMessage()}\n";
        $stats['failed']++;
    }
    
    usleep(200000); // 0.2s delay
}

$time = microtime(true) - $startTime;

echo "\n✅ COMPLETE!\n";
echo "============\n";
echo "Success: {$stats['success']} ✅\n";
echo "Skipped: {$stats['skipped']} ⏭️\n";
echo "Failed:  {$stats['failed']} ❌\n";
echo "Time:    " . number_format($time, 2) . "s\n";
echo "Avg:     " . number_format($time / $total, 2) . "s per word\n";
