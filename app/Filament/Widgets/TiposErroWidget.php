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
            $row = DB::table('dictation_metrics')
                ->selectRaw('
                    COALESCE(SUM(letter_substitution_count), 0) as substituicao,
                    COALESCE(SUM(letter_omission_count), 0)     as omissao,
                    COALESCE(SUM(letter_insertion_count), 0)    as insercao,
                    COALESCE(SUM(transposition_count), 0)       as transposicao,
                    COALESCE(SUM(split_join_count), 0)          as split_join,
                    COALESCE(SUM(capitalization_error_count), 0) as capitalizacao,
                    COALESCE(SUM(punctuation_error_count), 0)   as pontuacao
                ')
                ->first();

            $map = [
                'Substituição'  => (int) $row->substituicao,
                'Omissão'       => (int) $row->omissao,
                'Inserção'      => (int) $row->insercao,
                'Transposição'  => (int) $row->transposicao,
                'União/Divisão' => (int) $row->split_join,
                'Capitalização' => (int) $row->capitalizacao,
                'Pontuação'     => (int) $row->pontuacao,
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
