<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PrecisaoDificuldadeWidget extends ChartWidget
{
    protected ?string $heading = 'Precisão por Dificuldade';

    protected ?string $description = 'Precisão média (%) por nível';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        try {
            $rows = DB::table('dictation_metrics')
                ->selectRaw("difficulty, ROUND(AVG(accuracy_percent)::numeric, 1) as media")
                ->whereNotNull('accuracy_percent')
                ->whereNotNull('difficulty')
                ->groupBy('difficulty')
                ->orderBy('difficulty')
                ->get();

            $labels = $rows->pluck('difficulty')->toArray();
            $data   = $rows->pluck('media')->map(fn ($v) => (float) $v)->toArray();
        } catch (\Exception) {
            $labels = [];
            $data   = [];
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'           => 'Precisão média (%)',
                    'data'            => $data,
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

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['min' => 0, 'max' => 100],
            ],
        ];
    }
}
