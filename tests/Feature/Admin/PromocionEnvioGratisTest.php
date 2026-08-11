<?php

namespace Tests\Feature\Admin;

use App\Models\Carrito;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Models\PromocionEnvioGratis;
use App\Models\Usuario;
use App\Models\ZonaEnvio;
use App\Services\CuponService;
use App\Services\PedidoService;

/**
 * Pruebas de las Promociones de Envío Gratis (FASE 11).
 *
 * Cubre las rutas reales:
 *   GET    /admin/promociones/envio-gratis                → index
 *   POST   /admin/promociones/envio-gratis                → store
 *   PUT    /admin/promociones/envio-gratis/{id}           → update
 *   POST   /admin/promociones/envio-gratis/{id}/toggle    → toggle
 *   DELETE /admin/promociones/envio-gratis/{id}           → destroy
 *
 * HALLAZGOS:
 *  1. El esquema permite promociones con zona_envio_id = null ("todas las zonas"),
 *     pero el controlador exige una zona obligatoria y "evaluarEnvioGratis" filtra por
 *     zona exacta (una regla sin zona NUNCA califica).
 *  2. Cuando el formulario no envía "activo" (checkbox desmarcado), la regla se crea
 *     con fallback true pero en actualización se desactiva (fallback false).
 */
class PromocionEnvioGratisTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — Solo administradores pueden acceder
    // =====================================================================

    public function test_el_acceso_a_envio_gratis_requiere_iniciar_sesion(): void
    {
        $this->get('/admin/promociones/envio-gratis')
            ->assertRedirect('/login');
    }

    public function test_un_cliente_no_puede_acceder_a_las_promociones_de_envio_gratis(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/admin/promociones/envio-gratis')
            ->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_a_las_promociones_de_envio_gratis(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/promociones/envio-gratis')
            ->assertOk();
    }

    // =====================================================================
    //  LISTADO Y FORMULARIO
    // =====================================================================

    public function test_el_listado_muestra_zona_monto_minimo_vigencia_y_estado(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00]);
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'monto_minimo' => 50.00, 'activo' => true]);

        $this->actingAs($admin)
            ->get('/admin/promociones/envio-gratis')
            ->assertOk()
            ->assertSee('Panamá')
            ->assertSee('$50.00', false)
            ->assertSee('Tarifa estándar: $5.00', false);
    }

    public function test_el_listado_identifica_las_reglas_inactivas(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Chiriquí']);
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'activo' => false]);

        $this->actingAs($admin)
            ->get('/admin/promociones/envio-gratis')
            ->assertOk()
            ->assertSee('Inactivo / Expirado', false);
    }

    public function test_el_formulario_renderiza_zona_monto_y_fechas(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá']);

        $this->actingAs($admin)
            ->get('/admin/promociones/envio-gratis')
            ->assertOk()
            ->assertSee('id="zona_envio_id"', false)
            ->assertSee('name="zona_envio_id"', false)
            ->assertSee('name="monto_minimo"', false)
            ->assertSee('name="inicio_en"', false)
            ->assertSee('name="fin_en"', false)
            ->assertSee('name="activo"', false)
            ->assertSee('Panamá');
    }

    // =====================================================================
    //  CRUD ADMIN
    // =====================================================================

    public function test_un_administrador_puede_crear_una_regla_de_envio_gratis(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá']);

        $this->actingAs($admin)
            ->post('/admin/promociones/envio-gratis', [
                'zona_envio_id' => $zona->id,
                'monto_minimo' => 50.00,
                'inicio_en' => now()->format('Y-m-d'),
                'fin_en' => now()->addMonth()->format('Y-m-d'),
                'activo' => 1,
            ])
            ->assertRedirect(route('admin.promociones.envio-gratis'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('promociones_envio_gratis', [
            'zona_envio_id' => $zona->id,
            'monto_minimo' => '50.00',
            'activo' => true,
        ]);
    }

    public function test_la_zona_y_el_monto_minimo_son_obligatorios(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/promociones/envio-gratis')
            ->post('/admin/promociones/envio-gratis', [
                'zona_envio_id' => '',
                'monto_minimo' => '',
                'inicio_en' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors(['zona_envio_id', 'monto_minimo']);
    }

    public function test_un_administrador_puede_actualizar_una_regla(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create();
        $promo = PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'monto_minimo' => 20.00]);

        $this->actingAs($admin)
            ->put('/admin/promociones/envio-gratis/' . $promo->id, [
                'zona_envio_id' => $zona->id,
                'monto_minimo' => 75.00,
                'inicio_en' => now()->format('Y-m-d'),
                'fin_en' => now()->addMonth()->format('Y-m-d'),
                'activo' => 1,
            ])
            ->assertRedirect(route('admin.promociones.envio-gratis'))
            ->assertSessionHas('success');

        $this->assertSame(75.0, $promo->fresh()->monto_minimo);
    }

    public function test_un_administrador_puede_cambiar_el_estado_de_una_regla(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create();
        $promo = PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'activo' => true]);

        $this->actingAs($admin)
            ->post('/admin/promociones/envio-gratis/' . $promo->id . '/toggle')
            ->assertSessionHas('success');

        $this->assertFalse($promo->fresh()->activo);
    }

    public function test_un_administrador_puede_eliminar_una_regla(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create();
        $promo = PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id]);

        $this->actingAs($admin)
            ->delete('/admin/promociones/envio-gratis/' . $promo->id)
            ->assertRedirect(route('admin.promociones.envio-gratis'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('promociones_envio_gratis', ['id' => $promo->id]);
    }

    // =====================================================================
    //  LÓGICA — CuponService::evaluarEnvioGratis
    // =====================================================================

    public function test_la_promocion_activa_vigente_y_con_monto_cubierto_aplica_envio_gratis(): void
    {
        $zona = ZonaEnvio::factory()->create();
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'monto_minimo' => 50, 'activo' => true]);

        $this->assertTrue(app(CuponService::class)->evaluarEnvioGratis($zona->id, 100.0));
    }

    public function test_no_aplica_si_la_regla_esta_inactiva(): void
    {
        $zona = ZonaEnvio::factory()->create();
        PromocionEnvioGratis::factory()->inactiva()->create(['zona_envio_id' => $zona->id, 'monto_minimo' => 50]);

        $this->assertFalse(app(CuponService::class)->evaluarEnvioGratis($zona->id, 100.0));
    }

    public function test_no_aplica_fuera_de_la_ventana_de_fechas(): void
    {
        $zona = ZonaEnvio::factory()->create();
        $servicio = app(CuponService::class);

        // Aún no iniciada.
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'inicio_en' => now()->addDay(), 'fin_en' => now()->addMonth(), 'activo' => true]);
        $this->assertFalse($servicio->evaluarEnvioGratis($zona->id, 100.0));

        // Ya expirada.
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'inicio_en' => now()->subMonth(), 'fin_en' => now()->subDay(), 'activo' => true]);
        $this->assertFalse($servicio->evaluarEnvioGratis($zona->id, 100.0));
    }

    public function test_no_aplica_si_el_monto_es_inferior_al_minimo(): void
    {
        $zona = ZonaEnvio::factory()->create();
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'monto_minimo' => 50, 'activo' => true]);

        $this->assertFalse(app(CuponService::class)->evaluarEnvioGratis($zona->id, 49.99));
    }

    public function test_aplica_solo_a_la_zona_indicada(): void
    {
        $zonaA = ZonaEnvio::factory()->create(['nombre' => 'Panamá']);
        $zonaB = ZonaEnvio::factory()->create(['nombre' => 'Chiriquí']);
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zonaA->id, 'monto_minimo' => 0, 'activo' => true]);

        $servicio = app(CuponService::class);
        $this->assertTrue($servicio->evaluarEnvioGratis($zonaA->id, 100.0));
        $this->assertFalse($servicio->evaluarEnvioGratis($zonaB->id, 100.0));
    }

    public function test_una_regla_sin_zona_asignada_no_se_aplica_via_evaluar_envio_gratis(): void
    {
        // HALLAZGO: el esquema permite zona_envio_id = null ("todas las zonas"), pero
        // evaluarEnvioGratis filtra por zona_envio_id exacto, por lo que una regla sin
        // zona NUNCA califica (null no coincide con ninguna zona concreta).
        $zona = ZonaEnvio::factory()->create();
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => null, 'monto_minimo' => 0, 'activo' => true]);

        $this->assertFalse(app(CuponService::class)->evaluarEnvioGratis($zona->id, 100.0));
    }

    public function test_el_envio_gratis_se_refleja_en_el_total_del_pedido(): void
    {
        $cliente = $this->crearCliente();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00, 'activo' => true]);
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'monto_minimo' => 50, 'activo' => true]);
        $carrito = $this->crearCarritoConProducto($cliente, 1, 100.00);

        $totales = app(PedidoService::class)->calcularTotales($carrito, $zona, null);

        $this->assertSame(0.0, $totales['costo_envio']);
        $this->assertSame(5.0, $totales['descuento_envio']);
        $this->assertSame(107.0, $totales['total']); // 100 + 7 ITBMS, sin costo de envío
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
