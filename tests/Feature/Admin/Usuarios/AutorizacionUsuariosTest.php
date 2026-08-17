<?php

namespace Tests\Feature\Admin\Usuarios;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Role;
use Tests\Feature\Admin\BaseAdminTest;

class AutorizacionUsuariosTest extends BaseAdminTest
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // admin.usuarios.gestionar is already seeded by RolesSeeder
        Permission::firstOrCreate(
            ['name' => 'admin.dashboard.ver', 'guard_name' => 'web'],
            ['nombre' => 'Ver Dashboard', 'modulo' => 'Dashboard', 'creado_en' => now()]
        );
    }

    /**
     * Verifica que si un usuario entra al panel pero no tiene permiso de usuarios, reciba 403.
     */
    public function test_usuario_sin_permiso_gestionar_recibe_403(): void
    {
        // Creamos un admin que SOLO puede ver dashboard
        $adminLimitado = $this->crearAdmin();
        
        // Le damos solo el permiso de dashboard, explícitamente a su rol Admin
        $rolAdmin = Role::where('name', 'Admin')->first();
        $rolAdmin->syncPermissions(['admin.dashboard.ver']);
        
        // Intento de listar usuarios
        $responseListado = $this->actingAs($adminLimitado)
            ->get(route('admin.usuarios.index'));
        $responseListado->assertStatus(403);

        // Intento de crear un usuario
        $responseCrear = $this->actingAs($adminLimitado)
            ->post(route('admin.usuarios.store', $rolAdmin->id), [
                'nombre' => 'Test',
                'email' => 'test@example.com',
                'password' => '123',
                'rol_id' => $rolAdmin->id
            ]);
        $responseCrear->assertStatus(403);
    }

    /**
     * Verifica que para poder listar o modificar roles, se necesita un permiso explícito.
     * En FASE 18 usamos 'admin.usuarios.gestionar' para agrupar, así que si lo tiene,
     * debería poder entrar, y si no, 403.
     */
    public function test_administrador_regular_no_puede_modificar_roles_ni_permisos_sin_autorizacion(): void
    {
        $adminSinPermiso = $this->crearAdmin();
        $rolAdmin = Role::where('name', 'Admin')->first();
        $rolAdmin->syncPermissions([]); // Sin permisos

        // Intento de ver roles
        $responseRoles = $this->actingAs($adminSinPermiso)
            ->get(route('admin.usuarios.roles-permisos', $rolAdmin->id));
        $responseRoles->assertStatus(403);

        // Le asignamos el permiso
        $rolAdmin->givePermissionTo('admin.usuarios.gestionar');

        // Intento de ver roles (ahora con permiso)
        $responseRolesOk = $this->actingAs($adminSinPermiso)
            ->get(route('admin.usuarios.roles-permisos', $rolAdmin->id));
        $responseRolesOk->assertStatus(200);
    }
}
