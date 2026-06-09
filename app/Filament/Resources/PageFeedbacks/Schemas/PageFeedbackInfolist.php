<?php

namespace App\Filament\Resources\PageFeedbacks\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PageFeedbackInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informação')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Utilizador')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('user_type')
                            ->label('Tipo')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'profissional' => 'warning',
                                'aluno'        => 'success',
                                default        => 'gray',
                            }),
                        TextEntry::make('page_label')
                            ->label('Página')
                            ->icon('heroicon-o-document-text'),
                        TextEntry::make('page_path')
                            ->label('Caminho')
                            ->icon('heroicon-o-link')
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->label('Data')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-calendar'),
                    ])
                    ->columns(3),

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
