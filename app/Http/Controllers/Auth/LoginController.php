<?php

namespace App\Http\Controllers\Auth;

use App\Auth\SupabaseGuard;
use App\Http\Controllers\Controller;
use App\Services\SupabaseAuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function __construct(
        protected SupabaseAuthenticationService $supabaseAuth,
    ) {}

    /**
     * Mostrar formulário de login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('login');
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $auth = $this->supabaseAuth->authenticateWithPassword(
                $request->string('email')->toString(),
                $request->input('password'),
            );
        } catch (\RuntimeException $e) {
            Log::warning('Login bloqueado', [
                'message' => $e->getMessage(),
                'email' => $request->string('email')->toString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }

        /** @var SupabaseGuard $guard */
        $guard = Auth::guard('supabase');
        $guard->login($auth['user']);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Login efetuado com sucesso',
            'redirect' => '/dashboard',
            'token' => $auth['token'],
            'user' => $auth['user']->toArray(),
        ]);
    }

    /**
     * Logout — limpa sessão Laravel e retorna.
     */
    public function logout(Request $request): mixed
    {
        /** @var SupabaseGuard $guard */
        $guard = Auth::guard('supabase');
        $guard->logout();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect('/login');
    }
}
