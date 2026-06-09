<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informação do Contacto')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nome')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        TextEntry::make('role')
                            ->label('Papel')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'profissional' => 'warning',
                                'aluno'        => 'success',
                                default        => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label('Data')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(4),

                Section::make('Mensagem')
                    ->schema([
                        TextEntry::make('message')
                            ->label('')
                            ->columnSpanFull()
                            ->prose(),
                    ]),
            ]);
    }
}
