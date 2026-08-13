<?php

namespace Tests\Feature\Envios;

use App\Models\Devolucion;
use App\Models\ItemPedido;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\VarianteProducto;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de INTEGRACIÓN entre DEVOLUCIONES e INVENTARIO (FASE 13 + FASE 14).
 *
 * La integración SÍ existe en el código actual: al aprobar una devolución
 * (POST /admin/devoluciones/{id}/aprobar), DevolucionController llama a
 * InventarioService::registrarEntradaPorDevolucion, que registra un movimiento
 * de inventario tipo 'entrada' por cada ítem del pedido e incrementa el stock.
 *
 * Complementa tests/Feature/Inventario/DevolucionInventarioTest.php:
 * aquí se cubren pedidos con varios ítems, variantes, el estado del pedido
 * ('devolucion_aprobada') y la autorización de cliente.
 */
class IntegracionDevolucionInventarioTest extends BaseAdminTest
{
    #[Test]
    public function una_devolucion_aprobada_incrementa_el_stock_del_producto(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, null, 3);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);

        $this->assertSame(10, $producto->fresh()->stock);
    }

    #[Test]
    public function una_devolucion_aprobada_incrementa_el_stock_de_la_variante(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 4]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, $variante, 2);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);

        $this->assertSame(6, $variante->fresh()->stock);
        // El stock del producto base no se toca cuando el ítem usa variante.
        $this->assertSame(99, $producto->fresh()->stock);
    }

    #[Test]
    public function una_devolucion_aprobada_genera_un_movimiento_de_entrada_por_cada_item(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $productoA = Producto::factory()->create(['precio' => 30.00, 'stock' => 8]);
        $productoB = Producto::factory()->create(['precio' => 20.00, 'stock' => 6]);
        $pedido = $this->crearPedidoConDosItems($cliente, $productoA, $productoB);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);

        // 2 movimientos de entrada, uno por cada ítem, vinculados al pedido.
        $this->assertSame(
            2,
            MovimientoInventario::where('pedido_id', $pedido->id)->where('tipo', 'entrada')->count()
        );

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $productoA->id,
            'pedido_id'   => $pedido->id,
            'tipo'        => 'entrada',
            'cantidad'    => 2,
            'stock_antes' => 8,
            'stock_despues' => 10,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $productoB->id,
            'pedido_id'   => $pedido->id,
            'tipo'        => 'entrada',
            'cantidad'    => 4,
            'stock_antes' => 6,
            'stock_despues' => 10,
        ]);
    }

    #[Test]
    public function una_devolucion_aprobada_registra_el_estado_devolucion_aprobada_en_el_pedido(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, null, 3);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);

        // La devolución queda aprobada con comentario y fecha.
        $devolucion->refresh();
        $this->assertSame('aprobada', $devolucion->estado);
        $this->assertNotNull($devolucion->aprobado_en);

        // El pedido registra el estado 'devolucion_aprobada' con el admin como responsable.
        $this->assertDatabaseHas('estados_pedido', [
            'pedido_id'  => $pedido->id,
            'usuario_id' => $admin->id,
            'estado'     => 'devolucion_aprobada',
        ]);
    }

    #[Test]
    public function una_devolucion_rechazada_no_genera_movimientos_ni_modifica_el_stock(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, null, 3);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->actingAs($admin)
            ->from('/admin/devoluciones')
            ->post(route('admin.devoluciones.rechazar', $devolucion->id), [
                'comentario_admin' => 'La evidencia no es suficiente.',
            ])
            ->assertRedirect('/admin/devoluciones')
            ->assertSessionHas('toast_success');

        $devolucion->refresh();
        $this->assertSame('rechazada', $devolucion->estado);
        $this->assertSame('La evidencia no es suficiente.', $devolucion->comentario_admin);

        $this->assertSame(7, $producto->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::where('pedido_id', $pedido->id)->count());

        // El pedido registra el estado 'devolucion_rechazada'.
        $this->assertDatabaseHas('estados_pedido', [
            'pedido_id' => $pedido->id,
            'estado'    => 'devolucion_rechazada',
        ]);
    }

    #[Test]
    public function rechazar_una_devolucion_requiere_comentario_del_admin(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, null, 3);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->actingAs($admin)
            ->post(route('admin.devoluciones.rechazar', $devolucion->id), [])
            ->assertSessionHasErrors('comentario_admin');

        $this->assertSame('pendiente', $devolucion->fresh()->estado);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[Test]
    public function un_cliente_no_puede_aprobar_ni_rechazar_devoluciones(): void
    {
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, null, 3);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->actingAs($cliente)
            ->post(route('admin.devoluciones.aprobar', $devolucion->id))
            ->assertForbidden();

        $this->actingAs($cliente)
            ->post(route('admin.devoluciones.rechazar', $devolucion->id), [
                'comentario_admin' => 'Intento de cliente.',
            ])
            ->assertForbidden();

        $this->assertSame('pendiente', $devolucion->fresh()->estado);
        $this->assertSame(0, MovimientoInventario::count());
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    protected function crearDevolucionPendiente(Usuario $cliente, Pedido $pedido): Devolucion
    {
        return Devolucion::create([
            'pedido_id'   => $pedido->id,
            'usuario_id'  => $cliente->id,
            'motivo'      => 'defectuoso',
            'descripcion' => 'Producto dañado al llegar.',
            'estado'      => 'pendiente',
        ]);
    }

    protected function crearPedidoConItem(Usuario $cliente, Producto $producto, ?VarianteProducto $variante, int $cantidad): Pedido
    {
        $pedido = Pedido::factory()->create([
            'usuario_id'    => $cliente->id,
            'numero_pedido' => '#PM-' . uniqid(),
        ]);

        ItemPedido::factory()->create([
            'pedido_id'            => $pedido->id,
            'producto_id'          => $producto->id,
            'variante_producto_id' => $variante?->id,
            'cantidad'             => $cantidad,
            'precio_unitario'      => 50.00,
            'subtotal'             => 50.00 * $cantidad,
        ]);

        return $pedido;
    }

    protected function crearPedidoConDosItems(Usuario $cliente, Producto $productoA, Producto $productoB): Pedido
    {
        $pedido = Pedido::factory()->create([
            'usuario_id'    => $cliente->id,
            'numero_pedido' => '#PM-' . uniqid(),
        ]);

        ItemPedido::factory()->create([
            'pedido_id'       => $pedido->id,
            'producto_id'     => $productoA->id,
            'cantidad'        => 2,
            'precio_unitario' => 30.00,
            'subtotal'        => 60.00,
        ]);

        ItemPedido::factory()->create([
            'pedido_id'       => $pedido->id,
            'producto_id'     => $productoB->id,
            'cantidad'        => 4,
            'precio_unitario' => 20.00,
            'subtotal'        => 80.00,
        ]);

        return $pedido;
    }

    protected function aprobarDevolucion(Usuario $admin, Devolucion $devolucion): void
    {
        $this->actingAs($admin)
            ->from('/admin/devoluciones')
            ->post(route('admin.devoluciones.aprobar', $devolucion->id))
            ->assertRedirect('/admin/devoluciones')
            ->assertSessionHas('toast_success');
    }
}
