<?php

namespace Tests\Feature\Admin\Usuarios;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Role;
use Tests\Feature\Admin\BaseAdminTest;

class SincronizacionPermisosTest extends BaseAdminTest
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Permisos agrupados por módulo. Algunos ya existen por RolesSeeder.
        Permission::updateOrCreate(['name' => 'admin.usuarios.gestionar', 'guard_name' => 'web'], ['modulo' => 'Usuarios', 'nombre' => 'Gestionar Usuarios', 'creado_en' => now()]);
        Permission::updateOrCreate(['name' => 'admin.roles.gestionar', 'guard_name' => 'web'], ['modulo' => 'Roles', 'nombre' => 'Gestionar Roles', 'creado_en' => now()]);
        Permission::updateOrCreate(['name' => 'admin.productos.ver', 'guard_name' => 'web'], ['modulo' => 'Catálogo', 'nombre' => 'Ver Productos', 'creado_en' => now()]);
    }

    /**
     * Verifica que la pantalla de asignación de permisos renderiza correctamente
     * leyendo los permisos desde la tabla permissions agrupados por módulo.
     */
    public function test_carga_permisos_agrupados_por_modulo_en_interfaz(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        /** @var Role $rolAdmin */
        $rolAdmin = Role::where('name', 'Admin')->first();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.usuarios.roles-permisos', $rolAdmin->id));

        $response->assertStatus(200);

        // Debería ver los nombres de los módulos definidos
        $response->assertSee('Usuarios', false);
        $response->assertSee('Roles', false);
        $response->assertSee('Catálogo', false);

        // Debería ver los slugs/nombres de los permisos
        $response->assertSee('admin.usuarios.gestionar');
        $response->assertSee('admin.roles.gestionar');
        $response->assertSee('admin.productos.ver');
        
        // Verifica que la vista pasa la variable de permisos agrupados
        $response->assertViewHas('modulos', function ($modulos) {
            return $modulos->has('Usuarios') && $modulos->has('Roles') && $modulos->has('Catálogo');
        });
    }

    /**
     * Verifica que al enviar un array de permisos, se sincronizan correctamente en la tabla
     * y los permisos anteriores se remueven (syncPermissions).
     */
    public function test_actualiza_permisos_de_rol_exitosamente_sin_duplicar(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        /** @var Role $rolAdmin */
        $rolAdmin = Role::where('name', 'Admin')->first();
        
        // Asignamos un permiso inicial
        $rolAdmin->givePermissionTo('admin.productos.ver');
        $this->assertTrue($rolAdmin->hasPermissionTo('admin.productos.ver'));

        // Vamos a mandar a reemplazarlo solo con 'admin.usuarios.gestionar'
        $datos = [
            'permisos' => [
                'admin.usuarios.gestionar',
                'admin.roles.gestionar'
            ]
        ];

        $response = $this->actingAs($superAdmin)
            ->from(route('admin.usuarios.roles-permisos', $rolAdmin->id))
            ->put(route('admin.usuarios.update-permisos', $rolAdmin->id), $datos);

        $response->assertRedirect(route('admin.usuarios.roles-permisos', $rolAdmin->id));
        $response->assertSessionHas('toast_success', 'Permisos actualizados correctamente.');

        $rolAdmin->refresh();

        // El permiso anterior debe desaparecer
        $this->assertFalse($rolAdmin->hasPermissionTo('admin.productos.ver'));
        
        // Los nuevos deben estar
        $this->assertTrue($rolAdmin->hasPermissionTo('admin.usuarios.gestionar'));
        $this->assertTrue($rolAdmin->hasPermissionTo('admin.roles.gestionar'));

        // Verificamos directamente mediante la relación para evitar cache y nombres de tabla quemados
        $conteoPermisos = $rolAdmin->permissions()->count();
            
        $this->assertEquals(2, $conteoPermisos);
    }

    /**
     * Verifica que si se intenta actualizar permisos de un rol que no existe, falla limpiamente.
     */
    public function test_retorna_error_controlado_al_configurar_rol_inexistente(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        
        $datos = [
            'permisos' => [
                'admin.usuarios.gestionar'
            ]
        ];

        $response = $this->actingAs($superAdmin)
            ->put(route('admin.usuarios.update-permisos', 9999), $datos);

        $response->assertStatus(404);
    }
}
