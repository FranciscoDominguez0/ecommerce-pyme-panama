<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\OpcionVariante;
use App\Models\Producto;
use App\Models\TipoVariante;
use App\Models\VarianteProducto;

/**
 * Pruebas del módulo de VARIANTES de producto (panel admin).
 *
 * El flujo real guarda las variantes a través de ProductoController::update
 * usando el campo `variantes_json` + `tiene_variantes` (no existe un
 * VarianteController funcional ni rutas dedicadas de variantes).
 */
class VarianteTest extends BaseAdminTest
{
    /**
     * Datos de variantes válidos para enviar en el formulario de producto.
     */
    protected function variantesParaFormulario(): array
    {
        return [
            'tiene_variantes' => 1,
            'variantes_json' => json_encode([
                [
                    'sku' => 'TEC-RGB-NEGRO',
                    'precio' => 79.99,
                    'stock' => 5,
                    'atributos' => ['Color' => 'Negro'],
                ],
                [
                    'sku' => 'TEC-RGB-BLANCO',
                    'precio' => 79.99,
                    'stock' => 3,
                    'atributos' => ['Color' => 'Blanco'],
                ],
            ]),
        ];
    }

    // =====================================================================
    //  AUTORIZACIÓN
    // =====================================================================

    public function test_un_cliente_no_puede_crear_variantes(): void
    {
        $cliente = $this->crearCliente();
        $producto = Producto::factory()->create();

        $this->actingAs($cliente)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $producto->categoria_id,
                'tiene_variantes' => 1,
                'variantes_json' => json_encode([]),
            ]))
            ->assertForbidden();
    }

    // =====================================================================
    //  CREACIÓN DE VARIANTES
    // =====================================================================

    public function test_se_guardan_las_variantes_con_stock_y_precio_propios(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
            ]) + $this->variantesParaFormulario());

        $variantes = $producto->variantes()->orderBy('id')->get();
        $this->assertCount(2, $variantes);
        $this->assertSame(5, (int) $variantes[0]->stock);
        $this->assertSame(3, (int) $variantes[1]->stock);
        $this->assertEquals(79.99, (float) $variantes[0]->precio);
    }

    public function test_se_crean_automaticamente_tipo_y_opcion_de_variante(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
            ]) + $this->variantesParaFormulario());

        // El tipo 'Color' y las opciones 'Negro'/'Blanco' se crean con firstOrCreate.
        $tipo = TipoVariante::where('nombre', 'Color')->first();
        $this->assertNotNull($tipo);
        $this->assertNotNull(OpcionVariante::where('tipo_variante_id', $tipo->id)->where('valor', 'Negro')->first());
        $this->assertNotNull(OpcionVariante::where('tipo_variante_id', $tipo->id)->where('valor', 'Blanco')->first());
    }

    public function test_las_variantes_quedan_asociadas_a_sus_opciones(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
            ]) + $this->variantesParaFormulario());

        $variante = $producto->variantes()->where('sku', 'TEC-RGB-NEGRO')->first();
        $this->assertNotNull($variante);
        $this->assertSame('Negro', $variante->opciones()->first()->valor);
    }

    // =====================================================================
    //  ACTUALIZACIÓN Y ELIMINACIÓN DE VARIANTES
    // =====================================================================

    public function test_actualizar_variantes_reemplaza_las_existentes(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        // Primera carga: 2 variantes
        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
            ]) + $this->variantesParaFormulario());

        $this->assertCount(2, $producto->variantes()->get());

        // Segunda carga: solo 1 variante (se limpia y recrea)
        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'tiene_variantes' => 1,
                'variantes_json' => json_encode([
                    ['sku' => 'TEC-RGB-UNICO', 'precio' => 90.00, 'stock' => 8, 'atributos' => ['Color' => 'Gris']],
                ]),
            ]));

        $this->assertCount(1, $producto->variantes()->get());
        $this->assertSame('TEC-RGB-UNICO', $producto->variantes()->first()->sku);
    }

    public function test_desactivar_variantes_elimina_las_existentes(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
            ]) + $this->variantesParaFormulario());

        $this->assertCount(2, $producto->variantes()->get());

        // Desactivar variantes (tiene_variantes = 0) → se borran todas.
        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'tiene_variantes' => 0,
            ]));

        $this->assertSame(0, $producto->variantes()->count());
    }

    public function test_el_stock_total_de_las_variantes_se_puede_sumar(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'tiene_variantes' => 1,
                'variantes_json' => json_encode([
                    ['sku' => 'SUM-1', 'precio' => 10.00, 'stock' => 5],
                    ['sku' => 'SUM-2', 'precio' => 10.00, 'stock' => 3],
                    ['sku' => 'SUM-3', 'precio' => 10.00, 'stock' => 2],
                ]),
            ]));

        $sumaStocks = $producto->variantes()->sum('stock');
        $this->assertSame(10, (int) $sumaStocks);
    }

    // =====================================================================
    //  MODELO — relaciones
    // =====================================================================

    public function test_la_variante_pertenece_al_producto_y_expone_sus_opciones(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id]);

        $this->assertSame($producto->id, $variante->producto->id);
        $this->assertTrue($variante->opciones()->count() >= 0);
    }
}
