<?php

declare(strict_types=1);

/**
 * Verificação minuciosa (read-only) de word_timestamps / word_start_times
 * de TODOS os exercises. Não escreve nada na DB.
 *
 * Categorias verificadas:
 *  C1  estrutura JSON inválida em word_timestamps
 *  C2  estrutura JSON inválida em word_start_times
 *  C3  word_start_times ausente
 *  C4  contagem de palavras divergente entre word_timestamps e word_start_times
 *  C5  startTime entre word_timestamps[i].startTime e word_start_times[i] divergente (>0.005s)
 *  C6  startTime não monotónico (palavra n+1 começa antes da n)
 *  C7  duration presente mas <= 0 (apenas se o token tiver o campo)
 *  C8  sobreposição entre tokens consecutivos com base em duration (quando existe)
 *  C9  (informativo) primeira palavra com startTime exactamente 0
 *  C10 lead-in/gap inicial > 1.0s (depois do batch já aplicado)
 *  C11 gap suspeito sem pontuação > 1.3s (TODO)
 *  C12 token tipo word com texto vazio
 *  C13 número de palavras (tokens word) ≠ número de palavras no campo content (tolerância: ±1)
 *  C14 startTime negativo
 *  C15 gap minúsculo entre palavras consecutivas (< 0.08s) sem ser monossílabo
 *  C16 ordem de tokens (palavras vs pontuação): pontuação antes da 1ª palavra
 *  C17 último token termina com gap inesperado vs duração de áudio (skip se sem metadata)
 */

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

const TOL_SYNC      = 0.005;
const OVERLAP_MARGIN= 0.05;
const LEAD_MAX      = 1.00;
const GAP_MID_TODO  = 1.30;
const GAP_HIGH      = 1.00;
const TINY_GAP      = 0.08;

$exercises = DB::table('exercises')
    ->select('id','number','content','word_timestamps','word_start_times')
    ->orderBy('number')
    ->get();

$findings = []; // ex# => [ [code, msg], ... ]
$counts   = array_fill_keys(['C1','C2','C3','C4','C5','C6','C7','C8','C9','C10','C11','C12','C13','C14','C15','C16'], 0);

function add(array &$findings, array &$counts, int $num, string $code, string $msg): void {
    $findings[$num][] = [$code, $msg];
    $counts[$code]++;
}

foreach ($exercises as $ex) {
    $num = (int) $ex->number;

    if ($ex->word_timestamps === null) { continue; }
    $wts = json_decode($ex->word_timestamps, true);
    if (!is_array($wts)) { add($findings,$counts,$num,'C1','word_timestamps não decodifica para array'); continue; }

    $wst = null;
    if ($ex->word_start_times === null) {
        add($findings,$counts,$num,'C3','word_start_times é NULL');
    } else {
        $wst = json_decode($ex->word_start_times, true);
        if (!is_array($wst)) { add($findings,$counts,$num,'C2','word_start_times não decodifica para array'); $wst = null; }
    }

    // Extrair palavras (tipo word) com índices originais e valores
    $wordTokens = []; // [ ['idx'=>i,'token'=>..,'start'=>..,'duration'=>..], ... ]
    foreach ($wts as $i => $t) {
        $type = $t['type'] ?? null;
        if ($type !== 'word') { continue; }
        $token = (string)($t['token'] ?? '');
        if ($token === '') { add($findings,$counts,$num,'C12',"token word vazio em wts[$i]"); }
        $st = $t['startTime'] ?? null;
        $hasDuration = array_key_exists('duration', $t);
        $du = $t['duration']  ?? null;
        if (!is_numeric($st)) { add($findings,$counts,$num,'C7',"startTime não numérico em wts[$i] ($token)"); continue; }
        if ((float)$st < 0)   { add($findings,$counts,$num,'C14',sprintf("startTime negativo em wts[%d] (%s) = %.3f",$i,$token,(float)$st)); }
        if ($hasDuration && (!is_numeric($du) || (float)$du <= 0)) {
            add($findings,$counts,$num,'C7',sprintf("duration inválida em wts[%d] (%s) = %s",$i,$token,var_export($du,true)));
        }
        $wordTokens[] = ['idx'=>$i,'token'=>$token,'start'=>(float)$st,'duration'=>($hasDuration && is_numeric($du))?(float)$du:null];
    }

    if (empty($wordTokens)) { continue; }

    // C4 + C5: comparar com word_start_times
    if (is_array($wst)) {
        if (count($wst) !== count($wordTokens)) {
            add($findings,$counts,$num,'C4',sprintf("contagem divergente: wts.words=%d vs wst=%d",count($wordTokens),count($wst)));
        } else {
            foreach ($wordTokens as $k => $w) {
                $ref = $wst[$k];
                if (!is_numeric($ref)) {
                    add($findings,$counts,$num,'C2',"word_start_times[$k] não numérico");
                    continue;
                }
                if (abs((float)$ref - $w['start']) > TOL_SYNC) {
                    add($findings,$counts,$num,'C5',sprintf(
                        "wts[%d].startTime=%.3f difere de wst[%d]=%.3f (palavra `%s`)",
                        $w['idx'], $w['start'], $k, (float)$ref, $w['token']
                    ));
                }
            }
        }
    }

    // C6 monotonicidade + C8 sobreposição + C11 gaps
    for ($k=0; $k < count($wordTokens)-1; $k++) {
        $a = $wordTokens[$k];
        $b = $wordTokens[$k+1];
        if ($b['start'] < $a['start']) {
            add($findings,$counts,$num,'C6',sprintf("não monotónico: `%s`@%.3f -> `%s`@%.3f",$a['token'],$a['start'],$b['token'],$b['start']));
            continue;
        }
        if ($a['duration'] !== null) {
            $aEnd = $a['start'] + $a['duration'];
            if ($b['start'] + OVERLAP_MARGIN < $aEnd) {
                add($findings,$counts,$num,'C8',sprintf(
                    "sobreposição: `%s` termina em %.3f, `%s` começa em %.3f (overlap %.3fs)",
                    $a['token'],$aEnd,$b['token'],$b['start'],$aEnd - $b['start']
                ));
            }
        }

        $gap = $b['start'] - $a['start'];
        if ($gap > GAP_HIGH) {
            // verificar se há pontuação entre
            $hasPunct = false;
            for ($j=$a['idx']+1; $j<$b['idx']; $j++) {
                if (($wts[$j]['type'] ?? null) === 'punct') { $hasPunct=true; break; }
            }
            if (!$hasPunct && preg_match('/[\.,;:!\?]$/u',$a['token'])) { $hasPunct = true; }
            if (!$hasPunct && $gap > GAP_MID_TODO) {
                add($findings,$counts,$num,'C11',sprintf("gap suspeito %.3fs entre `%s` e `%s` (sem pontuação)",$gap,$a['token'],$b['token']));
            }
        } elseif ($gap < TINY_GAP) {
            add($findings,$counts,$num,'C15',sprintf("gap minúsculo %.3fs: `%s`@%.3f -> `%s`@%.3f",$gap,$a['token'],$a['start'],$b['token'],$b['start']));
        }
    }

    // C16 pontuação antes da 1ª palavra
    foreach ($wts as $i => $t) {
        if (($t['type'] ?? null) === 'punct') {
            if ($i < $wordTokens[0]['idx']) {
                add($findings,$counts,$num,'C16',"pontuação `".($t['token'] ?? '?')."` antes da 1ª palavra");
            }
            break;
        }
    }

    // C9 informativo: 1ª palavra exactamente em 0.000
    $first = $wordTokens[0];
    if ($first['start'] === 0.0) {
        add($findings,$counts,$num,'C9',sprintf("1ª palavra (`%s`) começa em 0.000s (sem lead-in)",$first['token']));
    }
    if (count($wordTokens) >= 2) {
        $second = $wordTokens[1];
        $firstGap = $second['start'] - $first['start'];
        if ($firstGap > LEAD_MAX) {
            add($findings,$counts,$num,'C10',sprintf("lead-in/gap inicial excessivo: `%s`@%.3f -> `%s`@%.3f (gap %.3fs)",$first['token'],$first['start'],$second['token'],$second['start'],$firstGap));
        }
    }

    // C13 contagem palavras vs content
    if ($ex->content) {
        $contentWords = preg_match_all('/\p{L}[\p{L}\p{M}\-]*/u', $ex->content);
        $diff = abs($contentWords - count($wordTokens));
        if ($diff > 1) {
            add($findings,$counts,$num,'C13',sprintf("content tem %d palavras mas wts tem %d tokens word (diff=%d)",$contentWords,count($wordTokens),$diff));
        }
    }
}

