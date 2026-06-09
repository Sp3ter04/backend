<?php

namespace App\Models;

use App\Models\SupabaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeechMetric extends SupabaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'speech_metrics';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'exercise_id',
        'task_id',
        'difficulty',
        'pron_score',
        'accuracy_score',
        'fluency_score',
        'completeness_score',
        'mispronunciation_count',
        'omission_count',
        'insertion_count',
        'substitution_count',
        'words_per_minute',
        'display_text',
        'error_words',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'student_id' => 'string',
            'exercise_id' => 'string',
            'task_id' => 'string',
            'pron_score' => 'decimal:2',
            'accuracy_score' => 'decimal:2',
            'fluency_score' => 'decimal:2',
            'completeness_score' => 'decimal:2',
            'mispronunciation_count' => 'integer',
            'omission_count' => 'integer',
            'insertion_count' => 'integer',
            'substitution_count' => 'integer',
            'words_per_minute' => 'decimal:2',
            'error_words' => 'array',
        ];
    }

    /**
     * Get the student that owns the SpeechMetric.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the exercise that owns the SpeechMetric.
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}
