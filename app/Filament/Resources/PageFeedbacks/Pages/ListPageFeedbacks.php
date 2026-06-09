<?php

namespace App\Filament\Resources\PageFeedbacks\Pages;

use App\Filament\Resources\PageFeedbacks\PageFeedbackResource;
use Filament\Resources\Pages\ListRecords;

class ListPageFeedbacks extends ListRecords
{
    protected static string $resource = PageFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
