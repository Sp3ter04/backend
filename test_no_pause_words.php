<?php

/**
 * Test No-Pause Words Logic
 * 
 * Tests the improved comma insertion that skips monosyllabic words
 */

require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Initialize service
$service = new App\Services\SimplePausedAudioService();

echo "🎵 TEST NO-PAUSE WORDS LOGIC\n";
echo "============================\n\n";

// Test sentences
$testSentences = [
    "A menina vê a mamã",
    "O pai tapou a panela da comida do bebé",
    "A Dália tem um livro de Estudo do Meio",
    "O Miguel gosta de jogar futebol",
    "O bebé dorme no berço",
    "A Rita e o Renato viajam com os pais",
    "Eu gosto de ler livros",
    "O gato dorme na cama",
];

echo "📝 TRANSFORMATION TEST (with no-pause for monosyllabic words)\n";
echo "-------------------------------------------------------------\n\n";

foreach ($testSentences as $sentence) {
    $transformed = $service->insertCommasForPauses($sentence);
    echo "Input:  {$sentence}\n";
    echo "Output: {$transformed}\n";
    echo "\n";
}

echo "✅ EXPLANATION\n";
echo "--------------\n";
echo "Commas are NOT added after:\n";
echo "  • Articles: a, o, os, as, um, uma, uns, umas\n";
echo "  • Prepositions: de, do, da, dos, das, em, no, na, nos, nas, por, ao, aos\n";
echo "  • Conjunctions: e, ou, que, se\n";
echo "  • Short words (1-2 letters)\n\n";

echo "This creates a more natural flow:\n";
echo "  Before: \"A, menina, vê, a, mamã\"\n";
echo "  After:  \"A menina, vê, a mamã\"\n\n";

echo "The pauses now only occur between meaningful content words!\n";
