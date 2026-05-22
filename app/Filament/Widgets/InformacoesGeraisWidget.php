<?php

namespace App\Filament\Widgets;

use App\Models\Exercise;
use App\Models\ProfissionalStudent;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InformacoesGeraisWidget extends BaseWidget
{
    protected ?string $heading = 'Informações Gerais';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        try {
            $totalAlunos        = User::where('role', 'aluno')->count();
            $totalProfissionais = User::where('role', 'profissional')->count();
            $totalExercicios    = Exercise::count();
            $totalDitados       = DB::table('dictation_metrics')->count();
            $totalEscolas       = User::where('role', 'aluno')
                                      ->whereNotNull('school_id')
                                      ->distinct('school_id')
                                      ->count('school_id');
            $totalLigacoes      = ProfissionalStudent::count();
        } catch (\Exception) {
            return [];
        }

        return [
            Stat::make('Alunos', number_format($totalAlunos))
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Profissionais', number_format($totalProfissionais))
                ->icon('heroicon-o-briefcase')
                ->color('warning'),

            Stat::make('Exercícios Criados', number_format($totalExercicios))
                ->icon('heroicon-o-book-open')
                ->color('info'),

            Stat::make('Exercícios Avaliados', number_format($totalDitados))
                ->icon('heroicon-o-microphone')
                ->color('success'),

            Stat::make('Escolas com Alunos', number_format($totalEscolas))
                ->icon('heroicon-o-building-office-2')
                ->color('warning'),

            Stat::make('Ligações Ativas', number_format($totalLigacoes))
                ->icon('heroicon-o-link')
                ->color('info'),
        ];
    }
}
