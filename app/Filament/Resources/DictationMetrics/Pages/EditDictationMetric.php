<?php

namespace App\Filament\Resources\DictationMetrics\Pages;

use App\Filament\Resources\DictationMetrics\DictationMetricResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDictationMetric extends EditRecord
{
    protected static string $resource = DictationMetricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
