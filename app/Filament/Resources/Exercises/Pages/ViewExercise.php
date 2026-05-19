<?php

namespace App\Filament\Resources\Exercises\Pages;

use App\Filament\Resources\Exercises\ExerciseResource;
use App\Services\ExerciseProcessorService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewExercise extends ViewRecord
{
    protected static string $resource = ExerciseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_timestamps')
                ->label('Gerar Timestamps')
                ->icon(Heroicon::OutlinedClock)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Gerar Word Timestamps')
                ->modalDescription('Isto vai regenerar o áudio da frase e os word_timestamps / word_start_times. Continuar?')
                ->modalSubmitActionLabel('Gerar')
                ->action(function () {
                    /** @var \App\Models\Exercise $exercise */
                    $exercise = $this->record;
                    $service = app(ExerciseProcessorService::class);
                    $success = $service->generateTimestamps($exercise, force: true);

                    if ($success) {
                        Notification::make()
                            ->title('Timestamps gerados com sucesso')
                            ->success()
                            ->send();

                        // Recarregar os dados da página
                        $this->refreshFormData(['word_timestamps', 'word_start_times']);
                    } else {
                        $workerUrl = env('ALIGNMENT_WORKER_URL', '');
                        $body = empty($workerUrl)
                            ? 'ALIGNMENT_WORKER_URL não está configurado no .env.'
                            : 'Verifica os logs do servidor e se o worker WhisperX está a correr.';

                        Notification::make()
                            ->title('Falha ao gerar timestamps')
                            ->body($body)
                            ->danger()
                            ->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
