<?php

namespace App\Services;

use App\Auth\SupabaseUser;
use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseAuthenticationService
{
    public function authenticateWithPassword(string $email, string $password): array
    {
        if ($fallback = $this->attemptLocalAdminFallback($email, $password)) {
            Log::warning('Local admin fallback activated before Supabase auth', [
                'email' => $email,
            ]);

            return $fallback;
        }

        try {
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.public_key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.supabase.url') . '/auth/v1/token?grant_type=password', [
                'email' => $email,
                'password' => $password,
            ]);
        } catch (\Throwable $e) {
            Log::error('Supabase login request failed', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Nao foi possivel contactar o Supabase.');
        }

        if ($response->failed()) {
            Log::warning('Supabase login rejected', [
                'email' => $email,
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body(),
            ]);

            if ($fallback = $this->attemptLocalAdminFallback($email, $password)) {
                Log::warning('Supabase login fallback activated for local admin', [
                    'email' => $email,
                ]);

                return $fallback;
            }

            throw new \RuntimeException(
                $response->json('msg')
                ?? $response->json('message')
                ?? 'Email ou palavra-passe incorretos.'
            );
        }

        $authData = $response->json();
        $token = $authData['access_token'] ?? null;

        if (! $token) {
            Log::warning('Supabase login missing access token', [
                'email' => $email,
                'body' => $authData,
            ]);

            throw new \RuntimeException('Resposta invalida do Supabase ao autenticar.');
        }

        try {
            $decoded = $this->validateToken($token);
        } catch (\Throwable $e) {
            Log::warning('Supabase token validation failed', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            throw new \RuntimeException('JWT invalido devolvido pelo Supabase.');
        }

        try {
            $user = $this->makeSessionUser($decoded, $token);
        } catch (\RuntimeException $e) {
            Log::warning('Supabase login blocked by public.users check', [
                'email' => $email,
                'message' => $e->getMessage(),
                'supabase_id' => $decoded->sub ?? null,
            ]);

            throw $e;
        }

        return [
            'token' => $token,
            'decoded' => $decoded,
            'user' => $user,
            'auth_data' => $authData,
        ];
    }

    protected function attemptLocalAdminFallback(string $email, string $password): ?array
    {
        $configuredEmail = mb_strtolower(trim((string) env('LOCAL_ADMIN_EMAIL', '')));
        $configuredPassword = (string) env('LOCAL_ADMIN_PASSWORD', '');

        if ($configuredEmail === '' || $configuredPassword === '') {
            return null;
        }

        if (mb_strtolower(trim($email)) !== $configuredEmail || ! hash_equals($configuredPassword, $password)) {
            return null;
        }

        $userId = (string) env('LOCAL_ADMIN_ID', '41c29ba9-55af-48c1-83cc-944fb7f2c5ec');
        $userName = (string) env('LOCAL_ADMIN_NAME', 'Administrador');
        $userRole = (string) env('LOCAL_ADMIN_ROLE', 'admin');

        $user = User::query()->firstOrNew([
            'email' => $configuredEmail,
        ]);

        if (! $user->exists && blank($user->id)) {
            $user->id = $userId !== '' ? $userId : (string) Str::uuid();
        }

        $user->fill([
            'auth_id' => $userId !== '' ? $userId : $user->auth_id,
            'name' => $userName,
            'email' => $configuredEmail,
            'role' => $userRole,
        ]);
        $user->save();

        $jwtPayload = (object) [
            'sub' => $user->auth_id ?: $user->id,
            'email' => $user->email,
            'role' => 'authenticated',
            'user_metadata' => [
                'name' => $user->name,
                'role' => $userRole,
            ],
            'app_metadata' => [
                'provider' => 'local-admin-fallback',
                'providers' => ['email'],
            ],
        ];

        return [
            'token' => null,
            'decoded' => $jwtPayload,
            'user' => new SupabaseUser($jwtPayload, null, $this->serializeLocalUser($user->fresh(['school']))),
            'auth_data' => [
                'provider' => 'local-admin-fallback',
            ],
        ];
    }

    public function validateToken(string $token): object
    {
        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            throw new \Exception('Token com formato inválido (' . count($segments) . ' segmentos)');
        }

        $jwks = Cache::remember('supabase_jwks', 3600, function () {
            $response = Http::withHeaders([
                'apikey' => config('services.supabase.public_key'),
            ])->get(config('services.supabase.url') . '/auth/v1/.well-known/jwks.json');

            if ($response->failed()) {
                throw new \Exception('Falha ao buscar JWKS: HTTP ' . $response->status());
            }

            return $response->json();
        });

        $keys = JWK::parseKeySet($jwks);

        return JWT::decode($token, $keys);
    }

    public function resolveLocalUser(object $jwtPayload): User
    {
        $supabaseId = (string) ($jwtPayload->sub ?? '');
        $supabaseEmail = mb_strtolower(trim((string) ($jwtPayload->email ?? '')));

        if ($supabaseId === '' || $supabaseEmail === '') {
            throw new \RuntimeException('Token do Supabase sem ID ou email.');
        }

        $user = User::query()
            ->with('school')
            ->where('auth_id', $supabaseId)
            ->first();

        if (! $user) {
            $user = User::query()
                ->with('school')
                ->find($supabaseId);
        }

        if (! $user) {
            $userWithSameEmail = User::query()
                ->with('school')
                ->whereRaw('LOWER(email) = ?', [$supabaseEmail])
                ->first();

            if ($userWithSameEmail) {
                if (blank($userWithSameEmail->auth_id)) {
                    $userWithSameEmail->forceFill([
                        'auth_id' => $supabaseId,
                    ])->save();

                    $user = $userWithSameEmail->fresh(['school']);
                } else {
                    throw new \RuntimeException('O email existe na tabela users, mas o auth_id não corresponde ao utilizador autenticado no Supabase.');
                }
            }
        }

        if (! $user) {
            throw new \RuntimeException('Utilizador autenticado no Supabase, mas inexistente na tabela users.');
        }

        if (mb_strtolower($user->email) !== $supabaseEmail) {
            throw new \RuntimeException('O email autenticado no Supabase não corresponde ao registo da tabela users.');
        }

        if (blank($user->auth_id)) {
            $user->forceFill([
                'auth_id' => $supabaseId,
            ])->save();
        }

        return $user;
    }

    public function makeSessionUser(object $jwtPayload, ?string $token = null): SupabaseUser
    {
        $user = $this->resolveLocalUser($jwtPayload);

        return new SupabaseUser($jwtPayload, $token, $this->serializeLocalUser($user));
    }

    public function serializeLocalUser(User $user): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role?->value ?? $user->role,
            'school_id' => $user->school_id,
            'school_name' => $user->school?->name,
            'school_year' => $user->school_year,
        ];
    }
}
