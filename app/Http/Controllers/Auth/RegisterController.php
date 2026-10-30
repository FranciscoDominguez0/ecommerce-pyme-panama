<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Muestra la vista del formulario de registro.
     */
    public function mostrarFormularioRegistro(): View
    {
        return view('auth.register');
    }

    /**
     * Alias para create.
     */
    public function create(): View
    {
        return $this->mostrarFormularioRegistro();
    }

    /**
     * Procesa la creación de un nuevo usuario en la base de datos.
     */
    public function registrar(Request $request): RedirectResponse
    {
        $reglas = [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ];

        // Se exige el token de Cloudflare Turnstile salvo en entorno de pruebas automatizadas o local
        if (!app()->environment(['local', 'testing'])) {
            $reglas['cf-turnstile-response'] = ['required', 'string'];
        }

        $request->validate($reglas, [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe contener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'terms.accepted' => 'Debe aceptar los términos y condiciones para registrarse.',
            'cf-turnstile-response.required' => 'Por favor, complete la verificación de seguridad.',
        ]);

        // Verificamos el token de Cloudflare Turnstile directamente
        $turnstileResponse = $request->input('cf-turnstile-response');
        if (!app()->environment(['local', 'testing']) || $turnstileResponse !== null) {
            $turnstileService = app(\App\Services\TurnstileService::class);
            if (!$turnstileService->verificar($turnstileResponse, $request->ip())) {
                return back()->withInput()->withErrors([
                    'cf-turnstile-response' => 'Error en la verificación de seguridad (Captcha). Por favor, inténtelo de nuevo.'
                ]);
            }
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
        ]);

        // Asignar automáticamente el rol de cliente
        try {
            $usuario->assignRole('cliente');
        } catch (\Throwable $e) {
            // Fallback directo en tabla usuario_roles. El rol se busca SIEMPRE por su
            // NAME (nunca por un id hardcodeado: los ids no son estables entre
            // entornos/resiembras). Ver RolesSeeder como fuente de verdad.
            $rolCliente = Role::where('name', 'cliente')->where('guard_name', 'web')->first();
            if ($rolCliente) {
                \Illuminate\Support\Facades\DB::table('usuario_roles')->insertOrIgnore([
                    'usuario_id' => $usuario->id,
                    'rol_id' => $rolCliente->id,
                    'model_type' => Usuario::class,
                    'asignado_en' => now(),
                ]);
            }
        }

        // Iniciar sesión automáticamente
        $sesionPreviaId = $request->session()->getId();
        Auth::login($usuario);
        $request->session()->regenerate();

        // Fusionar carritos de la sesión de visitante y el usuario registrado
        try {
            app(\App\Services\CarritoService::class)->fusionarCarritos($sesionPreviaId, $usuario->id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error al fusionar carrito en registro: ' . $e->getMessage());
        }

        // Enviar correo de bienvenida en segundo plano
        \App\Services\CorreoService::enviarEnSegundoPlano(
            $usuario,
            new \App\Mail\BienvenidaMailable($usuario),
            'Bienvenida nuevo cliente'
        );

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Alias para store.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->registrar($request);
    }
}
