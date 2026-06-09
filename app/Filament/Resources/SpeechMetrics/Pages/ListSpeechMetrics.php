<?php

namespace App\Filament\Resources\SpeechMetrics\Pages;

use App\Filament\Resources\SpeechMetrics\SpeechMetricResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpeechMetrics extends ListRecords
{
    protected static string $resource = SpeechMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
