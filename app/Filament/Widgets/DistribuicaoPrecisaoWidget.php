<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DistribuicaoPrecisaoWidget extends ChartWidget
{
    protected ?string $heading = 'Distribuição de Precisão';

    protected ?string $description = 'Ditados por faixa de desempenho';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        try {
            $row = DB::table('dictation_metrics')
                ->selectRaw('
                    COUNT(CASE WHEN accuracy_percent >= 90                         THEN 1 END) as excelente,
                    COUNT(CASE WHEN accuracy_percent >= 75 AND accuracy_percent < 90 THEN 1 END) as bom,
                    COUNT(CASE WHEN accuracy_percent >= 50 AND accuracy_percent < 75 THEN 1 END) as medio,
                    COUNT(CASE WHEN accuracy_percent <  50                         THEN 1 END) as fraco
                ')
                ->whereNotNull('accuracy_percent')
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
