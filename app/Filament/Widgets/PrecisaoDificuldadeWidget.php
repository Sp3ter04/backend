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
            $ditados = DB::table('dictation_metrics')
                ->selectRaw('difficulty, accuracy_percent as score')
                ->whereNotNull('accuracy_percent')
                ->whereNotNull('difficulty');

            $fala = DB::table('speech_metrics')
                ->selectRaw('difficulty, accuracy_score as score')
                ->whereNotNull('accuracy_score')
                ->whereNotNull('difficulty');

            $rows = DB::table(
                    DB::table($ditados->unionAll($fala), 'union_diff')
                        ->selectRaw("difficulty, ROUND(AVG(score)::numeric, 1) as media")
                        ->groupBy('difficulty'),
                    'combined'
                )
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
