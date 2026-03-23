<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class GoogleTtsTimestampService
{
    /**
     * Tokenize text preserving punctuation as independent tokens.
     *
     * @return array<int, array{token:string,type:string}>
     */
    public function tokenize(string $sentence): array
    {
        $sentence = trim($sentence);

        if ($sentence === '') {
            return [];
        }

        $parts = preg_split(
            '/(\s+|(?<=[\p{L}\p{N}])(?=[.,;!?])|(?<=[.,;!?])(?=\s))/u',
            $sentence,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if (!$parts) {
            return [];
        }

        $tokens = [];

        foreach ($parts as $part) {
            if (!is_string($part)) {
                continue;
            }

            $trimmed = trim($part);
            if ($trimmed === '') {
                continue;
            }

            $tokens[] = [
                'token' => $trimmed,
                'type' => preg_match('/^[.,;!?]$/u', $trimmed) ? 'punct' : 'word',
            ];
        }

        return $tokens;
    }

    /**
     * Build SSML with marks for every token except the first.
     */
    public function buildSSML(string $sentence): string
    {
        $tokens = $this->tokenize($sentence);
        return $this->buildSSMLFromTokens($tokens);
    }

    /**
     * Synthesize speech with SSML marks and return audio + token timestamps.
     *
     * @return array{audioContent:string,timestamps:array<int,array{token:string,type:string,startTime:float|null}>}|null
     */
    public function synthesizeWithTimestamps(
        string $sentence,
        string $languageCode = 'pt-PT',
        float $speakingRate = 0.9,
        string $gender = 'FEMALE'
    ): ?array {
        if (!class_exists(\Google\Cloud\TextToSpeech\V1\TextToSpeechClient::class)) {
            return null;
        }

        $tokens = $this->tokenize($sentence);
        if (empty($tokens)) {
            return null;
        }

        $ssml = $this->buildSSMLFromTokens($tokens);
        $client = null;

        try {
            $client = new \Google\Cloud\TextToSpeech\V1\TextToSpeechClient();

            $response = $client->synthesizeSpeech([
                'input' => ['ssml' => $ssml],
                'voice' => [
                    'language_code' => $languageCode,
                    'ssml_gender' => $gender,
                ],
                'audio_config' => [
                    'audio_encoding' => 'MP3',
                    'speaking_rate' => $speakingRate,
                ],
                'enable_time_pointing' => ['SSML_MARK'],
            ]);

            $audioContent = $response->getAudioContent();
            if (empty($audioContent)) {
                return null;
            }

            $timepointsByMark = [];
            foreach ($response->getTimepoints() as $timepoint) {
                $timepointsByMark[$timepoint->getMarkName()] = (float) $timepoint->getTimeSeconds();
            }

            return [
                'audioContent' => $audioContent,
                'timestamps' => $this->buildTimestampsFromMarks($tokens, $timepointsByMark),
            ];
        } catch (\Throwable $e) {
            Log::warning('Google TTS with timestamps failed: ' . $e->getMessage());
            return null;
        } finally {
            if ($client && method_exists($client, 'close')) {
                $client->close();
            }
        }
    }

    /**
     * Fallback timestamp structure when SSML marks are unavailable.
     *
     * @return array<int,array{token:string,type:string,startTime:float|null}>
     */
    public function buildFallbackTimestamps(string $sentence): array
    {
        $tokens = $this->tokenize($sentence);

        return array_map(
            static fn (array $item, int $index): array => [
                'token' => $item['token'],
                'type' => $item['type'],
                'startTime' => $index === 0 ? 0.0 : null,
            ],
            $tokens,
            array_keys($tokens)
        );
    }

    /**
     * @param array<int,array{token:string,type:string}> $tokens
     */
    protected function buildSSMLFromTokens(array $tokens): string
    {
        if (empty($tokens)) {
            return '<speak></speak>';
        }

        $parts = [];

        foreach ($tokens as $index => $tokenInfo) {
            $safeToken = $this->escapeForSSML($tokenInfo['token']);
            $parts[] = $index === 0 ? $safeToken : '<mark name="t' . $index . '"/>' . $safeToken;
        }

        return '<speak>' . implode(' ', $parts) . '</speak>';
    }

    /**
     * @param array<int,array{token:string,type:string}> $tokens
     * @param array<string,float> $timepointsByMark
     * @return array<int,array{token:string,type:string,startTime:float|null}>
     */
    protected function buildTimestampsFromMarks(array $tokens, array $timepointsByMark): array
    {
        $timestamps = [];

        foreach ($tokens as $index => $tokenInfo) {
            $startTime = 0.0;

            if ($index > 0) {
                $markName = 't' . $index;
                $startTime = $timepointsByMark[$markName] ?? null;
            }

            $timestamps[] = [
                'token' => $tokenInfo['token'],
                'type' => $tokenInfo['type'],
                'startTime' => $startTime,
            ];
        }

        return $timestamps;
    }

    protected function escapeForSSML(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
