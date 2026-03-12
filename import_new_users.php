<?php

/**
 * 🔄 Importar Novos Alunos e Profissionais do Supabase
 * 
 * Importa alunos e profissionais que ainda não foram migrados
 * 
 * Execute: php import_new_users.php
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Carregar ambiente Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║        🔄 Importar Novos Usuários do Supabase                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// =============================================================================
// NOVOS ALUNOS
// =============================================================================

$novosAlunos = [
    ['id' => '08637836-054c-415d-93eb-e5f2ddbd61f9', 'email' => 'margaridasequeira@gmail.com', 'nome' => 'Margarida Sequeira', 'escola' => 'Manuel Coco', 'ano' => 1, 'created_at' => '2026-01-07 12:27:36.598424+00'],
    ['id' => '096dfd82-daf5-48a8-98a0-3eace0655466', 'email' => 'startupdislexia@gmail.com', 'nome' => 'Arminda', 'escola' => 'Teste', 'ano' => 2, 'created_at' => '2025-12-21 22:16:07.521416+00'],
    ['id' => '0a1dd7f8-4d4b-4342-8dee-c4c6c76afcd1', 'email' => 'margarida@gmail.com', 'nome' => 'Margarida', 'escola' => 'ist', 'ano' => 9, 'created_at' => '2025-12-29 14:45:00.387475+00'],
    ['id' => '1ab6e6e0-f112-4222-a32e-0a4fdaeb863e', 'email' => 'Pedronunes.pmmn@gmail.com', 'nome' => 'Pedro Nunes', 'escola' => 'Particular', 'ano' => 1, 'created_at' => '2026-01-16 14:49:27.553509+00'],
    ['id' => '314c3d91-be93-4454-8786-74dd204cb514', 'email' => 'vitoraluno@gmail.com', 'nome' => 'Vitor Clara', 'escola' => 'IST', 'ano' => 3, 'created_at' => '2026-01-05 00:24:01.601702+00'],
    ['id' => '32ce0180-b888-4e43-b505-a49666ae4989', 'email' => 'guilherme@gmail.com', 'nome' => 'Guilherme', 'escola' => 'Manuel Coco', 'ano' => 4, 'created_at' => '2026-01-07 11:47:27.389351+00'],
    ['id' => '3c46a8b3-b966-40b5-be52-54d6ac00362a', 'email' => 'eduarda.santos@gmail.com', 'nome' => 'Eduarda Santos', 'escola' => 'CRSI', 'ano' => 6, 'created_at' => '2026-01-22 15:26:08.970718+00'],
    ['id' => '496afdc9-08c6-43b3-923f-b520641a4004', 'email' => 'margaridasimoes@gmail.com', 'nome' => 'Margarida Simões', 'escola' => 'Manuel Coco', 'ano' => 3, 'created_at' => '2026-01-07 10:04:00.369643+00'],
    ['id' => '56aea145-f73d-4742-aca0-6258d9af020c', 'email' => 'bea@gmail.com', 'nome' => 'Bea', 'escola' => 'Manuel Coco', 'ano' => 2, 'created_at' => '2026-01-07 11:31:40.569843+00'],
    ['id' => '585cba88-9644-493e-86b3-88f1a518a0e8', 'email' => 'joaodedeus2_1@gmail.com', 'nome' => 'Joaquim Dias', 'escola' => 'Joao de Deus', 'ano' => 1, 'created_at' => '2026-01-23 15:44:24.693881+00'],
    ['id' => '59dd17d8-682d-41b0-9d86-b96baf61a1bd', 'email' => 'diogo.castanheira@gmail.com', 'nome' => 'Diogo Castanheira', 'escola' => 'CRSI', 'ano' => 3, 'created_at' => '2026-01-22 14:42:59.820214+00'],
    ['id' => '5d742b57-d297-4bb6-b9c5-ae704630fedf', 'email' => 'andreazrods@gmail.com', 'nome' => 'André Rodrigues', 'escola' => 'Teste', 'ano' => 1, 'created_at' => '2025-12-16 15:55:02.174093+00'],
    ['id' => '72d6d667-1612-416f-9715-08c4d3e4b1f1', 'email' => 'anacalado1979@gmail.com', 'nome' => 'Ana Calado', 'escola' => 'Escola Manuel Coco', 'ano' => 1, 'created_at' => '2026-01-30 22:01:15.579988+00'],
    ['id' => '7cadae0c-086f-4f3b-8065-866318bafaf5', 'email' => 'joaodedeus1_1@gmail.com', 'nome' => 'Francisco Coelho', 'escola' => 'Joao de Deus', 'ano' => 1, 'created_at' => '2026-01-23 14:50:21.065669+00'],
    ['id' => '7e6a6984-4803-4fe0-a00b-357f5880d411', 'email' => 'marianaazevedo2004@gmail.com', 'nome' => 'Mariana Azevedo', 'escola' => 'IST', 'ano' => 6, 'created_at' => '2026-01-15 00:53:33.049045+00'],
    ['id' => '82fcff68-7301-4599-b818-2cfa383b603c', 'email' => 'sara@gmail.com', 'nome' => 'Sara', 'escola' => 'Manuel Coco', 'ano' => 3, 'created_at' => '2026-01-07 09:22:32.411255+00'],
    ['id' => '888d5cf8-563d-48fb-b8e2-5762fa280dfd', 'email' => 'marianacpereiraa@gmail.com', 'nome' => 'Mariana Pereira', 'escola' => 'Escola EB1/JI Manuel Coco', 'ano' => 4, 'created_at' => '2026-01-15 11:45:26.182941+00'],
    ['id' => '91cec526-b000-4893-99a2-f0d21a51b67f', 'email' => 'armando@gmail.com', 'nome' => 'armando djigidijonson', 'escola' => 'Lisboa', 'ano' => 4, 'created_at' => '2026-01-04 17:50:54.553055+00'],
    ['id' => '93e79d89-7ad2-406c-b29d-78a3bcd21d86', 'email' => 'dinisgoncales@gmail.com', 'nome' => 'Dinis Gonçalves', 'escola' => 'Pousa', 'ano' => 3, 'created_at' => '2026-01-29 12:13:20.465366+00'],
    ['id' => '9aa69c4e-3cb6-4288-a156-3334ccc890bc', 'email' => 'vitor@gmail.com', 'nome' => 'Vitor', 'escola' => 'Leiria', 'ano' => 3, 'created_at' => '2025-12-22 19:15:30.806961+00'],
    ['id' => '9ff0ec9f-133b-4944-9e7b-e2a1d8d26aea', 'email' => 'joaodedeus0_1@gmail.com', 'nome' => 'Théo Martins', 'escola' => 'Joao de Deus', 'ano' => 1, 'created_at' => '2026-01-23 16:17:59.133784+00'],
    ['id' => 'a68107e6-eab7-4d0b-9ae5-3b06163809da', 'email' => 'vascogomes@gmail.com', 'nome' => 'Vasco Gomes', 'escola' => 'Dona Maria', 'ano' => 3, 'created_at' => '2026-01-01 20:39:53.067319+00'],
    ['id' => 'ab3dbad0-652e-4a8b-a9d3-67f0f836a092', 'email' => 'beatriz@gmail.com', 'nome' => 'Beatriz', 'escola' => 'Pousa', 'ano' => 4, 'created_at' => '2026-01-29 11:23:49.64383+00'],
    ['id' => 'ae5b447c-f72b-45cf-9627-1a28812ff86b', 'email' => 'vicente@gmail.com', 'nome' => 'Vicente', 'escola' => 'Manuel Coco', 'ano' => 2, 'created_at' => '2026-01-07 11:15:59.594869+00'],
    ['id' => 'ae85a1b2-9106-4c87-8778-e9aa88c5d889', 'email' => 'margaridanunes@gmail.com', 'nome' => 'Margarida Nunes', 'escola' => 'ist', 'ano' => 6, 'created_at' => '2026-01-31 15:45:52.045992+00'],
    ['id' => 'b1212737-d9ee-4cbf-93be-ccfb8d425b69', 'email' => 'goncalo@gmail.com', 'nome' => 'Gonçalo', 'escola' => 'Manuel Coco', 'ano' => 1, 'created_at' => '2026-01-07 12:39:33.689499+00'],
    ['id' => 'b53dc0d0-a977-4696-8b40-61489c1ae123', 'email' => 'dinis@gmail.com', 'nome' => 'Dinis', 'escola' => 'Pousa', 'ano' => 1, 'created_at' => '2026-01-29 11:10:34.896705+00'],
    ['id' => 'b885a11a-8c6b-4bb4-bcb1-62c08bbe7275', 'email' => 'daiame@gmail.com', 'nome' => 'Daiame', 'escola' => 'pousa', 'ano' => 2, 'created_at' => '2026-01-29 11:41:34.061237+00'],
    ['id' => 'd0236b95-3830-4d8d-bb59-9bb8bc02f69f', 'email' => 'peafcc@gmail.com', 'nome' => 'Pedro Carvalho', 'escola' => 'Escola Secundária José Saramago', 'ano' => 9, 'created_at' => '2026-02-17 11:16:49.512113+00'],
    ['id' => 'd37c7d7c-aa9b-45d9-8d25-693333ad937f', 'email' => 'joaodedeus2_2@gmail.com', 'nome' => 'Carolina Mateus', 'escola' => 'Joao de Deus', 'ano' => 2, 'created_at' => '2026-01-23 15:59:16.206919+00'],
    ['id' => 'd95110b6-912c-42dc-bc46-aee75f0bad95', 'email' => 'matilde.madureira@junitec.pt', 'nome' => 'Matilde Madureira', 'escola' => 'Instituto Superior Técnico', 'ano' => 12, 'created_at' => '2026-02-05 19:01:19.659304+00'],
    ['id' => 'ea6ca1db-4460-45b5-a218-6676490a2d47', 'email' => 'joaodedeus1_2@gmail.com', 'nome' => 'Lara Romão', 'escola' => 'Joao de Deus', 'ano' => 1, 'created_at' => '2026-01-23 15:18:32.687355+00'],
    ['id' => 'f160026d-e1eb-4ae2-a966-9c2fdc33337b', 'email' => 'francisco.redondo@gmail.com', 'nome' => 'Francisco Redondo', 'escola' => 'CRSI', 'ano' => 7, 'created_at' => '2025-12-16 19:41:59.613468+00'],
    ['id' => 'fc8a2db6-8678-465e-9f8f-aceb87fd138a', 'email' => 'maria@gmail.com', 'nome' => 'Maria', 'escola' => 'Manuel Coco', 'ano' => 3, 'created_at' => '2026-01-07 12:07:45.488167+00'],
];

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "👨‍🎓 Importando Alunos\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$alunosImportados = 0;
$alunosAtualizados = 0;
$alunosIgnorados = 0;

foreach ($novosAlunos as $aluno) {
    $exists = DB::table('users')->where('id', $aluno['id'])->exists();
    
    try {
        if ($exists) {
            // Atualizar dados
            DB::table('users')
                ->where('id', $aluno['id'])
                ->update([
                    'name' => $aluno['nome'],
                    'email' => $aluno['email'],
                    'school_year' => (string) $aluno['ano'],
                    'updated_at' => \now(),
                ]);
            
            echo "   🔄 Atualizado: {$aluno['nome']} ({$aluno['email']})\n";
            $alunosAtualizados++;
        } else {
            // Inserir novo
            DB::table('users')->insert([
                'id' => $aluno['id'],
                'name' => $aluno['nome'],
                'email' => $aluno['email'],
                'role' => 'aluno',
                'school_year' => (string) $aluno['ano'],
                'email_verified_at' => $aluno['created_at'],
                'created_at' => $aluno['created_at'],
                'updated_at' => \now(),
            ]);
            
            echo "   ✅ Criado: {$aluno['nome']} ({$aluno['email']})\n";
            $alunosImportados++;
        }
    } catch (\Exception $e) {
        echo "   ❌ Erro ao importar {$aluno['nome']}: " . $e->getMessage() . "\n";
        $alunosIgnorados++;
    }
}

echo "\n📊 Alunos: $alunosImportados criados, $alunosAtualizados atualizados, $alunosIgnorados erros\n\n";

// =============================================================================
// NOVOS PROFISSIONAIS
// =============================================================================

$novosProfissionais = [
    ['id' => '0c8159c5-7eca-4930-bc2a-bd492335c351', 'email' => 'andre.rodrigues@junitec.pt', 'nome' => 'José Pinhal', 'escola' => 'ESAS', 'funcao' => 'Professor(a)', 'created_at' => '2025-12-21 21:30:14.933411+00'],
    ['id' => '0fc3660d-c0dd-4498-ab31-0b29d204cc41', 'email' => 'vitorclara@gmail.com', 'nome' => 'Vitor Clara', 'escola' => 'IST', 'funcao' => 'Professor(a)', 'created_at' => '2026-01-05 00:22:46.676984+00'],
    ['id' => '295d1ad1-f6b2-4424-aa70-3809f17457ed', 'email' => 'margaridan@gmail.com', 'nome' => 'Margarida Nunes', 'escola' => 'Manuel Coco', 'funcao' => 'Professor(a)', 'created_at' => '2026-01-07 10:28:47.919171+00'],
    ['id' => '5c147895-08dd-4a75-baeb-7984e06684ea', 'email' => 'alzirax@gmail.com', 'nome' => 'Alzira Vicente', 'escola' => 'EB1/JI Manuel Coco', 'funcao' => 'Professor(a)', 'created_at' => '2026-02-11 23:34:14.040427+00'],
    ['id' => '7f0945b6-3bb9-49b8-90c0-342f08613a0f', 'email' => 'andre.correia@yahoo.com', 'nome' => 'André Correia', 'escola' => 'Escola Amadeu', 'funcao' => 'Professor(a)', 'created_at' => '2025-12-20 20:06:28.561245+00'],
    ['id' => '9a80b9dc-84b5-4cc8-81af-7712944fccfd', 'email' => 'ana@gmail.com', 'nome' => 'Ana', 'escola' => 'ist', 'funcao' => 'Professor(a)', 'created_at' => '2025-12-29 14:46:31.177095+00'],
    ['id' => 'ce3831d3-4c2a-49a0-bb67-2dcd36a3af13', 'email' => 'andreclashroyalerods@gmail.com', 'nome' => 'André Rodrigues', 'escola' => 'Teste', 'funcao' => 'Outro', 'created_at' => '2025-12-16 15:56:27.195962+00'],
    ['id' => 'e62aecc7-b9e5-4e91-a94c-af0522b9da06', 'email' => 'anaritacalado@aemoinhosarroja.pt', 'nome' => 'Ana Calado', 'escola' => 'E.B1/J.I Manuel Coco', 'funcao' => 'Professor(a)', 'created_at' => '2026-01-30 21:55:29.65658+00'],
];

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "👨‍🏫 Importando Profissionais\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$profImportados = 0;
$profAtualizados = 0;
$profIgnorados = 0;

foreach ($novosProfissionais as $prof) {
    $exists = DB::table('users')->where('id', $prof['id'])->exists();
    
    try {
        if ($exists) {
            // Atualizar dados
            DB::table('users')
                ->where('id', $prof['id'])
                ->update([
                    'name' => $prof['nome'],
                    'email' => $prof['email'],
                    'updated_at' => \now(),
                ]);
            
            echo "   🔄 Atualizado: {$prof['nome']} ({$prof['email']})\n";
            $profAtualizados++;
        } else {
            // Inserir novo
            DB::table('users')->insert([
                'id' => $prof['id'],
                'name' => $prof['nome'],
                'email' => $prof['email'],
                'role' => 'profissional',
                'email_verified_at' => $prof['created_at'],
                'created_at' => $prof['created_at'],
                'updated_at' => \now(),
            ]);
            
            echo "   ✅ Criado: {$prof['nome']} ({$prof['email']})\n";
            $profImportados++;
        }
    } catch (\Exception $e) {
        echo "   ❌ Erro ao importar {$prof['nome']}: " . $e->getMessage() . "\n";
        $profIgnorados++;
    }
}

echo "\n📊 Profissionais: $profImportados criados, $profAtualizados atualizados, $profIgnorados erros\n\n";

// =============================================================================
// RESUMO FINAL
// =============================================================================

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                  ✨ IMPORTAÇÃO CONCLUÍDA                       ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$totalUsers = DB::table('users')->count();
$totalAlunos = DB::table('users')->where('role', 'aluno')->count();
$totalProf = DB::table('users')->where('role', 'profissional')->count();

echo "📊 Total de usuários: $totalUsers\n";
echo "   👨‍🎓 Alunos: $totalAlunos\n";
echo "   👨‍🏫 Profissionais: $totalProf\n\n";
