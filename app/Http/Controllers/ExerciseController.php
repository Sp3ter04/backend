<?php

namespace App\Http\Controllers;

use App\Enums\DictationDifficulty;
use App\Models\Exercise;
use App\Services\ExerciseProcessorService;
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
                'audio_url_1' => $exercise->audio_url_1,
                'audio_url_2' => $exercise->audio_url_2,
                'words' => $exercise->words->map(fn ($word) => [
                    'id' => $word->id,
                    'word' => $word->word,
                    'syllables' => $word->syllables,
                    'difficulty' => $word->difficulty,
                    'audio_url' => $word->audio_url,
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
}
