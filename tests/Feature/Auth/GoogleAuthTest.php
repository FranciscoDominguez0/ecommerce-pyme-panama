<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Roles básicos en español
        DB::table('roles')->updateOrInsert(
            ['name' => 'cliente', 'guard_name' => 'web'],
            ['nombre' => 'cliente', 'activo' => true, 'creado_en' => now(), 'actualizado_en' => now()]
        );
        DB::table('roles')->updateOrInsert(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['nombre' => 'admin', 'activo' => true, 'creado_en' => now(), 'actualizado_en' => now()]
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_redirigir_a_google_guarda_parametro_popup_en_sesion(): void
    {
        $response = $this->get(route('auth.google', ['popup' => 1]));

        $response->assertRedirect();
        $this->assertTrue(session('google_auth_is_popup'));
    }

    public function test_callback_de_google_autentica_usuario_y_retorna_script_de_cierre_de_popup(): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-id-12345');
        $socialiteUser->shouldReceive('getEmail')->andReturn('testgoogle@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Carlos Google');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');
        $socialiteUser->user = [
            'given_name' => 'Carlos',
            'family_name' => 'Google',
        ];

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertStatus(200);
        $this->assertAuthenticated();

        // Verifica que la respuesta contenga los mecanismos de comunicación para cerrar el popup y notificar a la ventana principal
        $response->assertSee('BroadcastChannel');
        $response->assertSee('payme_auth_google');
        $response->assertSee('payme_google_auth_result');
        $response->assertSee('window.close()');
        $response->assertSee('GOOGLE_AUTH_SUCCESS');
    }

    public function test_callback_de_google_para_admin_redirige_a_admin_dashboard(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Test',
            'email' => 'admin.google@example.com',
            'google_id' => 'google-admin-999',
            'password_hash' => null,
        ]);
        $usuario->assignRole('admin');

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-admin-999');
        $socialiteUser->shouldReceive('getEmail')->andReturn('admin.google@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Admin Test');
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);
        $socialiteUser->user = ['given_name' => 'Admin', 'family_name' => 'Test'];

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($usuario);
        $response->assertSee('admin/dashboard');
    }
}
