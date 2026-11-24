<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirige al usuario a la página de autenticación de Google.
     */
    public function redirigir(\Illuminate\Http\Request $request)
    {
        if ($request->has('popup') || $request->query('popup') === '1') {
            session()->put('google_auth_is_popup', true);
        } else {
            session()->forget('google_auth_is_popup');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtiene la información del usuario desde Google.
     */
    public function procesarCallback()
    {
        try {
            // FIX para entorno local Windows: Desactivar verificación SSL de cURL
            /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
            $driver = Socialite::driver('google');
            if (app()->environment('local')) {
                $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
            }
            $googleUser = $driver->user();

            // Buscar si ya existe un usuario con este correo
            $usuario = Usuario::where('email', $googleUser->getEmail())->first();

            if ($usuario) {
                // Si existe, actualizamos su google_id por si acaso no lo tenía
                if (!$usuario->google_id) {
                    $usuario->google_id = $googleUser->getId();
                    $usuario->save();
                }
            } else {
                // Si no existe, creamos un nuevo usuario
                $usuario = Usuario::create([
                    'nombre' => $googleUser->user['given_name'] ?? $googleUser->getName(),
                    'apellido' => $googleUser->user['family_name'] ?? '',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password_hash' => null, // Como permite nulos, lo dejamos en null
                    'foto_perfil_ruta' => $googleUser->getAvatar(), // Opcional, pero útil
                ]);
                
                // Asignar rol de cliente por defecto si utilizas Spatie Roles (Opcional, según tu lógica actual)
                $usuario->assignRole('cliente');
            }

            // Si el usuario tiene 2FA habilitado, interceptamos el login
            if ($usuario->two_fa_habilitado) {
                \App\Http\Controllers\Auth\TwoFactorController::iniciarDesafio($usuario, true);

                // Redirigir a la pantalla de verificación 2FA
                return $this->cerrarPopupYRedirigir(route('2fa.challenge'));
            }

            // Obtener el ID de la sesión antes de regenerarla para fusionar el carrito
            $sesionPreviaId = session()->getId();

            // Iniciar sesión (flujo normal sin 2FA)
            Auth::login($usuario, true);
            
            session()->regenerate();
            
            // Fusionar carritos de la sesión de visitante y el usuario autenticado
            try {
                app(\App\Services\CarritoService::class)->fusionarCarritos($sesionPreviaId, $usuario->id);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Error al fusionar carrito en Google Auth: ' . $e->getMessage());
            }

            session()->put('is_from_login', true);

            // Determinar la URL destino según el rol del usuario (Admin -> Dashboard Admin, Cliente -> Home/Intended)
            $intendedUrl = session()->pull('url.intended');
            $usuario->load('roles');
            $esAdmin = $usuario->roles->whereIn('name', ['admin', 'Admin', 'super_admin', 'Administrador'])->isNotEmpty();

            if ($esAdmin) {
                $url = ($intendedUrl && str_contains($intendedUrl, '/admin')) ? $intendedUrl : url('/admin/dashboard');
            } else {
                if ($intendedUrl && str_contains($intendedUrl, '/admin')) {
                    $intendedUrl = null;
                }
                $url = $intendedUrl ?? route('home');
            }

            return $this->cerrarPopupYRedirigir($url);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Error en Google Auth Callback: ' . $e->getMessage());
            return $this->cerrarPopupYRedirigir(route('login'), 'Ocurrió un error al intentar iniciar sesión con Google: ' . $e->getMessage());
        }
    }

    /**
     * Cierra el popup (si existe) y redirige la ventana padre o principal.
     * Utiliza BroadcastChannel, localStorage y window.opener para garantizar
     * comunicación fluida incluso en navegadores con aislamiento estricto como Brave.
     */
    private function cerrarPopupYRedirigir(string $url, ?string $error = null)
    {
        if ($error) {
            session()->flash('errors', (new \Illuminate\Support\MessageBag)->add('email', $error));
        }

        $payload = json_encode([
            'type' => $error ? 'GOOGLE_AUTH_ERROR' : 'GOOGLE_AUTH_SUCCESS',
            'url' => $url,
            'error' => $error,
            'timestamp' => round(microtime(true) * 1000),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

        $titulo = $error ? 'Error de autenticación' : 'Autenticación exitosa';
        $mensajeDesc = $error ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : 'Conectando con la ventana principal y cerrando...';

        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . $titulo . ' - PayMe Panamá</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px 24px;
            max-width: 360px;
            width: 100%;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid #E2E8F0;
        }
        .icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-success { background-color: #ECFDF5; color: #059669; }
        .icon-error { background-color: #FEF2F2; color: #DC2626; }
        .title { font-size: 17px; font-weight: 700; margin: 0 0 6px; }
        .desc { font-size: 13px; color: #64748B; line-height: 1.4; margin: 0 0 18px; }
        .btn {
            display: inline-block;
            background-color: #059669;
            color: #ffffff;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 18px;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
        }
        .btn:hover { background-color: #047857; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon ' . ($error ? 'icon-error' : 'icon-success') . '">
            ' . ($error ? '
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>' : '
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>') . '
        </div>
        <h1 class="title">' . ($error ? 'Error al iniciar sesión' : '¡Sesión iniciada!') . '</h1>
        <p class="desc" id="status-desc">' . $mensajeDesc . '</p>
        <button id="close-btn" class="btn" onclick="cerrarVentana()">' . ($error ? 'Volver al login' : 'Cerrar ventana') . '</button>
    </div>

    <script>
        const authData = ' . $payload . ';

        function comunicarYSalir() {
            // 1. Notificar por BroadcastChannel (mismo origen, funciona incluso sin window.opener)
            try {
                if ("BroadcastChannel" in window) {
                    const bc = new BroadcastChannel("payme_auth_google");
                    bc.postMessage(authData);
                    bc.close();
                }
            } catch (e) {}

            // 2. Notificar por localStorage (respaldo clave para navegadores como Brave con aislamiento estricto)
            try {
                localStorage.setItem("payme_google_auth_result", JSON.stringify(authData));
            } catch (e) {}

            // 3. Notificar directamente al opener si está accesible
            try {
                if (window.opener && !window.opener.closed) {
                    if (authData.type === "GOOGLE_AUTH_SUCCESS") {
                        if (typeof window.opener.handleGoogleAuthSuccess === "function") {
                            window.opener.handleGoogleAuthSuccess(authData.url);
                        } else {
                            window.opener.location.href = authData.url;
                        }
                    } else if (authData.type === "GOOGLE_AUTH_ERROR") {
                        if (typeof window.opener.handleGoogleAuthError === "function") {
                            window.opener.handleGoogleAuthError(authData.error);
                        } else {
                            window.opener.location.reload();
                        }
                    }
                }
            } catch (e) {}

            // 4. Intentar cerrar este popup inmediatamente
            try {
                window.close();
            } catch (e) {}

            // 5. Verificación de cierre diferida
            setTimeout(function() {
                const isPopup = (window.name === "GoogleSignIn") || (window.opener !== null);

                if (isPopup) {
                    // Si es popup pero el navegador no permitió window.close(), dejamos el botón de cerrar
                    const desc = document.getElementById("status-desc");
                    if (desc && authData.type === "GOOGLE_AUTH_SUCCESS") {
                        desc.textContent = "Ya puedes cerrar esta ventana para continuar en la aplicación.";
                    }
                } else {
                    // Si NO era popup, redirigimos la ventana actual
                    window.location.href = authData.url;
                }
            }, 600);
        }

        function cerrarVentana() {
            try {
                window.close();
            } catch (e) {}
            setTimeout(() => {
                window.location.href = authData.url;
            }, 200);
        }

        comunicarYSalir();
    </script>
</body>
</html>';

        return response($html);
    }
}
