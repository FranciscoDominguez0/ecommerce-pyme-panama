<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Muestra el formulario para solicitar restablecimiento de contraseña.
     */
    public function mostrarFormularioSolicitud(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Alias para create.
     */
    public function create(): View
    {
        return $this->mostrarFormularioSolicitud();
    }

    /**
     * Envía el enlace con el token de recuperación al correo indicado.
     */
    public function enviarEnlaceRestablecimiento(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'Por favor, ingrese un correo electrónico válido.',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if ($usuario) {
            $token = Str::random(64);

            // Guardar o actualizar en la tabla password_reset_tokens
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $request->email,
            ]);

            $emailDestino = $request->email;
            \App\Services\CorreoService::despacharEnSegundoPlano(function () use ($usuario, $token, $emailDestino, $resetUrl) {
                Mail::send('emails.reset-password', [
                    'usuario' => $usuario,
                    'token' => $token,
                    'email' => $emailDestino,
                    'resetUrl' => $resetUrl,
                ], function ($message) use ($emailDestino) {
                    $message->to($emailDestino)
                            ->subject('Restablece tu contraseña - PayMe Panamá');
                });
            }, 'Recuperación de contraseña');
        }

        // Mensaje genérico de confirmación por seguridad
        return back()->with('status', 'Si el correo electrónico existe en nuestra base de datos, te hemos enviado un enlace para restablecer tu contraseña.');
    }

    /**
     * Alias para store.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->enviarEnlaceRestablecimiento($request);
    }
}
