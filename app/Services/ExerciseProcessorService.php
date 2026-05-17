<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExerciseWord;
use App\Models\Syllable;
use App\Models\Word;
use App\Models\WordSyllable;
use App\Services\AudioService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExerciseProcessorService
{
    protected PortugueseSyllableSplitter $splitter;
    protected WordContextTimestampService $wordContextTimestampService;

    public function __construct()
    {
        $this->splitter = new PortugueseSyllableSplitter();
        $this->wordContextTimestampService = app(WordContextTimestampService::class);
    }

    /**
     * Processa um exercício: divide em palavras e sílabas, guarda tudo nas tabelas.
     *
     * @param Exercise $exercise
     * @return void
     */
    public function process(Exercise $exercise): void
    {
        $text = $exercise->sentence;

        // Extrair palavras (remover pontuação, manter apenas palavras)
        $rawWords = $this->extractWords($text);

        if (empty($rawWords)) {
            return;
        }

        // Convert enum difficulty to integer for Word model (1=easy, 2=medium, 3=hard)
        $wordDifficulty = match ($exercise->difficulty?->value ?? 'easy') {
            'easy' => 1,
            'medium' => 2,
            'hard' => 3,
            default => 1,
        };

        DB::transaction(function () use ($exercise, $rawWords, $wordDifficulty) {
            // Limpar relações existentes (para caso de edição)
            ExerciseWord::where('exercise_id', $exercise->id)->delete();

            $wordsData = [];

            foreach ($rawWords as $order => $rawWord) {
                $normalizedWord = mb_strtolower(trim($rawWord));

                if (empty($normalizedWord)) {
                    continue;
                }

                // Procurar ou criar a palavra na tabela words
                $word = Word::where('word', $normalizedWord)->first();

                if (!$word) {
                    $word = $this->createWordWithSyllables($normalizedWord, $wordDifficulty);
                }

                // Criar a relação exercise_words
                ExerciseWord::create([
                    'exercise_id' => $exercise->id,
                    'word_id' => $word->id,
                    'word_order' => $order + 1,
                ]);

                $syllables = $word->wordSyllables->map(fn ($ws) => $ws->syllable->syllable)->implode('-');

                $wordsData[] = [
                    'word' => $normalizedWord,
                    'order' => $order + 1,
                    'syllables' => $syllables,
                    'word_id' => $word->id,
                ];
            }

            // Atualizar o campo words_json do exercício
            $exercise->update([
                'words_json' => json_encode($wordsData),
            ]);
        });

        // Gerar áudio para a frase completa (fora da transaction para não bloquear)
        $this->generateSentenceAudio($exercise);

        // Propagar timestamps em contexto da frase para words.word_timestamps.
        $this->syncWordContextTimestamps($exercise);

        // Gerar áudio para cada palavra individual
        $this->generateWordsAudio($exercise);
    }

    /**
     * Cria uma palavra e as suas sílabas.
     */
    protected function createWordWithSyllables(string $word, int $difficulty = 1): Word
    {
        // Dividir em sílabas
        $syllables = $this->splitter->split($word);
        $syllablesText = implode('-', $syllables);

        // Criar a palavra
        $wordModel = Word::create([
            'word' => $word,
            'syllables' => $syllablesText,
            'difficulty' => $difficulty,
        ]);

        // Criar as sílabas e as relações
        foreach ($syllables as $position => $syllableText) {
            // Procurar ou criar a sílaba
            $syllableModel = Syllable::firstOrCreate([
                'syllable' => $syllableText,
            ]);

            // Criar a relação word_syllables
            WordSyllable::create([
                'word_id' => $wordModel->id,
                'syllable_id' => $syllableModel->id,
                'position' => $position + 1,
            ]);
        }

        return $wordModel;
    }

    /**
     * Gera áudio TTS para a frase completa do exercício.
     */
    protected function generateSentenceAudio(Exercise $exercise): void
    {
        try {
            $sentence = $exercise->sentence;
            if (empty($sentence)) {
                return;
            }

            $audioService = app(SimplePausedAudioService::class);

            // Gerar áudio da frase (audio_url_1) e timestamps com SSML marks.
            $result = $audioService->generateSentenceAudioWithTimestamps(
                $sentence,
                'pt-PT',
                true,
                0.9,
                $exercise->number
            );

            if ($result && !empty($result['path'])) {
                $updateData = ['audio_url_1' => $result['path']];

                // Only overwrite word_timestamps when new real timestamps were generated.
                // When the audio file already existed, $result['word_timestamps'] is null,
                // meaning we should preserve any previously-stored timestamps.
                if (!empty($result['word_timestamps'])) {
                    $updateData['word_timestamps'] = $result['word_timestamps'];
                    $updateData['word_start_times'] = Exercise::computeWordStartTimes($result['word_timestamps']);
                } elseif (empty($exercise->word_start_times) && !empty($exercise->word_timestamps)) {
                    // Backfill word_start_times if missing but word_timestamps exists
                    $updateData['word_start_times'] = Exercise::computeWordStartTimes($exercise->word_timestamps);
                }

                $exercise->update($updateData);
            }
        } catch (\Exception $e) {
            Log::warning('Falha ao gerar áudio da frase do exercício ' . $exercise->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Gera áudio TTS para cada palavra do exercício.
     */
    protected function generateWordsAudio(Exercise $exercise): void
    {
        try {
            $words = $exercise->words()->get();

            foreach ($words as $word) {
                // Apenas garantir audio_url da palavra; timestamps vêm do contexto da frase.
                if (!empty($word->audio_url)) {
                    continue;
                }

                $audioPath = AudioService::generateAndSave(
                    $word->word,
                    'pt-PT',
                    'words'
                );

                if ($audioPath) {
                    $word->update([
                        'audio_url' => $audioPath,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Falha ao gerar áudio das palavras do exercício ' . $exercise->id . ': ' . $e->getMessage());
        }
    }

    protected function syncWordContextTimestamps(Exercise $exercise): void
    {
        try {
            $this->wordContextTimestampService->syncFromExercise($exercise->fresh());
        } catch (\Throwable $e) {
            Log::warning('Falha ao sincronizar timestamps em contexto para exercício ' . $exercise->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Extrai palavras de um exercício (remove pontuação).
     *
     * @return array<string>
     */
    protected function extractWords(string $text): array
    {
        // Remover pontuação mas manter acentos/caracteres portugueses
        // Importante: o modificador /u é obrigatório, caso contrário a classe
        // trata bytes UTF-8 individualmente e destrói caracteres acentuados
        // (ex.: o byte A1 de "á" coincidiria com o byte A1 de "¡").
        $text = preg_replace('/[.,;:!?¿¡()\[\]{}"\'«»\-–—…\/\\\\]/u', ' ', $text);

        // Dividir por espaços e filtrar vazios
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        return $words ?: [];
    }
}
