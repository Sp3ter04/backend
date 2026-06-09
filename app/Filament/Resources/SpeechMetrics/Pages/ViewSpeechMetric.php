<?php

namespace App\Filament\Resources\SpeechMetrics\Pages;

use App\Filament\Resources\SpeechMetrics\SpeechMetricResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSpeechMetric extends ViewRecord
{
    protected static string $resource = SpeechMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
