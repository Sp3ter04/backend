-- Gerado em 2026-04-26 21:27 por scripts/fix_word_timing_gaps.php
-- REVER antes de executar. Cada UPDATE atualiza word_timestamps e word_start_times.
BEGIN;

-- ============================================================
-- ex#5: O padeiro prepara o pão bem dourado.
-- id: 019cb51c-7699-72b6-b2c2-6c0b202f3712
-- [0] O : startTime 0.000 -> 0.864  (gap 1.164s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.864'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.864'::jsonb)
WHERE id = '019cb51c-7699-72b6-b2c2-6c0b202f3712';


-- ============================================================
-- ex#6: Os pescadores partiram para pescar peixe fresco.
-- id: 019cb51c-a613-7241-bdc1-e487971ed312
-- [0] Os : startTime 0.000 -> 1.126  (gap 1.426s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '1.126'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '1.126'::jsonb)
WHERE id = '019cb51c-a613-7241-bdc1-e487971ed312';


-- ============================================================
-- ex#10: O bombeiro bravo apaga o fogo com a sua poderosa mangueira.
-- id: 019cb51d-306d-7314-8550-6e6711c32d73
-- [0] O : startTime 0.000 -> 0.901  (gap 1.201s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.901'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.901'::jsonb)
WHERE id = '019cb51d-306d-7314-8550-6e6711c32d73';


-- ============================================================
-- ex#11: A decisão de participar no desfile foi bem pensada e divertida.
-- id: 019cb51d-8194-7246-9289-123bc6b5ae74
-- [0] A : startTime 0.000 -> 0.777  (gap 1.077s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.777'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.777'::jsonb)
WHERE id = '019cb51d-8194-7246-9289-123bc6b5ae74';


-- ============================================================
-- ex#13: A beleza do castelo antigo perfurava a névoa matinal projetando uma sombra sobre a paisagem.
-- id: 019cb51e-10eb-7322-80e6-884a4a610d24
-- [0] A : startTime 0.000 -> 0.717  (gap 1.017s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.717'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.717'::jsonb)
WHERE id = '019cb51e-10eb-7322-80e6-884a4a610d24';


-- ============================================================
-- ex#14: O jardim tem diversas plantas e flores.
-- id: 019cb51e-8aa0-71cc-8cb9-44309fe31e15
-- [0] O : startTime 0.000 -> 0.768  (gap 1.068s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.768'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.768'::jsonb)
WHERE id = '019cb51e-8aa0-71cc-8cb9-44309fe31e15';


-- ============================================================
-- ex#17: A decisão do presidente para arranjar o parque público foi bem recebida pela população.
-- id: 019cb51f-32db-7370-955f-9e11f8517ab6
-- [0] A : startTime 0.000 -> 1.846  (gap 2.146s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '1.846'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '1.846'::jsonb)
WHERE id = '019cb51f-32db-7370-955f-9e11f8517ab6';


-- ============================================================
-- ex#20: O defensor da justiça prometeu lutar pelos direitos dos mais pobres e desfavorecidos da população.
-- id: 019cb520-23f1-71e2-8bea-add588584e14
-- TODO ex#20: gap 1.684s entre `e` e `desfavorecidos` (sem pontuação, > 1.3s). Verificar áudio antes de corrigir.

-- ============================================================
-- ex#24: O menino e a menina têm sapatos novos.
-- id: 019cb545-bd6a-7073-82ab-4ff6b329314b
-- [0] O : startTime 0.000 -> 0.725  (gap 1.025s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.725'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.725'::jsonb)
WHERE id = '019cb545-bd6a-7073-82ab-4ff6b329314b';


-- ============================================================
-- ex#25: O menino tem um sapato.
-- id: 019cb546-0688-7186-acfe-53769625ca30
-- [0] O : startTime 0.000 -> 0.762  (gap 1.062s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.762'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.762'::jsonb)
WHERE id = '019cb546-0688-7186-acfe-53769625ca30';


-- ============================================================
-- ex#49: O Miguel gosta de jogar futebol.
-- id: 019cbf61-2ce6-720b-bf37-a99492fbde5e
-- [0] O : startTime 0.000 -> 0.750  (gap 1.050s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.75'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.75'::jsonb)
WHERE id = '019cbf61-2ce6-720b-bf37-a99492fbde5e';


-- ============================================================
-- ex#52: O menino e a menina têm sapatos novos.
-- id: 5d1f7432-f9da-4bd8-b230-268052f497c2
-- [0] O : startTime 0.000 -> 0.725  (gap 1.025s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.725'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.725'::jsonb)
WHERE id = '5d1f7432-f9da-4bd8-b230-268052f497c2';


-- ============================================================
-- ex#59: A brigada de bombeiros demonstrou profissionalismo ao combater o incêndio com determinação e bravura.
-- id: 4145cbcc-5cff-4e39-90f1-b6db43cd3ab4
-- [0] A : startTime 0.000 -> 0.806  (gap 1.106s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.806'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.806'::jsonb)
WHERE id = '4145cbcc-5cff-4e39-90f1-b6db43cd3ab4';


-- ============================================================
-- ex#67: As descobertas científicas revolucionaram o universo.
-- id: 47539059-e77e-4c15-9328-9c1773927d84
-- TODO ex#67: gap 1.540s entre `o` e `universo` (sem pontuação, > 1.3s). Verificar áudio antes de corrigir.

-- ============================================================
-- ex#76: O projeto pedagógico proposto pela professora Bárbara despertou grande interesse nos alunos.
-- id: 19fdd00c-0804-4c61-b25d-b0202a118174
-- [0] O : startTime 0.000 -> 0.877  (gap 1.177s -> 0.300s)
UPDATE exercises SET
  word_timestamps  = jsonb_set(word_timestamps,  '{0,startTime}', '0.877'::jsonb),
  word_start_times = jsonb_set(word_start_times, '{0}',           '0.877'::jsonb)
WHERE id = '19fdd00c-0804-4c61-b25d-b0202a118174';


-- ============================================================
-- ex#77: A banda popular decidiu preparar um concerto benéfico para a comunidade local.
-- id: 34c835f9-48e9-4766-9d59-e41d77a3cb47
-- TODO ex#77: gap 1.668s entre `decidiu` e `preparar` (sem pontuação, > 1.3s). Verificar áudio antes de corrigir.

COMMIT;
