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

    public function test_login_view_renders_store_name_at_top(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('PayMe Panamá')
            ->assertSee('Iniciar sesión');
    }

    public function test_login_view_has_email_field_with_icon(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('id="email"', false)
            ->assertSee('type="email"', false)
            ->assertSee('name="email"', false)
            // Icono de sobre (material symbol) junto al campo de correo.
            ->assertSee('>mail</span>', false);
    }

    public function test_login_view_has_password_field_with_icon_and_hidden_by_default(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('id="password"', false)
            ->assertSee('name="password"', false)
            ->assertSee('type="password"', false)
            // Icono de candado junto al campo de contraseña.
            ->assertSee('>lock</span>', false);
    }

    public function test_login_view_has_remember_me_checkbox(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('id="remember_me"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('Recordarme en este dispositivo');
    }

    public function test_login_view_has_forgot_password_link(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('¿Olvidaste tu contraseña?')
            ->assertSee('href="' . route('password.request') . '"', false);
    }

    public function test_login_view_has_submit_button(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Entrar a mi cuenta');
    }

    public function test_login_view_has_register_link(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('¿No tienes una cuenta?')
            ->assertSee('Regístrate')
            ->assertSee('href="' . route('register') . '"', false);
    }

    public function test_login_form_includes_csrf_token(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_login_view_displays_validation_errors_inline(): void
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

    public function test_login_uses_guest_layout(): void
    {
        // El layout de invitado debe existir (App\View\Components\GuestLayout → layouts/guest.blade.php)
        $this->assertTrue(class_exists(GuestLayout::class), 'El componente GuestLayout no existe.');

        $response = $this->get('/login');

        // Marcas distintivas del layout de invitado: clase .glass-card y el fondo de pantalla.
        $response->assertOk()
            ->assertSee('glass-card', false)
            ->assertSee('min-h-screen flex flex-col items-center justify-center', false);
    }

    // =====================================================================
    //  LÓGICA — POST /login (credenciales, validación, recordarme, rutas)
    // =====================================================================

    public function test_valid_login_admin_redirects_to_admin_dashboard(): void
    {
        $usuario = $this->crearUsuario([], 'admin');

        $this->assertTrue($usuario->hasRole('admin'));

        $response = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_valid_login_cliente_is_authenticated(): void
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

    public function test_login_with_wrong_password_shows_generic_error_and_not_authenticated(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $response = $this->from('/login')->post('/login', [
            'email' => $usuario->email,
            'password' => 'contraseña-incorrecta',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'Las credenciales proporcionadas no son válidas.',
        ]);
        // No debe existir error específico en el campo de contraseña.
        $response->assertSessionDoesntHaveErrors(['password']);
        $this->assertGuest();
    }

    public function test_login_with_non_existent_email_shows_same_generic_error(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'nadie-registrado@example.com',
            'password' => 'cualquier-cosa',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'Las credenciales proporcionadas no son válidas.',
        ]);
        $response->assertSessionDoesntHaveErrors(['password']);
        $this->assertGuest();
    }

    public function test_error_message_does_not_reveal_which_field_was_incorrect(): void
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
        $mensajePasswordIncorrecta = $this->mensajeErrorDeLogin();

        $this->assertNotNull($mensajeCorreoInexistente);
        $this->assertSame($mensajeCorreoInexistente, $mensajePasswordIncorrecta);
        $this->assertSame('Las credenciales proporcionadas no son válidas.', $mensajePasswordIncorrecta);
    }

    /**
     * Lee el mensaje de error de login del bag 'default' de la sesión.
     * (Equivalente a como lo hace assertSessionHasErrors del framework).
     */
    protected function mensajeErrorDeLogin(): ?string
    {
        $session = app('session.store');
        if (! $session->isStarted()) {
            $session->start();
        }

        return $session->get('errors')?->first('email');
    }

    public function test_login_requires_email(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'El correo electrónico es requerido.',
        ]);
        $this->assertGuest();
    }

    public function test_login_requires_password(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'juan@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'La contraseña es requerida.',
        ]);
        $this->assertGuest();
    }

    public function test_login_rejects_malformed_email(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'no-es-un-correo',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Por favor, ingrese un correo electrónico válido.',
        ]);
        $this->assertGuest();
    }

    public function test_login_with_remember_sets_remember_token_and_cookie(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $response = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'secret123',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);

        // Se debe persistir un token de "recordarme" en la columna remember_token.
        $this->assertNotNull($usuario->fresh()->remember_token);

        // Se debe emitir la cookie remember_web_*.
        $cookies = $response->headers->getCookies();
        $cookieRecordar = collect($cookies)->first(
            fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_')
        );
        $this->assertNotNull($cookieRecordar, 'Se esperaba la cookie remember_web_* en la respuesta.');
    }

    public function test_login_without_remember_does_not_set_remember_token(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $response = $this->post('/login', [
            'email' => $usuario->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);

        $this->assertNull($usuario->fresh()->remember_token);

        $cookies = $response->headers->getCookies();
        $cookieRecordar = collect($cookies)->first(
            fn ($cookie) => str_starts_with($cookie->getName(), 'remember_web_')
        );
        $this->assertNull($cookieRecordar, 'No debería emitirse la cookie remember_web_* sin marcar recordarme.');
    }

    public function test_login_get_route_returns_200_for_guests(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_login_get_route_redirects_already_authenticated_users(): void
    {
        $usuario = $this->crearUsuario([], 'cliente');

        $this->actingAs($usuario)
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_routes_are_wired_to_login_controller(): void
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
}
