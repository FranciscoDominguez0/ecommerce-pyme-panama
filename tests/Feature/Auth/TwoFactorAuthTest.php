<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;
use App\Mail\TwoFactorCodeMail;
use Spatie\Permission\PermissionRegistrar;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_login_intercepta_si_2fa_esta_habilitado()
    {
        $usuario = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'Test',
            'email' => 'test2fa@example.com',
            'password_hash' => Hash::make('password123'),
            'two_fa_habilitado' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'test2fa@example.com',
            'password' => 'password123',
        ]);

        // No debe autenticar al usuario todavía
        $this->assertGuest();
        
        // Debe redirigir al challenge 2FA
        $response->assertRedirect(route('2fa.challenge'));
        
        // Debe haber guardado el usuario temporalmente en sesión
        $response->assertSessionHas('2fa:user:id', $usuario->id);
        
        // Debe haberse enviado el correo con el código
        Mail::assertSent(TwoFactorCodeMail::class, function ($mail) use ($usuario) {
            return $mail->hasTo($usuario->email);
        });
        
        // Debe haber guardado un código en cache
        $this->assertNotNull(Cache::get('2fa_code_' . $usuario->id));
    }

    public function test_usuario_no_puede_ver_challenge_sin_sesion_temporal()
    {
        $response = $this->get(route('2fa.challenge'));
        $response->assertRedirect(route('login'));
    }

    public function test_verificacion_exitosa_inicia_sesion()
    {
        $usuario = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'Test',
            'email' => 'test2fa2@example.com',
            'password_hash' => Hash::make('password123'),
            'two_fa_habilitado' => true,
        ]);

        // Simular que pasó por el login interceptado
        $this->withSession(['2fa:user:id' => $usuario->id]);
        Cache::put('2fa_code_' . $usuario->id, '1234', now()->addMinutes(10));

        $response = $this->post(route('2fa.verify'), [
            'code' => '1234'
        ]);

        // Debe haber iniciado sesión
        $this->assertAuthenticatedAs($usuario);
        
        // Debe haber limpiado la sesión temporal y caché
        $response->assertSessionMissing('2fa:user:id');
        $this->assertNull(Cache::get('2fa_code_' . $usuario->id));
        
        // Redirige al inicio (dashboard)
        $response->assertRedirect(route('dashboard'));
    }

    public function test_verificacion_fallida_muestra_error_y_no_inicia_sesion()
    {
        $usuario = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'Test',
            'email' => 'test2fa3@example.com',
            'password_hash' => Hash::make('password123'),
            'two_fa_habilitado' => true,
        ]);

        $this->withSession(['2fa:user:id' => $usuario->id]);
        Cache::put('2fa_code_' . $usuario->id, '1234', now()->addMinutes(10));

        $response = $this->post(route('2fa.verify'), [
            'code' => '9999' // Código incorrecto
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['code' => 'El código ingresado es incorrecto o ha expirado.']);
        $this->assertNotNull(Cache::get('2fa_code_' . $usuario->id)); // El código correcto debe seguir en caché
    }

    public function test_rate_limiter_bloquea_tras_5_intentos_fallidos()
    {
        $usuario = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'Test',
            'email' => 'test2fa4@example.com',
            'password_hash' => Hash::make('password123'),
            'two_fa_habilitado' => true,
        ]);

        $this->withSession(['2fa:user:id' => $usuario->id]);
        Cache::put('2fa_code_' . $usuario->id, '1234', now()->addMinutes(10));

        // Limpiar el rate limiter por si acaso
        $key = '2fa_verify_' . $usuario->id . '_' . request()->ip();
        RateLimiter::clear($key);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('2fa.verify'), ['code' => '9999']);
        }

        $response = $this->post(route('2fa.verify'), ['code' => '9999']);
        $response->assertSessionHasErrors('code');
        $this->assertStringContainsString('Demasiados intentos fallidos', session('errors')->first('code'));
    }

    public function test_cancelar_limpia_sesion_y_redirige_a_login()
    {
        $usuario = Usuario::create([
            'nombre' => 'Test',
            'apellido' => 'Test',
            'email' => 'test2fa5@example.com',
            'password_hash' => Hash::make('password123'),
        ]);
        $this->withSession(['2fa:user:id' => $usuario->id]);
        Cache::put('2fa_code_' . $usuario->id, '1234', now()->addMinutes(10));

        $response = $this->post(route('2fa.cancel'));
        
        $response->assertRedirect(route('login'));
        $response->assertSessionMissing('2fa:user:id');
        $this->assertNull(Cache::get('2fa_code_' . $usuario->id));
    }
}
