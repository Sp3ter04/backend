<?php

namespace App\Filament\Resources\Exercises\Schemas;

use App\Enums\DictationDifficulty;
use App\Enums\UserRole;
use App\Models\Exercise;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExerciseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Número do Exercício')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(fn() => (Exercise::max('number') ?? 0) + 1)
                    ->helperText('Este número será atribuído automaticamente ao criar o exercício')
                    ->prefixIcon('heroicon-o-hashtag')
                    ->columnSpanFull(),
                Textarea::make('sentence')
                    ->label('Exercício')
                    ->required()
                    ->rows(3)
                    ->helperText('Escreva o exercício. As palavras e sílabas serão geradas automaticamente.')
                    ->columnSpanFull(),
                Select::make('difficulty')
                    ->label('Dificuldade')
                    ->options(collect(DictationDifficulty::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])->toArray())
                    ->default(DictationDifficulty::EASY->value)
                    ->required(),
                Select::make('created_by')
                    ->label('Criado por')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->default(fn() => auth()->user()?->email)
                    ->options(function () {
                        return User::whereIn('role', [UserRole::PROFISSIONAL->value, UserRole::ADMIN->value])
                            ->orderBy('email')
                            ->pluck('email', 'email')
                            ->toArray();
                    })
                    ->helperText('Selecione o email do profissional ou administrador que criou este exercício'),
            ]);
    }
}
