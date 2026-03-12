<?php

namespace App\Filament\Resources\DictationMetrics\Pages;

use App\Filament\Resources\DictationMetrics\DictationMetricResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDictationMetric extends ViewRecord
{
    protected static string $resource = DictationMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
