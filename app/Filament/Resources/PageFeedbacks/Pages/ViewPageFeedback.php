<?php

namespace App\Filament\Resources\PageFeedbacks\Pages;

use App\Filament\Resources\PageFeedbacks\PageFeedbackResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPageFeedback extends ViewRecord
{
    protected static string $resource = PageFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
