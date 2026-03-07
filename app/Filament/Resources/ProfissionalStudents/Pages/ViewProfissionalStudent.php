<?php

namespace App\Filament\Resources\ProfissionalStudents\Pages;

use App\Filament\Resources\ProfissionalStudents\ProfissionalStudentResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProfissionalStudent extends ViewRecord
{
    protected static string $resource = ProfissionalStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
