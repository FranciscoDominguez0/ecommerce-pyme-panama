<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /**
     * Muestra la vista del formulario de registro.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Alias para create.
     */
    public function create(): View
    {
        return $this->showRegistrationForm();
    }

    /**
     * Procesa la creación de un nuevo usuario en la base de datos.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:usuarios,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe contener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'terms.accepted' => 'Debe aceptar los términos y condiciones para registrarse.',
        ]);

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
            // Fallback directo en tabla usuario_roles si fuera necesario
            \Illuminate\Support\Facades\DB::table('usuario_roles')->insertOrIgnore([
                'usuario_id' => $usuario->id,
                'rol_id' => 5, // ID rol cliente
                'model_type' => Usuario::class,
                'asignado_en' => now(),
            ]);
        }

        // Iniciar sesión automáticamente
        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Alias para store.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->register($request);
    }
}
