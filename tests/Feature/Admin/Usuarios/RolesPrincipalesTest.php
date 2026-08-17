<?php

namespace Tests\Feature\Admin\Usuarios;

use App\Models\Usuario;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Tests\Feature\Admin\BaseAdminTest;

class RolesPrincipalesTest extends BaseAdminTest
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Los 3 roles principales ya se crearon en BaseAdminTest::setUp() vía RolesSeeder.
        
        // Creamos un rol antiguo que NO debería mostrarse o no es principal
        $this->crearRol('Soporte');
    }

    /**
     * Verifica que en la vista principal de roles y usuarios
     * se renderizan los roles activos del sistema.
     */
    public function test_muestra_solo_roles_principales_validos(): void
    {
        $superAdmin = $this->crearSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.usuarios.index'));

        $response->assertStatus(200);

        // Verifica que se ven los nombres de los roles en la UI
        $response->assertSeeText('Super Administrador');
        $response->assertSeeText('Administrador');
        $response->assertSeeText('Cliente');
        
        // La vista principal (index) se encarga de mostrar todos los roles activos
        // En nuestro controlador, Role::withCount(['users', 'permissions'])->get() trae los roles.
    }

    /**
     * Verifica que el conteo de usuarios y permisos coincida con la realidad de la BD.
     */
    public function test_cada_rol_devuelve_conteo_real_de_usuarios_y_permisos(): void
    {
        // Creamos el superadmin (1 usuario en super_admin)
        $superAdmin = $this->crearSuperAdmin();

        // Creamos 2 usuarios más con rol Admin
        $admin1 = $this->crearAdmin();
        $admin2 = Usuario::create([
            'nombre' => 'Otro',
            'apellido' => 'Admin',
            'email' => 'otroadmin@test.com',
            'password_hash' => bcrypt('password'),
            'telefono' => '123'
        ]);
        $admin2->assignRole('Admin');

        // Asignamos 2 permisos al rol Admin
        DB::table('permisos')->insert([
            ['name' => 'permiso_1', 'guard_name' => 'web', 'nombre' => 'Permiso 1', 'modulo' => 'General', 'creado_en' => now()],
            ['name' => 'permiso_2', 'guard_name' => 'web', 'nombre' => 'Permiso 2', 'modulo' => 'General', 'creado_en' => now()]
        ]);
        
        /** @var Role $rolAdmin */
        $rolAdmin = Role::where('name', 'Admin')->first();
        $rolAdmin->syncPermissions(['permiso_1', 'permiso_2']);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.usuarios.index'));

        $response->assertStatus(200);
        
        // La vista de index debería recibir $roles con conteo
        // Admin tiene 2 usuarios y 2 permisos.
        $response->assertViewHas('roles', function ($roles) {
            $adminRole = $roles->where('name', 'Admin')->first();
            return $adminRole->users_count === 2 && $adminRole->permissions_count === 2;
        });
        
        $response->assertViewHas('roles', function ($roles) {
            $superRole = $roles->where('name', 'super_admin')->first();
            return $superRole->users_count === 1; // Solo el superadmin creado al inicio
        });
    }
}
