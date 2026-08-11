<?php

namespace Tests\Feature\Cliente;

use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Models\Usuario;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de la aplicación de cupones desde el carrito de cliente.
 *
 * Cubre la ruta real:
 *   POST /carrito/aplicar-cupon → CarritoController@aplicarCupon (cliente.carrito.aplicar-cupon)
 *
 * HALLAZGO: cliente/carrito.blade.php NO contiene el campo "Aplicar cupón" (ni input ni
 * form), aunque el endpoint existe y funciona. La respuesta JSON devuelve las claves
 * "valido/descuento/mensaje" (no "success/nuevo_total", que son de un endpoint muerto
 * en PromocionController sin rutas registradas).
 */
class AplicarCuponTest extends BaseAdminTest
{
    public function test_aplicar_un_cupon_valido_actualiza_el_total_del_carrito(): void
    {
        $cliente = $this->crearCliente();
        $carrito = $this->crearCarritoConProducto($cliente, 2, 100.00); // subtotal 200
        $cupon = Cupon::factory()->create(['codigo' => 'BIENVENIDO10', 'tipo' => 'porcentaje', 'valor' => 10, 'activo' => true]);

        $respuesta = $this->actingAs($cliente)
            ->postJson('/carrito/aplicar-cupon', ['codigo' => 'bienvenido10']);

        $respuesta->assertOk()
            ->assertJson([
                'valido' => true,
                'descuento' => 20.0,
            ]);

        $carrito->refresh();
        $this->assertSame($cupon->id, $carrito->cupon_id);
        $this->assertSame(20.0, (float) $carrito->descuento_aplicado);
    }

    public function test_aplicar_un_cupon_inexistente_muestra_error_y_no_aplica_descuento(): void
    {
        $cliente = $this->crearCliente();
        $carrito = $this->crearCarritoConProducto($cliente, 1, 100.00);

        $respuesta = $this->actingAs($cliente)
            ->postJson('/carrito/aplicar-cupon', ['codigo' => 'NOEXISTE']);

        $respuesta->assertStatus(422)
            ->assertJson([
                'valido' => false,
                'mensaje' => 'El código de cupón ingresado no existe.',
            ]);

        $carrito->refresh();
        $this->assertNull($carrito->cupon_id);
        $this->assertSame('0.00', (string) $carrito->descuento_aplicado);
    }

    public function test_aplicar_un_cupon_expirado_muestra_error_y_no_aplica_descuento(): void
    {
        $cliente = $this->crearCliente();
        $carrito = $this->crearCarritoConProducto($cliente, 1, 100.00);
        Cupon::factory()->expirado()->create(['codigo' => 'VENCIDO']);

        $respuesta = $this->actingAs($cliente)
            ->postJson('/carrito/aplicar-cupon', ['codigo' => 'VENCIDO']);

        $respuesta->assertStatus(422)
            ->assertJson([
                'valido' => false,
                'mensaje' => 'Este cupón ha expirado.',
            ]);

        $this->assertNull($carrito->fresh()->cupon_id);
    }

    public function test_aplicar_un_cupon_agotado_muestra_error_y_no_aplica_descuento(): void
    {
        $cliente = $this->crearCliente();
        $carrito = $this->crearCarritoConProducto($cliente, 1, 100.00);
        Cupon::factory()->agotado()->create(['codigo' => 'AGOTADO']);

        $respuesta = $this->actingAs($cliente)
            ->postJson('/carrito/aplicar-cupon', ['codigo' => 'AGOTADO']);

        $respuesta->assertStatus(422)
            ->assertJson(['valido' => false]);

        $this->assertNull($carrito->fresh()->cupon_id);
    }

    public function test_aplicar_un_cupon_inactivo_muestra_error_y_no_aplica_descuento(): void
    {
        $cliente = $this->crearCliente();
        $carrito = $this->crearCarritoConProducto($cliente, 1, 100.00);
        Cupon::factory()->inactivo()->create(['codigo' => 'INACTIVO']);

        $respuesta = $this->actingAs($cliente)
            ->postJson('/carrito/aplicar-cupon', ['codigo' => 'INACTIVO']);

        $respuesta->assertStatus(422)
            ->assertJson([
                'valido' => false,
                'mensaje' => 'Este cupón se encuentra inactivo.',
            ]);

        $this->assertNull($carrito->fresh()->cupon_id);
    }

    public function test_la_vista_del_carrito_no_renderiza_el_campo_de_aplicar_cupon(): void
    {
        // HALLAZGO: el endpoint POST /carrito/aplicar-cupon funciona, pero la vista
        // cliente/carrito.blade.php no expone ningún formulario/input "Aplicar cupón".
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/carrito')
            ->assertOk()
            ->assertDontSee('/carrito/aplicar-cupon', false);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Crea un carrito con un único producto (cantidad y precio indicados).
     */
    protected function crearCarritoConProducto(Usuario $usuario, int $cantidad, float $precio): Carrito
    {
        $producto = Producto::factory()->create(['precio' => $precio, 'stock' => 50]);

        $carrito = Carrito::create([
            'usuario_id' => $usuario->id,
            'descuento_aplicado' => 0.00,
        ]);

        ItemCarrito::create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
        ]);

        return $carrito;
    }
}
