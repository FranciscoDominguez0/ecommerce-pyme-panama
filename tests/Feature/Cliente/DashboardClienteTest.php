<?php

namespace Tests\Feature\Cliente;

use App\Models\Categoria;
use App\Models\Producto;
use Tests\Feature\Admin\BaseAdminTest;

class DashboardClienteTest extends BaseAdminTest
{
    public function test_dashboard_muestra_productos_principales_estables_y_recomendados(): void
    {
        $usuario = $this->crearCliente();

        $categoria = Categoria::factory()->create([
            'slug' => 'laptops-y-computadoras',
            'activo' => true,
            'exento_envio' => false,
        ]);

        Producto::factory()->count(12)->create([
            'categoria_id' => $categoria->id,
            'activo' => true,
            'es_digital' => false,
        ]);

        $response = $this->actingAs($usuario)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas('productos');
        $response->assertViewHas('recomendados');

        $productosCarga1 = $response->viewData('productos')->pluck('id')->all();

        // Segunda carga: los productos principales deben ser exactamente los mismos (no cambian al recargar)
        $response2 = $this->actingAs($usuario)->get(route('dashboard'));
        $productosCarga2 = $response2->viewData('productos')->pluck('id')->all();

        $this->assertSame($productosCarga1, $productosCarga2);
    }
}
