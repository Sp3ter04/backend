<?php

/**
 * Script para importar exercícios via API do Next.js
 * Simula a criação manual de exercícios através do formulário admin
 */

// Configurações
$API_URL = 'http://localhost:3001/api/admin/exercises';
$CSV_FILE = '/Users/vitorclara/Downloads/exercises_rows.csv';

// Ler CSV
echo "📥 Lendo arquivo CSV...\n";
$csv = array_map('str_getcsv', file($CSV_FILE));
$headers = array_shift($csv); // Remover cabeçalho

echo "📋 Encontrados " . count($csv) . " exercícios no CSV\n\n";

$imported = 0;
$errors = 0;

foreach ($csv as $index => $row) {
    $content = trim($row[1]);
    $difficulty = $row[2];
    $number = (int)$row[6];
    
    echo "🔄 [{$imported}] Criando exercício #{$number}: {$content}\n";
    
    // Preparar dados como se fosse do formulário
    $data = [
        'number' => $number,
        'difficulty' => $difficulty,
        'content' => $content,
        'audio_url_1' => null, // Sem áudio por enquanto
    ];
    
    // Fazer requisição POST para a API
    $ch = curl_init($API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        // Aqui precisaria do token de autenticação do admin
        // 'Authorization: Bearer SEU_TOKEN_AQUI'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 || $httpCode === 201) {
        $imported++;
        echo "   ✅ Sucesso!\n";
    } else {
        $errors++;
        echo "   ❌ Erro: HTTP {$httpCode}\n";
        echo "   Resposta: {$response}\n";
    }
    
    // Pequeno delay para não sobrecarregar
    usleep(100000); // 100ms
    echo "\n";
}

echo "\n🎉 Importação concluída!\n";
echo "✅ {$imported} exercícios importados\n";
if ($errors > 0) {
    echo "⚠️  {$errors} erros\n";
}
