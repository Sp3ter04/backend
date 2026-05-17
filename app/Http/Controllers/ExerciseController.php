<?php

namespace App\Http\Controllers;

use App\Enums\DictationDifficulty;
use App\Models\Exercise;
use App\Services\ExerciseProcessorService;
use App\Services\SimplePausedAudioService;
use App\Services\WordContextTimestampService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExerciseController extends Controller
{
    protected ExerciseProcessorService $processorService;

    public function __construct(ExerciseProcessorService $processorService)
    {
        $this->processorService = $processorService;
    }

    /**
     * Cria um novo exercício via API.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validar dados de entrada
        $validated = $request->validate([
            'number' => 'required|integer|min:1',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'sentence' => 'required|string|min:1', // Mudado de 'content' para 'sentence'
        ]);

        try {
            // Mapear difficulty string para enum
            $difficulty = DictationDifficulty::tryFrom($validated['difficulty']);
            if (!$difficulty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dificuldade inválida. Valores aceites: easy, medium, hard'
                ], 400);
            }

            // Criar o exercício
            // NOTA: content e created_by são preenchidos automaticamente pelo Model Event
            $exercise = Exercise::create([
                'number' => $validated['number'],
                'difficulty' => $difficulty,
                'sentence' => $validated['sentence'],
                // 'content' => preenchido automaticamente = sentence
                // 'created_by' => preenchido automaticamente = auth()->user()->email
                'words_json' => json_encode([]),
            ]);

            // Processar o exercício (dividir em palavras, gerar sílabas, áudios, etc.)
            $this->processorService->process($exercise);

            // Recarregar o exercício para retornar dados atualizados
            $exercise->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Exercício criado e processado com sucesso',
                'exercise' => [
                    'id' => $exercise->id,
                    'number' => $exercise->number,
                    'difficulty' => $exercise->difficulty->value,
                    'sentence' => $exercise->sentence,
                    'content' => $exercise->content,
                    'created_by' => $exercise->created_by,
                    'words_json' => $exercise->words_json,
                    'word_timestamps' => $exercise->word_timestamps,
                    'word_start_times' => $exercise->word_start_times,
                    'audio_url_1' => $exercise->audio_url_1,
                    'audio_url_2' => $exercise->audio_url_2,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar exercício: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtém um exercício pelo ID.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $exercise = Exercise::with(['words', 'exerciseWords'])->find($id);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercício não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'exercise' => [
                'id' => $exercise->id,
                'number' => $exercise->number,
                'difficulty' => $exercise->difficulty->value,
                'content' => $exercise->content,
                'sentence' => $exercise->sentence,
                'words_json' => $exercise->words_json,
                'word_timestamps' => $exercise->word_timestamps,
                'word_start_times' => $exercise->word_start_times,
                'audio_url_1' => $exercise->audio_url_1,
                'audio_url_2' => $exercise->audio_url_2,
                'words' => $exercise->words->map(fn ($word) => [
                    'id' => $word->id,
                    'word' => $word->word,
                    'syllables' => $word->syllables,
                    'difficulty' => $word->difficulty,
                    'audio_url' => $word->audio_url,
                    'word_timestamps' => $word->word_timestamps,
                ]),
            ]
        ]);
    }

    /**
     * Lista todos os exercícios.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $exercises = Exercise::with('words')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $exercises->items(),
            'pagination' => [
                'current_page' => $exercises->currentPage(),
                'last_page' => $exercises->lastPage(),
                'per_page' => $exercises->perPage(),
                'total' => $exercises->total(),
            ]
        ]);
    }

    /**
     * Atualiza um exercício.
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $exercise = Exercise::find($id);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercício não encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'number' => 'sometimes|integer|min:1',
            'difficulty' => 'sometimes|string|in:easy,medium,hard',
            'sentence' => 'sometimes|string|min:1', // Mudado de 'content' para 'sentence'
        ]);

        try {
            // Mapear difficulty se fornecida
            if (isset($validated['difficulty'])) {
                $difficulty = DictationDifficulty::tryFrom($validated['difficulty']);
                if (!$difficulty) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dificuldade inválida. Valores aceites: easy, medium, hard'
                    ], 400);
                }
                $validated['difficulty'] = $difficulty;
            }

            // NOTA: Se sentence foi atualizado, content será sincronizado automaticamente pelo Model Event
            $exercise->update($validated);

            // Se conteúdo foi alterado, reprocessar o exercício
            if (isset($validated['sentence']) || isset($validated['difficulty'])) {
                $this->processorService->process($exercise);
                $exercise->refresh();
            }

            return response()->json([
                'success' => true,
                'message' => 'Exercício atualizado com sucesso',
                'exercise' => [
                    'id' => $exercise->id,
                    'number' => $exercise->number,
                    'difficulty' => $exercise->difficulty->value,
                    'content' => $exercise->content,
                    'words_json' => $exercise->words_json,
                    'word_timestamps' => $exercise->word_timestamps,
                    'word_start_times' => $exercise->word_start_times,
                    'audio_url_1' => $exercise->audio_url_1,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar exercício: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deleta um exercício.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $exercise = Exercise::find($id);

        if (!$exercise) {
            return response()->json([
                'success' => false,
                'message' => 'Exercício não encontrado'
            ], 404);
        }

        try {
            // Limpar áudios do storage (opcional)
            if ($exercise->audio_url_1) {
                \App\Services\AudioService::delete($exercise->audio_url_1);
            }
            if ($exercise->audio_url_2) {
                \App\Services\AudioService::delete($exercise->audio_url_2);
            }

            $exercise->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exercício eliminado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao eliminar exercício: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Regenera áudio e timestamps para todos os exercícios.
     *
     * POST /api/regenerate-all-audio?force=true
     * Header: Authorization: Bearer {ADMIN_SECRET}
     */
    public function regenerateAllAudio(
        Request $request,
        SimplePausedAudioService $audioService,
        WordContextTimestampService $wordContextTimestampService
    ): JsonResponse
    {
        if (!$this->hasValidAdminSecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $force = filter_var((string) $request->query('force', 'false'), FILTER_VALIDATE_BOOLEAN);
        $query = Exercise::query()->whereNotNull('sentence')->where('sentence', '!=', '');

        $stats = [
            'total' => $query->count(),
            'sucesso' => 0,
            'erros' => 0,
            'saltados' => 0,
        ];

        foreach ($query->cursor() as $exercise) {
            if (!$force && !empty($exercise->word_timestamps)) {
                $stats['saltados']++;
                usleep(500000);
                continue;
            }

            try {
                $result = $audioService->generateSentenceAudioWithTimestamps(
                    $exercise->sentence,
                    'pt-PT',
                    true,
                    0.9,
                    $exercise->number,
                    $force
                );

                if ($result && !empty($result['path'])) {
                    $updateData = ['audio_url_1' => $result['path']];

                    if (!empty($result['word_timestamps'])) {
                        $updateData['word_timestamps'] = $result['word_timestamps'];
                        $updateData['word_start_times'] = Exercise::computeWordStartTimes($result['word_timestamps']);
                    } elseif (empty($exercise->word_start_times) && !empty($exercise->word_timestamps)) {
                        $updateData['word_start_times'] = Exercise::computeWordStartTimes($exercise->word_timestamps);
                    }

                    $exercise->update($updateData);
                    $stats['sucesso']++;
                } else {
                    $stats['erros']++;
                }
            } catch (\Throwable $e) {
                $stats['erros']++;
            }

            usleep(500000);
        }

        // Segunda fase: reconstruir timestamps das words a partir dos contextos das frases.
        $contextStats = $wordContextTimestampService->rebuildFromAllExercises(true);

        return response()->json([
            'success' => true,
            'total' => $stats['total'],
            'sucesso' => $stats['sucesso'],
            'erros' => $stats['erros'],
            'saltados' => $stats['saltados'],
            'force' => $force,
            'word_contexts' => $contextStats,
        ]);
    }

    protected function hasValidAdminSecret(Request $request): bool
    {
        $configuredSecret = (string) env('ADMIN_SECRET', env('LOCAL_ADMIN_PASSWORD', ''));
        if ($configuredSecret === '') {
            return false;
        }

        $authorization = (string) $request->header('Authorization', '');
        if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return false;
        }

        $provided = trim((string) ($matches[1] ?? ''));

        return $provided !== '' && hash_equals($configuredSecret, $provided);
    }
}
