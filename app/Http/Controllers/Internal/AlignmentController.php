<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AlignmentController extends Controller
{
    /**
     * Persist word timestamps produced by the WhisperX worker.
     * Authenticated by the `internal.token` middleware.
     */
    public function store(Request $request, string $id): JsonResponse
    {
        $exercise = Exercise::find($id);
        if (!$exercise) {
            return response()->json(['error' => 'exercise not found'], 404);
        }

        $validated = $request->validate([
            'word_timestamps' => ['required', 'array', 'min:1'],
            'word_timestamps.*.token' => ['required', 'string'],
            'word_timestamps.*.type' => ['required', Rule::in(['word', 'punctuation'])],
            'word_timestamps.*.startTime' => ['required', 'numeric', 'min:0'],
            'word_timestamps.*.endTime' => ['nullable', 'numeric', 'min:0'],
        ]);

        $timestamps = $validated['word_timestamps'];

        // Sanity check vs exercises.content
        $expected = $this->countWords((string) $exercise->content);
        $got = count(array_filter($timestamps, fn ($e) => ($e['type'] ?? '') === 'word'));
        if ($expected !== $got) {
            Log::warning('alignment word-count mismatch', [
                'exercise_id' => $id,
                'expected' => $expected,
                'got' => $got,
                'content' => $exercise->content,
            ]);
        }

        $exercise->update([
            'word_timestamps' => $timestamps,
            'word_start_times' => Exercise::computeWordStartTimes($timestamps),
        ]);

        return response()->json([
            'success' => true,
            'exercise_id' => $exercise->id,
            'count' => count($timestamps),
            'word_count_match' => $expected === $got,
        ]);
    }

    private function countWords(string $content): int
    {
        preg_match_all('/[\p{L}\p{N}\'-]+/u', $content, $m);
        return count($m[0]);
    }
}
