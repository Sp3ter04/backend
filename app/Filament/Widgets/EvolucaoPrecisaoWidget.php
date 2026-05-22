<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EvolucaoPrecisaoWidget extends ChartWidget
{
    protected ?string $heading = 'Evolução da Precisão Média';

    protected ?string $description = 'Precisão média (%) dos ditados ao longo do tempo';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $ptMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                     'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        try {
            $rows = DB::table('dictation_metrics')
                ->selectRaw("
                    TO_CHAR(DATE_TRUNC('month', created_at), 'YYYY-MM') as mes,
                    ROUND(AVG(accuracy_percent)::numeric, 1) as precisao
                ")
                ->whereNotNull('accuracy_percent')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();

            $labels = $rows->map(function ($row) use ($ptMonths) {
                [$y, $m] = explode('-', $row->mes);
                return $ptMonths[(int) $m - 1] . '/' . substr($y, 2);
            })->toArray();

            $data = $rows->pluck('precisao')->map(fn ($v) => (float) $v)->toArray();
        } catch (\Exception) {
            $labels = [];
            $data   = [];
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'       => 'Precisão (%)',
                    'data'        => $data,
                    'borderColor' => '#1D9E75',
                    'borderWidth' => 2.5,
                    'fill'        => false,
                    'tension'     => 0.3,
                    'pointRadius' => 4,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
        ];
    }
}
