<?php

namespace App\Console\Commands;

use App\Models\Exercise;
use App\Services\EnhancedAudioService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateAudiosWithPauses extends Command
{
    protected $signature = 'audio:regenerate-with-pauses 
                            {--pause=0.3 : Pause duration in seconds}
                            {--speed=0.9 : Speed multiplier}
                            {--limit= : Limit number of exercises to process}';
    
    protected $description = 'Regenerate exercise audios with configurable pauses between words';
    
    public function handle()
    {
        $pauseDuration = (float) $this->option('pause');
        $speed = (float) $this->option('speed');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        
        $this->info("🎙️  Regenerating exercise audios");
        $this->info("   Pause: {$pauseDuration}s | Speed: {$speed}x");
        $this->newLine();
        
        $audioService = app(EnhancedAudioService::class);
        
        $query = Exercise::whereNotNull('sentence')->where('sentence', '!=', '');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        $exercises = $query->orderBy('number')->get();
        $total = $exercises->count();
        
        $this->info("📊 Total exercises: {$total}");
        $this->newLine();
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $success = 0;
        $errors = 0;
        
        foreach ($exercises as $exercise) {
            try {
                // Delete old audio
                if (!empty($exercise->audio_url_1)) {
                    Storage::disk('public')->delete($exercise->audio_url_1);
                }
                
                // Generate new audio with pauses
                $audioPath = $audioService->generateSentenceWithPauses(
                    $exercise->sentence,
                    'pt-PT',
                    $pauseDuration,
                    $speed
                );
                
                if ($audioPath) {
                    $exercise->update(['audio_url_1' => $audioPath]);
                    $success++;
                } else {
                    $errors++;
                }
                
            } catch (\Exception $e) {
                $this->error("Error on exercise #{$exercise->number}: " . $e->getMessage());
                $errors++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("═══════════════════════════════════");
        $this->info("✅ Success: {$success}");
        $this->error("❌ Errors:  {$errors}");
        $this->info("═══════════════════════════════════");
        
        return 0;
    }
}