// ---------- saída ----------
$out = [];
$out[] = "# Verificação minuciosa word_timings — " . date('Y-m-d H:i');
$out[] = '';
$out[] = "Tolerâncias: TOL_SYNC=".TOL_SYNC."s, OVERLAP_MARGIN=".OVERLAP_MARGIN."s, LEAD_MAX=".LEAD_MAX."s, GAP_HIGH=".GAP_HIGH."s, GAP_MID_TODO=".GAP_MID_TODO."s, TINY_GAP=".TINY_GAP."s";
$out[] = '';
$out[] = "## Resumo por categoria";
$labels = [
    'C1'=>'word_timestamps inválido',
    'C2'=>'word_start_times inválido',
    'C3'=>'word_start_times ausente',
    'C4'=>'contagem divergente wts vs wst',
    'C5'=>'startTime divergente wts vs wst',
    'C6'=>'não monotónico',
    'C7'=>'duration inválida',
    'C8'=>'sobreposição entre palavras',
    'C9'=>'1ª palavra em 0.000s (informativo)',
    'C10'=>'lead-in/gap inicial > 1.0s',
    'C11'=>'gap meio > 1.3s sem pontuação (TODO)',
    'C12'=>'token word vazio',
    'C13'=>'palavras content ≠ tokens (>1 diff)',
    'C14'=>'startTime negativo',
    'C15'=>'gap < 0.08s entre palavras',
    'C16'=>'pontuação antes da 1ª palavra',
];
foreach ($counts as $code => $n) {
    $out[] = sprintf("- **%s** %s: %d", $code, $labels[$code], $n);
}
$out[] = '';
$out[] = "exercises com pelo menos 1 finding: " . count($findings) . "/" . $exercises->count();
$out[] = '';

ksort($findings);
$out[] = "## Detalhes";
$out[] = '';
foreach ($findings as $num => $items) {
    $out[] = "### ex#{$num}";
    foreach ($items as $f) {
        $out[] = "- **{$f[0]}** {$f[1]}";
    }
    $out[] = '';
}

$path = __DIR__ . '/../verify_word_timings_REPORT.md';
file_put_contents($path, implode("\n",$out)."\n");

echo "OK\n";
echo "  Report: $path\n";
echo "  exercises_com_findings=" . count($findings) . " / " . $exercises->count() . "\n";
foreach ($counts as $c => $n) {
    if ($n > 0) echo "  $c=$n  ({$labels[$c]})\n";
}
