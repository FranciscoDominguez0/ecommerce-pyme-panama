<?php

namespace Tests\Feature\Admin;

use App\Models\Producto;
use App\Models\ProductoDelMes;
use App\Services\CuponService;

/**
 * Pruebas del módulo ADMIN de Producto del Mes (FASE 11).
 *
 * Cubre las rutas reales:
 *   GET    /admin/promociones/producto-del-mes              → index
 *   POST   /admin/promociones/producto-del-mes              → store
 *   POST   /admin/promociones/producto-del-mes/{id}/toggle  → toggle
 *   DELETE /admin/promociones/producto-del-mes/{id}         → destroy
 *
 * HALLAZGO: la regla "solo 1 activo" (comentada en el esquema) es SOFT. El store
 * desactiva anteriores únicamente cuando el request incluye el campo "activo"; si el
 * checkbox va desmarcado (campo ausente), el nuevo registro se crea activo (fallback
 * true) SIN desactivar los anteriores → pueden quedar varias promociones activas.
 */
class ProductoDelMesTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — Solo administradores pueden acceder
    // =====================================================================

    public function test_el_acceso_a_producto_del_mes_requiere_iniciar_sesion(): void
    {
        $this->get('/admin/promociones/producto-del-mes')
            ->assertRedirect('/login');
    }

    public function test_un_cliente_no_puede_acceder_a_producto_del_mes(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/admin/promociones/producto-del-mes')
            ->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_a_producto_del_mes(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/promociones/producto-del-mes')
            ->assertOk();
    }

    // =====================================================================
    //  LISTADO / FORMULARIO — selector, descuento y vigencia
    // =====================================================================

    public function test_la_pagina_muestra_el_producto_seleccionado_y_su_precio_promocional(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['nombre' => 'Laptop Gamer', 'precio' => 100.00]);
        ProductoDelMes::factory()->create(['producto_id' => $producto->id, 'descuento_especial' => 20, 'activo' => true]);

        $this->actingAs($admin)
            ->get('/admin/promociones/producto-del-mes')
            ->assertOk()
            ->assertSee('Laptop Gamer')
            ->assertSee('-20%', false)
            ->assertSee('$80.00', false);
    }

    public function test_el_formulario_renderiza_el_selector_y_los_campos_de_promocion(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/promociones/producto-del-mes')
            ->assertOk()
            ->assertSee('name="producto_id"', false)
            ->assertSee('name="descuento_especial"', false)
            ->assertSee('name="inicio_en"', false)
            ->assertSee('name="fin_en"', false)
            ->assertSee('name="descripcion_mes"', false)
            ->assertSee('name="activo"', false)
            ->assertSee('id="modal-selector-producto"', false);
    }

    // =====================================================================
    //  CRUD ADMIN
    // =====================================================================

    public function test_un_administrador_puede_configurar_un_producto_del_mes(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['precio' => 100.00]);

        $this->actingAs($admin)
            ->post('/admin/promociones/producto-del-mes', $this->datosProductoDelMes($producto, ['activo' => 1]))
            ->assertRedirect(route('admin.promociones.producto-del-mes'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('producto_del_mes', [
            'producto_id' => $producto->id,
            'descuento_especial' => '20.00',
            'activo' => true,
        ]);
    }

    public function test_el_producto_es_obligatorio(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/promociones/producto-del-mes')
            ->post('/admin/promociones/producto-del-mes', $this->datosProductoDelMes(null, ['producto_id' => '']))
            ->assertSessionHasErrors('producto_id');
    }

    public function test_el_descuento_especial_es_obligatorio_y_debe_estar_entre_1_y_99(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create();

        $this->actingAs($admin)
            ->from('/admin/promociones/producto-del-mes')
            ->post('/admin/promociones/producto-del-mes', $this->datosProductoDelMes($producto, ['descuento_especial' => '']))
            ->assertSessionHasErrors('descuento_especial');

        $this->actingAs($admin)
            ->from('/admin/promociones/producto-del-mes')
            ->post('/admin/promociones/producto-del-mes', $this->datosProductoDelMes($producto, ['descuento_especial' => 0]))
            ->assertSessionHasErrors('descuento_especial');

        $this->actingAs($admin)
            ->from('/admin/promociones/producto-del-mes')
            ->post('/admin/promociones/producto-del-mes', $this->datosProductoDelMes($producto, ['descuento_especial' => 100]))
            ->assertSessionHasErrors('descuento_especial');
    }

    public function test_un_administrador_puede_cambiar_el_estado_de_un_producto_del_mes(): void
    {
        $admin = $this->crearAdmin();
        $promo = ProductoDelMes::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->post('/admin/promociones/producto-del-mes/' . $promo->id . '/toggle')
            ->assertSessionHas('success');

        $this->assertFalse($promo->fresh()->activo);
    }

    public function test_un_administrador_puede_eliminar_un_producto_del_mes(): void
    {
        $admin = $this->crearAdmin();
        $promo = ProductoDelMes::factory()->create();

        $this->actingAs($admin)
            ->delete('/admin/promociones/producto-del-mes/' . $promo->id)
            ->assertRedirect(route('admin.promociones.producto-del-mes'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('producto_del_mes', ['id' => $promo->id]);
    }

    // =====================================================================
    //  LÓGICA — "solo 1 activo", precio promocional y vigencia
    // =====================================================================

    public function test_al_crear_un_nuevo_producto_del_mes_activo_se_desactiva_el_anterior(): void
    {
        $admin = $this->crearAdmin();
        $productoA = Producto::factory()->create();
        $productoB = Producto::factory()->create();
        ProductoDelMes::factory()->create(['producto_id' => $productoA->id, 'activo' => true]);

        $this->actingAs($admin)
            ->post('/admin/promociones/producto-del-mes', $this->datosProductoDelMes($productoB, ['activo' => 1]))
            ->assertRedirect(route('admin.promociones.producto-del-mes'));

        $this->assertSame(1, ProductoDelMes::where('activo', true)->count());
        $this->assertFalse(ProductoDelMes::where('producto_id', $productoA->id)->first()->activo);
        $this->assertTrue(ProductoDelMes::where('producto_id', $productoB->id)->first()->activo);
    }

    public function test_el_crear_uno_nuevo_sin_campo_activo_no_desactiva_los_existentes(): void
    {
        // HALLAZGO: sin el campo "activo" (checkbox desmarcado) no se desactivan los
        // anteriores y el nuevo se crea activo por fallback → quedan 2+ activos.
        $admin = $this->crearAdmin();
        $productoA = Producto::factory()->create();
        $productoB = Producto::factory()->create();
        ProductoDelMes::factory()->create(['producto_id' => $productoA->id, 'activo' => true]);

        $this->actingAs($admin)
            ->post('/admin/promociones/producto-del-mes', $this->datosProductoDelMes($productoB))
            ->assertRedirect(route('admin.promociones.producto-del-mes'));

        $this->assertSame(2, ProductoDelMes::where('activo', true)->count());
    }

    public function test_el_toggle_no_desactiva_otras_promociones_activas(): void
    {
        // HALLAZGO: activar una promoción vía toggle no desactiva las demás activas.
        $admin = $this->crearAdmin();
        $promoA = ProductoDelMes::factory()->create(['activo' => true]);
        $promoB = ProductoDelMes::factory()->create(['activo' => false]);

        $this->actingAs($admin)
            ->post('/admin/promociones/producto-del-mes/' . $promoB->id . '/toggle')
            ->assertSessionHas('success');

        $this->assertTrue($promoA->fresh()->activo);
        $this->assertTrue($promoB->fresh()->activo);
        $this->assertSame(2, ProductoDelMes::where('activo', true)->count());
    }

    public function test_el_precio_promocional_aplica_el_descuento_especial(): void
    {
        $producto = Producto::factory()->create(['precio' => 200.00]);
        $promo = ProductoDelMes::factory()->create(['producto_id' => $producto->id, 'descuento_especial' => 25, 'activo' => true]);

        $this->assertSame(150.0, $promo->precioPromocional());
        $this->assertSame(150.0, $producto->fresh()->precioFinalPromocional());
    }

    public function test_el_precio_promocional_no_se_aplica_fuera_de_la_ventana(): void
    {
        $producto = Producto::factory()->create(['precio' => 200.00]);
        ProductoDelMes::factory()->create([
            'producto_id' => $producto->id,
            'descuento_especial' => 25,
            'activo' => true,
            'inicio_en' => now()->subMonth(),
            'fin_en' => now()->subDay(),
        ]);

        // Fuera de la ventana: la promoción no es vigente y el producto conserva su precio.
        $this->assertFalse(ProductoDelMes::where('producto_id', $producto->id)->first()->esVigente());
        $this->assertNull($producto->fresh()->promocionDelMesActiva());
        $this->assertSame(200.0, $producto->fresh()->precioFinalPromocional());
    }

    public function test_obtener_producto_del_mes_activo_devuelve_solo_el_vigente(): void
    {
        $productoA = Producto::factory()->create();
        $productoB = Producto::factory()->create();
        $vigente = ProductoDelMes::factory()->create(['producto_id' => $productoA->id, 'activo' => true]);
        ProductoDelMes::factory()->create([
            'producto_id' => $productoB->id,
            'activo' => true,
            'inicio_en' => now()->subMonth(),
            'fin_en' => now()->subDay(),
        ]);

        $activo = app(CuponService::class)->obtenerProductoDelMesActivo();

        $this->assertNotNull($activo);
        $this->assertSame($vigente->id, $activo->id);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Datos válidos para configurar el Producto del Mes.
     */
    protected function datosProductoDelMes(?Producto $producto, array $sobrescribir = []): array
    {
        return array_merge([
            'producto_id' => $producto?->id,
            'descripcion_mes' => 'La mejor oferta del mes',
            'descuento_especial' => 20,
            'inicio_en' => now()->format('Y-m-d'),
            'fin_en' => now()->addDays(30)->format('Y-m-d'),
        ], $sobrescribir);
    }
}
