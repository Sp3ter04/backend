<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BucketsDitadosWidget extends ChartWidget
{
    protected ?string $heading = 'Distribuição de Ditados por Aluno';

    protected ?string $description = 'Quantos ditados cada aluno realizou';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        try {
            $counts = DB::table('dictation_metrics')
                ->selectRaw('student_id, COUNT(*) as total')
                ->groupBy('student_id')
                ->get()
                ->pluck('total')
                ->toArray();

            $buckets = [
                '1–5'   => count(array_filter($counts, fn ($c) => $c >= 1  && $c <= 5)),
                '6–20'  => count(array_filter($counts, fn ($c) => $c >= 6  && $c <= 20)),
                '21–50' => count(array_filter($counts, fn ($c) => $c >= 21 && $c <= 50)),
                '51+'   => count(array_filter($counts, fn ($c) => $c > 50)),
            ];
        } catch (\Exception) {
            $buckets = ['1–5' => 0, '6–20' => 0, '21–50' => 0, '51+' => 0];
        }

        return [
            'labels'   => array_keys($buckets),
            'datasets' => [
                [
                    'label'           => 'Alunos',
                    'data'            => array_values($buckets),
                    'backgroundColor' => '#4BBFA0',
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
