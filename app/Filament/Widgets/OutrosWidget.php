<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class OutrosWidget extends BaseWidget
{
    protected ?string $heading = 'Outros — Pedidos e Feedback';

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        try {
            // Pedidos por status
            $pedidos = DB::table('pedidos_historico')
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->orderByDesc('total')
                ->get();

            $pedidosDesc = $pedidos->map(fn ($p) => ucfirst($p->status) . ': ' . $p->total)
                ->implode(' · ');

            $totalPedidos = $pedidos->sum('total');

            // Feedback — top página
            $topFeedback = DB::table('page_feedback')
                ->selectRaw('page_label, COUNT(*) as total')
                ->groupBy('page_label')
                ->orderByDesc('total')
                ->limit(1)
                ->first();

            $totalFeedback = DB::table('page_feedback')->count();
        } catch (\Exception) {
            $totalPedidos  = 0;
            $pedidosDesc   = '—';
            $topFeedback   = null;
            $totalFeedback = 0;
        }

        $topPaginaLabel = $topFeedback ? "{$topFeedback->page_label} ({$topFeedback->total})" : '—';

        return [
            Stat::make('Pedidos de Ligação', number_format($totalPedidos))
                ->description($pedidosDesc ?: '—')
                ->icon('heroicon-o-user-plus')
                ->color('info'),

            Stat::make('Total de Feedbacks', number_format($totalFeedback))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success'),

            Stat::make('Página com Mais Feedback', $topPaginaLabel)
                ->icon('heroicon-o-flag')
                ->color('warning'),
        ];
    }
}
