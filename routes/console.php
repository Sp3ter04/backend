<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\SyncSupabaseAll;
use App\Console\Commands\SyncSupabaseExercises;
use App\Console\Commands\RegenerateWithCommas;
use App\Services\SimplePausedAudioService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Registrar comandos de sincronização do Supabase
Artisan::command('supabase:sync-all {--table=}', function () {
    $command = new SyncSupabaseAll();
    $command->handle();
})->purpose('Sincronizar todas as tabelas do Supabase para SQLite local');

Artisan::command('supabase:sync-exercises', function () {
    $command = new SyncSupabaseExercises();
    $command->handle();
})->purpose('Sincronizar exercícios do Supabase para SQLite local');

// Registrar comando de regeneração de áudio com pausas
Artisan::command('audio:regenerate-with-commas {--speed=0.9} {--lang=pt-PT} {--limit=} {--force}', function () {
    $audioService = app(SimplePausedAudioService::class);
    $command = new RegenerateWithCommas();
    return $command->handle($audioService);
})->purpose('Regenerate exercise audio using comma insertion for natural pauses');
