<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DitadosMesWidget extends ChartWidget
{
    protected ?string $heading = 'Exercícios por Mês';

    protected ?string $description = 'Volume de exercícios (ditados + fala) nos últimos 6 meses';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $ptMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                     'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        try {
            $ditados = DB::table('dictation_metrics')
                ->selectRaw("TO_CHAR(DATE_TRUNC('month', created_at), 'YYYY-MM') as mes, COUNT(*) as total")
                ->groupBy('mes');

            $fala = DB::table('speech_metrics')
                ->selectRaw("TO_CHAR(DATE_TRUNC('month', created_at), 'YYYY-MM') as mes, COUNT(*) as total")
                ->groupBy('mes');

            $rows = DB::table(
                    DB::table($ditados->unionAll($fala), 'union_all')
                        ->selectRaw('mes, SUM(total) as total')
                        ->groupBy('mes'),
                    'combined'
                )
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
                    'label'           => 'Exercícios',
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
