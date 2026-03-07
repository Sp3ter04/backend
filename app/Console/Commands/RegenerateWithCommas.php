<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exercise;
use App\Services\SimplePausedAudioService;
use Illuminate\Support\Facades\Storage;

class RegenerateWithCommas extends Command
{
    protected $signature = 'audio:regenerate-with-commas
                          {--speed=0.9 : Audio speed multiplier (0.5-1.5)}
                          {--lang=pt-PT : Language code}
                          {--limit= : Limit number of exercises to process}
                          {--force : Regenerate even if audio exists}';

    protected $description = 'Regenerate exercise audio using comma insertion for natural pauses';

    public function handle(SimplePausedAudioService $audioService)
    {
        $speed = (float) $this->option('speed');
        $lang = $this->option('lang');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $force = $this->option('force');

        $this->info('🎵 Regenerating sentence audio with comma-paused speech...');
        $this->newLine();
        
        // Validate speed
        if ($speed < 0.5 || $speed > 1.5) {
            $this->error('Speed must be between 0.5 and 1.5');
            return 1;
        }

        // Get exercises
        $query = Exercise::query()->whereNotNull('sentence')->where('sentence', '!=', '');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        $exercises = $query->get();
        $total = $exercises->count();

        if ($total === 0) {
            $this->warn('No exercises found with sentences.');
            return 0;
        }

        $this->info("Found {$total} exercises to process");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        $stats = [
            'success' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        foreach ($exercises as $exercise) {
            $sentence = $exercise->sentence;
            $progressBar->setMessage("Processing: " . substr($sentence, 0, 40) . '...');

            // Check if already exists (unless force)
            if (!$force && $audioService->audioExists($sentence, true, $speed)) {
                $stats['skipped']++;
                $progressBar->advance();
                continue;
            }

            // Generate audio
            $audioPath = $audioService->generateSentenceAudio($sentence, $lang, true, $speed);

            if ($audioPath) {
                // Update exercise with new audio path
                $exercise->audio_url_1 = $audioPath;
                $exercise->save();
                $stats['success']++;
            } else {
                $stats['failed']++;
                $this->newLine();
                $this->error("Failed to generate audio for exercise #{$exercise->id}: {$sentence}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->info('✅ Processing complete!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Success', $stats['success']],
                ['Skipped (already exists)', $stats['skipped']],
                ['Failed', $stats['failed']],
                ['Total', $total],
            ]
        );

        // Show sample transformation
        if ($total > 0) {
            $this->newLine();
            $this->info('📝 Sample transformation:');
            $sampleSentence = $exercises->first()->sentence;
            $transformed = $audioService->insertCommasForPauses($sampleSentence);
            
            $this->line("  Original:    {$sampleSentence}");
            $this->line("  Transformed: {$transformed}");
        }

        return $stats['failed'] > 0 ? 1 : 0;
    }
}
