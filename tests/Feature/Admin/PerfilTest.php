<?php

namespace Tests\Feature\Admin;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Spatie\Permission\PermissionRegistrar;

class PerfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function crearUsuarioConRol(array $atributos = [], string $rol = 'admin'): Usuario
    {
        $usuario = Usuario::create(array_merge([
            'nombre' => 'Admin',
            'apellido' => 'Test',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password_hash' => Hash::make('password123'),
        ], $atributos));

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

        return $usuario;
    }

    public function test_admin_puede_ver_su_perfil()
    {
        $admin = $this->crearUsuarioConRol();

        $response = $this->actingAs($admin)->get(route('admin.perfil'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.perfil.index');
        $response->assertSee($admin->nombre);
    }

    public function test_admin_puede_actualizar_datos()
    {
        $admin = $this->crearUsuarioConRol();

        $response = $this->actingAs($admin)->put(route('admin.perfil.datos.update'), [
            'nombre' => 'NuevoNombre',
            'apellido' => 'NuevoApellido',
            'telefono' => '12345678',
            'fecha_nacimiento' => '1990-01-01',
        ]);

        $response->assertRedirect(route('admin.perfil'));
        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('usuarios', [
            'id' => $admin->id,
            'nombre' => 'NuevoNombre',
            'apellido' => 'NuevoApellido',
            'telefono' => '12345678',
        ]);
    }

    public function test_admin_puede_actualizar_password()
    {
        $admin = $this->crearUsuarioConRol([
            'password_hash' => Hash::make('password123')
        ]);

        $response = $this->actingAs($admin)->put(route('admin.perfil.password.update'), [
            'current_password' => 'password123',
            'password' => 'NuevaPassword123!',
            'password_confirmation' => 'NuevaPassword123!'
        ]);

        $response->assertRedirect(route('admin.perfil'));
        
        $admin->refresh();
        $this->assertTrue(Hash::check('NuevaPassword123!', $admin->password_hash));
    }

    public function test_admin_puede_activar_y_desactivar_2fa()
    {
        $admin = $this->crearUsuarioConRol([
            'two_fa_habilitado' => false
        ]);

        // Activar 2FA
        $response = $this->actingAs($admin)->put(route('admin.perfil.2fa.update'), [
            'two_fa_habilitado' => '1'
        ]);

        $response->assertRedirect(route('admin.perfil'));
        $admin->refresh();
        $this->assertTrue($admin->two_fa_habilitado);

        // Desactivar 2FA
        $response2 = $this->actingAs($admin)->put(route('admin.perfil.2fa.update'), [
            'two_fa_habilitado' => '0'
        ]);

        $response2->assertRedirect(route('admin.perfil'));
        $admin->refresh();
        $this->assertFalse($admin->two_fa_habilitado);
    }
}
