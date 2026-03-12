<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn (UserRole $state): string => $state->label())
                    ->badge()
                    ->color(fn (UserRole $state): string => match ($state) {
                        UserRole::ADMIN => 'danger',
                        UserRole::PROFISSIONAL => 'success',
                        UserRole::PROFESSOR => 'info',
                        UserRole::ALUNO => 'warning',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('school.name')
                    ->label('School')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('school_year')
                    ->label('School year')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Função')
                    ->options(UserRole::options())
                    ->placeholder('Todas as funções'),
                
                SelectFilter::make('school_id')
                    ->label('Escola')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todas as escolas'),
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
