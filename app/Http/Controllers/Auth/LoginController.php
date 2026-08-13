<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
    public function login(Request $request): RedirectResponse
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
            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no son válidas.',
            ])->onlyInput('email');
        }

        // Iniciar sesión con soporte para "Recordarme"
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
            if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
                return redirect()->to($intendedUrl);
            }
            return redirect()->to('/admin/dashboard');
        }

        // Si es normal, NUNCA debería ir a una ruta de admin, forzamos dashboard si estaba yendo allá por error
        if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
            $intendedUrl = null;
        }

        return redirect()->to($intendedUrl ?? route('dashboard'));
    }

    /**
     * Alias para procesar la petición de login.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->login($request);
    }

    /**
     * Cierra la sesión activa del usuario.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Alias para logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        return $this->logout($request);
    }
}
