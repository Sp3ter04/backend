<?php

namespace App\Filament\Resources\DictationMetrics;

use App\Filament\Resources\DictationMetrics\Pages\CreateDictationMetric;
use App\Filament\Resources\DictationMetrics\Pages\EditDictationMetric;
use App\Filament\Resources\DictationMetrics\Pages\ListDictationMetrics;
use App\Filament\Resources\DictationMetrics\Pages\ViewDictationMetric;
use App\Filament\Resources\DictationMetrics\Schemas\DictationMetricForm;
use App\Filament\Resources\DictationMetrics\Schemas\DictationMetricInfolist;
use App\Filament\Resources\DictationMetrics\Tables\DictationMetricsTable;
use App\Models\DictationMetric;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DictationMetricResource extends Resource
{
    protected static ?string $model = DictationMetric::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $recordTitleAttribute = 'Metricas';
    
    protected static ?string $navigationLabel = 'Métricas de Ditados';
    
    protected static ?string $modelLabel = 'Métrica de Ditado';
    
    protected static ?string $pluralModelLabel = 'Métricas de Ditados';
    
    protected static UnitEnum|string|null $navigationGroup = 'Gestão de Conteúdos';
    
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return DictationMetricForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DictationMetricInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DictationMetricsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDictationMetrics::route('/'),
            'create' => CreateDictationMetric::route('/create'),
            'view' => ViewDictationMetric::route('/{record}'),
            'edit' => EditDictationMetric::route('/{record}/edit'),
        ];
    }
}
