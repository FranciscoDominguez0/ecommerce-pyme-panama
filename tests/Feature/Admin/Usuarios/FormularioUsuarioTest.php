<?php

namespace Tests\Feature\Admin\Usuarios;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTest;

class FormularioUsuarioTest extends BaseAdminTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Verifica la creación exitosa de un usuario con todos sus datos válidos y rol.
     */
    public function test_puede_crear_usuario_con_datos_validos(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        $rolAdmin = Role::where('name', 'Admin')->first();

        $datos = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan.perez' . uniqid() . '@example.com',
            'password' => 'Secreto123!',
            'password_confirmation' => 'Secreto123!',
            'telefono' => '12345678',
            'rol_id' => $rolAdmin->id,
            'estado' => '1',
        ];

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.usuarios.store', $rolAdmin->id), $datos);

        $response->assertRedirect(route('admin.usuarios.por-rol', $rolAdmin->id));
        $response->assertSessionHas('toast_success', 'Usuario creado correctamente.');

        $this->assertDatabaseHas('usuarios', [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => $datos['email'],
            'activo' => true
        ]);

        $usuario = Usuario::where('email', $datos['email'])->first();
        $this->assertTrue($usuario->hasRole('Admin'));
    }

    /**
     * Verifica que el request falle si hay duplicidad o errores de validación.
     */
    public function test_falla_creacion_con_correo_duplicado_o_invalido(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        $rolAdmin = Role::where('name', 'Admin')->first();

        // Intento con datos faltantes e inválidos
        $datosInvalidos = [
            'nombre' => '',
            'email' => 'correo-invalido',
            'password' => '123',
            'password_confirmation' => 'abc',
            'rol_id' => 9999, // Rol inexistente
        ];

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.usuarios.store', $rolAdmin->id), $datosInvalidos);

        $response->assertSessionHasErrors(['nombre', 'email', 'password', 'rol_id']);

        // Intento con correo duplicado
        $datosDuplicados = [
            'nombre' => 'Ana',
            'email' => $superAdmin->email, // Email ya existe
            'password' => 'Secreto123!',
            'password_confirmation' => 'Secreto123!',
            'rol_id' => $rolAdmin->id,
        ];

        $response2 = $this->actingAs($superAdmin)
            ->post(route('admin.usuarios.store', $rolAdmin->id), $datosDuplicados);

        $response2->assertSessionHasErrors(['email']);
    }

    /**
     * Verifica la correcta actualización de datos y sincronización de rol sin duplicar.
     */
    public function test_puede_cambiar_rol_de_usuario_en_edicion(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        
        // El usuario arranca como cliente
        $usuario = $this->crearCliente();
        
        $this->assertTrue($usuario->hasRole('cliente'));
        $this->assertFalse($usuario->hasRole('Admin'));

        $rolAdmin = Role::where('name', 'Admin')->first();

        $datosActualizados = [
            'nombre' => 'Cliente Editado',
            'apellido' => $usuario->apellido,
            'telefono' => $usuario->telefono,
            'email' => $usuario->email,
            'rol_id' => $rolAdmin->id,
            'estado' => '1',
            // password vacío significa que no se cambia
        ];

        $response = $this->actingAs($superAdmin)
            ->put(route('admin.usuarios.update', $usuario->id), $datosActualizados);

        $response->assertRedirect(route('admin.usuarios.por-rol', $rolAdmin->id));
        $response->assertSessionHas('toast_success', 'Usuario actualizado correctamente.');

        $usuario->refresh();
        $this->assertEquals('Cliente Editado', $usuario->nombre);
        
        // Verifica la sincronización de roles (solo un rol activo)
        $this->assertFalse($usuario->hasRole('cliente'));
        $this->assertTrue($usuario->hasRole('Admin'));
        
        // Verifica directamente en model_has_roles para evitar caché de spatie
        $rolesCount = $usuario->roles()->count();
            
        $this->assertEquals(1, $rolesCount);
    }
}
