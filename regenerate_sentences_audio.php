#!/usr/bin/env php
<?php

/**
 * Script para regenerar todos os áudios das frases dos exercícios
 * Usa o AudioService para gerar novos áudios TTS com velocidade de 0.9x
 * e adiciona 0.5s de espaçamento entre palavras
 */

require __DIR__ . '/vendor/autoload.php';

use App\Models\Exercise;
use App\Services\AudioService;
use Illuminate\Support\Facades\Storage;

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "🎙️  Regravando áudios de todas as frases (velocidade 0.9x + espaçamento 0.5s)...\n\n";

// Verificar se FFmpeg está disponível
$ffmpegPath = trim(shell_exec('which ffmpeg'));
if (empty($ffmpegPath)) {
    echo "❌ Erro: FFmpeg não encontrado. Por favor instale FFmpeg.\n";
    exit(1);
}

// Buscar todos os exercícios
$exercises = Exercise::whereNotNull('sentence')
    ->where('sentence', '!=', '')
    ->orderBy('number')
    ->get();

$total = $exercises->count();
echo "📊 Total de exercícios encontrados: {$total}\n\n";

$regenerated = 0;
$skipped = 0;
$errors = 0;

$storagePath = storage_path('app/public');

foreach ($exercises as $index => $exercise) {
    $current = $index + 1;
    $sentence = $exercise->sentence;
    
    echo "[{$current}/{$total}] Exercício #{$exercise->number}: ";
    echo substr($sentence, 0, 50) . (strlen($sentence) > 50 ? '...' : '') . "\n";
    
    try {
        // Apagar áudio antigo se existir
        if (!empty($exercise->audio_url_1)) {
            if (Storage::disk('public')->exists($exercise->audio_url_1)) {
                Storage::disk('public')->delete($exercise->audio_url_1);
                echo "  🗑️  Áudio antigo removido\n";
            }
        }
        
        // Dividir a frase em palavras (remover pontuação)
        $words = preg_split('/\s+/', preg_replace('/[.,;:!?¿¡()\[\]{}"\'«»\-–—…\/\\\\]/', ' ', $sentence), -1, PREG_SPLIT_NO_EMPTY);
        
        $wordAudios = [];
        $tempDir = $storagePath . '/audio/sentences/temp_' . $exercise->id;
        
        // Criar diretório temporário
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        echo "  📝 Gerando áudio para " . count($words) . " palavras...\n";
        
        // Gerar áudio para cada palavra
        foreach ($words as $wordIndex => $word) {
            $wordAudioData = null;
            
            // Tentar Google Translate TTS
            try {
                $encodedText = urlencode($word);
                $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=pt-PT&client=tw-ob&q={$encodedText}";
                
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                curl_setopt($ch, CURLOPT_REFERER, 'https://translate.google.com/');
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $wordAudioData = curl_exec($ch);
                curl_close($ch);
            } catch (\Exception $e) {
                // Continuar
            }
            
            if ($wordAudioData && strlen($wordAudioData) > 100) {
                $wordFile = $tempDir . '/word_' . $wordIndex . '.mp3';
                file_put_contents($wordFile, $wordAudioData);
                
                // Aplicar velocidade 0.9x
                $wordFileProcessed = $tempDir . '/word_' . $wordIndex . '_processed.mp3';
                $cmd = sprintf(
                    '%s -i %s -filter:a "atempo=0.9,loudnorm" -ar 44100 -b:a 128k %s -y 2>&1',
                    escapeshellarg($ffmpegPath),
                    escapeshellarg($wordFile),
                    escapeshellarg($wordFileProcessed)
                );
                exec($cmd, $output, $returnCode);
                
                if ($returnCode === 0 && file_exists($wordFileProcessed)) {
                    $wordAudios[] = $wordFileProcessed;
                    unlink($wordFile); // Remover arquivo temporário
                }
            }
            
            usleep(100000); // 0.1s entre palavras para não sobrecarregar API
        }
        
        if (empty($wordAudios)) {
            echo "  ⚠️  Falha ao gerar áudio das palavras\n";
            $errors++;
            continue;
        }
        
        echo "  🔗 Concatenando áudios com espaçamento de 0.5s...\n";
        
        // Criar arquivo de silêncio de 0.5s
        $silenceFile = $tempDir . '/silence.mp3';
        $cmd = sprintf(
            '%s -f lavfi -i anullsrc=r=44100:cl=stereo -t 0.5 -q:a 2 -acodec libmp3lame %s -y 2>&1',
            escapeshellarg($ffmpegPath),
            escapeshellarg($silenceFile)
        );
        exec($cmd);
        
        // Criar lista de arquivos para concatenar
        $concatList = $tempDir . '/concat_list.txt';
        $concatContent = '';
        
        foreach ($wordAudios as $wordIndex => $wordAudio) {
            $concatContent .= "file '" . basename($wordAudio) . "'\n";
            // Adicionar silêncio entre palavras (mas não após a última)
            if ($wordIndex < count($wordAudios) - 1) {
                $concatContent .= "file 'silence.mp3'\n";
            }
        }
        
        file_put_contents($concatList, $concatContent);
        
        // Concatenar todos os áudios
        $finalFile = $tempDir . '/final.mp3';
        $cmd = sprintf(
            'cd %s && %s -f concat -safe 0 -i concat_list.txt -c copy %s -y 2>&1',
            escapeshellarg($tempDir),
            escapeshellarg($ffmpegPath),
            escapeshellarg('final.mp3')
        );
        exec($cmd, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($finalFile)) {
            // Mover para o local final
            $filename = 'exercise-' . $exercise->id . '-' . \Illuminate\Support\Str::slug(substr($sentence, 0, 50)) . '.mp3';
            $finalPath = $storagePath . '/audio/sentences/' . $filename;
            
            copy($finalFile, $finalPath);
            
            // Limpar arquivos temporários
            array_map('unlink', glob($tempDir . '/*'));
            rmdir($tempDir);
            
            // Atualizar no banco
            $audioUrl = 'audio/sentences/' . $filename;
            $exercise->update(['audio_url_1' => $audioUrl]);
            
            echo "  ✅ Áudio gerado (0.9x + 0.5s): {$audioUrl}\n";
            $regenerated++;
        } else {
            echo "  ⚠️  Falha ao concatenar áudios\n";
            $errors++;
            
            // Limpar arquivos temporários
            if (file_exists($tempDir)) {
                array_map('unlink', glob($tempDir . '/*'));
                rmdir($tempDir);
            }
        }
        
    } catch (\Exception $e) {
        echo "  ❌ Erro: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    echo "\n";
}

echo "\n";
echo "═══════════════════════════════════════\n";
echo "📊 RESUMO\n";
echo "═══════════════════════════════════════\n";
echo "Total de exercícios: {$total}\n";
echo "✅ Regravados: {$regenerated}\n";
echo "❌ Erros: {$errors}\n";
echo "═══════════════════════════════════════\n";
