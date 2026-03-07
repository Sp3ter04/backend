<?php

namespace App\Filament\Resources\ProfissionalStudents\Pages;

use App\Filament\Resources\ProfissionalStudents\ProfissionalStudentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProfissionalStudent extends EditRecord
{
    protected static string $resource = ProfissionalStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Relação profissional-aluno atualizada com sucesso!';
    }
}
