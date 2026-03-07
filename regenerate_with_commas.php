<?php

/**
 * Regenerate All Exercise Audio with Comma Pauses
 * 
 * Simple script to regenerate all exercise audio using comma insertion method
 * Run: php regenerate_with_commas.php
 */

require __DIR__.'/vendor/autoload.php';

use App\Models\Exercise;
use App\Services\SimplePausedAudioService;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Configuration
$config = [
    'speed' => 0.9,           // Audio speed (0.5-1.5)
    'lang' => 'pt-PT',        // Language code
    'insertPauses' => true,   // Insert commas for pauses
    'limit' => null,          // Limit processing (null = all)
    'force' => false,         // Force regenerate existing audio
];

// Parse command line arguments
$options = getopt('', [
    'speed::',
    'lang::',
    'limit::',
    'force',
    'help',
]);

if (isset($options['help'])) {
    echo "
🎵 Regenerate Exercise Audio with Comma Pauses

Usage: php regenerate_with_commas.php [options]

Options:
  --speed=0.9       Audio speed multiplier (0.5-1.5)
  --lang=pt-PT      Language code
  --limit=10        Process only first N exercises
  --force           Force regenerate even if audio exists
  --help            Show this help message

Examples:
  php regenerate_with_commas.php
  php regenerate_with_commas.php --limit=5
  php regenerate_with_commas.php --speed=0.85 --force
  php regenerate_with_commas.php --lang=pt-BR

";
    exit(0);
}

if (isset($options['speed'])) {
    $config['speed'] = (float) $options['speed'];
}
if (isset($options['lang'])) {
    $config['lang'] = $options['lang'];
}
if (isset($options['limit'])) {
    $config['limit'] = (int) $options['limit'];
}
if (isset($options['force'])) {
    $config['force'] = true;
}

// Validate speed
if ($config['speed'] < 0.5 || $config['speed'] > 1.5) {
    echo "❌ Error: Speed must be between 0.5 and 1.5\n";
    exit(1);
}

// Initialize service
$audioService = new SimplePausedAudioService();

echo "🎵 REGENERATE EXERCISE AUDIO WITH COMMA PAUSES\n";
echo "==============================================\n\n";

echo "📋 Configuration:\n";
echo "   Speed:         {$config['speed']}x\n";
echo "   Language:      {$config['lang']}\n";
echo "   Insert pauses: " . ($config['insertPauses'] ? 'Yes' : 'No') . "\n";
echo "   Limit:         " . ($config['limit'] ? $config['limit'] : 'All') . "\n";
echo "   Force:         " . ($config['force'] ? 'Yes' : 'No') . "\n\n";

// Get exercises
$query = Exercise::query()->whereNotNull('sentence')->where('sentence', '!=', '');

if ($config['limit']) {
    $query->limit($config['limit']);
}

$exercises = $query->get();
$total = $exercises->count();

if ($total === 0) {
    echo "⚠️  No exercises found with sentences.\n";
    exit(0);
}

echo "📊 Found {$total} exercises to process\n\n";

// Process exercises
$stats = [
    'total' => $total,
    'success' => 0,
    'skipped' => 0,
    'failed' => 0,
];

$startTime = microtime(true);

foreach ($exercises as $index => $exercise) {
    $num = $index + 1;
    $sentence = $exercise->sentence;
    $shortSentence = substr($sentence, 0, 50) . (strlen($sentence) > 50 ? '...' : '');
    
    echo "[{$num}/{$total}] Processing: {$shortSentence}\n";
    
    // Check if already exists (unless force)
    if (!$config['force'] && $audioService->audioExists($sentence, $config['insertPauses'], $config['speed'])) {
        echo "         ⏭️  Skipped (already exists)\n";
        $stats['skipped']++;
        continue;
    }
    
    // Generate audio
    $audioPath = $audioService->generateSentenceAudio(
        $sentence,
        $config['lang'],
        $config['insertPauses'],
        $config['speed']
    );
    
    if ($audioPath) {
        // Update exercise
        $exercise->audio_url_1 = $audioPath;
        $exercise->save();
        
        // Show transformation
        $transformed = $audioService->insertCommasForPauses($sentence);
        echo "         ✅ Success! → {$audioPath}\n";
        echo "         📝 \"{$transformed}\"\n";
        
        $stats['success']++;
    } else {
        echo "         ❌ Failed!\n";
        $stats['failed']++;
    }
    
    echo "\n";
}

$endTime = microtime(true);
$totalTime = $endTime - $startTime;
$avgTime = $total > 0 ? $totalTime / $total : 0;

echo "\n";
echo "✅ PROCESSING COMPLETE!\n";
echo "=======================\n\n";

echo "📊 Statistics:\n";
echo "   Total:         {$stats['total']}\n";
echo "   Success:       {$stats['success']} ✅\n";
echo "   Skipped:       {$stats['skipped']} ⏭️\n";
echo "   Failed:        {$stats['failed']} ❌\n\n";

echo "⏱️  Performance:\n";
echo "   Total time:    " . number_format($totalTime, 2) . " seconds\n";
echo "   Average time:  " . number_format($avgTime, 2) . " seconds per exercise\n\n";

if ($stats['success'] > 0) {
    echo "📝 Sample transformation:\n";
    $firstExercise = $exercises->first();
    $originalSentence = $firstExercise->sentence;
    $transformedSentence = $audioService->insertCommasForPauses($originalSentence);
    
    echo "   Original:    \"{$originalSentence}\"\n";
    echo "   Transformed: \"{$transformedSentence}\"\n\n";
}

echo "🎧 Audio files saved in: storage/app/public/audio/sentences/\n";
echo "🌐 Public URL: http://localhost:8000/storage/audio/sentences/\n\n";

if ($stats['failed'] > 0) {
    echo "⚠️  Some exercises failed. Check logs for details.\n";
    exit(1);
}

echo "🎉 All done!\n";
exit(0);
