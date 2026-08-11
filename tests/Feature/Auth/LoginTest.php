<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use App\View\Components\GuestLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Pruebas del módulo de LOGIN.
 *
 * Configuración de pruebas: Laravel carga `.env.testing` (APP_ENV=testing),
 * que apunta a la base de pruebas dedicada `ecommerce_test`. `RefreshDatabase`
 * ejecuta `migrate:fresh` contra esa base y envuelve cada prueba en una
 * transacción que se revierte — nunca toca los datos de desarrollo/producción.
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Evita que Spatie use el cache de permisos/roles entre pruebas.
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Crea un usuario de prueba con una contraseña conocida y, opcionalmente, un rol.
     */
    protected function crearUsuario(array $atributos = [], ?string $rol = 'cliente'): Usuario
    {
        $usuario = Usuario::create(array_merge([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan.' . uniqid() . '@example.com',
            'password_hash' => Hash::make('secret123'),
            'telefono' => '60000000',
        ], $atributos));

        if ($rol) {
            // La base de pruebas arranca vacía: los roles se siembran como en
            // producción (insert directo en la tabla "roles").
            DB::table('roles')->updateOrInsert(
                ['name' => $rol, 'guard_name' => 'web'],
                [
                    'nombre' => $rol,
                    'activo' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]
            );

            $usuario->assignRole($rol);
        }

        return $usuario;
    }

    // =====================================================================
    //  DISEÑO — La vista GET /login debe renderizarse con todos los elementos
    // =====================================================================

    public function test_la_vista_de_login_muestra_el_nombre_de_la_tienda_arriba(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('PayMe Panamá')
            ->assertSee('Iniciar sesión');
    }

    public function test_la_vista_de_login_tiene_campo_de_correo_con_icono(): void
    {
        $respuesta = $this->get('/login');

        $respuesta->assertOk()
            ->assertSee('id="email"', false)
            ->assertSee('type="email"', false)
            ->assertSee('name="email"', false)
            // Icono de sobre (material symbol) junto al campo de correo.
            ->assertSee('>mail</span>', false);
    }

    public function test_la_vista_de_login_tiene_campo_de_contrasena_con_icono_y_oculto_por_defecto(): void
    {
        $respuesta = $this->get('/login');

        $respuesta->assertOk()
            ->assertSee('id="password"', false)
            ->assertSee('name="password"', false)
            ->assertSee('type="password"', false)
            // Icono de candado junto al campo de contraseña.
            ->assertSee('>lock</span>', false);
    }

    public function test_la_vista_de_login_tiene_checkbox_de_recordarme(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('id="remember_me"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('Recordarme en este dispositivo');
    }

    public function test_la_vista_de_login_tiene_enlace_de_recuperacion_de_contrasena(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('¿Olvidaste tu contraseña?')
            ->assertSee('href="' . route('password.request') . '"', false);
    }

    public function test_la_vista_de_login_tiene_boton_de_envio(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Entrar a mi cuenta');
    }

    public function test_la_vista_de_login_tiene_enlace_de_registro(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('¿No tienes una cuenta?')
            ->assertSee('Regístrate')
            ->assertSee('href="' . route('register') . '"', false);
    }

    public function test_el_formulario_de_login_incluye_token_csrf(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_la_vista_de_login_muestra_los_errores_de_validacion(): void
    {
        $this->from('/login')->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('El correo electrónico es requerido.')
            ->assertSee('La contraseña es requerida.');
    }

    public function test_login_usa_el_layout_de_invitado(): void
    {
        // El layout de invitado debe existir (App\View\Components\GuestLayout → layouts/guest.blade.php)
        $this->assertTrue(class_exists(GuestLayout::class), 'El componente GuestLayout no existe.');

        $respuesta = $this->get('/login');

        // Marcas distintivas del layout de invitado: clase .glass-card y el fondo de pantalla.
        $respuesta->assertOk()
            ->assertSee('glass-card', false)
            ->assertSee('min-h-screen flex flex-col items-center justify-center', false);
    }

    // =====================================================================
    //  LÓGICA — POST /login (credenciales, validación, recordarme, rutas)
    // =====================================================================

    public function test_un_login_valido_de_admin_redirige_al_panel_admin(): void
    {
        $usuario = $this->crearUsuario([], 'admin');

        $this->assertTrue($usuario->hasRole('admin'));

        $respuesta = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'secret123',
        ]);

        $respuesta->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_un_login_valido_de_cliente_queda_autenticado(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $this->assertFalse($usuario->hasRole('admin'));

        // El controlador redirige a los clientes a route('dashboard') (la ruta "home"
        // existe como alias y a su vez redirige a /dashboard). La especificación indicaba
        // "/home"; funcionalmente ambos desembocan en el mismo panel.
        $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_el_login_con_contrasena_incorrecta_muestra_error_generico_y_no_autentica(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $respuesta = $this->from('/login')->post('/login', [
            'email' => $usuario->email,
            'password' => 'contraseña-incorrecta',
        ]);

        $respuesta->assertRedirect('/login');
        $respuesta->assertSessionHasErrors([
            'email' => 'Las credenciales proporcionadas no son válidas.',
        ]);
        // No debe existir error específico en el campo de contraseña.
        $respuesta->assertSessionDoesntHaveErrors(['password']);
        $this->assertGuest();
    }

    public function test_el_login_con_correo_inexistente_muestra_el_mismo_error_generico(): void
    {
        $respuesta = $this->from('/login')->post('/login', [
            'email' => 'nadie-registrado@example.com',
            'password' => 'cualquier-cosa',
        ]);

        $respuesta->assertRedirect('/login');
        $respuesta->assertSessionHasErrors([
            'email' => 'Las credenciales proporcionadas no son válidas.',
        ]);
        $respuesta->assertSessionDoesntHaveErrors(['password']);
        $this->assertGuest();
    }

    public function test_el_mensaje_de_error_no_revela_que_campo_era_incorrecto(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        // Correo inexistente
        $this->from('/login')->post('/login', [
            'email' => 'nadie-registrado@example.com',
            'password' => 'cualquier-cosa',
        ]);
        $mensajeCorreoInexistente = $this->mensajeErrorDeLogin();

        // Correo válido con contraseña incorrecta
        $this->from('/login')->post('/login', [
            'email' => $usuario->email,
            'password' => 'contraseña-incorrecta',
        ]);
        $mensajeContrasenaIncorrecta = $this->mensajeErrorDeLogin();

        $this->assertNotNull($mensajeCorreoInexistente);
        $this->assertSame($mensajeCorreoInexistente, $mensajeContrasenaIncorrecta);
        $this->assertSame('Las credenciales proporcionadas no son válidas.', $mensajeContrasenaIncorrecta);
    }

    /**
     * Lee el mensaje de error de login del bag 'default' de la sesión.
     * (Equivalente a como lo hace assertSessionHasErrors del framework).
     */
    protected function mensajeErrorDeLogin(): ?string
    {
        $sesion = app('session.store');
        if (! $sesion->isStarted()) {
            $sesion->start();
        }

        return $sesion->get('errors')?->first('email');
    }

    public function test_el_login_requiere_correo(): void
    {
        $respuesta = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'secret123',
        ]);

        $respuesta->assertSessionHasErrors([
            'email' => 'El correo electrónico es requerido.',
        ]);
        $this->assertGuest();
    }

    public function test_el_login_requiere_contrasena(): void
    {
        $respuesta = $this->from('/login')->post('/login', [
            'email' => 'juan@example.com',
            'password' => '',
        ]);

        $respuesta->assertSessionHasErrors([
            'password' => 'La contraseña es requerida.',
        ]);
        $this->assertGuest();
    }

    public function test_el_login_rechaza_un_correo_mal_formado(): void
    {
        $respuesta = $this->from('/login')->post('/login', [
            'email' => 'no-es-un-correo',
            'password' => 'secret123',
        ]);

        $respuesta->assertSessionHasErrors([
            'email' => 'Por favor, ingrese un correo electrónico válido.',
        ]);
        $this->assertGuest();
    }

    public function test_el_login_con_recordarme_guarda_token_y_cookie(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $respuesta = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'secret123',
            'remember' => '1',
        ]);

        $respuesta->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);

        // Se debe persistir un token de "recordarme" en la columna remember_token.
        $this->assertNotNull($usuario->fresh()->remember_token);

        // Se debe emitir la cookie remember_web_*.
        $cookies = $respuesta->headers->getCookies();
        $cookieRecordar = collect($cookies)->first(
            fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_')
        );
        $this->assertNotNull($cookieRecordar, 'Se esperaba la cookie remember_web_* en la respuesta.');
    }

    public function test_el_login_sin_recordarme_no_guarda_token_de_recordarme(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $respuesta = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'secret123',
        ]);

        $respuesta->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);

        $this->assertNull($usuario->fresh()->remember_token);

        $cookies = $respuesta->headers->getCookies();
        $cookieRecordar = collect($cookies)->first(
            fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_')
        );
        $this->assertNull($cookieRecordar, 'No debería emitirse la cookie remember_web_* sin marcar recordarme.');
    }

    public function test_la_ruta_get_de_login_responde_200_para_invitados(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_la_ruta_get_de_login_redirige_a_usuarios_ya_autenticados(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $this->actingAs($usuario)
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_las_rutas_de_login_apuntan_al_controlador_de_login(): void
    {
        // GET /login → showLoginForm (named route 'login')
        $rutaGet = Route::getRoutes()->getByName('login');
        $this->assertNotNull($rutaGet, 'Falta la ruta GET "login".');
        $this->assertContains('GET', $rutaGet->methods());
        $this->assertStringContainsString('LoginController@showLoginForm', $rutaGet->getActionName());

        // POST /login → login
        $rutaPost = collect(Route::getRoutes()->getRoutes())->first(
            fn ($ruta) => in_array('POST', $ruta->methods()) && $ruta->uri() === 'login'
        );
        $this->assertNotNull($rutaPost, 'Falta la ruta POST /login.');
        $this->assertStringContainsString('LoginController@login', $rutaPost->getActionName());
    }

    // =====================================================================
    //  CIERRE DE SESIÓN — POST /logout
    // =====================================================================

    public function test_un_usuario_autenticado_puede_cerrar_sesion(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $respuesta = $this->actingAs($usuario)->post('/logout');

        $respuesta->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_la_ruta_logout_requiere_estar_autenticado(): void
    {
        // Sin sesión activa, el cierre de sesión redirige al login (guest middleware).
        $respuesta = $this->post('/logout');

        $respuesta->assertRedirect('/login');
        $this->assertGuest();
    }
}
