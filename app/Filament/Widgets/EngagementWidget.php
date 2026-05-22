<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class EngagementWidget extends BaseWidget
{
    protected ?string $heading = 'Evolução dos Alunos — Estrelas e Gamificação';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        try {
            $totalEstrelas = (int) DB::table('user_progress')->sum('stars_total');

            $niveis = DB::table('user_progress')
                ->selectRaw('level, COUNT(*) as total')
                ->groupBy('level')
                ->orderBy('level')
                ->get();

            $totalComNivel = $niveis->sum('total');

            // Top 3 níveis
            $topNiveis = $niveis->sortByDesc('total')->take(3);

            $nivelDesc = $topNiveis->map(fn ($n) => "Nível {$n->level}: {$n->total}")->implode(' · ');
        } catch (\Exception) {
            $totalEstrelas = 0;
            $totalComNivel = 0;
            $nivelDesc     = '—';
        }

        return [
            Stat::make('Total de Estrelas Ganhas', number_format($totalEstrelas))
                ->icon('heroicon-o-star')
                ->color('warning'),

            Stat::make('Alunos com Nível', number_format($totalComNivel))
                ->description('com dados de gamificação')
                ->icon('heroicon-o-trophy')
                ->color('success'),

            Stat::make('Top 3 Níveis', $nivelDesc ?: '—')
                ->description('alunos por nível de gamificação')
                ->icon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}
