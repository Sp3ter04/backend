<?php

namespace App\Filament\Resources\SpeechMetrics\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SpeechMetricForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->relationship('student', 'name')
                    ->label('Aluno')
                    ->required(),
                Select::make('exercise_id')
                    ->relationship('exercise', 'sentence')
                    ->label('Exercício')
                    ->required(),
                TextInput::make('difficulty')
                    ->label('Dificuldade')
                    ->required(),
                TextInput::make('pron_score')
                    ->label('Pontuação de Pronúncia')
                    ->numeric()
                    ->default(0),
                TextInput::make('accuracy_score')
                    ->label('Precisão')
                    ->numeric()
                    ->default(0),
                TextInput::make('fluency_score')
                    ->label('Fluência')
                    ->numeric()
                    ->default(0),
                TextInput::make('completeness_score')
                    ->label('Completude')
                    ->numeric()
                    ->default(0),
                TextInput::make('words_per_minute')
                    ->label('Palavras por Minuto')
                    ->numeric()
                    ->default(0),
                TextInput::make('mispronunciation_count')
                    ->label('Erros de Pronúncia')
                    ->numeric()
                    ->default(0),
                TextInput::make('omission_count')
                    ->label('Omissões')
                    ->numeric()
                    ->default(0),
                TextInput::make('insertion_count')
                    ->label('Inserções')
                    ->numeric()
                    ->default(0),
                TextInput::make('substitution_count')
                    ->label('Substituições')
                    ->numeric()
                    ->default(0),
                Textarea::make('display_text')
                    ->label('Texto Apresentado')
                    ->columnSpanFull(),
            ]);
    }
}
