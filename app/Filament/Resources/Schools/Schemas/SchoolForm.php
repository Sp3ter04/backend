<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('address')
                    ->label('Morada')
                    ->maxLength(255),
                TextInput::make('director_name')
                    ->label('Nome do Director')
                    ->maxLength(255),
            ]);
    }
}
