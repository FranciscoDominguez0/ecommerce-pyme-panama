<?php

namespace Tests\Feature\Cliente;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Spatie\Permission\PermissionRegistrar;

class PerfilClienteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->artisan('db:seed', ['--class' => 'RolesSeeder']);
    }

    public function test_cliente_puede_activar_y_desactivar_2fa()
    {
        $cliente = Usuario::create([
            'nombre' => 'Cliente',
            'apellido' => 'Test',
            'email' => 'cliente_test1@example.com',
            'password_hash' => Hash::make('password123'),
            'two_fa_habilitado' => false
        ]);
        $cliente->assignRole('cliente');

        $response = $this->actingAs($cliente)->put(route('cliente.perfil.2fa.update'), [
            'two_fa_habilitado' => '1'
        ]);

        $response->assertRedirect();
        $cliente->refresh();
        $this->assertTrue($cliente->two_fa_habilitado);

        $response2 = $this->actingAs($cliente)->put(route('cliente.perfil.2fa.update'), [
            'two_fa_habilitado' => '0'
        ]);

        $response2->assertRedirect();
        $cliente->refresh();
        $this->assertFalse($cliente->two_fa_habilitado);
    }
}
