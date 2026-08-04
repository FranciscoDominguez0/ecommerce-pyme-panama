<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    /**
     * Muestra el formulario para ingresar la nueva contraseña.
     */
    public function showResetForm(Request $request, ?string $token = null): View
    {
        return view('auth.reset-password', [
            'token' => $token ?? $request->route('token'),
            'email' => $request->email,
        ]);
    }

    /**
     * Alias para create.
     */
    public function create(Request $request): View
    {
        return $this->showResetForm($request, $request->route('token'));
    }

    /**
     * Valida el token y restablece la contraseña del usuario.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => 'El token de restablecimiento es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'password.required' => 'La nueva contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Validar existencia del registro
        if (!$record) {
            return back()->withErrors([
                'email' => 'No se encontró una solicitud activa de restablecimiento para este correo.',
            ])->onlyInput('email');
        }

        // Validar expiración (máximo 60 minutos)
        $tokenCreatedAt = Carbon::parse($record->created_at);
        if ($tokenCreatedAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors([
                'email' => 'El enlace de restablecimiento ha expirado. Por favor, solicita uno nuevo.',
            ])->onlyInput('email');
        }

        // Validar coincidencia del token (admite hash o texto plano)
        $tokenMatches = Hash::check($request->token, $record->token) || $request->token === $record->token;
        if (!$tokenMatches) {
            return back()->withErrors([
                'email' => 'El token de restablecimiento de contraseña no es válido.',
            ])->onlyInput('email');
        }

        // Actualizar contraseña en la tabla usuarios
        $usuario = Usuario::where('email', $request->email)->first();
        if ($usuario) {
            $usuario->password_hash = Hash::make($request->password);
            $usuario->save();
        }

        // Eliminar el token ya utilizado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with(
            'status',
            'Tu contraseña ha sido restablecida exitosamente. Ya puedes iniciar sesión con tu nueva clave.'
        );
    }

    /**
     * Alias para store.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->reset($request);
    }
}
