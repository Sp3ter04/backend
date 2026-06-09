<?php

namespace App\Filament\Resources\PageFeedbacks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PageFeedbacksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utilizador')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'profissional' => 'warning',
                        'aluno'        => 'success',
                        default        => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('page_label')
                    ->label('Página')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('page_path')
                    ->label('Caminho')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('message')
                    ->label('Mensagem')
                    ->limit(80)
                    ->tooltip(fn ($state) => $state)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('user_type')
                    ->label('Tipo de utilizador')
                    ->options([
                        'profissional' => 'Profissional',
                        'aluno'        => 'Aluno',
                    ])
                    ->placeholder('Todos'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
