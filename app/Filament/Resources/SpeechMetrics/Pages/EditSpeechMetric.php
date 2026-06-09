<?php

namespace App\Filament\Resources\SpeechMetrics\Pages;

use App\Filament\Resources\SpeechMetrics\SpeechMetricResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSpeechMetric extends EditRecord
{
    protected static string $resource = SpeechMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
