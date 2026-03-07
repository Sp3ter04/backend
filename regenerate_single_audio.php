#!/usr/bin/env php
<?php

/**
 * Script para regenerar um único áudio de exercício
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\Exercise;
use App\Services\AudioService;
use Illuminate\Support\Facades\Storage;

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$exerciseId = $argv[1] ?? '019cb51c-31a2-72c3-a6c3-e9d8ca41226e';

echo "🎙️  Regravando áudio do exercício: {$exerciseId}\n\n";

$exercise = Exercise::find($exerciseId);

if (!$exercise) {
    echo "❌ Exercício não encontrado!\n";
    exit(1);
}

echo "Frase: {$exercise->sentence}\n";
echo "Exercício: #{$exercise->number}\n\n";

// Verificar FFmpeg
$ffmpegPath = trim(shell_exec('which ffmpeg'));
if (empty($ffmpegPath)) {
    echo "❌ Erro: FFmpeg não encontrado.\n";
    exit(1);
}

$storagePath = storage_path('app/public');

try {
    // Apagar áudio antigo
    if (!empty($exercise->audio_url_1)) {
        if (Storage::disk('public')->exists($exercise->audio_url_1)) {
            Storage::disk('public')->delete($exercise->audio_url_1);
            echo "🗑️  Áudio antigo removido\n";
        }
    }
    
    // Gerar novo áudio temporário
    $audioPath = AudioService::generateAndSave(
        $exercise->sentence,
        'pt-PT',
        'sentences',
        'exercise-' . $exercise->id
    );
    
    if (!$audioPath) {
        echo "❌ Falha ao gerar áudio\n";
        exit(1);
    }
    
    echo "✅ Áudio base gerado: {$audioPath}\n";
    
    // Verificar duração do áudio base
    $fullPath = $storagePath . '/' . $audioPath;
    $duration = trim(shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($fullPath)));
    echo "⏱️  Duração original: {$duration}s\n";
    
    // Aplicar velocidade 0.8x com normalização de áudio
    $tempPath = $storagePath . '/audio/sentences/temp_' . basename($fullPath);
    
    $command = sprintf(
        '%s -i %s -filter:a "atempo=0.8,loudnorm" -ar 44100 -b:a 128k -vn %s -y 2>&1',
        escapeshellarg($ffmpegPath),
        escapeshellarg($fullPath),
        escapeshellarg($tempPath)
    );
    
    echo "🔄 Aplicando velocidade 0.8x e normalizando volume...\n";
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($tempPath)) {
        // Verificar duração do áudio processado
        $newDuration = trim(shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($tempPath)));
        echo "⏱️  Duração com 0.8x: {$newDuration}s\n";
        
        // Substituir o arquivo original
        rename($tempPath, $fullPath);
        
        // Atualizar no banco
        $exercise->update(['audio_url_1' => $audioPath]);
        
        echo "✅ Áudio regravado com sucesso!\n";
        echo "📁 Caminho: {$audioPath}\n";
        
        // Verificar tamanho do arquivo
        $fileSize = filesize($fullPath);
        echo "📊 Tamanho: " . round($fileSize / 1024, 2) . " KB\n";
        
    } else {
        echo "❌ Falha ao processar velocidade\n";
        echo "Output: " . implode("\n", $output) . "\n";
        exit(1);
    }
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
