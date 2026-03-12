<?php

namespace App\Filament\Resources\DictationMetrics\Pages;

use App\Filament\Resources\DictationMetrics\DictationMetricResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDictationMetrics extends ListRecords
{
    protected static string $resource = DictationMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
