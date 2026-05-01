# Auditoria word_timestamps — 2026-04-26 23:50

Regras: GAP_HIGH=1s, GAP_MID_TODO=1.3s, LEAD_SECONDS=0.3s.

## Resumo
- exercises analisados: 79
- exercises afetados:   17
- correções de 1ª palavra geradas: 0
- TODOs (gap meio > 1.3s, sem pontuação): 3
- pausas naturais ignoradas (1.0–1.3s, sem pontuação): 24
- pausas legítimas (com pontuação): 0
- gaps < 0.08s detetados (fora de scope): 1

## ex#4
- frase: A Dora desenha um pássaro belo no seu caderno.
- id: `019cb51c-31a2-72c3-a6c3-e9d8ca41226e`
- pausas naturais ignoradas (1.0–1.3s):
  - `seu` → `caderno` : gap 1.004s

## ex#11
- frase: A decisão de participar no desfile foi bem pensada e divertida.
- id: `019cb51d-8194-7246-9289-123bc6b5ae74`
- pausas naturais ignoradas (1.0–1.3s):
  - `pensada` → `e` : gap 1.009s

## ex#12
- frase: Os pássaros pousam nos ramos e cantam belíssimas melodias.
- id: `019cb51d-c0e2-7238-b5ca-954013bc0459`
- pausas naturais ignoradas (1.0–1.3s):
  - `pássaros` → `pousam` : gap 1.027s
  - `nos` → `ramos` : gap 1.069s
  - `belíssimas` → `melodias` : gap 1.211s

## ex#13
- frase: A beleza do castelo antigo perfurava a névoa matinal projetando uma sombra sobre a paisagem.
- id: `019cb51e-10eb-7322-80e6-884a4a610d24`
- pausas naturais ignoradas (1.0–1.3s):
  - `sombra` → `sobre` : gap 1.086s

## ex#14
- frase: O jardim tem diversas plantas e flores.
- id: `019cb51e-8aa0-71cc-8cb9-44309fe31e15`
- pausas naturais ignoradas (1.0–1.3s):
  - `tem` → `diversas` : gap 1.204s

## ex#15
- frase: O diretor pediu paciência a todos para o projeto de estudo do meio.
- id: `019cb51e-c302-73af-bd80-845b81449049`
- pausas naturais ignoradas (1.0–1.3s):
  - `paciência` → `a` : gap 1.044s

## ex#17
- frase: A decisão do presidente para arranjar o parque público foi bem recebida pela população.
- id: `019cb51f-32db-7370-955f-9e11f8517ab6`
- pausas naturais ignoradas (1.0–1.3s):
  - `bem` → `recebida` : gap 1.004s

## ex#20
- frase: O defensor da justiça prometeu lutar pelos direitos dos mais pobres e desfavorecidos da população.
- id: `019cb520-23f1-71e2-8bea-add588584e14`
- ⚠️ gaps suspeitos no meio (> 1.3s, sem pontuação) — REVISÃO MANUAL:
  - `e` → `desfavorecidos` : gap 1.684s
- pausas naturais ignoradas (1.0–1.3s):
  - `da` → `justiça` : gap 1.119s
  - `dos` → `mais` : gap 1.023s

## ex#32
- frase: O Pedro pinta uma parede branca.
- id: `53498836-f6c6-49fe-8f9b-92f408dcb58b`
- pausas naturais ignoradas (1.0–1.3s):
  - `uma` → `parede` : gap 1.059s

## ex#34
- frase: O céu ficou escuro e todas as gotas começaram a cair rapidamente para o chão.
- id: `d55b1119-00cb-498b-a739-c05cfa8e7e90`
- pausas naturais ignoradas (1.0–1.3s):
  - `para` → `o` : gap 1.115s

## ex#45
- frase: Levo dicionários e palavras que agora já sei usar.
- id: `019cb978-4ef7-709f-8321-bf1d299ccf3e`
- pausas naturais ignoradas (1.0–1.3s):
  - `que` → `agora` : gap 1.010s

## ex#47
- frase: A professora vai perguntar o que queremos ser quando formos crescidos.
- id: `019cb978-f3a7-7269-83f8-079b92c9c937`
- pausas naturais ignoradas (1.0–1.3s):
  - `professora` → `vai` : gap 1.017s

## ex#59
- frase: A brigada de bombeiros demonstrou profissionalismo ao combater o incêndio com determinação e bravura.
- id: `4145cbcc-5cff-4e39-90f1-b6db43cd3ab4`
- pausas naturais ignoradas (1.0–1.3s):
  - `brigada` → `de` : gap 1.126s
  - `profissionalismo` → `ao` : gap 1.146s
  - `incêndio` → `com` : gap 1.093s
  - `e` → `bravura` : gap 1.195s

## ex#66
- frase: O professor Pedro pediu para os alunos desenvolverem um debate sobre a proteção do ambiente.
- id: `3eb2f422-972d-4931-be28-f6629e96608e`
- pausas naturais ignoradas (1.0–1.3s):
  - `os` → `alunos` : gap 1.163s
  - `alunos` → `desenvolverem` : gap 1.231s

## ex#67
- frase: As descobertas científicas revolucionaram o universo.
- id: `47539059-e77e-4c15-9328-9c1773927d84`
- ⚠️ gaps suspeitos no meio (> 1.3s, sem pontuação) — REVISÃO MANUAL:
  - `o` → `universo` : gap 1.540s

## ex#76
- frase: O projeto pedagógico proposto pela professora Bárbara despertou grande interesse nos alunos.
- id: `19fdd00c-0804-4c61-b25d-b0202a118174`
- pausas naturais ignoradas (1.0–1.3s):
  - `Bárbara` → `despertou` : gap 1.091s
  - `grande` → `interesse` : gap 1.024s
  - `nos` → `alunos` : gap 1.083s

## ex#77
- frase: A banda popular decidiu preparar um concerto benéfico para a comunidade local.
- id: `34c835f9-48e9-4766-9d59-e41d77a3cb47`
- ⚠️ gaps suspeitos no meio (> 1.3s, sem pontuação) — REVISÃO MANUAL:
  - `decidiu` → `preparar` : gap 1.668s

