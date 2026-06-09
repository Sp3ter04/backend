<?php

namespace App\Filament\Resources\PageFeedbacks;

use App\Filament\Resources\PageFeedbacks\Pages\ListPageFeedbacks;
use App\Filament\Resources\PageFeedbacks\Pages\ViewPageFeedback;
use App\Filament\Resources\PageFeedbacks\Schemas\PageFeedbackInfolist;
use App\Filament\Resources\PageFeedbacks\Tables\PageFeedbacksTable;
use App\Models\PageFeedback;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PageFeedbackResource extends Resource
{
    protected static ?string $model = PageFeedback::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $recordTitleAttribute = 'page_label';

    protected static ?string $navigationLabel = 'Feedback de Páginas';

    protected static ?string $modelLabel = 'Feedback';

    protected static ?string $pluralModelLabel = 'Feedbacks';

    protected static UnitEnum|string|null $navigationGroup = 'Gestão de Contactos';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return PageFeedbackInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PageFeedbacksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPageFeedbacks::route('/'),
            'view'  => ViewPageFeedback::route('/{record}'),
        ];
    }
}
