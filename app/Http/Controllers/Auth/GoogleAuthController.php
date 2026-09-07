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
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtiene la información del usuario desde Google.
     */
    public function callback()
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

            // Iniciar sesión
            Auth::login($usuario, true);

            // Redirigir al home o donde corresponda
            return redirect()->intended(route('home'));

        } catch (\Exception $e) {
            // En caso de error o cancelación, redirigir al login con un mensaje
            return redirect()->route('login')->withErrors([
                'email' => 'Ocurrió un error al intentar iniciar sesión con Google: ' . $e->getMessage(),
            ]);
        }
    }
}
