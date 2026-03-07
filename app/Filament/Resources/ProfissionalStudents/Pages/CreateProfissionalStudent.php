<?php

namespace App\Filament\Resources\ProfissionalStudents\Pages;

use App\Filament\Resources\ProfissionalStudents\ProfissionalStudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProfissionalStudent extends CreateRecord
{
    protected static string $resource = ProfissionalStudentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Relação profissional-aluno criada com sucesso!';
    }
}
