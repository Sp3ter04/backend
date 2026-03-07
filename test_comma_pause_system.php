<?php

/**
 * Test Comma-Based Pause System
 * 
 * Tests the simple comma insertion approach for creating pauses in TTS
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Storage;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Initialize service
$service = new App\Services\SimplePausedAudioService();

echo "🎵 COMMA-BASED PAUSE SYSTEM TEST\n";
echo "=================================\n\n";

// Test sentences
$testSentences = [
    "A menina vê a mamã",
    "O gato dorme na cama",
    "Eu gosto de ler livros",
];

echo "📝 TRANSFORMATION TEST\n";
echo "----------------------\n";
foreach ($testSentences as $sentence) {
    $transformed = $service->insertCommasForPauses($sentence);
    echo "Input:  {$sentence}\n";
    echo "Output: {$transformed}\n";
    echo "\n";
}

echo "\n🎙️ AUDIO GENERATION TEST\n";
echo "-------------------------\n";

$testSentence = "A menina vê a mamã";
echo "Generating audio for: {$testSentence}\n";
echo "Transformed version: " . $service->insertCommasForPauses($testSentence) . "\n\n";

$startTime = microtime(true);

// Generate with commas (paused)
echo "1️⃣ Generating WITH pauses (comma method)...\n";
$pausedPath = $service->generateSentenceAudio($testSentence, 'pt-PT', true, 0.9);

if ($pausedPath) {
    $fullPath = storage_path('app/public/' . $pausedPath);
    $fileSize = filesize($fullPath) / 1024; // KB
    $duration = shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($fullPath));
    $duration = trim($duration);
    
    echo "   ✅ Success!\n";
    echo "   📁 Path: {$pausedPath}\n";
    echo "   📊 Size: " . number_format($fileSize, 2) . " KB\n";
    echo "   ⏱️ Duration: {$duration}s\n";
    echo "   🌐 URL: http://localhost:8000/storage/{$pausedPath}\n\n";
} else {
    echo "   ❌ Failed!\n\n";
}

// Generate without commas (normal)
echo "2️⃣ Generating WITHOUT pauses (normal method)...\n";
$normalPath = $service->generateSentenceAudio($testSentence, 'pt-PT', false, 0.9);

if ($normalPath) {
    $fullPath = storage_path('app/public/' . $normalPath);
    $fileSize = filesize($fullPath) / 1024; // KB
    $duration = shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($fullPath));
    $duration = trim($duration);
    
    echo "   ✅ Success!\n";
    echo "   📁 Path: {$normalPath}\n";
    echo "   📊 Size: " . number_format($fileSize, 2) . " KB\n";
    echo "   ⏱️ Duration: {$duration}s\n";
    echo "   🌐 URL: http://localhost:8000/storage/{$normalPath}\n\n";
} else {
    echo "   ❌ Failed!\n\n";
}

$elapsedTime = microtime(true) - $startTime;

echo "⏱️ Total generation time: " . number_format($elapsedTime, 2) . " seconds\n\n";

// Comparison
echo "📊 COMPARISON\n";
echo "-------------\n";
echo "Method 1: Comma insertion (SIMPLE)\n";
echo "  • Insert commas between words\n";
echo "  • Single TTS API call\n";
echo "  • Google TTS naturally pauses at commas\n";
echo "  • Fast: ~1-2 seconds per sentence\n";
echo "  • Pause duration: ~0.3-0.5s (automatic)\n\n";

echo "Method 2: Word-by-word concatenation (COMPLEX)\n";
echo "  • Generate audio per word\n";
echo "  • Multiple TTS API calls\n";
echo "  • FFmpeg concatenation with silence files\n";
echo "  • Slow: ~5-10 seconds per sentence\n";
echo "  • Pause duration: Configurable (e.g., 0.3s)\n\n";

echo "✅ RECOMMENDATION: Use comma insertion method!\n";
echo "   • Much simpler and faster\n";
echo "   • Natural pronunciation\n";
echo "   • No FFmpeg complexity\n";
echo "   • Scales better to 100+ sentences\n\n";

// Test batch generation
echo "📦 BATCH GENERATION TEST\n";
echo "------------------------\n";
echo "Generating 3 sentences...\n\n";

$batchStart = microtime(true);
$results = $service->batchGenerate($testSentences, 'pt-PT', true, 0.9);
$batchTime = microtime(true) - $batchStart;

foreach ($testSentences as $sentence) {
    $path = $results[$sentence];
    $status = $path ? '✅' : '❌';
    echo "{$status} {$sentence}\n";
    if ($path) {
        echo "    → {$path}\n";
    }
}

echo "\n⏱️ Batch time: " . number_format($batchTime, 2) . " seconds\n";
echo "📈 Average: " . number_format($batchTime / count($testSentences), 2) . " seconds per sentence\n\n";

echo "✅ TEST COMPLETE!\n";
echo "Listen to the audio files to compare:\n";
echo "  • WITH pauses: http://localhost:8000/storage/{$pausedPath}\n";
echo "  • WITHOUT pauses: http://localhost:8000/storage/{$normalPath}\n";
