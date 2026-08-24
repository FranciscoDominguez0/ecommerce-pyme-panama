<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    /**
     * Muestra la vista de desafío de 2FA.
     */
    public function showChallenge(Request $request)
    {
        if (!session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-challenge');
    }

    /**
     * Verifica el código ingresado.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:4'],
        ], [
            'code.required' => 'El código es requerido.',
            'code.size' => 'El código debe tener exactamente 4 dígitos.',
        ]);

        $userId = session('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $key = '2fa_attempts_' . request()->ip();

        // Limitar a 5 intentos por minuto
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'code' => "Demasiados intentos fallidos. Por favor intente de nuevo en {$seconds} segundos.",
            ]);
        }

        $cachedCode = Cache::get('2fa_code_' . $userId);

        if (!$cachedCode || $cachedCode !== $request->code) {
            RateLimiter::hit($key);
            
            $msg = 'El código ingresado es incorrecto o ha expirado.';
            if ($request->wantsJson()) {
                return response()->json(['errors' => ['code' => [$msg]]], 422);
            }
            throw ValidationException::withMessages([
                'code' => $msg,
            ]);
        }

        // Código correcto: limpiar caché y sesión 2FA
        RateLimiter::clear($key);
        Cache::forget('2fa_code_' . $userId);

        $usuario = Usuario::find($userId);
        $remember = session('2fa:remember', false);
        
        session()->forget(['2fa:user:id', '2fa:remember']);

        if (!$usuario) {
            return redirect()->route('login');
        }

        // Realizar el login normal
        $sesionPreviaId = $request->session()->getId();
        Auth::login($usuario, $remember);

        $request->session()->regenerate();

        // Fusionar carritos de la sesión de visitante y el usuario autenticado
        try {
            app(\App\Services\CarritoService::class)->fusionarCarritos($sesionPreviaId, $usuario->id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error al fusionar carrito en login 2FA: ' . $e->getMessage());
        }

        $intendedUrl = $request->session()->pull('url.intended');

        // Lógica de redirección basada en roles
        $usuario->load('roles');
        $esAdmin = $usuario->roles->whereIn('name', ['admin', 'Admin', 'super_admin', 'Administrador'])->isNotEmpty();

        if ($esAdmin) {
            if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
                $url = $intendedUrl;
            } else {
                $url = '/admin/dashboard';
            }
            if ($request->wantsJson()) {
                return response()->json(['redirect' => url($url), 'isAdmin' => true]);
            }
            return redirect()->to($url);
        }

        if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
            $intendedUrl = null;
        }

        $url = $intendedUrl ?? route('dashboard');
        if ($request->wantsJson()) {
            return response()->json(['redirect' => url($url), 'isAdmin' => false]);
        }
        return redirect()->to($url);
    }

    /**
     * Reenvía un nuevo código al correo del usuario.
     */
    public function resend(Request $request)
    {
        $userId = session('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $usuario = Usuario::find($userId);

        if (!$usuario) {
            return redirect()->route('login');
        }

        $key = '2fa_resend_' . $usuario->id;

        // Limitar reenvío a 1 vez por minuto
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('toast_warning', "Debes esperar {$seconds} segundos para reenviar el código.");
        }

        RateLimiter::hit($key, 60);

        // Generar nuevo código
        $code = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        
        Cache::put('2fa_code_' . $usuario->id, $code, now()->addMinutes(10));
        
        try {
            Mail::to($usuario->email)->send(new TwoFactorCodeMail($code, $usuario->nombre));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al reenviar correo de 2FA: ' . $e->getMessage());
        }

        return back()->with('toast_success', 'Se ha enviado un nuevo código a tu correo.');
    }

    /**
     * Cancela el inicio de sesión y limpia la sesión temporal de 2FA.
     */
    public function cancel(Request $request)
    {
        $userId = session('2fa:user:id');
        if ($userId) {
            Cache::forget('2fa_code_' . $userId);
            session()->forget('2fa:user:id');
        }

        return redirect()->route('login');
    }
}
