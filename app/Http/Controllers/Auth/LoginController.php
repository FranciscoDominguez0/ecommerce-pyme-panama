<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorCodeMail;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Muestra la vista del formulario de inicio de sesión.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Alias para compatibilidad con convenciones de recursos.
     */
    public function create(): View
    {
        return $this->showLoginForm();
    }

    /**
     * Procesa el inicio de sesión del usuario.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'Por favor, ingrese un correo electrónico válido.',
            'password.required' => 'La contraseña es requerida.',
        ]);

        $ip = $request->ip();
        $throttleKey = strtolower($request->email).'|'.$ip;
        $lockKey = $throttleKey . ':lockout';
        $attemptsKey = $throttleKey . ':fail_count';

        $usuario = Usuario::where('email', $request->email)->first();

        // 1. Revisar si el usuario está bloqueado permanentemente en la BD
        if ($usuario && $usuario->bloqueado) {
            return $this->loginFailedResponse($request, 'Su cuenta ha sido inhabilitada por seguridad tras múltiples intentos fallidos. Por favor, comuníquese con soporte.');
        }

        // 2. Revisar si hay un bloqueo temporal activo
        if (Cache::has($lockKey)) {
            $expiresAt = Cache::get($lockKey);
            $seconds = max(0, $expiresAt - now()->timestamp);
            return $this->loginFailedResponse($request, "Demasiados intentos de acceso. Por favor intente nuevamente en {$seconds} segundos.");
        }

        // Validar credenciales contra la columna password_hash
        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            $attempts = (int) Cache::get($attemptsKey, 0) + 1;
            Cache::put($attemptsKey, $attempts, now()->addHours(24));
            
            $msg = 'Las credenciales proporcionadas no son válidas.';

            if ($attempts >= 9) {
                if ($usuario) {
                    $usuario->update([
                        'bloqueado' => true,
                        'motivo_bloqueo' => 'Demasiados intentos fallidos de inicio de sesión (9).',
                        'bloqueado_en' => now()
                    ]);
                    $msg = 'Su cuenta ha sido inhabilitada por seguridad tras múltiples intentos fallidos. Por favor, comuníquese con soporte.';
                } else {
                    Cache::put($lockKey, now()->addYears(1)->timestamp, now()->addYears(1));
                    $msg = 'Demasiados intentos. Bloqueado temporalmente.';
                }
            } elseif ($attempts == 6) {
                Cache::put($lockKey, now()->addMinutes(5)->timestamp, now()->addMinutes(5));
                $msg = 'Demasiados intentos de acceso. Por favor intente nuevamente en 5 minutos.';
            } elseif ($attempts == 3) {
                Cache::put($lockKey, now()->addMinutes(1)->timestamp, now()->addMinutes(1));
                $msg = 'Demasiados intentos de acceso. Por favor intente nuevamente en 1 minuto.';
            }

            return $this->loginFailedResponse($request, $msg);
        }

        // Login exitoso, limpiamos fallos
        Cache::forget($attemptsKey);
        Cache::forget($lockKey);

        // Si el usuario tiene 2FA habilitado, interceptamos el login
        if ($usuario->two_fa_habilitado) {
            \App\Http\Controllers\Auth\TwoFactorController::triggerChallenge($usuario, $request->boolean('remember'));

            // Redirigir a la pantalla de verificación
            if ($request->wantsJson()) {
                return response()->json([
                    'redirect' => route('2fa.challenge'),
                    'isAdmin' => false,
                    'is2fa' => true
                ]);
            }
            return redirect()->route('2fa.challenge');
        }

        // Iniciar sesión con soporte para "Recordarme" (Flujo normal sin 2FA)
        $sesionPreviaId = $request->session()->getId();
        Auth::login($usuario, $request->boolean('remember'));

        $request->session()->regenerate();

        // Fusionar carritos de la sesión de visitante y el usuario autenticado
        try {
            app(\App\Services\CarritoService::class)->fusionarCarritos($sesionPreviaId, $usuario->id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error al fusionar carrito en login: ' . $e->getMessage());
        }

        $intendedUrl = $request->session()->pull('url.intended');

        // Forzar carga de roles para evitar problemas de caché (Spatie) justo al iniciar sesión
        $usuario->load('roles');
        
        $request->session()->put('is_from_login', true);

        $esAdmin = $usuario->roles->whereIn('name', ['admin', 'Admin', 'super_admin', 'Administrador'])->isNotEmpty();

        if ($esAdmin) {
            // Un admin siempre debe ir al panel, a menos que el intendedUrl sea de admin
            $url = ($intendedUrl && str_contains($intendedUrl, '/admin')) ? $intendedUrl : '/admin/dashboard';

            if ($request->wantsJson()) {
                return response()->json(['redirect' => url($url), 'isAdmin' => true]);
            }
            return redirect()->to($url);
        }

        // Si es normal, NUNCA debería ir a una ruta de admin, forzamos dashboard si estaba yendo allá por error
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
     * Alias para procesar la petición de login.
     */
    public function store(Request $request)
    {
        return $this->login($request);
    }

    /**
     * Cierra la sesión activa del usuario.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Alias para logout.
     */
    public function destroy(Request $request)
    {
        return $this->logout($request);
    }

    /**
     * Helper para responder con error de login
     */
    protected function loginFailedResponse(Request $request, string $msg)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => $msg,
                'errors' => ['email' => [$msg]]
            ], 422);
        }
        return back()->withErrors([
            'email' => $msg,
        ])->onlyInput('email');
    }
}
