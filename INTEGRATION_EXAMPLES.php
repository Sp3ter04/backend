<?php

/**
 * INTEGRATION EXAMPLE
 * 
 * Shows how to integrate comma-based paused audio into your existing system
 */

namespace App\Services;

use App\Models\Exercise;
use App\Services\SimplePausedAudioService;
use Illuminate\Support\Facades\Log;

/**
 * Example: Extend existing ExerciseProcessorService
 */
class ExerciseProcessorServiceWithPauses
{
    protected SimplePausedAudioService $pausedAudioService;
    
    public function __construct(SimplePausedAudioService $pausedAudioService)
    {
        $this->pausedAudioService = $pausedAudioService;
    }
    
    /**
     * Process exercise with paused audio generation
     */
    public function processExercise(Exercise $exercise): void
    {
        // 1. Generate paused audio for the sentence
        $audioPath = $this->pausedAudioService->generateSentenceAudio(
            sentence: $exercise->phrase,
            lang: 'pt-PT',
            insertPauses: true,  // Enable comma pauses
            speed: 0.9           // 90% speed for children
        );
        
        if ($audioPath) {
            $exercise->audio_url = $audioPath;
            $exercise->save();
            
            Log::info("Generated paused audio for exercise #{$exercise->id}: {$exercise->phrase}");
        } else {
            Log::error("Failed to generate audio for exercise #{$exercise->id}");
        }
        
        // 2. Continue with word processing...
        // $this->processWords($exercise);
        
        // 3. Continue with syllable processing...
        // $this->processSyllables($exercise);
    }
    
    /**
     * Batch process multiple exercises
     */
    public function batchProcessExercises(int $limit = null): array
    {
        $query = Exercise::query()->whereNotNull('phrase')->where('phrase', '!=', '');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        $exercises = $query->get();
        $stats = [
            'total' => $exercises->count(),
            'success' => 0,
            'failed' => 0,
        ];
        
        foreach ($exercises as $exercise) {
            try {
                $this->processExercise($exercise);
                $stats['success']++;
            } catch (\Exception $e) {
                Log::error("Error processing exercise #{$exercise->id}: " . $e->getMessage());
                $stats['failed']++;
            }
        }
        
        return $stats;
    }
}

/**
 * Example: Controller for on-demand audio regeneration
 */
class ExerciseAudioController
{
    /**
     * Regenerate audio for a single exercise
     * 
     * POST /exercises/{id}/regenerate-audio
     */
    public function regenerateAudio(int $id, SimplePausedAudioService $audioService)
    {
        $exercise = Exercise::findOrFail($id);
        
        $audioPath = $audioService->generateSentenceAudio(
            sentence: $exercise->phrase,
            lang: 'pt-PT',
            insertPauses: true,
            speed: 0.9
        );
        
        if ($audioPath) {
            $exercise->audio_url = $audioPath;
            $exercise->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Audio generated successfully',
                'audio_url' => asset('storage/' . $audioPath),
                'transformation' => [
                    'original' => $exercise->phrase,
                    'with_pauses' => $audioService->insertCommasForPauses($exercise->phrase),
                ],
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to generate audio',
        ], 500);
    }
    
    /**
     * Batch regenerate audio for all exercises
     * 
     * POST /exercises/regenerate-all-audio
     */
    public function regenerateAllAudio(SimplePausedAudioService $audioService)
    {
        $exercises = Exercise::whereNotNull('phrase')->get();
        
        $results = [
            'total' => $exercises->count(),
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
        
        foreach ($exercises as $exercise) {
            // Check if already has good audio
            if ($audioService->audioExists($exercise->phrase, true, 0.9)) {
                $results['skipped']++;
                continue;
            }
            
            $audioPath = $audioService->generateSentenceAudio(
                sentence: $exercise->phrase,
                lang: 'pt-PT',
                insertPauses: true,
                speed: 0.9
            );
            
            if ($audioPath) {
                $exercise->audio_url = $audioPath;
                $exercise->save();
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            // Rate limiting
            usleep(200000); // 0.2 seconds
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Batch regeneration complete',
            'results' => $results,
        ]);
    }
}

/**
 * Example: Livewire component for admin interface
 */
class AudioManagementComponent
{
    public $exercise;
    public $isGenerating = false;
    
    public function regenerateAudio()
    {
        $this->isGenerating = true;
        
        $audioService = app(SimplePausedAudioService::class);
        
        $audioPath = $audioService->generateSentenceAudio(
            sentence: $this->exercise->phrase,
            lang: 'pt-PT',
            insertPauses: true,
            speed: 0.9
        );
        
        if ($audioPath) {
            $this->exercise->audio_url = $audioPath;
            $this->exercise->save();
            
            session()->flash('message', 'Audio regenerated successfully!');
        } else {
            session()->flash('error', 'Failed to generate audio.');
        }
        
        $this->isGenerating = false;
    }
    
    public function render()
    {
        return view('livewire.audio-management', [
            'audioUrl' => $this->exercise->audio_url 
                ? asset('storage/' . $this->exercise->audio_url) 
                : null,
        ]);
    }
}

/**
 * Example: Queue job for background processing
 */
class GeneratePausedAudioJob
{
    public function __construct(public int $exerciseId) {}
    
    public function handle(SimplePausedAudioService $audioService)
    {
        $exercise = Exercise::find($this->exerciseId);
        
        if (!$exercise) {
            return;
        }
        
        $audioPath = $audioService->generateSentenceAudio(
            sentence: $exercise->phrase,
            lang: 'pt-PT',
            insertPauses: true,
            speed: 0.9
        );
        
        if ($audioPath) {
            $exercise->audio_url = $audioPath;
            $exercise->save();
        }
    }
}

/**
 * Example: Service Provider registration
 */
class AppServiceProvider
{
    public function register()
    {
        // Singleton for better performance (reuses same instance)
        $this->app->singleton(SimplePausedAudioService::class);
    }
}

/**
 * USAGE EXAMPLES
 */

// Example 1: Direct usage
$audioService = new SimplePausedAudioService();
$path = $audioService->generateSentenceAudio("A menina vê a mamã", 'pt-PT', true, 0.9);

// Example 2: Dependency injection
Route::post('/exercises/{id}/audio', function(int $id, SimplePausedAudioService $audioService) {
    $exercise = Exercise::findOrFail($id);
    $path = $audioService->generateSentenceAudio($exercise->phrase, 'pt-PT', true, 0.9);
    return ['path' => $path];
});

// Example 3: Batch processing
$sentences = Exercise::pluck('phrase')->toArray();
$results = $audioService->batchGenerate($sentences, 'pt-PT', true, 0.9);

// Example 4: Check before generating
if (!$audioService->audioExists($sentence, true, 0.9)) {
    $path = $audioService->generateSentenceAudio($sentence, 'pt-PT', true, 0.9);
}

// Example 5: Compare transformations
$original = "A menina vê a mamã";
$withPauses = $audioService->insertCommasForPauses($original);
// Original: "A menina vê a mamã"
// With pauses: "A, menina, vê, a, mamã"
