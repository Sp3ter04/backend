<?php

declare(strict_types=1);

/**
 * Audita word_timestamps / word_start_times de todos os exercises e
 * gera dois ficheiros para revisão humana:
 *
 *   - fix_word_timings_REPORT.md   (antes/depois, legível)
 *   - fix_word_timings.sql         (UPDATEs comentados + TODOs)
 *
 * NÃO escreve nada na base de dados.
 *
 * Regras:
 *   first_gap_too_long       gap [0]->[1] > GAP_HIGH       => corrige startTime[0] = startTime[1] - LEAD_SECONDS
 *   mid_gap natural          GAP_HIGH < gap <= GAP_MID_TODO sem pontuação  => ignora (pausa natural)
 *   mid_gap legítimo         qualquer gap > GAP_HIGH com pontuação adjacente => ignora
 *   mid_gap_too_long         gap > GAP_MID_TODO sem pontuação              => marca TODO no SQL (sem UPDATE)
 *   tiny_gap (<GAP_LOW)      fora de scope (problema de UI), apenas contado
 */

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

const GAP_HIGH     = 1.0;   // limiar inicial: acima disto é "suspeito"
const GAP_MID_TODO = 1.3;   // gaps no meio entre 1.0–1.3s tratados como pausa natural; >1.3s = TODO
const GAP_LOW      = 0.08;  // só para reporte
const LEAD_SECONDS = 0.30;  // janela visual desejada antes da palavra seguinte

$reportPath = __DIR__ . '/../fix_word_timings_REPORT.md';
$sqlPath    = __DIR__ . '/../fix_word_timings.sql';

$exercises = DB::table('exercises')
    ->select('id', 'number', 'content', 'word_timestamps', 'word_start_times')
    ->whereNotNull('word_timestamps')
    ->orderBy('number')
    ->get();

$stats = [
    'total'        => 0,
    'affected'     => 0,
    'first_fixes'  => 0,
    'mid_todos'    => 0,
    'mid_legit'    => 0,
    'mid_natural'  => 0,
    'tiny_seen'    => 0,
];

$report = [];
$sql = [];
$sql[] = "-- Gerado em " . date('Y-m-d H:i') . " por scripts/fix_word_timing_gaps.php";
$sql[] = "-- REVER antes de executar. Cada UPDATE atualiza word_timestamps e word_start_times.";
$sql[] = "BEGIN;";
$sql[] = '';

