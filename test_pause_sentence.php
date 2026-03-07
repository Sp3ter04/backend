#!/usr/bin/env php
<?php

/**
 * Test script: Generate sentence audio with 0.3s pauses between words
 * 
 * Technical Approach:
 * 1. Split sentence into individual words
 * 2. Generate TTS audio for each word using Google Translate
 * 3. Process each word audio (apply speed, normalize volume)
 * 4. Generate silence file (0.3s)
 * 5. Concatenate: word1 + silence + word2 + silence + word3...
 * 6. Use FFmpeg concat demuxer (fast, no re-encoding)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🎙️  Testing sentence with 0.3s pauses between words\n\n";

// Test sentence
$sentence = "A menina vê a mamã.";
echo "📝 Sentence: {$sentence}\n\n";

// Verify FFmpeg
$ffmpegPath = trim(shell_exec('which ffmpeg'));
if (empty($ffmpegPath)) {
    echo "❌ Error: FFmpeg not found\n";
    exit(1);
}

$storagePath = storage_path('app/public');
$tempDir = $storagePath . '/audio/temp_test';

// Clean and create temp directory
if (file_exists($tempDir)) {
    array_map('unlink', glob($tempDir . '/*'));
    rmdir($tempDir);
}
mkdir($tempDir, 0755, true);

try {
    // Step 1: Split into words (remove punctuation)
    $words = preg_split('/\s+/', preg_replace('/[.,;:!?¿¡()\[\]{}"\'«»\-–—…\/\\\\]/', ' ', $sentence), -1, PREG_SPLIT_NO_EMPTY);
    
    echo "🔤 Words detected: " . count($words) . " - [" . implode(', ', $words) . "]\n\n";
    
    $wordFiles = [];
    
    // Step 2 & 3: Generate and process each word audio
    foreach ($words as $index => $word) {
        echo "  [{$index}] Generating audio for: '{$word}'\n";
        
        // Google Translate TTS request
        $encodedText = urlencode($word);
        $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=pt-PT&client=tw-ob&q={$encodedText}";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_REFERER, 'https://translate.google.com/');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $audioData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || strlen($audioData) < 100) {
            echo "    ⚠️  Failed to generate audio\n";
            continue;
        }
        
        // Save raw audio
        $rawFile = $tempDir . '/word_' . $index . '_raw.mp3';
        file_put_contents($rawFile, $audioData);
        echo "    ✓ Raw audio: " . round(strlen($audioData) / 1024, 1) . " KB\n";
        
        // Process: apply speed 0.9x + normalize volume
        $processedFile = $tempDir . '/word_' . $index . '.mp3';
        $cmd = sprintf(
            '%s -i %s -filter:a "atempo=0.9,loudnorm" -ar 44100 -b:a 128k %s -y 2>&1',
            escapeshellarg($ffmpegPath),
            escapeshellarg($rawFile),
            escapeshellarg($processedFile)
        );
        
        exec($cmd, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($processedFile)) {
            $wordFiles[] = $processedFile;
            
            // Get duration
            $duration = trim(shell_exec(sprintf(
                'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
                escapeshellarg($processedFile)
            )));
            
            echo "    ✓ Processed (0.9x): {$duration}s\n";
            unlink($rawFile);
        } else {
            echo "    ⚠️  Processing failed\n";
        }
        
        usleep(150000); // 0.15s delay between API calls
    }
    
    if (empty($wordFiles)) {
        echo "\n❌ No word audio files generated\n";
        exit(1);
    }
    
    echo "\n";
    
    // Step 4: Generate 0.3s silence file
    echo "🔇 Generating 0.3s silence file...\n";
    $silenceFile = $tempDir . '/silence_0.3s.mp3';
    $cmd = sprintf(
        '%s -f lavfi -i anullsrc=r=44100:cl=stereo -t 0.3 -q:a 2 -acodec libmp3lame %s -y 2>&1',
        escapeshellarg($ffmpegPath),
        escapeshellarg($silenceFile)
    );
    exec($cmd);
    
    if (!file_exists($silenceFile)) {
        echo "❌ Failed to generate silence file\n";
        exit(1);
    }
    echo "✓ Silence file created (0.3s)\n\n";
    
    // Step 5: Create concat list
    echo "🔗 Creating concatenation file list...\n";
    $concatList = $tempDir . '/concat_list.txt';
    $concatContent = '';
    
    foreach ($wordFiles as $idx => $wordFile) {
        $concatContent .= "file '" . basename($wordFile) . "'\n";
        // Add silence between words (but not after the last word)
        if ($idx < count($wordFiles) - 1) {
            $concatContent .= "file 'silence_0.3s.mp3'\n";
        }
    }
    
    file_put_contents($concatList, $concatContent);
    echo "✓ Concat list created (" . count($wordFiles) . " words + pauses)\n\n";
    
    // Step 6: Concatenate using FFmpeg concat demuxer (fast, no re-encoding)
    echo "🎵 Concatenating audio files...\n";
    $finalFile = $tempDir . '/final_sentence.mp3';
    $cmd = sprintf(
        'cd %s && %s -f concat -safe 0 -i concat_list.txt -c copy %s -y 2>&1',
        escapeshellarg($tempDir),
        escapeshellarg($ffmpegPath),
        escapeshellarg('final_sentence.mp3')
    );
    
    exec($cmd, $output, $returnCode);
    
    if ($returnCode !== 0 || !file_exists($finalFile)) {
        echo "❌ Concatenation failed\n";
        echo "Output: " . implode("\n", $output) . "\n";
        exit(1);
    }
    
    // Move to final location
    $outputDir = $storagePath . '/audio/sentences';
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    $finalPath = $outputDir . '/test_sentence_03s_pause.mp3';
    copy($finalFile, $finalPath);
    
    // Get final audio info
    $finalDuration = trim(shell_exec(sprintf(
        'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s',
        escapeshellarg($finalPath)
    )));
    $finalSize = filesize($finalPath);
    
    echo "✓ Concatenation complete\n\n";
    
    // Cleanup temp files
    array_map('unlink', glob($tempDir . '/*'));
    rmdir($tempDir);
    
    // Results
    echo "═══════════════════════════════════════════════════════════\n";
    echo "✅ SUCCESS!\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "Sentence:     {$sentence}\n";
    echo "Words:        " . count($words) . " words\n";
    echo "Pause:        0.3s between words\n";
    echo "Speed:        0.9x\n";
    echo "Duration:     {$finalDuration}s\n";
    echo "File size:    " . round($finalSize / 1024, 1) . " KB\n";
    echo "Output:       {$finalPath}\n";
    echo "Public URL:   /storage/audio/sentences/test_sentence_03s_pause.mp3\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "🎧 Test the audio:\n";
    echo "   http://localhost:8000/storage/audio/sentences/test_sentence_03s_pause.mp3\n\n";
    
    echo "📊 Technical Summary:\n";
    echo "   • Google Translate TTS: " . count($wordFiles) . " requests\n";
    echo "   • FFmpeg operations: " . (count($wordFiles) + 2) . " (processing + silence + concat)\n";
    echo "   • Concat method: FFmpeg demuxer (no re-encoding, fast)\n";
    echo "   • Audio quality: 128kbps, 44.1kHz, stereo\n";
    echo "   • Volume normalization: loudnorm filter applied\n\n";
    
    echo "💡 Performance Tips for 100+ sentences:\n";
    echo "   1. Cache word audio files (reuse common words like 'a', 'o', 'e')\n";
    echo "   2. Pre-generate silence files (0.3s, 0.5s) once\n";
    echo "   3. Use queue jobs (Laravel queues) for parallel processing\n";
    echo "   4. Consider CDN for audio delivery\n";
    echo "   5. Implement retry logic for TTS API failures\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    
    // Cleanup on error
    if (file_exists($tempDir)) {
        array_map('unlink', glob($tempDir . '/*'));
        rmdir($tempDir);
    }
    
    exit(1);
}
