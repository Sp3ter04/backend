<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioService
{
    /**
     * Gera áudio TTS para uma palavra/texto usando Google Translate TTS
     * e guarda o ficheiro MP3 no storage.
     *
     * @param string $text Texto para converter em áudio
     * @param string $lang Código do idioma (default: pt-PT)
     * @param string $folder Subpasta dentro de storage/public/audio (default: words)
     * @param string|null $filenamePrefix Prefixo opcional para diferenciar ficheiros com o mesmo texto
     * @return string|null Caminho público do ficheiro ou null se falhar
     */
    public static function generateAndSave(string $text, string $lang = 'pt-PT', string $folder = 'words', ?string $filenamePrefix = null): ?string
    {
        $result = self::generateAndSaveWithTimestamps($text, $lang, $folder, $filenamePrefix);

        return $result['path'] ?? null;
    }

    /**
     * Generate and store audio while returning token timestamps.
     *
     * @return array{path:string,word_timestamps:array|null}|null
     */
    public static function generateAndSaveWithTimestamps(
        string $text,
        string $lang = 'pt-PT',
        string $folder = 'words',
        ?string $filenamePrefix = null,
        float $speakingRate = 0.9
    ): ?array {
        $text = trim($text);
        if (empty($text)) {
            return null;
        }

        $folder = trim($folder, '/');

        // Nome do ficheiro baseado no texto.
        // Se o texto contém caracteres não-ASCII (ex.: acentos como á, ã, ç),
        // adicionamos um hash curto ao nome para evitar colisões entre palavras
        // que produzem o mesmo slug (ex.: "da" e "dá" → ambos slug "da").
        $slugBase = $filenamePrefix ? ($filenamePrefix . ' ' . $text) : $text;
        $slug = Str::slug($slugBase);
        $filename = preg_match('/[^\x00-\x7F]/u', $text)
            ? $slug . '-' . substr(md5($text), 0, 8) . '.mp3'
            : $slug . '.mp3';
        $path = "audio/{$folder}/{$filename}";

        // Se já existe, retornar o caminho
        if (Storage::disk('public')->exists($path)) {
            $timestamps = app(GoogleTtsTimestampService::class)->buildFallbackTimestamps($text);

            return [
                'path' => $path,
                'word_timestamps' => $timestamps,
            ];
        }

        $timestamps = null;

        // Preferir Google Cloud TTS com SSML marks para obter timestamps.
        $ttsResult = app(GoogleTtsTimestampService::class)->synthesizeWithTimestamps(
            $text,
            $lang,
            $speakingRate,
            'FEMALE'
        );

        $audioData = $ttsResult['audioContent'] ?? null;
        $timestamps = $ttsResult['timestamps'] ?? null;

        if (!$audioData) {
            // Fallback para fluxo antigo, sem quebrar geração de áudio.
            $audioData = self::tryGoogleTranslateTTS($text, $lang)
                ?? self::trySoundOfTextTTS($text, $lang);
            $timestamps = app(GoogleTtsTimestampService::class)->buildFallbackTimestamps($text);
        }

        if ($audioData && strlen($audioData) > 100) {
            Storage::disk('public')->put($path, $audioData);

            return [
                'path' => $path,
                'word_timestamps' => $timestamps,
            ];
        }

        return null;
    }

    /**
     * Google Translate TTS (não oficial).
     */
    private static function tryGoogleTranslateTTS(string $text, string $lang): ?string
    {
        try {
            $encodedText = urlencode($text);
            $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl={$lang}&client=tw-ob&q={$encodedText}";

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Referer' => 'https://translate.google.com/',
            ])->timeout(10)->get($url);

            if ($response->successful() && strlen($response->body()) > 100) {
                return $response->body();
            }
        } catch (\Exception $e) {
            // Silenciar
        }
        return null;
    }

    /**
     * SoundOfText API (gratuita).
     */
    private static function trySoundOfTextTTS(string $text, string $lang): ?string
    {
        try {
            // SoundOfText não aceita pt-PT, usar apenas 'pt'
            $voice = str_contains($lang, '-') ? explode('-', $lang)[0] : $lang;

            // Passo 1: Solicitar criação do áudio
            $response = Http::timeout(15)->post('https://api.soundoftext.com/sounds', [
                'engine' => 'Google',
                'data' => [
                    'text' => $text,
                    'voice' => $voice,
                ],
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            if (!isset($data['id'])) {
                return null;
            }

            $id = $data['id'];

            // Passo 2: Esperar pelo áudio ficar pronto (polling)
            $attempts = 0;
            $audioUrl = null;

            while ($attempts < 10) {
                usleep(500000); // 0.5s
                $statusResponse = Http::timeout(10)->get("https://api.soundoftext.com/sounds/{$id}");

                if ($statusResponse->successful()) {
                    $statusData = $statusResponse->json();
                    if (isset($statusData['status']) && $statusData['status'] === 'Done' && isset($statusData['location'])) {
                        $audioUrl = $statusData['location'];
                        break;
                    }
                }
                $attempts++;
            }

            if (!$audioUrl) {
                return null;
            }

            // Passo 3: Descarregar o ficheiro MP3
            $audioResponse = Http::timeout(15)->get($audioUrl);
            if ($audioResponse->successful() && strlen($audioResponse->body()) > 100) {
                return $audioResponse->body();
            }
        } catch (\Exception $e) {
            report($e);
        }
        return null;
    }

    /**
     * Remove o ficheiro de áudio do storage.
     */
    public static function delete(string $audioUrl): void
    {
        if (empty($audioUrl)) {
            return;
        }

        // Extrair path relativo do URL
        $storagePath = str_replace('/storage/', '', parse_url($audioUrl, PHP_URL_PATH));
        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }
    }
}
