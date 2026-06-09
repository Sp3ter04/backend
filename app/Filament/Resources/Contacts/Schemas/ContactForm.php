<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                Select::make('role')
                    ->label('Papel')
                    ->options([
                        'profissional' => 'Profissional',
                        'aluno'        => 'Aluno',
                    ]),
                Textarea::make('message')
                    ->label('Mensagem')
                    ->columnSpanFull(),
            ]);
    }
}
