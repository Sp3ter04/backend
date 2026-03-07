<?php

namespace App\Filament\Resources\ProfissionalStudents\Pages;

use App\Filament\Resources\ProfissionalStudents\ProfissionalStudentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfissionalStudents extends ListRecords
{
    protected static string $resource = ProfissionalStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo'),
        ];
    }
}
