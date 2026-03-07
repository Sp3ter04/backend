<?php

/**
 * Generate Audio Test with No-Pause Words
 * 
 * Tests audio generation with the improved comma logic
 */

require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Initialize service
$service = new App\Services\SimplePausedAudioService();

echo "🎵 AUDIO GENERATION TEST - NO-PAUSE WORDS\n";
echo "==========================================\n\n";

$testSentence = "A menina vê a mamã";

echo "Test sentence: \"{$testSentence}\"\n\n";

// Show transformation
$transformed = $service->insertCommasForPauses($testSentence);
echo "Transformation:\n";
echo "  Original:    \"{$testSentence}\"\n";
echo "  Transformed: \"{$transformed}\"\n\n";

// Generate audio with new logic
echo "Generating audio with improved pause logic...\n\n";

$startTime = microtime(true);

$audioPath = $service->generateSentenceAudio($testSentence, 'pt-PT', true, 0.9);

$elapsedTime = microtime(true) - $startTime;

if ($audioPath) {
    $fullPath = storage_path('app/public/' . $audioPath);
    $fileSize = filesize($fullPath) / 1024; // KB
    $duration = trim(shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($fullPath)));
    
    echo "✅ SUCCESS!\n\n";
    echo "Details:\n";
    echo "  📁 Path:     {$audioPath}\n";
    echo "  📊 Size:     " . number_format($fileSize, 2) . " KB\n";
    echo "  ⏱️  Duration: {$duration}s\n";
    echo "  ⚡ Generated: " . number_format($elapsedTime, 2) . "s\n";
    echo "  🌐 URL:      http://localhost:8000/storage/{$audioPath}\n\n";
    
    echo "🎧 Listen to verify the natural pauses!\n\n";
    
    echo "Expected result:\n";
    echo "  \"A menina\" (no pause) → \"vê\" (pause) → \"a mamã\"\n";
    echo "  Pauses only between meaningful words!\n";
    
} else {
    echo "❌ Failed to generate audio\n";
}
