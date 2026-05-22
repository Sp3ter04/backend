<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AlunosPorAnoWidget extends ChartWidget
{
    protected ?string $heading = 'Alunos por Ano Escolar';

    protected ?string $description = 'Distribuição dos alunos registados';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        try {
            $rows = DB::table('users')
                ->selectRaw('school_year, COUNT(*) as total')
                ->where('role', 'aluno')
                ->whereNotNull('school_year')
                ->groupBy('school_year')
                ->get()
                ->keyBy(fn ($r) => (int) $r->school_year);

            $labels = [];
            $data   = [];
            for ($ano = 1; $ano <= 12; $ano++) {
                $labels[] = (string) $ano;
                $data[]   = isset($rows[$ano]) ? (int) $rows[$ano]->total : 0;
            }
        } catch (\Exception) {
            $labels = array_map('strval', range(1, 12));
            $data   = array_fill(0, 12, 0);
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'           => 'Alunos',
                    'data'            => $data,
                    'backgroundColor' => '#157A5A',
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
