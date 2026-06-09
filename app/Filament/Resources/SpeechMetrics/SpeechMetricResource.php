<?php

namespace App\Filament\Resources\SpeechMetrics;

use App\Filament\Resources\SpeechMetrics\Pages\CreateSpeechMetric;
use App\Filament\Resources\SpeechMetrics\Pages\EditSpeechMetric;
use App\Filament\Resources\SpeechMetrics\Pages\ListSpeechMetrics;
use App\Filament\Resources\SpeechMetrics\Pages\ViewSpeechMetric;
use App\Filament\Resources\SpeechMetrics\Schemas\SpeechMetricForm;
use App\Filament\Resources\SpeechMetrics\Schemas\SpeechMetricInfolist;
use App\Filament\Resources\SpeechMetrics\Tables\SpeechMetricsTable;
use App\Models\SpeechMetric;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SpeechMetricResource extends Resource
{
    protected static ?string $model = SpeechMetric::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-microphone';

    protected static ?string $recordTitleAttribute = 'display_text';

    protected static ?string $navigationLabel = 'Métricas de Fala';

    protected static ?string $modelLabel = 'Métrica de Fala';

    protected static ?string $pluralModelLabel = 'Métricas de Fala';

    protected static UnitEnum|string|null $navigationGroup = 'Gestão de Conteúdos';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return SpeechMetricForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SpeechMetricInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpeechMetricsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpeechMetrics::route('/'),
            'create' => CreateSpeechMetric::route('/create'),
            'view' => ViewSpeechMetric::route('/{record}'),
            'edit' => EditSpeechMetric::route('/{record}/edit'),
        ];
    }
}
