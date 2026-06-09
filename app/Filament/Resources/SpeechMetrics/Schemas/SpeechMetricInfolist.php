<?php

namespace App\Filament\Resources\SpeechMetrics\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SpeechMetricInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informação Geral')
                    ->schema([
                        TextEntry::make('student.name')
                            ->label('Aluno')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('difficulty')
                            ->label('Dificuldade')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'easy' => 'success',
                                'medium' => 'warning',
                                'hard' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('words_per_minute')
                            ->label('Palavras/min')
                            ->icon('heroicon-o-clock'),
                        TextEntry::make('created_at')
                            ->label('Data')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-calendar'),
                        TextEntry::make('exercise.sentence')
                            ->label('Exercício')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull(),
                        TextEntry::make('display_text')
                            ->label('Resposta do Aluno')
                            ->copyable()
                            ->icon('heroicon-o-microphone')
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Section::make('Pontuações')
                    ->schema([
                        TextEntry::make('pron_score')
                            ->label('Pronúncia')
                            ->suffix('%')
                            ->badge()
                            ->color(fn (float $state): string => match (true) {
                                $state >= 90 => 'success',
                                $state >= 70 => 'warning',
                                $state >= 50 => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('accuracy_score')
                            ->label('Precisão')
                            ->suffix('%')
                            ->badge()
                            ->color(fn (float $state): string => match (true) {
                                $state >= 90 => 'success',
                                $state >= 70 => 'warning',
                                $state >= 50 => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('fluency_score')
                            ->label('Fluência')
                            ->suffix('%')
                            ->badge()
                            ->color(fn (float $state): string => match (true) {
                                $state >= 90 => 'success',
                                $state >= 70 => 'warning',
                                $state >= 50 => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('completeness_score')
                            ->label('Completude')
                            ->suffix('%')
                            ->badge()
                            ->color(fn (float $state): string => match (true) {
                                $state >= 90 => 'success',
                                $state >= 70 => 'warning',
                                $state >= 50 => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(4),

                Section::make('Erros')
                    ->schema([
                        TextEntry::make('mispronunciation_count')
                            ->label('Erros de Pronúncia')
                            ->icon('heroicon-o-exclamation-triangle')
                            ->color('danger'),
                        TextEntry::make('omission_count')
                            ->label('Omissões')
                            ->icon('heroicon-o-minus-circle'),
                        TextEntry::make('insertion_count')
                            ->label('Inserções')
                            ->icon('heroicon-o-plus-circle'),
                        TextEntry::make('substitution_count')
                            ->label('Substituições')
                            ->icon('heroicon-o-arrow-path'),
                        TextEntry::make('error_words')
                            ->label('Palavras com Erro')
                            ->state(function ($record) {
                                if (!is_array($record->error_words) || empty($record->error_words)) {
                                    return 'Nenhuma';
                                }
                                return array_values(array_unique(array_map('strval', $record->error_words)));
                            })
                            ->badge()
                            ->listWithLineBreaks()
                            ->icon('heroicon-o-x-circle')
                            ->columnSpanFull(),
                    ])
                    ->columns(4),
            ]);
    }
}
