<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Pruebas del módulo de RECUPERACIÓN DE CONTRASEÑA (FASE 4).
 *
 * Configuración de pruebas: Laravel carga `.env.testing` (APP_ENV=testing),
 * que apunta a la base de pruebas dedicada `ecommerce_test`. `RefreshDatabase`
 * ejecuta `migrate:fresh` contra esa base y envuelve cada prueba en una
 * transacción que se revierte — nunca toca los datos de desarrollo/producción.
 *
 * Nota sobre el correo: el controlador envía la plantilla `emails.reset-password`
 * con `Mail::send('vista', ...)` (un correo de vista, no un Mailable). Por eso
 * NO usamos `Mail::fake()` (que solo registra Mailables) sino que inspeccionamos
 * el transporte `array` configurado en phpunit.xml (MAIL_MAILER=array).
 */
class RecuperarContrasenaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea un usuario de prueba con una contraseña conocida.
     */
    protected function crearUsuario(array $atributos = []): Usuario
    {
        return Usuario::create(array_merge([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan.' . uniqid() . '@example.com',
            'password_hash' => Hash::make('secret123'),
            'telefono' => '60000000',
        ], $atributos));
    }

    /**
     * Crea una fila de token de restablecimiento para un correo dado.
     */
    protected function crearTokenEnBD(string $email, string $tokenPlano, ?\DateTimeInterface $creadoEn = null): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($tokenPlano),
                'created_at' => $creadoEn ?? now(),
            ]
        );
    }

    /**
     * Inserta un registro de token con una fecha de creación controlada.
     */
    protected function insertarTokenConFecha(string $email, string $tokenPlano, string $fechaCreacion): void
    {
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($tokenPlano),
            'created_at' => $fechaCreacion,
        ]);
    }

    /**
     * Recupera los correos "enviados" desde el transporte array.
     *
     * @return array<int, \Symfony\Component\Mime\Email>
     */
    protected function correosEnviados(): array
    {
        $transport = app('mailer')->getSymfonyTransport();
        if (! method_exists($transport, 'messages')) {
            return [];
        }

        return collect($transport->messages())
            ->map(fn ($mensaje) => $mensaje->getOriginalMessage())
            ->all();
    }

    /**
     * Devuelve los correos enviados al destinatario indicado.
     *
     * @return array<int, \Symfony\Component\Mime\Email>
     */
    protected function correosPara(string $email): array
    {
        return array_values(array_filter(
            $this->correosEnviados(),
            function ($correo) use ($email) {
                $destinatarios = $correo->getTo() ?? [];
                foreach ($destinatarios as $direccion) {
                    if ($direccion->getAddress() === $email) {
                        return true;
                    }
                }

                return false;
            }
        ));
    }

    // =====================================================================
    //  DISEÑO — Vista GET /forgot-password
    // =====================================================================

    public function test_la_vista_de_solicitud_de_recuperacion_se_renderiza(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Recuperar Contraseña')
            ->assertSee('PayMe Panamá');
    }

    public function test_la_vista_de_recuperacion_tiene_campo_de_correo(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('id="email"', false)
            ->assertSee('name="email"', false)
            ->assertSee('type="email"', false);
    }

    public function test_la_vista_de_recuperacion_tiene_boton_de_envio(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Enviar enlace de recuperación');
    }

    public function test_la_vista_de_recuperacion_usa_la_ruta_correcta(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('action="' . route('password.email') . '"', false)
            ->assertSee('name="_token"', false);
    }

    // =====================================================================
    //  SOLICITUD — POST /forgot-password
    // =====================================================================

    public function test_al_solicitar_recuperacion_se_genera_un_token_y_se_guarda(): void
    {
        $usuario = $this->crearUsuario();

        $this->post('/forgot-password', ['email' => $usuario->email]);

        $registro = DB::table('password_reset_tokens')->where('email', $usuario->email)->first();
        $this->assertNotNull($registro, 'No se creó el token en password_reset_tokens.');
        $this->assertNotEmpty($registro->token);
    }

    public function test_al_solicitar_recuperacion_se_envia_un_correo_con_el_enlace(): void
    {
        $usuario = $this->crearUsuario();

        $this->post('/forgot-password', ['email' => $usuario->email]);

        $correos = $this->correosPara($usuario->email);
        $this->assertCount(1, $correos, 'No se envió el correo de recuperación al usuario.');
        $correo = $correos[0];

        $this->assertStringContainsString('Restablece tu contraseña', $correo->getSubject() ?? '');
        $this->assertStringContainsString('reset-password/', $correo->toString());
    }

    public function test_el_correo_recibido_contiene_el_token_generado(): void
    {
        $usuario = $this->crearUsuario();

        $this->post('/forgot-password', ['email' => $usuario->email]);

        $correos = $this->correosPara($usuario->email);
        $this->assertCount(1, $correos);
        $correo = $correos[0];

        // El cuerpo viaja en quoted-printable: decodificamos antes de buscar el token.
        $cuerpo = quoted_printable_decode($correo->toString());
        preg_match('#/reset-password/([A-Za-z0-9]+)(?:\?|&|"|$)#', $cuerpo, $coincidencias);
        $this->assertNotEmpty($coincidencias[1] ?? '', 'El correo no contiene el token en el enlace.');

        $registro = DB::table('password_reset_tokens')->where('email', $usuario->email)->first();
        $this->assertTrue(Hash::check($coincidencias[1], $registro->token), 'El token del correo no coincide con el guardado.');
    }

    public function test_el_mensaje_de_confirmacion_es_generico_si_el_correo_no_existe(): void
    {
        $respuesta = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'no-existe@example.com',
        ]);

        $respuesta->assertRedirect('/forgot-password');
        $respuesta->assertSessionHas('status', 'Si el correo electrónico existe en nuestra base de datos, te hemos enviado un enlace para restablecer tu contraseña.');

        // No debe generarse token para un correo inexistente.
        $this->assertNull(DB::table('password_reset_tokens')->where('email', 'no-existe@example.com')->first());
    }

    public function test_el_mensaje_de_confirmacion_no_revela_si_el_correo_existe(): void
    {
        // Mismo mensaje para correo existente que para inexistente (seguridad).
        $usuario = $this->crearUsuario();

        $mensajeExistente = $this->obtenerMensajeDeEstado($usuario->email);
        $mensajeInexistente = $this->obtenerMensajeDeEstado('no-existe@example.com');

        $this->assertSame($mensajeExistente, $mensajeInexistente);
        $this->assertSame(
            'Si el correo electrónico existe en nuestra base de datos, te hemos enviado un enlace para restablecer tu contraseña.',
            $mensajeInexistente
        );
    }

    public function test_solicitar_recuperacion_con_correo_invalido_muestra_error(): void
    {
        $respuesta = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'no-es-un-correo',
        ]);

        $respuesta->assertSessionHasErrors([
            'email' => 'Por favor, ingrese un correo electrónico válido.',
        ]);
    }

    public function test_solicitar_recuperacion_requiere_correo(): void
    {
        $respuesta = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => '',
        ]);

        $respuesta->assertSessionHasErrors([
            'email' => 'El correo electrónico es requerido.',
        ]);
    }

    // =====================================================================
    //  RESTABLECIMIENTO — Vista GET /reset-password/{token} y POST /reset-password
    // =====================================================================

    public function test_la_vista_de_restablecimiento_se_renderiza_con_los_campos(): void
    {
        $this->get('/reset-password/token-de-prueba?email=juan@example.com')
            ->assertOk()
            ->assertSee('Restablecer Contraseña')
            ->assertSee('id="password"', false)
            ->assertSee('name="password"', false)
            ->assertSee('id="password_confirmation"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('Restablecer Contraseña');
    }

    public function test_una_contrasena_se_restablece_con_un_token_valido(): void
    {
        $usuario = $this->crearUsuario();
        $tokenPlano = 'token-valido-' . uniqid();
        $this->crearTokenEnBD($usuario->email, $tokenPlano);

        $respuesta = $this->from('/reset-password/' . $tokenPlano)->post('/reset-password', [
            'token' => $tokenPlano,
            'email' => $usuario->email,
            'password' => 'NuevaContraseña123',
            'password_confirmation' => 'NuevaContraseña123',
        ]);

        $respuesta->assertRedirect(route('login'));
        $respuesta->assertSessionHasNoErrors();

        // El hash de la contraseña se actualizó en la tabla usuarios.
        $usuario->refresh();
        $this->assertTrue(Hash::check('NuevaContraseña123', $usuario->password_hash));
        $this->assertFalse(Hash::check('secret123', $usuario->password_hash));

        // El token usado fue eliminado.
        $this->assertNull(DB::table('password_reset_tokens')->where('email', $usuario->email)->first());
    }

    public function test_un_token_invalido_es_rechazado(): void
    {
        $usuario = $this->crearUsuario();
        $this->crearTokenEnBD($usuario->email, 'token-correcto');

        $respuesta = $this->from('/reset-password/token-incorrecto')->post('/reset-password', [
            'token' => 'token-incorrecto',
            'email' => $usuario->email,
            'password' => 'NuevaContraseña123',
            'password_confirmation' => 'NuevaContraseña123',
        ]);

        $respuesta->assertSessionHasErrors([
            'email' => 'El token de restablecimiento de contraseña no es válido.',
        ]);

        // La contraseña no cambió.
        $usuario->refresh();
        $this->assertTrue(Hash::check('secret123', $usuario->password_hash));
    }

    public function test_un_token_expirado_es_rechazado(): void
    {
        $usuario = $this->crearUsuario();
        $tokenPlano = 'token-expirado';
        // El token caduca a los 60 minutos. Lo creamos con 61 minutos de antigüedad.
        $this->insertarTokenConFecha($usuario->email, $tokenPlano, now()->subMinutes(61)->toDateTimeString());

        $respuesta = $this->from('/reset-password/' . $tokenPlano)->post('/reset-password', [
            'token' => $tokenPlano,
            'email' => $usuario->email,
            'password' => 'NuevaContraseña123',
            'password_confirmation' => 'NuevaContraseña123',
        ]);

        $respuesta->assertSessionHasErrors([
            'email' => 'El enlace de restablecimiento ha expirado. Por favor, solicita uno nuevo.',
        ]);

        // La contraseña no cambió.
        $usuario->refresh();
        $this->assertTrue(Hash::check('secret123', $usuario->password_hash));
    }

    public function test_restablecer_con_token_inexistente_muestra_error(): void
    {
        $usuario = $this->crearUsuario();

        $respuesta = $this->from('/reset-password/token-cualquiera')->post('/reset-password', [
            'token' => 'token-cualquiera',
            'email' => $usuario->email,
            'password' => 'NuevaContraseña123',
            'password_confirmation' => 'NuevaContraseña123',
        ]);

        $respuesta->assertSessionHasErrors([
            'email' => 'No se encontró una solicitud activa de restablecimiento para este correo.',
        ]);
    }

    public function test_restablecer_requiere_nueva_contrasena_minima_de_8_caracteres(): void
    {
        $usuario = $this->crearUsuario();
        $tokenPlano = 'token-requisitos';
        $this->crearTokenEnBD($usuario->email, $tokenPlano);

        $respuesta = $this->post('/reset-password', [
            'token' => $tokenPlano,
            'email' => $usuario->email,
            'password' => 'corto',
            'password_confirmation' => 'corto',
        ]);

        $respuesta->assertSessionHasErrors([
            'password' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);
    }

    /**
     * Envía una solicitud de recuperación y devuelve el mensaje de estado de la sesión.
     */
    protected function obtenerMensajeDeEstado(string $email): ?string
    {
        $this->from('/forgot-password')->post('/forgot-password', ['email' => $email]);

        $sesion = app('session.store');
        if (! $sesion->isStarted()) {
            $sesion->start();
        }

        return $sesion->get('status');
    }
}
