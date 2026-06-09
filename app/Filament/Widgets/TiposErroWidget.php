<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TiposErroWidget extends ChartWidget
{
    protected ?string $heading = 'Tipos de Erro (agregado)';

    protected ?string $description = 'Padrão de erros — indicador de perfil dislexia';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        try {
            $ditados = DB::table('dictation_metrics')
                ->selectRaw('
                    COALESCE(SUM(letter_substitution_count), 0)  as substituicao,
                    COALESCE(SUM(letter_omission_count), 0)      as omissao,
                    COALESCE(SUM(letter_insertion_count), 0)     as insercao,
                    COALESCE(SUM(transposition_count), 0)        as transposicao,
                    COALESCE(SUM(split_join_count), 0)           as split_join,
                    COALESCE(SUM(capitalization_error_count), 0) as capitalizacao,
                    COALESCE(SUM(punctuation_error_count), 0)    as pontuacao,
                    COALESCE(SUM(mispronunciation_count), 0)     as pronuncia
                ')
                ->first();

            $fala = DB::table('speech_metrics')
                ->selectRaw('
                    COALESCE(SUM(substitution_count), 0)     as substituicao,
                    COALESCE(SUM(omission_count), 0)         as omissao,
                    COALESCE(SUM(insertion_count), 0)        as insercao,
                    0                                        as transposicao,
                    0                                        as split_join,
                    0                                        as capitalizacao,
                    0                                        as pontuacao,
                    COALESCE(SUM(mispronunciation_count), 0) as pronuncia
                ')
                ->first();

            $map = [
                'Substituição'  => (int) $ditados->substituicao  + (int) $fala->substituicao,
                'Omissão'       => (int) $ditados->omissao        + (int) $fala->omissao,
                'Inserção'      => (int) $ditados->insercao       + (int) $fala->insercao,
                'Pronúncia'     => (int) $ditados->pronuncia      + (int) $fala->pronuncia,
                'Transposição'  => (int) $ditados->transposicao,
                'União/Divisão' => (int) $ditados->split_join,
                'Capitalização' => (int) $ditados->capitalizacao,
                'Pontuação'     => (int) $ditados->pontuacao,
            ];

            // Order by descending total
            arsort($map);
        } catch (\Exception) {
            $map = [];
        }

        return [
            'labels'   => array_keys($map),
            'datasets' => [
                [
                    'label'           => 'Total de erros',
                    'data'            => array_values($map),
                    'backgroundColor' => '#1D9E75',
                    'borderRadius'    => 4,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
