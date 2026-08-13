<?php

namespace Tests\Feature\Inventario;

use App\Models\Producto;
use App\Models\VarianteProducto;
use App\Services\InventarioService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de DETECCIÓN de stock bajo (FASE 14).
 *
 * Regla vigente: un producto/variante está en "stock bajo" cuando su stock es
 * menor o igual al stock_minimo del producto (stock <= stock_minimo).
 *
 * Cubre:
 *   - KPIs de InventarioService::calcularKpis (stockBajo).
 *   - Filtro "Solo stock bajo" de GET /admin/inventario/stock (?stock_bajo=1)
 *     que aplica sobre productos sin variantes y sobre variantes.
 *   - Nota: el Dashboard admin calcula $productosBajoStock en
 *     DashboardController::index, pero la vista dashboard.blade.php NO lo
 *     renderiza (no existe alerta de stock en el Dashboard actual).
 */
class InventarioStockBajoTest extends BaseAdminTest
{
    #[Test]
    public function detecta_productos_con_stock_igual_o_menor_al_minimo(): void
    {
        $admin = $this->crearAdmin();
        // Baseline: otros tests (ej. CatalogoTest, sin RefreshDatabase) pueden dejar
        // productos en ecommerce_test → se compara el delta, no el total absoluto.
        $antes = $this->inventario()->calcularKpis()['stockBajo'];

        Producto::factory()->create(['stock' => 5, 'stock_minimo' => 5]);   // igual → bajo
        Producto::factory()->create(['stock' => 2, 'stock_minimo' => 5]);   // menor → bajo
        Producto::factory()->create(['stock' => 20, 'stock_minimo' => 5]);  // sano

        $kpis = $this->inventario()->calcularKpis();

        $this->assertSame($antes + 2, $kpis['stockBajo']);
    }

    #[Test]
    public function no_marca_productos_que_esten_por_encima_del_minimo(): void
    {
        $admin = $this->crearAdmin();
        $antes = $this->inventario()->calcularKpis()['stockBajo'];

        Producto::factory()->create(['stock' => 50, 'stock_minimo' => 5]);
        Producto::factory()->create(['stock' => 6, 'stock_minimo' => 5]); // 6 > 5 → sano

        $kpis = $this->inventario()->calcularKpis();

        $this->assertSame($antes, $kpis['stockBajo']);
    }

    #[Test]
    public function el_filtro_de_stock_bajo_del_stock_actual_solo_muestra_productos_bajos(): void
    {
        $admin = $this->crearAdmin();
        $bajo = Producto::factory()->create(['nombre' => 'Teclado Bajo', 'stock' => 2, 'stock_minimo' => 5]);
        $sano = Producto::factory()->create(['nombre' => 'Monitor Sano', 'stock' => 50, 'stock_minimo' => 5]);

        $this->actingAs($admin)
            ->get('/admin/inventario/stock?stock_bajo=1')
            ->assertOk()
            ->assertSee('Teclado Bajo')
            ->assertSee($bajo->sku)
            ->assertDontSee('Monitor Sano')
            ->assertDontSee($sano->sku);
    }

    #[Test]
    public function detecta_variantes_con_stock_bajo_respecto_al_minimo_del_producto(): void
    {
        $admin = $this->crearAdmin();
        $antes = $this->inventario()->calcularKpis()['stockBajo'];

        $producto = Producto::factory()->create(['stock' => 99, 'stock_minimo' => 5]);
        VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 4]);  // bajo
        VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 50]); // sano

        $kpis = $this->inventario()->calcularKpis();

        $this->assertSame($antes + 1, $kpis['stockBajo']);
    }

    #[Test]
    public function el_filtro_de_stock_bajo_tambien_cubre_las_variantes(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['nombre' => 'Camisa Básica', 'stock' => 99, 'stock_minimo' => 5]);
        $varianteBaja = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 1]);
        $varianteSana = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 30]);

        $this->actingAs($admin)
            ->get('/admin/inventario/stock?stock_bajo=1')
            ->assertOk()
            ->assertSee($varianteBaja->sku)
            ->assertDontSee($varianteSana->sku);
    }

    #[Test]
    public function un_producto_con_variantes_no_cuenta_su_propio_stock_en_el_kpi(): void
    {
        $admin = $this->crearAdmin();
        $antes = $this->inventario()->calcularKpis()['stockBajo'];

        // Producto con variantes: su stock propio NO participa en el KPI de stock bajo
        // (cuentan sus variantes, según InventarioService::calcularKpis).
        $producto = Producto::factory()->create(['stock' => 1, 'stock_minimo' => 5]);
        VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 20]);

        $kpis = $this->inventario()->calcularKpis();

        $this->assertSame($antes, $kpis['stockBajo']);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    protected function inventario(): InventarioService
    {
        return app(InventarioService::class);
    }
}
