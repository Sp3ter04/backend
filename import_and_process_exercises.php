#!/usr/bin/env php
<?php

/**
 * Script para importar e processar exercícios completamente
 * - Insere no Supabase
 * - Separa em palavras e sílabas
 * - Gera áudios TTS
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use App\Services\ExerciseProcessorService;
use App\Models\Exercise;
use Illuminate\Support\Str;

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Configurações do Supabase
$supabaseUrl = env('SUPABASE_URL') ?: env('NEXT_PUBLIC_SUPABASE_URL');
$serviceRoleKey = env('SUPABASE_SERVICE_ROLE') ?: env('SUPABASE_SERVICE_ROLE_KEY');
$supabaseUrl = trim($supabaseUrl, '"');
$serviceRoleKey = trim($serviceRoleKey, '"');

if (!$supabaseUrl || !$serviceRoleKey) {
    echo "❌ Erro: Variáveis de ambiente não configuradas!\n";
    exit(1);
}

// Ler CSV
$csvFile = '/Users/vitorclara/Downloads/new_exercises.csv';
echo "📥 Lendo arquivo CSV: {$csvFile}\n";

if (!file_exists($csvFile)) {
    echo "❌ Arquivo não encontrado!\n";
    exit(1);
}

$csv = array_map('str_getcsv', file($csvFile));
$headers = array_shift($csv);

echo "📋 Encontrados " . count($csv) . " exercícios no CSV\n";
echo "🔧 Modo: PROCESSAMENTO COMPLETO (palavras, sílabas, áudios)\n\n";

$imported = 0;
$processed = 0;
$skipped = 0;
$errors = 0;

$processor = new ExerciseProcessorService();

foreach ($csv as $index => $row) {
    // Pular linhas inválidas
    if (count($row) < 7) {
        echo "⚠️  Linha " . ($index + 2) . " inválida, pulando...\n\n";
        continue;
    }
    
    $content = trim($row[1]);
    $difficulty = $row[2];
    $number = (int)$row[6];
    $csvId = $row[0];
    
    // Gerar UUID válido
    $id = Str::uuid()->toString();
    
    echo str_repeat('=', 70) . "\n";
    echo "🔄 EXERCÍCIO #{$number}: {$content}\n";
    echo "   Dificuldade: {$difficulty}\n";
    echo "   ID: {$id}\n";
    echo str_repeat('-', 70) . "\n";
    
    // 1. Verificar se já existe no Supabase (por número, não por ID)
    $checkUrl = "{$supabaseUrl}/rest/v1/exercises?number=eq.{$number}&select=id";
    $checkResponse = Http::withHeaders([
        'apikey' => $serviceRoleKey,
        'Authorization' => "Bearer {$serviceRoleKey}",
    ])->get($checkUrl);
    
    if ($checkResponse->successful() && count($checkResponse->json()) > 0) {
        echo "   ⏭️  Já existe no Supabase (número #{$number})\n";
        
        // Pegar o ID existente
        $existingId = $checkResponse->json()[0]['id'];
        
        // Verificar se existe localmente para processar
        $localExercise = Exercise::find($existingId);
        if (!$localExercise) {
            echo "   📥 Sincronizando para SQLite local...\n";
            // Buscar do Supabase e inserir localmente
            $getUrl = "{$supabaseUrl}/rest/v1/exercises?id=eq.{$existingId}&select=*";
            $getResponse = Http::withHeaders([
                'apikey' => $serviceRoleKey,
                'Authorization' => "Bearer {$serviceRoleKey}",
            ])->get($getUrl);
            
            if ($getResponse->successful()) {
                $data = $getResponse->json()[0];
                $localExercise = Exercise::create([
                    'id' => $data['id'],
                    'number' => $data['number'],
                    'difficulty' => $data['difficulty'],
                    'content' => $data['content'],
                    'sentence' => $data['sentence'] ?? $data['content'],
                    'audio_url_1' => $data['audio_url_1'] ?? null,
                ]);
            }
        }
        
        if ($localExercise) {
            echo "   🔧 Processando (palavras, sílabas, áudios)...\n";
            try {
                $processor->process($localExercise);
                echo "   ✅ Processado com sucesso!\n";
                $processed++;
            } catch (\Exception $e) {
                echo "   ❌ Erro ao processar: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
        
        $skipped++;
        echo "\n";
        continue;
    }
    
    // 2. Inserir no Supabase
    echo "   📤 Inserindo no Supabase...\n";
    $exercise = [
        'id' => $id,
        'number' => $number,
        'difficulty' => $difficulty,
        'content' => $content,
        'sentence' => $content,
        'audio_url_1' => null,
    ];
    
    $url = "{$supabaseUrl}/rest/v1/exercises";
    $response = Http::withHeaders([
        'apikey' => $serviceRoleKey,
        'Authorization' => "Bearer {$serviceRoleKey}",
        'Content-Type' => 'application/json',
        'Prefer' => 'return=representation',
    ])->post($url, $exercise);
    
    if (!$response->successful()) {
        echo "   ❌ Erro ao inserir no Supabase: " . $response->status() . "\n";
        echo "   " . $response->body() . "\n\n";
        $errors++;
        continue;
    }
    
    echo "   ✅ Inserido no Supabase\n";
    $imported++;
    
    // 3. Criar localmente no SQLite
    echo "   💾 Criando no SQLite local...\n";
    try {
        $localExercise = Exercise::create([
            'id' => $id,
            'number' => $number,
            'difficulty' => $difficulty,
            'content' => $content,
            'sentence' => $content,
            'audio_url_1' => null,
        ]);
        echo "   ✅ Criado localmente\n";
    } catch (\Exception $e) {
        echo "   ❌ Erro ao criar localmente: " . $e->getMessage() . "\n\n";
        $errors++;
        continue;
    }
    
    // 4. Processar completamente
    echo "   🔧 Processando (palavras, sílabas, áudios)...\n";
    try {
        $processor->process($localExercise);
        echo "   ✅ Processado com sucesso!\n";
        $processed++;
        
        // Atualizar áudio no Supabase se foi gerado
        if ($localExercise->fresh()->audio_url_1) {
            echo "   🔄 Atualizando áudio no Supabase...\n";
            $updateUrl = "{$supabaseUrl}/rest/v1/exercises?number=eq.{$number}";
            Http::withHeaders([
                'apikey' => $serviceRoleKey,
                'Authorization' => "Bearer {$serviceRoleKey}",
                'Content-Type' => 'application/json',
            ])->patch($updateUrl, [
                'audio_url_1' => $localExercise->audio_url_1,
            ]);
            echo "   ✅ Áudio atualizado\n";
        }
        
    } catch (\Exception $e) {
        echo "   ❌ Erro ao processar: " . $e->getMessage() . "\n";
        $errors++;
    }
    
    echo "\n";
    usleep(100000); // 100ms delay
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "🎉 IMPORTAÇÃO E PROCESSAMENTO CONCLUÍDOS!\n";
echo str_repeat('=', 70) . "\n";
echo "✅ {$imported} novos exercícios importados\n";
echo "🔧 {$processed} exercícios processados (palavras, sílabas, áudios)\n";
echo "⏭️  {$skipped} exercícios já existentes\n";
if ($errors > 0) {
    echo "⚠️  {$errors} erros encontrados\n";
}
echo str_repeat('=', 70) . "\n";
