<?php

namespace App\Filament\Resources\Contacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('role')
                    ->label('Papel')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'profissional' => 'warning',
                        'aluno'        => 'success',
                        default        => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('message')
                    ->label('Mensagem')
                    ->limit(60)
                    ->tooltip(fn ($state) => $state)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Papel')
                    ->options([
                        'profissional' => 'Profissional',
                        'aluno'        => 'Aluno',
                    ])
                    ->placeholder('Todos'),
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
