<?php

namespace App\Auth;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;

class SupabaseUser implements Authenticatable, FilamentUser
{
    protected string $id;
    protected string $email;
    protected string $role;
    protected array $userMetadata;
    protected array $appMetadata;
    protected ?string $token;
    protected array $claims;
    protected array $localUser;

    public function __construct(object $jwtPayload, ?string $token = null, array $localUser = [])
    {
        $this->id = $jwtPayload->sub ?? '';
        $this->email = $jwtPayload->email ?? '';
        $this->role = $jwtPayload->role ?? 'authenticated';
        $this->userMetadata = (array) ($jwtPayload->user_metadata ?? []);
        $this->appMetadata = (array) ($jwtPayload->app_metadata ?? []);
        $this->token = $token;
        $this->claims = (array) $jwtPayload;
        $this->localUser = $localUser;
    }

    // --- Authenticatable interface ---

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // Supabase não usa remember token
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    // --- Helpers ---

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->localUser['email'] ?? $this->email;
    }

    public function getRole(): string
    {
        return $this->localUser['role'] ?? $this->role;
    }

    public function getNome(): string
    {
        return $this->localUser['name'] ?? $this->userMetadata['nome'] ?? $this->userMetadata['name'] ?? '';
    }

    public function getEscolaInstituicao(): string
    {
        return $this->localUser['school_name'] ?? $this->userMetadata['escola_instituicao'] ?? '';
    }

    public function getAnoEscolaridade(): ?int
    {
        return isset($this->localUser['school_year'])
            ? (int) $this->localUser['school_year']
            : $this->userMetadata['ano_escolaridade'] ?? null;
    }

    public function getUserMetadata(): array
    {
        return $this->userMetadata;
    }

    public function getAppMetadata(): array
    {
        return $this->appMetadata;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function getLocalUser(): array
    {
        return $this->localUser;
    }

    /**
     * Filament: verificar se o utilizador pode aceder ao painel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return filled($this->localUser['id'] ?? null);
    }

    /**
     * Permite aceder a propriedades como $user->email, $user->nome, etc.
     */
    public function __get(string $name): mixed
    {
        return $this->getAttributeValue($name);
    }

    // --- Eloquent-like methods required by Filament ---

    public function getAttributeValue(string $key): mixed
    {
        return match ($key) {
            'id' => $this->id,
            'email' => $this->getEmail(),
            'role' => $this->getRole(),
            'nome' => $this->getNome(),
            'name' => $this->getNome(),
            'escola_instituicao' => $this->getEscolaInstituicao(),
            'ano_escolaridade' => $this->getAnoEscolaridade(),
            'school_id' => $this->localUser['school_id'] ?? null,
            'school_name' => $this->localUser['school_name'] ?? null,
            'school_year' => $this->localUser['school_year'] ?? null,
            'token' => $this->token,
            'avatar_url' => null,
            default => $this->localUser[$key] ?? $this->userMetadata[$key] ?? $this->claims[$key] ?? null,
        };
    }

    public function getAttribute(string $key): mixed
    {
        return $this->getAttributeValue($key);
    }

    public function getKey(): string
    {
        return $this->id;
    }

    public function getKeyName(): string
    {
        return 'id';
    }

    public function getRouteKey(): string
    {
        return $this->id;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->getEmail(),
            'role' => $this->getRole(),
            'nome' => $this->getNome(),
            'name' => $this->getNome(),
            'escola_instituicao' => $this->getEscolaInstituicao(),
            'ano_escolaridade' => $this->getAnoEscolaridade(),
            'school_id' => $this->localUser['school_id'] ?? null,
            'school_name' => $this->localUser['school_name'] ?? null,
            'school_year' => $this->localUser['school_year'] ?? null,
        ];
    }
}
