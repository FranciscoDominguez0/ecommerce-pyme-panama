<?php

namespace Tests\Feature\Admin\Usuarios;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTest;

class UsuariosPorRolTest extends BaseAdminTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Verifica que al entrar al detalle de un rol, solo salgan usuarios de ese rol.
     */
    public function test_lista_solo_usuarios_que_pertenecen_al_rol(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();

        $rolAdmin = Role::where('name', 'Admin')->first();

        // Entramos a ver el detalle del rol Admin
        $response = $this->actingAs($superAdmin)
            ->get(route('admin.usuarios.por-rol', $rolAdmin->id));

        $response->assertStatus(200);

        // El usuario admin debería estar visible
        $response->assertSee($admin->email);
        
        // Los usuarios super_admin y cliente NO deberían estar visibles
        $response->assertDontSee($superAdmin->email);
        $response->assertDontSee($cliente->email);
        
        $response->assertViewHas('usuarios', function ($usuarios) use ($admin, $superAdmin) {
            return $usuarios->contains($admin) && !$usuarios->contains($superAdmin);
        });
    }

    /**
     * Verifica que si un rol no tiene usuarios asociados, se muestra el empty state.
     */
    public function test_muestra_estado_vacio_si_el_rol_no_tiene_usuarios(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        
        // El rol 'cliente' no tiene usuarios aún
        $rolCliente = Role::where('name', 'cliente')->first();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.usuarios.por-rol', $rolCliente->id));

        $response->assertStatus(200);
        
        // Busca texto clave del empty state
        $response->assertSee('No hay usuarios asignados a este rol');
        $response->assertSee('Crea un usuario nuevo');
        
        // Verifica que la tabla no se está renderizando (no debería haber <thead> con 'Usuario', 'Estado', etc.)
        // Asumiendo que el thead se oculta si la colección está vacía.
        $response->assertViewHas('usuarios', function ($usuarios) {
            return $usuarios->isEmpty();
        });
    }

    /**
     * Verifica que el estado vacío desaparece cuando se añade el primer usuario.
     */
    public function test_estado_vacio_desaparece_al_crear_usuario(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        $rolCliente = Role::where('name', 'cliente')->first();

        // Creamos un usuario cliente
        $cliente = $this->crearCliente();

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.usuarios.por-rol', $rolCliente->id));

        $response->assertStatus(200);
        
        // El empty state ya no debería estar
        $response->assertDontSee('Crea un usuario nuevo para asignarle este rol');
        
        // Debería mostrar la fila de la tabla del usuario
        $response->assertSee($cliente->email);
    }
}
