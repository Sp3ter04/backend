<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;

class SupabaseSyncServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Sincronizar ao iniciar a aplicação (apenas no ambiente local)
        if (app()->environment('local')) {
            try {
                // Verificar se foi há mais de 5 minutos desde a última sincronização
                $lastSync = cache('supabase_last_sync');
                $now = now();
                
                if (!$lastSync || $now->diffInMinutes($lastSync) >= 5) {
                    try {
                        // Executar em background para não bloquear
                        Artisan::call('supabase:sync-all');
                        cache(['supabase_last_sync' => $now], now()->addMinutes(5));
                    } catch (\Exception $e) {
                        logger()->error('Erro ao sincronizar Supabase', [
                            'message' => $e->getMessage()
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Falha ao acessar cache (provavelmente sem conexão ao Supabase)
                // Continuar sem sincronização
                logger()->warning('Supabase sync skipped - connection unavailable', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
