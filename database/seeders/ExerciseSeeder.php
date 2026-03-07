<?php

namespace Database\Seeders;

use App\Enums\DictationDifficulty;
use App\Models\Exercise;
use App\Models\Word;
use App\Models\Syllable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar algumas sílabas
        $syllables = ['ca', 'sa', 'ma', 'pa', 'to', 'gran', 'de'];

        foreach ($syllables as $syllable) {
            Syllable::firstOrCreate(
                ['syllable' => $syllable],
                ['id' => Str::uuid()]
            );
        }

        // Criar algumas palavras
        $words = ['casa', 'sapato', 'mapa', 'pato', 'grande', 'lago'];

        foreach ($words as $word) {
            Word::firstOrCreate(
                ['word' => $word],
                ['id' => Str::uuid(), 'difficulty' => 1]
            );
        }

        // Criar exercícios de exemplo
        $exercises = [
            [
                'sentence' => 'A casa é grande',
                'words_json' => ['A', 'casa', 'é', 'grande'],
                'difficulty' => DictationDifficulty::EASY,
                'content' => 'Ditado sobre a casa',
                'number' => 1,
            ],
            [
                'sentence' => 'O sapato está no mapa',
                'words_json' => ['O', 'sapato', 'está', 'no', 'mapa'],
                'difficulty' => DictationDifficulty::MEDIUM,
                'content' => 'Ditado sobre objetos',
                'number' => 2,
            ],
            [
                'sentence' => 'O pato nada no lago',
                'words_json' => ['O', 'pato', 'nada', 'no', 'lago'],
                'difficulty' => DictationDifficulty::EASY,
                'content' => 'Ditado sobre animais',
                'number' => 3,
            ],
        ];

        foreach ($exercises as $exerciseData) {
            Exercise::firstOrCreate(
                ['number' => $exerciseData['number']],
                array_merge(['id' => Str::uuid()], $exerciseData)
            );
        }

        $this->command->info('✅ Exercícios de exemplo criados com sucesso!');
    }
}
