<?php

namespace Tests\Feature\Inventario;

use App\Models\Devolucion;
use App\Models\ItemPedido;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de INTEGRACIÓN entre devoluciones e inventario (FASE 14).
 *
 * La funcionalidad SÍ existe en el código actual: al aprobar una devolución
 * (POST /admin/devoluciones/{id}/aprobar), DevolucionController llama a
 * InventarioService::registrarEntradaPorDevolucion, que registra una ENTRADA
 * de stock por cada ítem del pedido y deja el movimiento vinculado al pedido.
 */
class DevolucionInventarioTest extends BaseAdminTest
{
    #[Test]
    public function una_devolucion_aprobada_genera_una_entrada_de_stock(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, 3, 50.00);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);

        // El stock vuelve de 7 a 10 (3 unidades reintegradas).
        $this->assertSame(10, $producto->fresh()->stock);
    }

    #[Test]
    public function una_devolucion_aprobada_genera_un_movimiento_de_entrada_por_item(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, 3, 50.00);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'   => $producto->id,
            'pedido_id'     => $pedido->id,
            'tipo'          => 'entrada',
            'cantidad'      => 3,
            'stock_antes'   => 7,
            'stock_despues' => 10,
            'usuario_id'    => $admin->id,
        ]);
    }

    #[Test]
    public function el_movimiento_de_la_devolucion_guarda_el_motivo_con_el_numero_de_pedido(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, 3, 50.00);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);

        $movimiento = MovimientoInventario::where('tipo', 'entrada')->where('pedido_id', $pedido->id)->first();
        $this->assertNotNull($movimiento);
        $this->assertStringContainsString('Devolución aprobada', $movimiento->motivo);
        $this->assertStringContainsString($pedido->numero_pedido, $movimiento->motivo);
        $this->assertStringContainsString('Devolución ID #' . $devolucion->id, $movimiento->notas);
    }

    #[Test]
    public function el_movimiento_de_la_devolucion_queda_vinculado_al_usuario_admin_que_la_aprobo(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, 3, 50.00);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);

        $movimiento = MovimientoInventario::where('tipo', 'entrada')->where('pedido_id', $pedido->id)->first();
        $this->assertNotNull($movimiento);
        $this->assertSame($admin->id, $movimiento->usuario_id);
    }

    #[Test]
    public function una_devolucion_pendiente_no_altera_el_stock(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, 3, 50.00);

        $this->crearDevolucionPendiente($cliente, $pedido);

        // Solo se creó la solicitud: el stock permanece intacto y sin movimientos.
        $this->assertSame(7, $producto->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[Test]
    public function solo_se_puede_aprobar_una_devolucion_en_estado_pendiente(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, 3, 50.00);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->aprobarDevolucion($admin, $devolucion);
        $this->assertSame(10, $producto->fresh()->stock);

        // Segundo intento de aprobación sobre una devolución ya aprobada: rechazado.
        $this->actingAs($admin)
            ->from('/admin/devoluciones')
            ->post(route('admin.devoluciones.aprobar', $devolucion->id))
            ->assertRedirect('/admin/devoluciones')
            ->assertSessionHas('toast_error');

        // No se duplica la entrada de stock.
        $this->assertSame(10, $producto->fresh()->stock);
        $this->assertSame(1, MovimientoInventario::where('tipo', 'entrada')->count());
    }

    #[Test]
    public function una_devolucion_rechazada_no_genera_entrada_de_stock(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 7]);
        $pedido = $this->crearPedidoConItem($cliente, $producto, 3, 50.00);
        $devolucion = $this->crearDevolucionPendiente($cliente, $pedido);

        $this->actingAs($admin)
            ->from('/admin/devoluciones')
            ->post(route('admin.devoluciones.rechazar', $devolucion->id), [
                'comentario_admin' => 'Sin evidencia suficiente',
            ])
            ->assertRedirect('/admin/devoluciones')
            ->assertSessionHas('toast_success');

        $this->assertSame(7, $producto->fresh()->stock);
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
            'motivo'      => 'Producto defectuoso',
            'descripcion' => 'No funciona correctamente',
            'estado'      => 'pendiente',
        ]);
    }

    protected function crearPedidoConItem(Usuario $cliente, Producto $producto, int $cantidad, float $precio): Pedido
    {
        $pedido = Pedido::factory()->create([
            'usuario_id'    => $cliente->id,
            'numero_pedido' => '#PM-' . uniqid(),
        ]);

        ItemPedido::factory()->create([
            'pedido_id'       => $pedido->id,
            'producto_id'     => $producto->id,
            'cantidad'        => $cantidad,
            'precio_unitario' => $precio,
            'subtotal'        => $precio * $cantidad,
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
