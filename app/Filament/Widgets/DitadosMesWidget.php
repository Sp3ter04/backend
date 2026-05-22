<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DitadosMesWidget extends ChartWidget
{
    protected ?string $heading = 'Ditados por Mês';

    protected ?string $description = 'Volume de ditados nos últimos 6 meses';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $ptMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                     'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        try {
            $rows = DB::table('dictation_metrics')
                ->selectRaw("TO_CHAR(DATE_TRUNC('month', created_at), 'YYYY-MM') as mes, COUNT(*) as total")
                ->groupBy('mes')
                ->orderBy('mes', 'desc')
                ->limit(6)
                ->get()
                ->reverse()
                ->values();

            $labels = $rows->map(function ($row) use ($ptMonths) {
                [$y, $m] = explode('-', $row->mes);
                return $ptMonths[(int) $m - 1] . '/' . substr($y, 2);
            })->toArray();

            $data = $rows->pluck('total')->toArray();
        } catch (\Exception) {
            $labels = [];
            $data   = [];
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'           => 'Ditados',
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
}
