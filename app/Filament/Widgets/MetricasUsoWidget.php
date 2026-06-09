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
            $totalAlunos    = DB::table('users')->where('role', 'aluno')->count();
            $totalDitados   = DB::table('dictation_metrics')->count();
            $totalFala      = DB::table('speech_metrics')->count();
            $totalAvaliados = $totalDitados + $totalFala;

            $mediaExercicios = $totalAlunos > 0
                ? round($totalAvaliados / $totalAlunos, 1)
                : 0;

            // Retenção: alunos ativos em mais de 1 mês (ditados + fala)
            $mesPorAlunoDitados = DB::table('dictation_metrics')
                ->selectRaw("student_id, TO_CHAR(DATE_TRUNC('month', created_at), 'YYYY-MM') as mes");

            $mesPorAlunoFala = DB::table('speech_metrics')
                ->selectRaw("student_id, TO_CHAR(DATE_TRUNC('month', created_at), 'YYYY-MM') as mes");

            $mesPorAluno = DB::table(
                    $mesPorAlunoDitados->unionAll($mesPorAlunoFala),
                    'combined'
                )
                ->selectRaw('student_id, mes')
                ->groupBy('student_id', 'mes')
                ->get()
                ->groupBy('student_id');

            $multiMes = $mesPorAluno->filter(fn ($meses) => $meses->count() > 1)->count();
            $pctRetencao = $totalAlunos > 0
                ? round($multiMes / $totalAlunos * 100, 1)
                : 0;

            // Alunos com 5+ exercícios (ditados + fala)
            $ditadosPorAluno = DB::table('dictation_metrics')
                ->selectRaw('student_id, COUNT(*) as total')
                ->groupBy('student_id');

            $falaPorAluno = DB::table('speech_metrics')
                ->selectRaw('student_id, COUNT(*) as total')
                ->groupBy('student_id');

            $com5Mais = DB::table(
                    DB::table(
                        $ditadosPorAluno->unionAll($falaPorAluno),
                        'union_totals'
                    )->selectRaw('student_id, SUM(total) as grand_total')->groupBy('student_id'),
                    'per_student'
                )
                ->where('grand_total', '>=', 5)
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
            Stat::make('Exercícios / Aluno (média)', $mediaExercicios)
                ->description("{$totalDitados} ditados · {$totalFala} fala")
                ->icon('heroicon-o-microphone')
                ->color('success'),

            Stat::make('Retenção multi-mês', "{$pctRetencao}%")
                ->description('alunos ativos em mais de 1 mês')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Alunos c/ 5+ exercícios', "{$pct5Mais}%")
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
