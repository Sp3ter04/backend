<?php

namespace App\Filament\Resources\DictationMetrics\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DictationMetricsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exercise.sentence')
                    ->label('Exercise')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('difficulty')
                    ->label('Difficulty')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'easy' => 'success',
                        'medium' => 'warning',
                        'hard' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('accuracy_percent')
                    ->label('Accuracy')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 70 => 'warning',
                        $state >= 50 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('correct_count')
                    ->label('Correct')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('error_count')
                    ->label('Errors')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('missing_count')
                    ->label('Missing')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('letter_omission_count')
                    ->label('Omissions')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('letter_substitution_count')
                    ->label('Substitutions')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('error_words')
                    ->label('Error Words')
                    ->formatStateUsing(function ($record) {
                        $words = $record->errorWordModels();
                        if ($words->isEmpty()) {
                            return '-';
                        }
                        $wordTexts = $words->pluck('word')->unique()->values()->toArray();
                        return implode(', ', array_slice($wordTexts, 0, 5)) . (count($wordTexts) > 5 ? '...' : '');
                    })
                    ->tooltip(function ($record) {
                        $words = $record->errorWordModels();
                        if ($words->isEmpty()) {
                            return null;
                        }
                        return implode(', ', $words->pluck('word')->unique()->values()->toArray());
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('resolution')
                    ->label('Student Answer')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Date')
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
