<?php

/**
 * 🔧 Corrigir Role dos Usuários
 * 
 * Atualiza 'student' → 'aluno' e 'profissional' → pode manter
 * 
 * Execute: php fix_user_roles.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║               🔧 Corrigir Roles dos Usuários                   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// =============================================================================
// ATUALIZAR ROLES
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔄 Atualizando roles...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Contar antes
$studentCount = DB::table('users')->where('role', 'student')->count();
$profissionalCount = DB::table('users')->where('role', 'profissional')->count();

echo "📊 Estado Atual:\n";
echo "   - role='student': $studentCount\n";
echo "   - role='profissional': $profissionalCount\n\n";

// Atualizar student → aluno
if ($studentCount > 0) {
    $updated = DB::table('users')
        ->where('role', 'student')
        ->update(['role' => 'aluno']);
    
    echo "✅ Atualizados: $updated usuários de 'student' → 'aluno'\n\n";
} else {
    echo "⏭️  Nenhum usuário com role='student'\n\n";
}

// Verificar depois
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Estado Final:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$roleDistribution = DB::table('users')
    ->select('role', DB::raw('COUNT(*) as count'))
    ->groupBy('role')
    ->get();

foreach ($roleDistribution as $dist) {
    echo "   - role='{$dist->role}': {$dist->count}\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  ✨ CORREÇÃO CONCLUÍDA                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";
