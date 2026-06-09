<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DistribuicaoPrecisaoWidget extends ChartWidget
{
    protected ?string $heading = 'Distribuição de Precisão';

    protected ?string $description = 'Exercícios por faixa de desempenho';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        try {
            $ditados = DB::table('dictation_metrics')
                ->selectRaw('accuracy_percent as score')
                ->whereNotNull('accuracy_percent');

            $fala = DB::table('speech_metrics')
                ->selectRaw('accuracy_score as score')
                ->whereNotNull('accuracy_score');

            $row = DB::table($ditados->unionAll($fala), 'all_scores')
                ->selectRaw('
                    COUNT(CASE WHEN score >= 90                 THEN 1 END) as excelente,
                    COUNT(CASE WHEN score >= 75 AND score < 90 THEN 1 END) as bom,
                    COUNT(CASE WHEN score >= 50 AND score < 75 THEN 1 END) as medio,
                    COUNT(CASE WHEN score <  50                THEN 1 END) as fraco
                ')
                ->first();

            $data = [
                (int) ($row->excelente ?? 0),
                (int) ($row->bom       ?? 0),
                (int) ($row->medio     ?? 0),
                (int) ($row->fraco     ?? 0),
            ];
        } catch (\Exception) {
            $data = [0, 0, 0, 0];
        }

        return [
            'labels'   => ['Excelente (≥90%)', 'Bom (75–89%)', 'Médio (50–74%)', 'Fraco (<50%)'],
            'datasets' => [
                [
                    'data'            => $data,
                    'backgroundColor' => ['#1D9E75', '#4BBFA0', '#EAB308', '#F87171'],
                    'hoverOffset'     => 4,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
