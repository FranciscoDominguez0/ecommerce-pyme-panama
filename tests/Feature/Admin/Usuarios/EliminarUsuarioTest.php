<?php

namespace Tests\Feature\Admin\Usuarios;

use App\Models\Usuario;
use Tests\Feature\Admin\BaseAdminTest;

class EliminarUsuarioTest extends BaseAdminTest
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_puede_eliminar_usuario_normal(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();

        $response = $this->actingAs($admin)
            ->delete(route('admin.usuarios.destroy', $cliente->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast_success', 'Usuario eliminado correctamente.');
        $this->assertDatabaseMissing('usuarios', ['id' => $cliente->id]);
    }

    public function test_admin_no_puede_eliminarse_a_si_mismo(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)
            ->delete(route('admin.usuarios.destroy', $admin->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast_error', 'No puedes eliminar tu propia cuenta.');
        $this->assertDatabaseHas('usuarios', ['id' => $admin->id]);
    }

    public function test_admin_no_puede_eliminar_superadmin(): void
    {
        $admin = $this->crearAdmin();
        $superAdmin = $this->crearSuperAdmin();

        $response = $this->actingAs($admin)
            ->delete(route('admin.usuarios.destroy', $superAdmin->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast_error', 'No tienes permiso para eliminar un Super Administrador.');
        $this->assertDatabaseHas('usuarios', ['id' => $superAdmin->id]);
    }

    public function test_superadmin_puede_eliminar_superadmin_distinto(): void
    {
        $superAdmin1 = $this->crearSuperAdmin();
        $superAdmin2 = $this->crearSuperAdmin();

        $response = $this->actingAs($superAdmin1)
            ->delete(route('admin.usuarios.destroy', $superAdmin2->id));

        $response->assertRedirect();
        $response->assertSessionHas('toast_success', 'Usuario eliminado correctamente.');
        $this->assertDatabaseMissing('usuarios', ['id' => $superAdmin2->id]);
    }
}