foreach ($exercises as $ex) {
    $stats['total']++;
    $tokens = json_decode($ex->word_timestamps, true);
    if (!is_array($tokens)) {
        continue;
    }

    $wordIdx = [];
    foreach ($tokens as $i => $t) {
        if (($t['type'] ?? null) === 'word') {
            $wordIdx[] = $i;
        }
    }
    if (count($wordIdx) < 2) {
        continue;
    }

    $changes = [];
    $todos   = [];
    $legit   = [];
    $natural = [];

    for ($k = 0; $k < count($wordIdx) - 1; $k++) {
        $iA = $wordIdx[$k];
        $iB = $wordIdx[$k + 1];
        $tA = (float) ($tokens[$iA]['startTime'] ?? 0);
        $tB = (float) ($tokens[$iB]['startTime'] ?? 0);
        $gap = round($tB - $tA, 3);

        if ($gap < GAP_LOW) {
            $stats['tiny_seen']++;
            continue;
        }

        if ($gap <= GAP_HIGH) {
            continue;
        }

        // gap > 1.0s
        if ($k === 0) {
            $newStart = max(0.0, round($tB - LEAD_SECONDS, 3));
            if (abs($newStart - $tA) >= 0.01) {
                $changes[] = [
                    'token_index' => $iA,
                    'word_index'  => 0,
                    'token'       => $tokens[$iA]['token'] ?? '?',
                    'next_token'  => $tokens[$iB]['token'] ?? '?',
                    'old'         => $tA,
                    'new'         => $newStart,
                    'old_gap'     => $gap,
                    'new_gap'     => round($tB - $newStart, 3),
                ];
            }
            continue;
        }

        // pausa legítima se houver token punct entre, ou palavra A acabar em pontuação
        $hasPunct = false;
        for ($j = $iA + 1; $j < $iB; $j++) {
            if (($tokens[$j]['type'] ?? null) === 'punct') {
                $hasPunct = true;
                break;
            }
        }
        $prevToken = (string) ($tokens[$iA]['token'] ?? '');
        if (!$hasPunct && $prevToken !== '' && preg_match('/[\.,;:!\?]$/u', $prevToken)) {
            $hasPunct = true;
        }

        $entry = [
            'a' => $tokens[$iA]['token'] ?? '?',
            'b' => $tokens[$iB]['token'] ?? '?',
            'gap' => $gap,
        ];

        if ($hasPunct) {
            $legit[] = $entry;
        } elseif ($gap <= GAP_MID_TODO) {
            $natural[] = $entry;
            $stats['mid_natural']++;
        } else {
            $todos[] = $entry;
        }
    }

    if (!$changes && !$todos && !$legit && !$natural) {
        continue;
    }

    $stats['affected']++;
    $stats['first_fixes'] += count($changes);
    $stats['mid_todos']   += count($todos);
    $stats['mid_legit']   += count($legit);

    // ---- REPORT ----
    $report[] = "## ex#{$ex->number}";
    $report[] = "- frase: " . trim($ex->content);
    $report[] = "- id: `{$ex->id}`";

    if ($changes) {
        $report[] = "- correções propostas:";
        foreach ($changes as $c) {
            $report[] = sprintf(
                "  - `[%d] %s` startTime %.3f → %.3f  (gap antes %.3fs → depois %.3fs ; próxima palavra: `%s`)",
                $c['word_index'], $c['token'], $c['old'], $c['new'], $c['old_gap'], $c['new_gap'], $c['next_token']
            );
        }
    }
    if ($todos) {
        $report[] = "- ⚠️ gaps suspeitos no meio (> " . GAP_MID_TODO . "s, sem pontuação) — REVISÃO MANUAL:";
        foreach ($todos as $t) {
            $report[] = sprintf("  - `%s` → `%s` : gap %.3fs", $t['a'], $t['b'], $t['gap']);
        }
    }
    if ($natural) {
        $report[] = "- pausas naturais ignoradas (1.0–" . GAP_MID_TODO . "s):";
        foreach ($natural as $n) {
            $report[] = sprintf("  - `%s` → `%s` : gap %.3fs", $n['a'], $n['b'], $n['gap']);
        }
    }
    if ($legit) {
        $report[] = "- pausas legítimas (com pontuação, mantidas):";
        foreach ($legit as $l) {
            $report[] = sprintf("  - `%s` → `%s` : gap %.3fs", $l['a'], $l['b'], $l['gap']);
        }
    }
    $report[] = '';

    // ---- SQL ----
    if ($changes || $todos) {
        $sql[] = "-- ============================================================";
        $sql[] = "-- ex#{$ex->number}: " . trim(preg_replace('/\s+/', ' ', $ex->content));
        $sql[] = "-- id: {$ex->id}";
    }
    foreach ($changes as $c) {
        $sql[] = sprintf(
            "-- [%d] %s : startTime %.3f -> %.3f  (gap %.3fs -> %.3fs)",
            $c['word_index'], $c['token'], $c['old'], $c['new'], $c['old_gap'], $c['new_gap']
        );
        $sql[] = sprintf(
            "UPDATE exercises SET\n  word_timestamps  = jsonb_set(word_timestamps,  '{%d,startTime}', '%s'::jsonb),\n  word_start_times = jsonb_set(word_start_times, '{%d}',           '%s'::jsonb)\nWHERE id = '%s';",
            $c['token_index'], json_encode($c['new']),
            $c['word_index'],  json_encode($c['new']),
            $ex->id
        );
        $sql[] = '';
    }
    foreach ($todos as $t) {
        $sql[] = sprintf(
            "-- TODO ex#%d: gap %.3fs entre `%s` e `%s` (sem pontuação, > %.1fs). Verificar áudio antes de corrigir.",
            $ex->number, $t['gap'], $t['a'], $t['b'], GAP_MID_TODO
        );
    }
    if ($changes || $todos) {
        $sql[] = '';
    }
}

$sql[] = "COMMIT;";

file_put_contents($sqlPath, implode("\n", $sql) . "\n");

$header = [
    "# Auditoria word_timestamps — " . date('Y-m-d H:i'),
    '',
    "Regras: GAP_HIGH=" . GAP_HIGH . "s, GAP_MID_TODO=" . GAP_MID_TODO . "s, LEAD_SECONDS=" . LEAD_SECONDS . "s.",
    '',
    "## Resumo",
    "- exercises analisados: {$stats['total']}",
    "- exercises afetados:   {$stats['affected']}",
    "- correções de 1ª palavra geradas: {$stats['first_fixes']}",
    "- TODOs (gap meio > " . GAP_MID_TODO . "s, sem pontuação): {$stats['mid_todos']}",
    "- pausas naturais ignoradas (1.0–" . GAP_MID_TODO . "s, sem pontuação): {$stats['mid_natural']}",
    "- pausas legítimas (com pontuação): {$stats['mid_legit']}",
    "- gaps < " . GAP_LOW . "s detetados (fora de scope): {$stats['tiny_seen']}",
    '',
];

file_put_contents($reportPath, implode("\n", array_merge($header, $report)) . "\n");

echo "OK" . PHP_EOL;
echo "  Report: {$reportPath}" . PHP_EOL;
echo "  SQL:    {$sqlPath}" . PHP_EOL;
echo "  total={$stats['total']}  afetados={$stats['affected']}  first_fixes={$stats['first_fixes']}  todos={$stats['mid_todos']}  naturais={$stats['mid_natural']}  legitimas={$stats['mid_legit']}  tiny={$stats['tiny_seen']}" . PHP_EOL;
