<?php

/**
 * Regenerate All Word Audio with Speed Control
 * 
 * Run: php regenerate_all_words.php --speed=0.85 --force
 */

require __DIR__.'/vendor/autoload.php';

use App\Models\Word;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

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
$ffmpeg = trim(shell_exec('which ffmpeg') ?? '');

foreach ($words as $i => $word) {
    $num = $i + 1;
    $text = $word->word;
    echo "[{$num}/{$total}] {$text}\n";
    
    try {
        $filename = generateWordFilename($text, $config['speed']);
        $path = "audio/words/{$filename}";
        
        if (!$config['force'] && Storage::disk('public')->exists($path)) {
            echo "         ⏭️  Skipped\n";
            $stats['skipped']++;
            continue;
        }
        
        // Fetch audio
        $audioData = fetchTTS($text, $config['lang']);
        if (!$audioData) {
            echo "         ❌ TTS failed\n";
            $stats['failed']++;
            continue;
        }
        
        // Apply speed if FFmpeg available
        if ($config['speed'] !== 1.0 && !empty($ffmpeg)) {
            $audioData = applySpeed($audioData, $config['speed'], $ffmpeg);
        }
        
        // Save
        Storage::disk('public')->put($path, $audioData);
        $word->audio_url = $path;
        $word->save();
        
        echo "         ✅ {$path}\n";
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

// Helper functions
function generateWordFilename($word, $speed) {
    $slug = preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($word));
    $slug = trim($slug, '-');
    $speedStr = str_replace('.', '', (string)$speed);
    $hash = substr(md5($word . $speed), 0, 8);
    return substr($slug, 0, 30) . "_{$speedStr}x_{$hash}.mp3";
}

function fetchTTS($text, $lang) {
    $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl={$lang}&client=tw-ob&q=" . urlencode($text);
    $response = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0',
        'Referer' => 'https://translate.google.com/',
    ])->timeout(15)->get($url);
    
    return $response->successful() && strlen($response->body()) > 100 ? $response->body() : null;
}

function applySpeed($audioData, $speed, $ffmpeg) {
    $temp = storage_path('app/temp');
    if (!is_dir($temp)) mkdir($temp, 0755, true);
    
    $in = $temp . '/' . uniqid('in_') . '.mp3';
    $out = $temp . '/' . uniqid('out_') . '.mp3';
    
    file_put_contents($in, $audioData);
    
    $cmd = sprintf(
        '%s -i %s -filter:a "atempo=%s,loudnorm" -ar 44100 -b:a 128k %s -y 2>&1',
        escapeshellarg($ffmpeg),
        escapeshellarg($in),
        $speed,
        escapeshellarg($out)
    );
    
    exec($cmd, $output, $code);
    
    $result = $code === 0 && file_exists($out) ? file_get_contents($out) : $audioData;
    
    @unlink($in);
    @unlink($out);
    
    return $result;
}
