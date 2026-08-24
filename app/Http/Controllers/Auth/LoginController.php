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

        $usuario = Usuario::where('email', $request->email)->first();

        // Validar credenciales contra la columna password_hash
        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            $msg = 'Las credenciales proporcionadas no son válidas.';
            if ($request->wantsJson()) {
                return response()->json(['errors' => ['email' => [$msg]]], 422);
            }
            return back()->withErrors([
                'email' => $msg,
            ])->onlyInput('email');
        }

        // Si el usuario tiene 2FA habilitado, interceptamos el login
        if ($usuario->two_fa_habilitado) {
            // Generar código numérico de 4 dígitos
            $code = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            
            // Guardar en caché por 10 minutos
            Cache::put('2fa_code_' . $usuario->id, $code, now()->addMinutes(10));
            
            // Guardar datos en la sesión temporalmente
            session([
                '2fa:user:id' => $usuario->id,
                '2fa:remember' => $request->boolean('remember')
            ]);
            
            // Enviar correo
            try {
                Mail::to($usuario->email)->send(new TwoFactorCodeMail($code, $usuario->nombre));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al enviar correo de 2FA: ' . $e->getMessage());
            }
            
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
}
