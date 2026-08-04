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
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Alias para create.
     */
    public function create(): View
    {
        return $this->showLinkRequestForm();
    }

    /**
     * Envía el enlace con el token de recuperación al correo indicado.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
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

            try {
                Mail::send('emails.reset-password', [
                    'usuario' => $usuario,
                    'token' => $token,
                    'email' => $request->email,
                    'resetUrl' => $resetUrl,
                ], function ($message) use ($request) {
                    $message->to($request->email)
                            ->subject('Restablece tu contraseña - PayMe Panamá');
                });
            } catch (\Throwable $e) {
                // Registro silencioso en logs en caso de que el driver de correo no esté configurado localmente
                logger()->error('Error al enviar correo de recuperación: ' . $e->getMessage());
            }
        }

        // Mensaje genérico de confirmación por seguridad
        return back()->with('status', 'Si el correo electrónico existe en nuestra base de datos, te hemos enviado un enlace para restablecer tu contraseña.');
    }

    /**
     * Alias para store.
     */
    public function store(Request $request): RedirectResponse
    {
        return $this->sendResetLinkEmail($request);
    }
}
