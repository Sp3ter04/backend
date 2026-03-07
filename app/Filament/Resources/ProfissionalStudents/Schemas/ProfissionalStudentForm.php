<?php

namespace App\Filament\Resources\ProfissionalStudents\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ProfissionalStudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('profissional_id')
                    ->label('Profissional')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->options(function () {
                        return User::where('role', UserRole::PROFISSIONAL->value)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn($user) => [$user->id => "{$user->name} ({$user->email})"])
                            ->toArray();
                    })
                    ->helperText('Selecione o profissional responsável')
                    ->preload()
                    ->columnSpanFull(),
                
                Select::make('student_id')
                    ->label('Aluno')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->options(function () {
                        return User::where('role', UserRole::ALUNO->value)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn($user) => [
                                $user->id => "{$user->name} ({$user->email})" . 
                                    ($user->school ? " - {$user->school->name}" : "")
                            ])
                            ->toArray();
                    })
                    ->helperText('Selecione o aluno que será atendido pelo profissional')
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
}
