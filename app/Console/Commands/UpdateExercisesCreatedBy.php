<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateExercisesCreatedBy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exercises:update-created-by {email=admin@gmail.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o campo created_by de todos os exercícios para um email específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('========================================');
        $this->info("Atualizando created_by para: {$email}");
        $this->info('========================================');
        $this->newLine();
        
        // Contar total
        $totalExercises = Exercise::count();
        $this->info("📊 Total de exercícios: {$totalExercises}");
        
        // Contar quantos serão atualizados
        $toUpdate = Exercise::where(function($query) use ($email) {
            $query->whereNull('created_by')
                  ->orWhere('created_by', '!=', $email);
        })->count();
        
        $this->info("🔄 Exercícios a serem atualizados: {$toUpdate}");
        $this->newLine();
        
        if ($toUpdate === 0) {
            $this->info("✅ Todos os exercícios já têm created_by = '{$email}'");
            return 0;
        }
        
        // Confirmação
        if (!$this->confirm("Deseja atualizar {$toUpdate} exercícios?", true)) {
            $this->error('❌ Operação cancelada.');
            return 1;
        }
        
        $this->newLine();
        $this->info('🔄 Atualizando...');
        
        // Executar update
        $bar = $this->output->createProgressBar($toUpdate);
        $bar->start();
        
        DB::table('exercises')->update([
            'created_by' => $email,
            'updated_at' => now(),
        ]);
        
        $bar->finish();
        $this->newLine(2);
        
        // Verificação final
        $adminExercises = Exercise::where('created_by', $email)->count();
        $nullExercises = Exercise::whereNull('created_by')->count();
        
        $this->info('========================================');
        $this->info('📊 RESULTADO FINAL');
        $this->info('========================================');
        $this->table(
            ['Descrição', 'Quantidade'],
            [
                ['Total de exercícios', $totalExercises],
                ["Com created_by = '{$email}'", $adminExercises],
                ['Com created_by = NULL', $nullExercises],
                ['Outros', $totalExercises - $adminExercises - $nullExercises],
            ]
        );
        
        if ($nullExercises === 0 && $adminExercises === $totalExercises) {
            $this->info("✅ SUCESSO! Todos os exercícios agora têm created_by = '{$email}'");
            return 0;
        } else {
            $this->warn('⚠️  Atenção: Alguns exercícios podem não ter sido atualizados.');
            return 1;
        }
    }
}
