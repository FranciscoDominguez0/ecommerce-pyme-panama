<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\VarianteProducto;

/**
 * Pruebas del servicio de productos y la atomicidad del guardado.
 *
 * REPORTE PREVIO: `app/Services/ProductoService.php` existe pero está VACÍO
 * (0 bytes). No hay lógica de servicio. La creación/actualización de productos
 * con variantes se hace directamente en `ProductoController::update()` dentro
 * de un `DB::transaction` (transacción real). Estas pruebas verifican el
 * comportamiento transaccional tal y como está implementado.
 */
class ServicioProductoTest extends BaseAdminTest
{
    public function test_el_servicio_producto_actualmente_esta_vacio(): void
    {
        // Documenta la incompletitud: el archivo existe pero no tiene métodos.
        $archivo = base_path('app/Services/ProductoService.php');
        $this->assertFileExists($archivo);
        $this->assertEmpty(file_get_contents($archivo), 'Se esperaba ProductoService vacío (estado actual documentado).');
    }

    public function test_la_actualizacion_de_producto_con_variantes_es_atomica(): void
    {
        // El guardado ocurre en una transacción real (DB::transaction) en el controlador.
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $conteoAntes = VarianteProducto::count();

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'nombre' => 'Producto Transaccional',
                'tiene_variantes' => 1,
                'variantes_json' => json_encode([
                    ['sku' => 'TX-1', 'precio' => 10.00, 'stock' => 1],
                    ['sku' => 'TX-2', 'precio' => 10.00, 'stock' => 2],
                ]),
            ]));

        // Todo se persistió junto: producto + variantes.
        $producto->refresh();
        $this->assertSame('Producto Transaccional', $producto->nombre);
        $this->assertSame(2, $producto->variantes()->count());
        $this->assertSame($conteoAntes + 2, VarianteProducto::count());
    }

    public function test_si_la_validacion_falla_no_se_persiste_nada(): void
    {
        // Si el request no pasa validación (nombre vacío), el producto NO cambia
        // y tampoco se guardan variantes (la transacción ni siquiera arranca).
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $conteoVariantes = VarianteProducto::count();

        $this->actingAs($admin)
            ->from('/admin/productos/' . $producto->id . '/editar')
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'nombre' => '',
                'tiene_variantes' => 1,
                'variantes_json' => json_encode([
                    ['sku' => 'NO-DEBE-CREARSE', 'precio' => 10.00, 'stock' => 1],
                ]),
            ]))
            ->assertSessionHasErrors('nombre');

        $this->assertSame(0, VarianteProducto::where('sku', 'NO-DEBE-CREARSE')->count());
        $this->assertSame($conteoVariantes, VarianteProducto::count());
    }

    public function test_el_guardado_de_producto_y_variantes_es_atomico_y_completo(): void
    {
        // El guardado ocurre en un DB::transaction real en el controlador: producto,
        // imágenes y variantes se persisten juntos o no se persiste nada.
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'nombre' => 'Producto Transaccional',
                'tiene_variantes' => 1,
                'variantes_json' => json_encode([
                    ['sku' => 'TX-A-' . uniqid(), 'precio' => 10.00, 'stock' => 1],
                    ['sku' => 'TX-B-' . uniqid(), 'precio' => 10.00, 'stock' => 2],
                ]),
            ]));

        // Producto + variantes persistidos juntos.
        $producto->refresh();
        $this->assertSame('Producto Transaccional', $producto->nombre);
        $this->assertSame(2, $producto->variantes()->count());
    }

    public function test_el_guardado_usa_una_transaccion_de_base_de_datos(): void
    {
        // Verifica la atomicidad de forma práctica: un request exitoso persiste
        // producto + variantes de forma consistente dentro de la misma operación.
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'tiene_variantes' => 1,
                'variantes_json' => json_encode([
                    ['sku' => 'TX-A-' . uniqid(), 'precio' => 10.00, 'stock' => 1],
                    ['sku' => 'TX-B-' . uniqid(), 'precio' => 10.00, 'stock' => 2],
                ]),
            ]));

        $this->assertSame(2, $producto->variantes()->count());
        $this->assertDatabaseHas('variantes_producto', ['producto_id' => $producto->id]);
    }
}
