<?php

/**
 * 🔧 Configuração Automática do Storage no Hostinger
 * 
 * Execute este arquivo UMA VEZ via navegador:
 * https://education.medtrack.click/setup-hostinger-storage.php
 * 
 * ⚠️ IMPORTANTE: Apague este arquivo após a execução!
 */

// Configurações de segurança
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar se está rodando via HTTP
if (php_sapi_name() === 'cli') {
    die("❌ Execute este script via navegador, não via CLI\n");
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Hostinger Storage</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid #3498db;
            background: #f8f9fa;
        }
        .success {
            border-left-color: #27ae60;
            background: #d4edda;
        }
        .error {
            border-left-color: #e74c3c;
            background: #f8d7da;
        }
        .warning {
            border-left-color: #f39c12;
            background: #fff3cd;
        }
        code {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            display: inline-block;
        }
        .status-ok { background: #27ae60; color: white; }
        .status-error { background: #e74c3c; color: white; }
        .status-warning { background: #f39c12; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Configuração do Storage no Hostinger</h1>

<?php

// =============================================================================
// 1. VERIFICAR SE ESTÁ NA RAIZ DO PROJETO
// =============================================================================

echo '<h2>📍 1. Localização do Projeto</h2>';
echo '<div class="section">';

$projectRoot = realpath(__DIR__);
$hasArtisan = file_exists($projectRoot . '/artisan');

if ($hasArtisan) {
    echo '<div class="status status-ok">✅ OK</div>';
    echo '<p>Projeto Laravel encontrado em: <code>' . $projectRoot . '</code></p>';
} else {
    echo '<div class="status status-error">❌ ERRO</div>';
    echo '<p>Arquivo <code>artisan</code> não encontrado. Certifique-se de que este script está na raiz do projeto.</p>';
    echo '</div></div></body></html>';
    exit;
}

echo '</div>';

// =============================================================================
// 2. VERIFICAR SYMLINK
// =============================================================================

echo '<h2>🔗 2. Verificar Symlink</h2>';
echo '<div class="section">';

$symlinkPath = $projectRoot . '/public/storage';
$symlinkExists = is_link($symlinkPath);

if ($symlinkExists) {
    $symlinkTarget = readlink($symlinkPath);
    $realPath = realpath($symlinkPath);
    
    echo '<div class="status status-ok">✅ OK</div>';
    echo '<p><strong>Symlink existe:</strong></p>';
    echo '<ul>';
    echo '<li>Link: <code>public/storage</code></li>';
    echo '<li>Aponta para: <code>' . $symlinkTarget . '</code></li>';
    echo '<li>Caminho real: <code>' . $realPath . '</code></li>';
    echo '</ul>';
    
    // Detectar tipo de estrutura
    if (strpos($symlinkTarget, 'app/public') !== false) {
        $storageType = 'padrao';
        $storagePath = $projectRoot . '/storage/app/public';
        echo '<div class="status status-ok">📂 Estrutura Padrão Laravel</div>';
    } else {
        $storageType = 'customizado';
        $storagePath = $realPath;
        echo '<div class="status status-warning">📂 Estrutura Customizada Hostinger</div>';
    }
} else {
    echo '<div class="status status-error">❌ ERRO</div>';
    echo '<p>Symlink não existe! Você precisa criar primeiro.</p>';
    $storageType = 'nenhum';
}

echo '</div>';

// =============================================================================
// 3. CRIAR PASTAS DE ÁUDIO
// =============================================================================

echo '<h2>🎵 3. Criar Pastas de Áudio</h2>';
echo '<div class="section">';

if ($storageType !== 'nenhum') {
    $audioPath = $storagePath . '/audio/sentences';
    $audioDirExists = is_dir($audioPath);
    
    if (!$audioDirExists) {
        echo '<div class="status status-warning">⚠️ Criando...</div>';
        $created = @mkdir($audioPath, 0775, true);
        
        if ($created) {
            echo '<div class="status status-ok">✅ CRIADO</div>';
            echo '<p>Pasta criada: <code>' . $audioPath . '</code></p>';
        } else {
            echo '<div class="status status-error">❌ ERRO</div>';
            echo '<p>Não foi possível criar a pasta automaticamente.</p>';
            echo '<p>Execute manualmente via SSH:</p>';
            echo '<pre>mkdir -p ' . $audioPath . '</pre>';
        }
    } else {
        echo '<div class="status status-ok">✅ OK</div>';
        echo '<p>Pasta já existe: <code>' . $audioPath . '</code></p>';
        
        // Contar arquivos MP3
        $mp3Files = glob($audioPath . '/*.mp3');
        $mp3Count = count($mp3Files);
        echo '<p>Arquivos MP3: <strong>' . $mp3Count . '</strong></p>';
    }
    
    // Verificar permissões
    if (is_dir($audioPath)) {
        $perms = substr(sprintf('%o', fileperms($audioPath)), -3);
        echo '<p>Permissões: <code>' . $perms . '</code>';
        
        if ($perms >= '755') {
            echo ' <span class="status status-ok">OK</span></p>';
        } else {
            echo ' <span class="status status-warning">Recomendado: 775</span></p>';
            echo '<p>Execute via SSH:</p>';
            echo '<pre>chmod -R 775 ' . $storagePath . '/audio</pre>';
        }
    }
} else {
    echo '<div class="status status-error">❌ IGNORADO</div>';
    echo '<p>Configure o symlink primeiro.</p>';
}

echo '</div>';

// =============================================================================
// 4. GERAR CONFIGURAÇÃO DO .ENV
// =============================================================================

echo '<h2>⚙️ 4. Configuração do .env</h2>';
echo '<div class="section">';

$envPath = $projectRoot . '/.env';
$envExists = file_exists($envPath);

if ($envExists) {
    echo '<div class="status status-ok">✅ Arquivo .env encontrado</div>';
    
    $envContent = file_get_contents($envPath);
    $hasStorageRoot = strpos($envContent, 'PUBLIC_STORAGE_ROOT') !== false;
    
    if ($storageType === 'customizado') {
        echo '<div class="section warning">';
        echo '<p><strong>⚠️ Você precisa adicionar esta linha ao .env:</strong></p>';
        echo '<pre>PUBLIC_STORAGE_ROOT=' . $storagePath . '</pre>';
        
        if ($hasStorageRoot) {
            echo '<div class="status status-ok">✅ Já existe</div>';
        } else {
            echo '<div class="status status-warning">⚠️ Adicione manualmente</div>';
        }
        echo '</div>';
        
    } else if ($storageType === 'padrao') {
        echo '<div class="section success">';
        echo '<p><strong>✅ Estrutura padrão - não precisa de PUBLIC_STORAGE_ROOT</strong></p>';
        
        if ($hasStorageRoot) {
            echo '<p><em>Você pode remover a linha PUBLIC_STORAGE_ROOT do .env</em></p>';
        }
        echo '</div>';
    }
    
    // Verificar APP_URL
    preg_match('/APP_URL=(.+)/', $envContent, $matches);
    if (!empty($matches[1])) {
        $appUrl = trim($matches[1]);
        echo '<p>APP_URL: <code>' . htmlspecialchars($appUrl) . '</code></p>';
    } else {
        echo '<div class="section warning">';
        echo '<p><strong>⚠️ APP_URL não configurado!</strong></p>';
        echo '<p>Adicione ao .env:</p>';
        echo '<pre>APP_URL=https://' . ($_SERVER['HTTP_HOST'] ?? 'seu-dominio.com') . '</pre>';
        echo '</div>';
    }
    
} else {
    echo '<div class="status status-error">❌ .env não encontrado</div>';
    echo '<p>Copie o arquivo <code>.env.example</code> para <code>.env</code></p>';
}

echo '</div>';

// =============================================================================
// 5. TESTE DE ESCRITA
// =============================================================================

echo '<h2>🧪 5. Teste de Escrita</h2>';
echo '<div class="section">';

if ($storageType !== 'nenhum' && isset($audioPath)) {
    $testFile = $audioPath . '/test-' . time() . '.txt';
    $testContent = 'Teste de escrita - ' . date('Y-m-d H:i:s');
    
    $written = @file_put_contents($testFile, $testContent);
    
    if ($written !== false) {
        echo '<div class="status status-ok">✅ Escrita OK</div>';
        echo '<p>Arquivo criado: <code>' . basename($testFile) . '</code></p>';
        
        // Tentar acessar via URL
        $testUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' 
                  . $_SERVER['HTTP_HOST'] 
                  . '/storage/audio/sentences/' . basename($testFile);
        
        echo '<p>Teste de acesso via URL:</p>';
        echo '<a href="' . $testUrl . '" target="_blank" class="btn">🔗 Abrir arquivo de teste</a>';
        
        // Limpar arquivo de teste após 5 segundos
        echo '<p><em>Este arquivo de teste será usado apenas para verificação.</em></p>';
        
    } else {
        echo '<div class="status status-error">❌ Erro na escrita</div>';
        echo '<p>Não foi possível escrever no diretório de áudio.</p>';
        echo '<p>Verifique as permissões:</p>';
        echo '<pre>chmod -R 775 ' . $storagePath . '/audio</pre>';
    }
} else {
    echo '<div class="status status-error">❌ IGNORADO</div>';
    echo '<p>Configure o storage primeiro.</p>';
}

echo '</div>';

// =============================================================================
// 6. PRÓXIMOS PASSOS
// =============================================================================

echo '<h2>📋 Próximos Passos</h2>';
echo '<div class="section">';

if ($storageType === 'padrao') {
    echo '<ol>';
    echo '<li>✅ Estrutura está correta</li>';
    echo '<li>Execute via SSH: <code>php artisan config:clear</code></li>';
    echo '<li>Teste o botão "Listen" no admin</li>';
    echo '<li><strong>⚠️ APAGUE ESTE ARQUIVO: setup-hostinger-storage.php</strong></li>';
    echo '</ol>';
    
} else if ($storageType === 'customizado') {
    echo '<ol>';
    echo '<li>Adicione <code>PUBLIC_STORAGE_ROOT</code> ao .env (veja acima)</li>';
    echo '<li>Execute via SSH: <code>php artisan config:clear</code></li>';
    echo '<li>Teste o botão "Listen" no admin</li>';
    echo '<li><strong>⚠️ APAGUE ESTE ARQUIVO: setup-hostinger-storage.php</strong></li>';
    echo '</ol>';
    
} else {
    echo '<ol>';
    echo '<li>Crie o symlink (veja HOSTINGER_SETUP_QUICK.md)</li>';
    echo '<li>Execute este script novamente</li>';
    echo '<li><strong>⚠️ APAGUE ESTE ARQUIVO: setup-hostinger-storage.php</strong></li>';
    echo '</ol>';
}

echo '</div>';

// =============================================================================
// 7. COMANDOS ÚTEIS
// =============================================================================

echo '<h2>📞 Comandos Úteis via SSH</h2>';
echo '<div class="section">';
echo '<pre>';
echo "# Limpar caches\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan route:clear\n\n";

echo "# Ver estrutura de pastas\n";
echo "ls -la storage/*/public/audio\n\n";

echo "# Ver symlink\n";
echo "ls -la public/storage\n\n";

echo "# Testar Storage do Laravel\n";
echo "php artisan tinker\n";
echo ">>> Storage::disk('public')->put('audio/test.txt', 'funciona!');\n";
echo ">>> Storage::disk('public')->url('audio/test.txt');\n";
echo ">>> exit\n";
echo '</pre>';
echo '</div>';

?>

        <h2>✨ Resumo</h2>
        <div class="section success">
            <p><strong>Status da Configuração:</strong></p>
            <ul>
                <li>Projeto Laravel: <?php echo $hasArtisan ? '✅ OK' : '❌ Não encontrado'; ?></li>
                <li>Symlink: <?php echo $symlinkExists ? '✅ OK' : '❌ Não configurado'; ?></li>
                <li>Pasta de Áudio: <?php echo isset($audioDirExists) && $audioDirExists ? '✅ OK' : '❌ Precisa criar'; ?></li>
                <li>Tipo de Estrutura: <?php echo strtoupper($storageType); ?></li>
            </ul>
            
            <p><strong>⚠️ IMPORTANTE:</strong> Após concluir a configuração, <strong>APAGUE ESTE ARQUIVO</strong> por segurança!</p>
            <pre>rm <?php echo __FILE__; ?></pre>
        </div>
        
        <p style="text-align: center; margin-top: 30px; color: #7f8c8d;">
            📖 Para mais detalhes, consulte <code>HOSTINGER_SETUP_QUICK.md</code>
        </p>
    </div>
</body>
</html>
