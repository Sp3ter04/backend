<?php

namespace App\Filament\Resources\DictationMetrics\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DictationMetricInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informação Geral')
                    ->schema([
                        TextEntry::make('student.name')
                            ->label('Student')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('difficulty')
                            ->label('Difficulty')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'easy' => 'success',
                                'medium' => 'warning',
                                'hard' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('accuracy_percent')
                            ->label('Accuracy')
                            ->suffix('%')
                            ->badge()
                            ->color(fn (float $state): string => match (true) {
                                $state >= 90 => 'success',
                                $state >= 70 => 'warning',
                                $state >= 50 => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label('Date')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-calendar'),
                        TextEntry::make('exercise.sentence')
                            ->label('Exercise')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull(),
                    ])
                    ->columns(4),
                
                Section::make('Resposta e Erros')
                    ->schema([
                        TextEntry::make('resolution')
                            ->label('Student Answer')
                            ->default('-')
                            ->copyable()
                            ->icon('heroicon-o-pencil'),
                        TextEntry::make('error_words_text')
                            ->label('Error Words')
                            ->state(function ($record) {
                                $words = $record->errorWordModels();
                                if ($words->isEmpty()) {
                                    return 'Nenhuma';
                                }
                                return $words->pluck('word')->unique()->values()->toArray();
                            })
                            ->badge()
                            ->listWithLineBreaks()
                            ->icon('heroicon-o-exclamation-triangle'),
                    ])
                    ->columns(2),
                
                Section::make('Estatísticas')
                    ->schema([
                        TextEntry::make('correct_count')
                            ->label('Correct')
                            ->icon('heroicon-o-check-circle')
                            ->color('success'),
                        TextEntry::make('error_count')
                            ->label('Errors')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger'),
                        TextEntry::make('missing_count')
                            ->label('Missing')
                            ->icon('heroicon-o-minus-circle'),
                        TextEntry::make('extra_count')
                            ->label('Extra')
                            ->icon('heroicon-o-plus-circle'),
                        TextEntry::make('letter_omission_count')
                            ->label('Omissions')
                            ->icon('heroicon-o-arrow-left'),
                        TextEntry::make('letter_insertion_count')
                            ->label('Insertions')
                            ->icon('heroicon-o-arrow-right'),
                        TextEntry::make('letter_substitution_count')
                            ->label('Substitutions')
                            ->icon('heroicon-o-arrow-path'),
                        TextEntry::make('transposition_count')
                            ->label('Transpositions')
                            ->icon('heroicon-o-arrows-right-left'),
                        TextEntry::make('split_join_count')
                            ->label('Split/Join')
                            ->icon('heroicon-o-scissors'),
                        TextEntry::make('punctuation_error_count')
                            ->label('Punctuation')
                            ->icon('heroicon-o-exclamation-circle'),
                        TextEntry::make('capitalization_error_count')
                            ->label('Capitalization')
                            ->icon('heroicon-o-arrow-up-circle'),
                    ])
                    ->columns(4)
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }
}
