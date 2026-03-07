#!/usr/bin/env php
<?php

/**
 * Script para importar exercícios diretamente no Supabase
 * Simula a criação manual através do formulário admin
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Configurações do Supabase
$supabaseUrl = env('SUPABASE_URL') ?: env('NEXT_PUBLIC_SUPABASE_URL');
$serviceRoleKey = env('SUPABASE_SERVICE_ROLE') ?: env('SUPABASE_SERVICE_ROLE_KEY');

// Remover aspas se existirem
$supabaseUrl = trim($supabaseUrl, '"');
$serviceRoleKey = trim($serviceRoleKey, '"');

if (!$supabaseUrl || !$serviceRoleKey) {
    echo "❌ Erro: Variáveis de ambiente não configuradas!\n";
    echo "SUPABASE_URL: " . ($supabaseUrl ?: 'não definido') . "\n";
    echo "SERVICE_ROLE: " . ($serviceRoleKey ? 'definido' : 'não definido') . "\n";
    exit(1);
}

// Ler CSV
$csvFile = '/Users/vitorclara/Downloads/exercises_rows.csv';
echo "📥 Lendo arquivo CSV: {$csvFile}\n";

if (!file_exists($csvFile)) {
    echo "❌ Arquivo não encontrado!\n";
    exit(1);
}

$csv = array_map('str_getcsv', file($csvFile));
$headers = array_shift($csv); // Remover cabeçalho

echo "📋 Encontrados " . count($csv) . " exercícios no CSV\n\n";

$imported = 0;
$errors = 0;
$skipped = 0;

foreach ($csv as $index => $row) {
    // Pular linhas inválidas
    if (count($row) < 7) {
        echo "⚠️  Linha " . ($index + 2) . " inválida, pulando...\n\n";
        continue;
    }
    
    $content = trim($row[1]);
    $difficulty = $row[2];
    $number = (int)$row[6];
    $id = $row[0];
    
    echo "🔄 [{$number}] {$content} ({$difficulty})\n";
    
    // Verificar se já existe
    $checkUrl = "{$supabaseUrl}/rest/v1/exercises?id=eq.{$id}&select=id";
    $checkResponse = Http::withHeaders([
        'apikey' => $serviceRoleKey,
        'Authorization' => "Bearer {$serviceRoleKey}",
    ])->get($checkUrl);
    
    if ($checkResponse->successful() && count($checkResponse->json()) > 0) {
        echo "   ⏭️  Já existe, pulando...\n\n";
        $skipped++;
        continue;
    }
    
    // Preparar dados do exercício (igual ao formulário)
    $exercise = [
        'id' => $id,
        'number' => $number,
        'difficulty' => $difficulty,
        'content' => $content,
        'sentence' => $content, // Preencher sentence também (obrigatório)
        'audio_url_1' => null,
    ];
    
    // Inserir no Supabase
    $url = "{$supabaseUrl}/rest/v1/exercises";
    $response = Http::withHeaders([
        'apikey' => $serviceRoleKey,
        'Authorization' => "Bearer {$serviceRoleKey}",
        'Content-Type' => 'application/json',
        'Prefer' => 'return=representation',
    ])->post($url, $exercise);
    
    if ($response->successful()) {
        $imported++;
        echo "   ✅ Inserido com sucesso!\n";
    } else {
        $errors++;
        echo "   ❌ Erro: " . $response->status() . "\n";
        echo "   " . $response->body() . "\n";
    }
    
    echo "\n";
    usleep(50000); // 50ms delay
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "🎉 Importação concluída!\n";
echo "✅ {$imported} exercícios importados\n";
echo "⏭️  {$skipped} exercícios já existentes (pulados)\n";
if ($errors > 0) {
    echo "⚠️  {$errors} erros\n";
}
echo str_repeat('=', 50) . "\n";
