<?php

namespace App\Models;

use App\Enums\DictationDifficulty;
use App\Models\SupabaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Exercise extends SupabaseModel
{
    /**
     * The table associated with the model.
     */
    protected $table = 'exercises';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'sentence',
        'words_json',
        'difficulty',
        'content',
        'number',
        'audio_url_1',
        'audio_url_2',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'difficulty' => DictationDifficulty::class,
            'words_json' => 'array',
        ];
    }

    /**
     * Boot method - Laravel Model Events (solução profissional!)
     * Executado automaticamente antes de criar um novo exercício
     */
    protected static function boot()
    {
        parent::boot();

        // Event: creating - executado ANTES de inserir no banco
        static::creating(function ($exercise) {
            // 1. Auto-preencher created_by com email do usuário autenticado
            if (empty($exercise->created_by) && Auth::check()) {
                $exercise->created_by = Auth::user()->email;
            }

            // 2. Auto-preencher content com o valor de sentence (se vazio)
            if (empty($exercise->content) && !empty($exercise->sentence)) {
                $exercise->content = $exercise->sentence;
            }

            // 3. Auto-incrementar number baseado no último exercício
            if (empty($exercise->number)) {
                $lastNumber = static::max('number') ?? 0;
                $exercise->number = $lastNumber + 1;
            }
        });

        // Event: updating - executado ANTES de atualizar
        static::updating(function ($exercise) {
            // Se sentence foi alterado e content está vazio, sincronizar
            if ($exercise->isDirty('sentence') && empty($exercise->content)) {
                $exercise->content = $exercise->sentence;
            }
        });
    }

    /**
     * Palavras deste exercício (pivot).
     */
    public function exerciseWords(): HasMany
    {
        return $this->hasMany(ExerciseWord::class, 'exercise_id')->orderBy('word_order');
    }

    /**
     * Palavras associadas via many-to-many.
     */
    public function words(): BelongsToMany
    {
        return $this->belongsToMany(Word::class, 'exercise_words', 'exercise_id', 'word_id')
            ->withPivot('word_order')
            ->orderByPivot('word_order');
    }
}
