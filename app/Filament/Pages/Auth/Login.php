<?php

namespace App\Filament\Pages\Auth;

use App\Auth\SupabaseGuard;
use App\Services\SupabaseAuthenticationService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $supabaseAuth = app(SupabaseAuthenticationService::class);
        $data = $this->form->getState();

        $email = $data['email'];
        $password = $data['password'];

        try {
            $auth = $supabaseAuth->authenticateWithPassword($email, $password);
        } catch (\RuntimeException $e) {
            Log::warning('Supabase login blocked in Filament', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'data.email' => $e->getMessage(),
            ]);
        }

        /** @var SupabaseGuard $guard */
        $guard = Auth::guard('supabase');
        $guard->login($auth['user']);

        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }
}
