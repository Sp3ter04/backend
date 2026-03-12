<?php

/**
 * 🔍 Verificar Vínculos entre Usuários e Escolas
 * 
 * Execute: php verify_school_links.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         🔍 Verificação de Vínculos Usuários-Escolas          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Buscar usuários com suas escolas
$usersComEscola = DB::table('users')
    ->leftJoin('schools', 'users.school_id', '=', 'schools.id')
    ->select('users.name', 'users.email', 'users.role', 'schools.name as school_name')
    ->whereNotNull('users.school_id')
    ->orderBy('schools.name')
    ->orderBy('users.name')
    ->get();

$usersSemEscola = DB::table('users')
    ->whereNull('school_id')
    ->select('name', 'email', 'role')
    ->get();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Usuários COM escola vinculada: " . count($usersComEscola) . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$escolasAgrupadas = [];
foreach ($usersComEscola as $user) {
    if (!isset($escolasAgrupadas[$user->school_name])) {
        $escolasAgrupadas[$user->school_name] = [];
    }
    $escolasAgrupadas[$user->school_name][] = $user;
}

foreach ($escolasAgrupadas as $escolaNome => $usuarios) {
    echo "🏫 $escolaNome (" . count($usuarios) . " usuários)\n";
    foreach ($usuarios as $user) {
        $roleIcon = $user->role === 'aluno' ? '👨‍🎓' : '👨‍🏫';
        echo "   $roleIcon {$user->name} ({$user->email})\n";
    }
    echo "\n";
}

if (count($usersSemEscola) > 0) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "⚠️  Usuários SEM escola vinculada: " . count($usersSemEscola) . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    foreach ($usersSemEscola as $user) {
        $roleIcon = $user->role === 'aluno' ? '👨‍🎓' : ($user->role === 'profissional' ? '👨‍🏫' : '👤');
        echo "   $roleIcon {$user->name} ({$user->email}) - {$user->role}\n";
    }
    echo "\n";
}

// Estatísticas
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                      📊 ESTATÍSTICAS                           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$totalUsers = DB::table('users')->count();
$totalEscolas = DB::table('schools')->count();
$alunosComEscola = DB::table('users')->where('role', 'aluno')->whereNotNull('school_id')->count();
$profComEscola = DB::table('users')->where('role', 'profissional')->whereNotNull('school_id')->count();

echo "👥 Total de usuários: $totalUsers\n";
echo "🏫 Total de escolas: $totalEscolas\n";
echo "👨‍🎓 Alunos com escola: $alunosComEscola\n";
echo "👨‍🏫 Profissionais com escola: $profComEscola\n";
echo "✅ Total vinculados: " . count($usersComEscola) . "\n";
echo "⚠️  Sem vínculo: " . count($usersSemEscola) . "\n\n";
