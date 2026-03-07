<?php

namespace App\Filament\Resources\ProfissionalStudents\Tables;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProfissionalStudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profissional.name')
                    ->label('Profissional')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->profissional?->email)
                    ->placeholder('Sem profissional'),
                
                TextColumn::make('profissional.email')
                    ->label('Email do Profissional')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('student.name')
                    ->label('Aluno')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->student?->email)
                    ->placeholder('Sem aluno'),
                
                TextColumn::make('student.email')
                    ->label('Email do Aluno')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('student.school.name')
                    ->label('Escola')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->placeholder('Sem escola'),
                
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('profissional_id')
                    ->label('Profissional')
                    ->relationship('profissional', 'name')
                    ->searchable()
                    ->preload()
                    ->optionsLimit(50),
                
                SelectFilter::make('student_id')
                    ->label('Aluno')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->optionsLimit(50),
                
                SelectFilter::make('school')
                    ->label('Escola')
                    ->options(function () {
                        return \App\Models\School::orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereHas('student', function ($q) use ($data) {
                                $q->where('school_id', $data['value']);
                            });
                        }
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('Nenhuma relação profissional-aluno encontrada')
            ->emptyStateDescription('Crie uma nova relação clicando no botão "Novo".')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
