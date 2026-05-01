# Verificação minuciosa word_timings — 2026-04-26 23:52

Tolerâncias: TOL_SYNC=0.005s, OVERLAP_MARGIN=0.05s, LEAD_MAX=1s, GAP_HIGH=1s, GAP_MID_TODO=1.3s, TINY_GAP=0.08s

## Resumo por categoria
- **C1** word_timestamps inválido: 0
- **C2** word_start_times inválido: 0
- **C3** word_start_times ausente: 0
- **C4** contagem divergente wts vs wst: 0
- **C5** startTime divergente wts vs wst: 0
- **C6** não monotónico: 0
- **C7** duration inválida: 0
- **C8** sobreposição entre palavras: 0
- **C9** 1ª palavra em 0.000s (informativo): 63
- **C10** lead-in/gap inicial > 1.0s: 0
- **C11** gap meio > 1.3s sem pontuação (TODO): 3
- **C12** token word vazio: 0
- **C13** palavras content ≠ tokens (>1 diff): 0
- **C14** startTime negativo: 0
- **C15** gap < 0.08s entre palavras: 1
- **C16** pontuação antes da 1ª palavra: 0

exercises com pelo menos 1 finding: 60/79

## Detalhes

### ex#2
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#3
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#4
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#7
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#9
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#12
- **C9** 1ª palavra (`Os`) começa em 0.000s (sem lead-in)

### ex#15
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#16
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#18
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#19
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#20
- **C11** gap suspeito 1.684s entre `e` e `desfavorecidos` (sem pontuação)
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#21
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#22
- **C9** 1ª palavra (`Era`) começa em 0.000s (sem lead-in)

### ex#23
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#26
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#27
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#28
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#29
- **C9** 1ª palavra (`Ela`) começa em 0.000s (sem lead-in)

### ex#30
- **C9** 1ª palavra (`Eu`) começa em 0.000s (sem lead-in)

### ex#31
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#32
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#33
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)
- **C9** 1ª palavra (`No`) começa em 0.000s (sem lead-in)

### ex#34
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#35
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#36
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#37
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#38
- **C9** 1ª palavra (`Ela`) começa em 0.000s (sem lead-in)

### ex#39
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#40
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#41
- **C9** 1ª palavra (`Eu`) começa em 0.000s (sem lead-in)

### ex#42
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#43
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#44
- **C9** 1ª palavra (`Um`) começa em 0.000s (sem lead-in)

### ex#45
- **C15** gap minúsculo 0.057s: `agora`@3.241 -> `já`@3.298
- **C9** 1ª palavra (`Levo`) começa em 0.000s (sem lead-in)

### ex#46
- **C9** 1ª palavra (`Quero`) começa em 0.000s (sem lead-in)

### ex#47
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#51
- **C9** 1ª palavra (`Eu`) começa em 0.000s (sem lead-in)

### ex#53
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#54
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#56
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#57
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#60
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#61
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#62
- **C9** 1ª palavra (`Eu`) começa em 0.000s (sem lead-in)

### ex#63
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#66
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#67
- **C11** gap suspeito 1.540s entre `o` e `universo` (sem pontuação)
- **C9** 1ª palavra (`As`) começa em 0.000s (sem lead-in)

### ex#68
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#70
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#72
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#75
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#77
- **C11** gap suspeito 1.668s entre `decidiu` e `preparar` (sem pontuação)
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#78
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#79
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#80
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#81
- **C9** 1ª palavra (`Era`) começa em 0.000s (sem lead-in)

### ex#82
- **C9** 1ª palavra (`Ela`) começa em 0.000s (sem lead-in)

### ex#83
- **C9** 1ª palavra (`A`) começa em 0.000s (sem lead-in)

### ex#90
- **C9** 1ª palavra (`O`) começa em 0.000s (sem lead-in)

### ex#91
- **C9** 1ª palavra (`Eu`) começa em 0.000s (sem lead-in)

