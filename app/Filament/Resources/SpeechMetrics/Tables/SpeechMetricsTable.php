<?php

namespace App\Filament\Resources\SpeechMetrics\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpeechMetricsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exercise.sentence')
                    ->label('Exercício')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('difficulty')
                    ->label('Dificuldade')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('pron_score')
                    ->label('Pronúncia')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        $state >= 50 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('accuracy_score')
                    ->label('Precisão')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        $state >= 50 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('fluency_score')
                    ->label('Fluência')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        $state >= 50 => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('completeness_score')
                    ->label('Completude')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        $state >= 50 => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('words_per_minute')
                    ->label('Palavras/min')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('mispronunciation_count')
                    ->label('Erros de Pronúncia')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('omission_count')
                    ->label('Omissões')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('insertion_count')
                    ->label('Inserções')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('substitution_count')
                    ->label('Substituições')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('display_text')
                    ->label('Resposta do Aluno')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('difficulty')
                    ->label('Dificuldade')
                    ->options([
                        'easy' => 'Fácil',
                        'medium' => 'Médio',
                        'hard' => 'Difícil',
                    ])
                    ->placeholder('Todas as dificuldades'),

                SelectFilter::make('student_id')
                    ->label('Aluno')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos os alunos'),

                SelectFilter::make('exercise_id')
                    ->label('Exercício')
                    ->relationship('exercise', 'sentence')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos os exercícios'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
