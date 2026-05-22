<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MetricasUsoWidget extends BaseWidget
{
    protected ?string $heading = 'Métricas de Uso';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        try {
            $totalAlunos  = DB::table('users')->where('role', 'aluno')->count();
            $totalDitados = DB::table('dictation_metrics')->count();

            $mediaDitados = $totalAlunos > 0
                ? round($totalDitados / $totalAlunos, 1)
                : 0;

            // Retenção: alunos ativos em mais de 1 mês
            $mesPorAluno = DB::table('dictation_metrics')
                ->selectRaw("student_id, TO_CHAR(DATE_TRUNC('month', created_at), 'YYYY-MM') as mes")
                ->groupBy('student_id', 'mes')
                ->get()
                ->groupBy('student_id');

            $multiMes = $mesPorAluno->filter(fn ($meses) => $meses->count() > 1)->count();
            $pctRetencao = $totalAlunos > 0
                ? round($multiMes / $totalAlunos * 100, 1)
                : 0;

            // Alunos com 5+ ditados
            $com5Mais = DB::table('dictation_metrics')
                ->selectRaw('student_id, COUNT(*) as total')
                ->groupBy('student_id')
                ->havingRaw('COUNT(*) >= 5')
                ->get()
                ->count();
            $pct5Mais = $totalAlunos > 0
                ? round($com5Mais / $totalAlunos * 100, 1)
                : 0;

            // Tarefas
            $totalTarefas      = DB::table('tasks')->count();
            $tarefasRealizadas = DB::table('tasks')->where('realizado', true)->count();
            $taxaConc          = $totalTarefas > 0
                ? round($tarefasRealizadas / $totalTarefas * 100, 1)
                : 0;
        } catch (\Exception) {
            return [];
        }

        return [
            Stat::make('Ditados / Aluno (média)', $mediaDitados)
                ->icon('heroicon-o-microphone')
                ->color('success'),

            Stat::make('Retenção multi-mês', "{$pctRetencao}%")
                ->description('alunos ativos em mais de 1 mês')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Alunos c/ 5+ ditados', "{$pct5Mais}%")
                ->description('dos alunos registados')
                ->icon('heroicon-o-check-badge')
                ->color('warning'),

            Stat::make('Conclusão de Tarefas', "{$taxaConc}%")
                ->description("{$tarefasRealizadas} de {$totalTarefas} tarefas realizadas")
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success'),
        ];
    }
}
