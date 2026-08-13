<?php

namespace Tests\Feature\Inventario;

use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\VarianteProducto;
use App\Services\InventarioService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del HISTORIAL de movimientos de inventario (FASE 14).
 *
 * Ruta cubierta: GET /admin/inventario (index) con filtros reales:
 *   ?tipo=entrada|salida|ajuste, ?q=..., ?desde=..., ?hasta=...
 *
 * También valida la integridad de datos: relaciones del movimiento con
 * producto, variante, usuario y pedido; y los KPIs de InventarioService.
 */
class MovimientosInventarioTest extends BaseAdminTest
{
    // =====================================================================
    //  HISTORIAL — render y filtros
    // =====================================================================

    #[Test]
    public function el_historial_muestra_los_movimientos_registrados(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10, 'nombre' => 'Audífonos Pro']);
        $this->inventario()->registrarEntrada($producto, null, 5, 'Compra a proveedor', null, null, null, $admin->id);

        $this->actingAs($admin)
            ->get('/admin/inventario')
            ->assertOk()
            ->assertSee('Compra a proveedor')
            ->assertSee('Audífonos Pro');
    }

    #[Test]
    public function el_historial_filtra_los_movimientos_por_tipo(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 100]);
        $this->inventario()->registrarEntrada($producto, null, 5, 'Entrada de prueba', null, null, null, $admin->id);
        $this->inventario()->registrarSalida($producto, null, 3, 'Salida de prueba', null, $admin->id);

        $this->actingAs($admin)
            ->get('/admin/inventario?tipo=entrada')
            ->assertOk()
            ->assertSee('Entrada de prueba')
            ->assertDontSee('Salida de prueba');
    }

    #[Test]
    public function el_historial_filtra_los_movimientos_por_motivo(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 100]);
        $this->inventario()->registrarEntrada($producto, null, 5, 'Devolución de cliente', null, null, null, $admin->id);
        $this->inventario()->registrarEntrada($producto, null, 9, 'Compra a proveedor', null, null, null, $admin->id);

        $this->actingAs($admin)
            ->get('/admin/inventario?q=Devolución')
            ->assertOk()
            ->assertSee('Devolución de cliente')
            ->assertDontSee('Compra a proveedor');
    }

    #[Test]
    public function el_historial_filtra_los_movimientos_por_rango_de_fechas(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 100]);

        $antiguo = $this->inventario()->registrarEntrada($producto, null, 5, 'Entrada antigua', null, null, null, $admin->id);
        // creado_en no está en $fillable del modelo → actualizar por query builder.
        DB::table('movimientos_inventario')
            ->where('id', $antiguo->id)
            ->update(['creado_en' => now()->subDays(30)]);

        $this->inventario()->registrarSalida($producto, null, 2, 'Salida reciente', null, $admin->id);

        $this->actingAs($admin)
            ->get('/admin/inventario?desde=' . now()->subDays(7)->toDateString())
            ->assertOk()
            ->assertSee('Salida reciente')
            ->assertDontSee('Entrada antigua');
    }

    // =====================================================================
    //  CONTENIDO DEL MOVIMIENTO — fecha, tipo, cantidad, producto, motivo, usuario
    // =====================================================================

    #[Test]
    public function el_movimiento_guarda_fecha_tipo_cantidad_producto_motivo_y_usuario(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $movimiento = $this->inventario()->registrarEntrada(
            $producto,
            null,
            7,
            'Inventario inicial',
            'Proveedor Central',
            'F-0001',
            'Primer lote',
            $admin->id
        );

        $this->assertSame('entrada', $movimiento->tipo);
        $this->assertSame(7, $movimiento->cantidad);
        $this->assertSame($producto->id, $movimiento->producto_id);
        $this->assertSame($admin->id, $movimiento->usuario_id);
        $this->assertSame('Inventario inicial', $movimiento->motivo);
        $this->assertSame('Proveedor Central', $movimiento->proveedor);
        $this->assertSame('F-0001', $movimiento->factura_proveedor);
        $this->assertSame(10, $movimiento->stock_antes);
        $this->assertSame(17, $movimiento->stock_despues);
        $this->assertNotNull($movimiento->creado_en);
    }

    #[Test]
    public function el_movimiento_con_variante_guarda_la_variante_en_la_relacion(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 5]);

        $movimiento = $this->inventario()->registrarEntrada($producto, $variante, 3, 'Reabastecimiento', null, null, null, $admin->id);

        $this->assertSame($variante->id, $movimiento->variante_producto_id);
        $this->assertSame($producto->id, $movimiento->producto_id);
    }

    #[Test]
    public function el_movimiento_apunta_al_pedido_cuando_se_registra_con_pedido_id(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $movimiento = $this->inventario()->registrarSalida($producto, null, 2, 'Venta - Pedido test', null, $admin->id, $pedido->id);

        $this->assertSame($pedido->id, $movimiento->pedido_id);
    }

    // =====================================================================
    //  INTEGRIDAD DE DATOS — relaciones del movimiento
    // =====================================================================

    #[Test]
    public function las_relaciones_del_movimiento_resuelven_producto_variante_usuario_y_pedido(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 50]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 5]);
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $movimiento = $this->inventario()->registrarSalida($producto, $variante, 3, 'Venta con variante', null, $admin->id, $pedido->id);

        $cargado = MovimientoInventario::with(['producto', 'variante', 'usuario', 'pedido'])->find($movimiento->id);

        $this->assertNotNull($cargado->producto);
        $this->assertSame($producto->id, $cargado->producto->id);
        $this->assertNotNull($cargado->variante);
        $this->assertSame($variante->id, $cargado->variante->id);
        $this->assertNotNull($cargado->usuario);
        $this->assertSame($admin->id, $cargado->usuario->id);
        $this->assertNotNull($cargado->pedido);
        $this->assertSame($pedido->id, $cargado->pedido->id);
    }

    #[Test]
    public function el_movimiento_sin_usuario_ni_pedido_permite_relaciones_nulas(): void
    {
        $producto = Producto::factory()->create(['stock' => 10]);

        $movimiento = $this->inventario()->registrarEntrada($producto, null, 5, 'Sin usuario registrado');

        $cargado = MovimientoInventario::with(['producto', 'variante', 'usuario', 'pedido'])->find($movimiento->id);

        $this->assertNull($cargado->usuario);
        $this->assertNull($cargado->pedido);
        $this->assertNull($cargado->variante);
        $this->assertSame($producto->id, $cargado->producto->id);
    }

    // =====================================================================
    //  KPIs — métricas del módulo de inventario
    // =====================================================================

    #[Test]
    public function los_kpis_calculan_stock_total_entradas_y_salidas_recientes(): void
    {
        $admin = $this->crearAdmin();
        // Baseline: otros tests (ej. CatalogoTest, sin RefreshDatabase) dejan datos en
        // ecommerce_test → se compara el delta que genera ESTE test, no el total absoluto.
        $antes = $this->inventario()->calcularKpis();

        // Producto SIN variantes: su stock cuenta en el KPI total.
        $producto = Producto::factory()->create(['stock' => 10]);
        // Producto CON variantes: solo cuenta el stock de sus variantes activas.
        $productoConVariante = Producto::factory()->create(['stock' => 99]);
        VarianteProducto::factory()->create(['producto_id' => $productoConVariante->id, 'stock' => 10]);

        $this->inventario()->registrarEntrada($producto, null, 5, 'Compra', null, null, null, $admin->id);
        $this->inventario()->registrarSalida($producto, null, 3, 'Venta', null, $admin->id);

        $kpis = $this->inventario()->calcularKpis();

        // 12 del producto sin variantes (10+5-3) + 10 de la variante = delta +22.
        $this->assertSame($antes['stockTotal'] + 22, $kpis['stockTotal']);
        $this->assertSame($antes['entradasSiete'] + 5, $kpis['entradasSiete']);
        $this->assertSame($antes['salidasSiete'] + 3, $kpis['salidasSiete']);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    protected function inventario(): InventarioService
    {
        return app(InventarioService::class);
    }
}
