<?php

/**
 * Script para testar conexão ao PostgreSQL do Supabase
 * 
 * Para usar:
 * 1. Obtenha sua senha em: https://supabase.com/dashboard/project/emwgjilzdxlvpkrvkhmc/settings/database
 * 2. Execute: php test-supabase-connection.php YOUR_PASSWORD_HERE
 */

if ($argc < 2) {
    echo "❌ Uso: php test-supabase-connection.php YOUR_PASSWORD\n";
    echo "\n📝 Obtenha sua senha em:\n";
    echo "   https://supabase.com/dashboard/project/emwgjilzdxlvpkrvkhmc/settings/database\n\n";
    exit(1);
}

$password = $argv[1];
$host = 'aws-0-eu-central-1.pooler.supabase.com';
$port = '6543';
$dbname = 'postgres';
$user = 'postgres.emwgjilzdxlvpkrvkhmc';

$connectionString = "host=$host port=$port dbname=$dbname user=$user password=$password sslmode=require";

echo "🔍 Testando conexão ao Supabase PostgreSQL...\n\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $dbname\n";
echo "User: $user\n";
echo "\n";

try {
    $conn = pg_connect($connectionString);
    
    if (!$conn) {
        throw new Exception("Falha na conexão");
    }
    
    echo "✅ Conexão estabelecida com sucesso!\n\n";
    
    // Testar query simples
    $result = pg_query($conn, "SELECT version()");
    if ($result) {
        $row = pg_fetch_row($result);
        echo "📊 Versão PostgreSQL: " . $row[0] . "\n\n";
    }
    
    // Listar tabelas
    $result = pg_query($conn, "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
    if ($result) {
        echo "📋 Tabelas disponíveis:\n";
        while ($row = pg_fetch_row($result)) {
            echo "   - " . $row[0] . "\n";
        }
    }
    
    echo "\n✨ Tudo funcionando! Pode usar esta senha no .env\n";
    
    pg_close($conn);
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "\n💡 Verifique:\n";
    echo "   1. Senha correta\n";
    echo "   2. Extensão pgsql do PHP instalada: php -m | grep pgsql\n";
    echo "   3. Firewall/rede permitindo conexão\n";
    exit(1);
}
