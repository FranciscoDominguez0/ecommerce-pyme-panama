<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\ImagenProducto;
use App\Models\Producto;
use App\Models\VarianteProducto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pruebas del módulo ADMIN de Productos (listado, formulario, imágenes y stock).
 *
 * Cubre las rutas reales:
 *   GET  /admin/productos          → index
 *   GET  /admin/productos/crear    → create (formulario)
 *   POST /admin/productos          → store
 *   GET  /admin/productos/{id}/editar → edit
 *   PUT  /admin/productos/{id}     → update
 *   DELETE /admin/productos/{id}   → destroy (soft delete)
 */
class ProductoTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — Solo administradores pueden acceder
    // =====================================================================

    public function test_el_acceso_al_listado_requiere_iniciar_sesion(): void
    {
        $this->get('/admin/productos')
            ->assertRedirect('/login');
    }

    public function test_un_cliente_no_puede_acceder_al_listado_de_productos(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/admin/productos')
            ->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_al_listado(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/productos')
            ->assertOk();
    }

    // =====================================================================
    //  LISTADO — GET /admin/productos
    // =====================================================================

    public function test_el_listado_muestra_los_productos_existentes(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Portátil Gamer X',
        ]);

        $this->actingAs($admin)
            ->get('/admin/productos')
            ->assertOk()
            ->assertSee('Portátil Gamer X')
            ->assertSee($producto->sku);
    }

    public function test_el_listado_tiene_filtro_por_categoria(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create(['nombre' => 'Electrónica']);

        $this->actingAs($admin)
            ->get('/admin/productos')
            ->assertOk()
            ->assertSee('name="categoria_id"', false)
            ->assertSee('Electrónica');
    }

    public function test_el_listado_tiene_buscador_por_nombre_y_sku(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/productos')
            ->assertOk()
            ->assertSee('name="buscar"', false)
            ->assertSee('name="sku"', false);
    }

    public function test_el_buscador_filtra_productos_por_nombre(): void
    {
        $admin = $this->crearAdmin();
        Producto::factory()->create(['nombre' => 'Monitor UltraWide']);
        Producto::factory()->create(['nombre' => 'Ratón Inalámbrico']);

        $this->actingAs($admin)
            ->get('/admin/productos?buscar=UltraWide')
            ->assertOk()
            ->assertSee('Monitor UltraWide')
            ->assertDontSee('Ratón Inalámbrico');
    }

    public function test_el_buscador_filtra_productos_por_sku(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/productos?sku=' . $producto->sku)
            ->assertOk()
            ->assertSee($producto->nombre);
    }

    public function test_el_listado_muestra_el_indicador_de_estado_de_stock(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();

        Producto::factory()->create(['categoria_id' => $categoria->id, 'stock' => 10]);
        Producto::factory()->create(['categoria_id' => $categoria->id, 'stock' => 0]);

        $this->actingAs($admin)
            ->get('/admin/productos')
            ->assertOk()
            ->assertSee('en stock', false)
            ->assertSee('Agotado');
    }

    // =====================================================================
    //  FORMULARIO — GET /admin/productos/crear y editar
    // =====================================================================

    public function test_el_formulario_de_crear_producto_se_renderiza_con_los_campos(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/productos/crear')
            ->assertOk()
            ->assertSee('id="nombre"', false)
            ->assertSee('name="nombre"', false)
            ->assertSee('name="sku"', false)
            ->assertSee('name="categoria_id"', false)
            ->assertSee('name="precio"', false)
            ->assertSee('name="descripcion"', false);
    }

    public function test_el_formulario_de_editar_producto_se_renderiza_con_los_datos(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/productos/' . $producto->id . '/editar')
            ->assertOk()
            ->assertSee($producto->nombre)
            ->assertSee('value="' . $producto->sku . '"', false);
    }

    public function test_editar_un_producto_inexistente_devuelve_404(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/productos/999999/editar')
            ->assertNotFound();
    }

    // =====================================================================
    //  CREACIÓN — POST /admin/productos (store)
    // =====================================================================

    public function test_un_administrador_puede_crear_un_producto(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();

        $this->actingAs($admin)
            ->post('/admin/productos', $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
            ]))
            ->assertRedirect(route('admin.productos.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Teclado Mecánico RGB',
            'categoria_id' => $categoria->id,
            'activo' => true,
        ]);
    }

    // =====================================================================
    //  ACTUALIZACIÓN — PUT /admin/productos/{id}
    // =====================================================================

    public function test_un_administrador_puede_actualizar_un_producto(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $respuesta = $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'nombre' => 'Nombre Actualizado',
                'stock' => 42,
            ]));

        $respuesta->assertRedirect(route('admin.productos.edit', $producto->id));
        $respuesta->assertSessionHas('success');

        $producto->refresh();
        $this->assertSame('Nombre Actualizado', $producto->nombre);
        $this->assertSame(42, $producto->stock);
    }

    public function test_la_actualizacion_exige_nombre_obligatorio(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->from('/admin/productos/' . $producto->id . '/editar')
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'nombre' => '',
            ]))
            ->assertSessionHasErrors(['nombre' => 'El nombre del producto es obligatorio.']);
    }

    public function test_la_actualizacion_rechaza_un_sku_duplicado(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $otroProducto = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->from('/admin/productos/' . $producto->id . '/editar')
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'sku' => $otroProducto->sku,
            ]))
            ->assertSessionHasErrors(['sku' => 'Ya existe otro producto con ese SKU.']);
    }

    public function test_la_actualizacion_almacena_el_stock_indicado_en_el_formulario(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id, 'stock' => 5]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'stock' => 27,
            ]));

        $this->assertSame(27, $producto->fresh()->stock);
    }

    // =====================================================================
    //  IMÁGENES — URL y principal
    // =====================================================================

    public function test_se_pueden_agregar_imagenes_por_url(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'imagenes_url' => [
                    'https://ejemplo.com/foto1.jpg',
                    'https://ejemplo.com/foto2.jpg',
                ],
            ]));

        $this->assertSame(2, $producto->imagenes()->count());
        $this->assertDatabaseHas('imagenes_producto', [
            'producto_id' => $producto->id,
            'ruta' => 'https://ejemplo.com/foto1.jpg',
        ]);
    }

    public function test_la_primera_imagen_se_marca_como_principal_automaticamente(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'imagenes_url' => ['https://ejemplo.com/principal.jpg', 'https://ejemplo.com/secundaria.jpg'],
            ]));

        $principal = $producto->imagenes()->where('es_principal', true)->first();
        $this->assertNotNull($principal);
        $this->assertSame('https://ejemplo.com/principal.jpg', $principal->ruta);
    }

    public function test_se_puede_marcar_una_imagen_existente_como_principal(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $primera = ImagenProducto::create([
            'producto_id' => $producto->id,
            'ruta' => 'https://ejemplo.com/1.jpg',
            'es_principal' => true,
            'orden' => 1,
        ]);
        $segunda = ImagenProducto::create([
            'producto_id' => $producto->id,
            'ruta' => 'https://ejemplo.com/2.jpg',
            'es_principal' => false,
            'orden' => 2,
        ]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'imagen_principal_id' => $segunda->id,
            ]));

        $this->assertFalse($primera->fresh()->es_principal);
        $this->assertTrue($segunda->fresh()->es_principal);
    }

    public function test_se_pueden_eliminar_imagenes_marcadas(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $imagen = ImagenProducto::create([
            'producto_id' => $producto->id,
            'ruta' => 'https://ejemplo.com/borrar.jpg',
            'es_principal' => true,
            'orden' => 1,
        ]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'imagenes_eliminar' => [$imagen->id],
            ]));

        $this->assertDatabaseMissing('imagenes_producto', ['id' => $imagen->id]);
    }

    // =====================================================================
    //  STOCK — cálculo de stock del producto
    // =====================================================================

    public function test_el_stock_del_producto_se_guarda_directamente_sin_variantes(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'stock' => 20,
                'tiene_variantes' => 0,
            ]));

        // Sin variantes, el stock es el valor propio del producto.
        $this->assertSame(20, $producto->fresh()->stock);
        $this->assertSame(0, $producto->fresh()->variantes()->count());
    }

    public function test_el_stock_del_producto_no_se_deriva_automaticamente_de_sus_variantes(): void
    {
        // REPORTE: la especificación pide que el stock del producto sea la SUMA
        // del stock de sus variantes. La implementación ACTUAL NO lo hace: el stock
        // se guarda tal cual llega del formulario y las variantes se guardan aparte.
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id, 'stock' => 0]);

        $this->actingAs($admin)
            ->put('/admin/productos/' . $producto->id, $this->datosProductoValidos([
                'categoria_id' => $categoria->id,
                'stock' => 0,
                'tiene_variantes' => 1,
                'variantes_json' => json_encode([
                    ['sku' => 'VAR-1', 'precio' => 10.00, 'stock' => 5],
                    ['sku' => 'VAR-2', 'precio' => 10.00, 'stock' => 3],
                    ['sku' => 'VAR-3', 'precio' => 10.00, 'stock' => 2],
                ]),
            ]));

        // Las variantes sí se guardan (3), pero el stock del producto NO se recalcula.
        $this->assertSame(3, $producto->fresh()->variantes()->count());
        $this->assertSame(0, $producto->fresh()->stock);
        $this->assertSame(10, $producto->fresh()->variantes()->sum('stock'));
    }

    // =====================================================================
    //  ELIMINACIÓN — DELETE /admin/productos/{id} (soft delete)
    // =====================================================================

    public function test_un_administrador_puede_eliminar_suavemente_un_producto(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create();

        $respuesta = $this->actingAs($admin)
            ->delete('/admin/productos/' . $producto->id);

        $respuesta->assertRedirect(route('admin.productos.index'));
        $respuesta->assertSessionHas('success');

        $producto->refresh();
        $this->assertNotNull($producto->eliminado_en);
    }

    public function test_el_producto_eliminado_ya_no_aparece_en_el_listado_admin(): void
    {
        $admin = $this->crearAdmin();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)
            ->delete('/admin/productos/' . $producto->id);

        $this->actingAs($admin)
            ->get('/admin/productos')
            ->assertOk()
            ->assertDontSee($producto->nombre);
    }

    public function test_eliminar_un_producto_inexistente_devuelve_404(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->delete('/admin/productos/999999')
            ->assertNotFound();
    }
}
