-- Gerado em 2026-04-26 23:50 por scripts/fix_word_timing_gaps.php
-- REVER antes de executar. Cada UPDATE atualiza word_timestamps e word_start_times.
BEGIN;

-- ============================================================
-- ex#20: O defensor da justiça prometeu lutar pelos direitos dos mais pobres e desfavorecidos da população.
-- id: 019cb520-23f1-71e2-8bea-add588584e14
-- TODO ex#20: gap 1.684s entre `e` e `desfavorecidos` (sem pontuação, > 1.3s). Verificar áudio antes de corrigir.

-- ============================================================
-- ex#67: As descobertas científicas revolucionaram o universo.
-- id: 47539059-e77e-4c15-9328-9c1773927d84
-- TODO ex#67: gap 1.540s entre `o` e `universo` (sem pontuação, > 1.3s). Verificar áudio antes de corrigir.

-- ============================================================
-- ex#77: A banda popular decidiu preparar um concerto benéfico para a comunidade local.
-- id: 34c835f9-48e9-4766-9d59-e41d77a3cb47
-- TODO ex#77: gap 1.668s entre `decidiu` e `preparar` (sem pontuação, > 1.3s). Verificar áudio antes de corrigir.

COMMIT;
