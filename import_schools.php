<?php

/**
 * 🏫 Importar Escolas do Supabase
 * 
 * Cria registros na tabela schools para todas as escolas mencionadas nos dados
 * 
 * Execute: php import_schools.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║              🏫 Importar Escolas do Supabase                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// =============================================================================
// LISTA DE ESCOLAS ÚNICAS
// =============================================================================

$escolas = [
    'Manuel Coco',
    'Teste',
    'ist',
    'IST',
    'Particular',
    'CRSI',
    'Joao de Deus',
    'Escola Manuel Coco',
    'Lisboa',
    'Pousa',
    'Leiria',
    'Dona Maria',
    'Escola Secundária José Saramago',
    'Instituto Superior Técnico',
    'ESAS',
    'EB1/JI Manuel Coco',
    'Escola EB1/JI Manuel Coco',
    'Escola Amadeu',
    'E.B1/J.I Manuel Coco',
];

// Normalizar escolas (remover duplicatas considerando case-insensitive)
$escolasNormalizadas = [];
foreach ($escolas as $escola) {
    $key = strtolower(trim($escola));
    
    // Unificar variações do IST
    if (in_array($key, ['ist', 'instituto superior técnico', 'instituto superior tecnico'])) {
        $escolasNormalizadas['ist'] = 'Instituto Superior Técnico';
    }
    // Unificar variações do Manuel Coco
    elseif (strpos($key, 'manuel coco') !== false) {
        if (strpos($key, 'eb1') !== false || strpos($key, 'e.b1') !== false) {
            $escolasNormalizadas['eb1_manuel_coco'] = 'EB1/JI Manuel Coco';
        } else {
            $escolasNormalizadas['manuel_coco'] = 'Escola Manuel Coco';
        }
    }
    // Unificar variações do Joao de Deus
    elseif (strpos($key, 'joao de deus') !== false || strpos($key, 'joão de deus') !== false) {
        $escolasNormalizadas['joao_deus'] = 'Colégio João de Deus';
    }
    // Outras escolas
    else {
        $escolasNormalizadas[$key] = $escola;
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🏫 Importando Escolas\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$escolasCriadas = 0;
$escolasExistentes = 0;
$escolasMap = []; // Mapear nome original -> UUID

foreach ($escolasNormalizadas as $key => $nomeEscola) {
    // Verificar se já existe (por nome)
    $exists = DB::table('schools')->where('name', $nomeEscola)->first();
    
    if ($exists) {
        echo "   ⏭️  Já existe: $nomeEscola\n";
        $escolasExistentes++;
        $escolasMap[$nomeEscola] = $exists->id;
    } else {
        try {
            $id = Str::uuid()->toString();
            
            DB::table('schools')->insert([
                'id' => $id,
                'name' => $nomeEscola,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "   ✅ Criada: $nomeEscola\n";
            $escolasCriadas++;
            $escolasMap[$nomeEscola] = $id;
        } catch (\Exception $e) {
            echo "   ❌ Erro ao criar $nomeEscola: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n📊 Escolas: $escolasCriadas criadas, $escolasExistentes já existiam\n\n";

// =============================================================================
// ATUALIZAR SCHOOL_ID DOS USUÁRIOS
// =============================================================================

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🔗 Vinculando Usuários às Escolas\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Buscar todos os usuários
$users = DB::table('users')->get();

$usuariosAtualizados = 0;

foreach ($users as $user) {
    // Dados originais dos CSVs para fazer o match
    $userSchoolMappings = [
        'margaridasequeira@gmail.com' => 'Manuel Coco',
        'startupdislexia@gmail.com' => 'Teste',
        'margarida@gmail.com' => 'ist',
        'Pedronunes.pmmn@gmail.com' => 'Particular',
        'vitoraluno@gmail.com' => 'IST',
        'guilherme@gmail.com' => 'Manuel Coco',
        'eduarda.santos@gmail.com' => 'CRSI',
        'margaridasimoes@gmail.com' => 'Manuel Coco',
        'bea@gmail.com' => 'Manuel Coco',
        'joaodedeus2_1@gmail.com' => 'Joao de Deus',
        'diogo.castanheira@gmail.com' => 'CRSI',
        'andreazrods@gmail.com' => 'Teste',
        'anacalado1979@gmail.com' => 'Escola Manuel Coco',
        'joaodedeus1_1@gmail.com' => 'Joao de Deus',
        'marianaazevedo2004@gmail.com' => 'IST',
        'sara@gmail.com' => 'Manuel Coco',
        'marianacpereiraa@gmail.com' => 'Escola EB1/JI Manuel Coco',
        'armando@gmail.com' => 'Lisboa',
        'dinisgoncales@gmail.com' => 'Pousa',
        'vitor@gmail.com' => 'Leiria',
        'joaodedeus0_1@gmail.com' => 'Joao de Deus',
        'vascogomes@gmail.com' => 'Dona Maria',
        'beatriz@gmail.com' => 'Pousa',
        'vicente@gmail.com' => 'Manuel Coco',
        'margaridanunes@gmail.com' => 'ist',
        'goncalo@gmail.com' => 'Manuel Coco',
        'dinis@gmail.com' => 'Pousa',
        'daiame@gmail.com' => 'pousa',
        'peafcc@gmail.com' => 'Escola Secundária José Saramago',
        'joaodedeus2_2@gmail.com' => 'Joao de Deus',
        'matilde.madureira@junitec.pt' => 'Instituto Superior Técnico',
        'joaodedeus1_2@gmail.com' => 'Joao de Deus',
        'francisco.redondo@gmail.com' => 'CRSI',
        'maria@gmail.com' => 'Manuel Coco',
        // Profissionais
        'andre.rodrigues@junitec.pt' => 'ESAS',
        'vitorclara@gmail.com' => 'IST',
        'margaridan@gmail.com' => 'Manuel Coco',
        'alzirax@gmail.com' => 'EB1/JI Manuel Coco',
        'andre.correia@yahoo.com' => 'Escola Amadeu',
        'ana@gmail.com' => 'ist',
        'andreclashroyalerods@gmail.com' => 'Teste',
        'anaritacalado@aemoinhosarroja.pt' => 'E.B1/J.I Manuel Coco',
    ];
    
    if (!isset($userSchoolMappings[$user->email])) {
        continue;
    }
    
    $escolaOriginal = $userSchoolMappings[$user->email];
    
    // Normalizar nome da escola para encontrar o UUID
    $escolaNormalizada = null;
    $escolaKey = strtolower(trim($escolaOriginal));
    
    // Aplicar mesma lógica de normalização
    if (in_array($escolaKey, ['ist', 'instituto superior técnico'])) {
        $escolaNormalizada = 'Instituto Superior Técnico';
    } elseif (strpos($escolaKey, 'manuel coco') !== false) {
        if (strpos($escolaKey, 'eb1') !== false || strpos($escolaKey, 'e.b1') !== false) {
            $escolaNormalizada = 'EB1/JI Manuel Coco';
        } else {
            $escolaNormalizada = 'Escola Manuel Coco';
        }
    } elseif (strpos($escolaKey, 'joao de deus') !== false) {
        $escolaNormalizada = 'Colégio João de Deus';
    } elseif ($escolaKey === 'pousa') {
        $escolaNormalizada = 'Pousa';
    } else {
        // Buscar no mapa
        foreach ($escolasNormalizadas as $normalKey => $normalNome) {
            if ($normalKey === $escolaKey) {
                $escolaNormalizada = $normalNome;
                break;
            }
        }
    }
    
    if ($escolaNormalizada && isset($escolasMap[$escolaNormalizada])) {
        $schoolId = $escolasMap[$escolaNormalizada];
        
        try {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'school_id' => $schoolId,
                    'updated_at' => now(),
                ]);
            
            echo "   🔗 {$user->name} → $escolaNormalizada\n";
            $usuariosAtualizados++;
        } catch (\Exception $e) {
            echo "   ❌ Erro ao vincular {$user->name}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n📊 Usuários vinculados: $usuariosAtualizados\n\n";

// =============================================================================
// RESUMO FINAL
// =============================================================================

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  ✨ IMPORTAÇÃO CONCLUÍDA                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$totalEscolas = DB::table('schools')->count();
$totalUsersComEscola = DB::table('users')->whereNotNull('school_id')->count();

echo "📊 Total de escolas: $totalEscolas\n";
echo "👥 Usuários vinculados a escolas: $totalUsersComEscola\n\n";
