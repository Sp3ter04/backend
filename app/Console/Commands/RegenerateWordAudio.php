<?php

namespace App\Console\Commands;

use App\Models\Word;
use App\Services\AudioService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateWordAudio extends Command
{
    protected $signature = 'words:regenerate-audio
                            {--only-missing : Only process words whose audio file is missing from storage}
                            {--only-accented : Only process words with accented/non-ASCII characters}
                            {--force : Force regeneration even if file already exists}';

    protected $description = 'Regenerate audio files for words (use --only-missing to fix 403s)';

    public function handle(): int
    {
        $onlyMissing  = $this->option('only-missing');
        $onlyAccented = $this->option('only-accented');
        $force        = $this->option('force');

        $query = Word::query();

        if ($onlyAccented) {
            // Supabase/PostgreSQL: filter non-ASCII using regex
            $query->whereRaw("word ~ '[^\\x00-\\x7F]'");
        }

        $words = $query->get(['id', 'word', 'audio_url']);

        $updated = 0;
        $skipped = 0;
        $failed  = 0;

        $bar = $this->output->createProgressBar($words->count());
        $bar->start();

        foreach ($words as $word) {
            $bar->advance();

            $fileExists = $word->audio_url
                && Storage::disk('public')->exists($word->audio_url);

            if ($onlyMissing && $fileExists && !$force) {
                $skipped++;
                continue;
            }

            if (!$force && $fileExists) {
                $skipped++;
                continue;
            }

            // Delete old file so AudioService doesn't skip it
            if ($force && $fileExists) {
                Storage::disk('public')->delete($word->audio_url);
            }

            $newPath = AudioService::generateAndSave($word->word, 'pt-PT', 'words');

            if ($newPath) {
                $word->update(['audio_url' => $newPath]);
                $updated++;
            } else {
                $this->newLine();
                $this->warn("FAIL: {$word->word}");
                $failed++;
            }

            usleep(100_000); // 100ms anti-rate-limit
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done — Updated: {$updated} | Skipped: {$skipped} | Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
