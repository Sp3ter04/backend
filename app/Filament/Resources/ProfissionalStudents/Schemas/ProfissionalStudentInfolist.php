<?php

namespace App\Filament\Resources\ProfissionalStudents\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProfissionalStudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Relação')
                    ->schema([
                        TextEntry::make('profissional.name')
                            ->label('Profissional')
                            ->placeholder('Não definido'),
                        
                        TextEntry::make('profissional.email')
                            ->label('Email do Profissional')
                            ->placeholder('Não definido'),
                        
                        TextEntry::make('student.name')
                            ->label('Aluno')
                            ->placeholder('Não definido'),
                        
                        TextEntry::make('student.email')
                            ->label('Email do Aluno')
                            ->placeholder('Não definido'),
                        
                        TextEntry::make('student.school.name')
                            ->label('Escola do Aluno')
                            ->placeholder('Sem escola'),
                        
                        TextEntry::make('student.school_year')
                            ->label('Ano Escolar')
                            ->placeholder('Não definido'),
                    ])
                    ->columns(2),
                
                Section::make('Metadados')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i:s'),
                        
                        TextEntry::make('updated_at')
                            ->label('Atualizado em')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
