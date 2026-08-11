<?php

namespace Tests\Feature\Admin;

use App\Models\Cupon;
use Illuminate\Support\Carbon;

/**
 * Pruebas del módulo ADMIN de Cupones (FASE 11).
 *
 * Cubre las rutas reales:
 *   GET    /admin/promociones/cupones              → index
 *   GET    /admin/promociones/cupones/crear        → create (formulario)
 *   POST   /admin/promociones/cupones              → store
 *   GET    /admin/promociones/cupones/{id}/editar  → edit
 *   PUT    /admin/promociones/cupones/{id}         → update
 *   POST   /admin/promociones/cupones/{id}/toggle  → toggleEstado
 *   DELETE /admin/promociones/cupones/{id}         → destroy
 *
 * Esquema verificado: `cupones.tipo` (CHECK porcentaje/monto_fijo/envio_gratis),
 * `cupones.aplica_a` (CHECK catalogo/categoria/producto), `cupones.valor` (CHECK
 * valor > 0) y `cupones.codigo` (unique cupones_codigo_key). El controlador valida
 * los mismos valores permitidos.
 */
class CuponTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — Solo administradores pueden acceder
    // =====================================================================

    public function test_el_acceso_a_la_gestion_de_cupones_requiere_iniciar_sesion(): void
    {
        $this->get('/admin/promociones/cupones')
            ->assertRedirect('/login');
    }

    public function test_un_cliente_no_puede_acceder_a_la_gestion_de_cupones(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/admin/promociones/cupones')
            ->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_a_la_gestion_de_cupones(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones')
            ->assertOk();
    }

    // =====================================================================
    //  LISTADO — GET /admin/promociones/cupones
    // =====================================================================

    public function test_el_listado_muestra_codigo_tipo_valor_vigencia_y_usos(): void
    {
        $admin = $this->crearAdmin();
        Cupon::factory()->create(['codigo' => 'BIENVENIDO', 'tipo' => 'porcentaje', 'valor' => 10, 'maximo_usos_total' => 10, 'usos_actuales' => 3]);
        Cupon::factory()->montoFijo()->create(['codigo' => 'FIJO25', 'valor' => 25]);
        Cupon::factory()->envioGratis()->create(['codigo' => 'ENVIOGRATIS']);

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones')
            ->assertOk()
            ->assertSee('BIENVENIDO')
            ->assertSee('10% OFF', false)
            ->assertSee('3/10', false)
            ->assertSee('FIJO25')
            ->assertSee('$25.00 OFF', false)
            ->assertSee('ENVIOGRATIS')
            ->assertSee('Envío Gratis');
    }

    public function test_el_listado_muestra_usos_ilimitados_cuando_no_hay_limite_total(): void
    {
        $admin = $this->crearAdmin();
        Cupon::factory()->create(['codigo' => 'ILIMITADO', 'maximo_usos_total' => null]);

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones')
            ->assertOk()
            ->assertSee('ILIMITADO')
            ->assertSee('ilimitado', false);
    }

    public function test_el_listado_muestra_los_estados_inactivo_vencido_y_agotado(): void
    {
        $admin = $this->crearAdmin();
        Cupon::factory()->inactivo()->create(['codigo' => 'INACTIVO1']);
        Cupon::factory()->expirado()->create(['codigo' => 'VENCIDO1']);
        Cupon::factory()->agotado()->create(['codigo' => 'AGOTADO1']);

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones')
            ->assertOk()
            ->assertSee('INACTIVO1')
            ->assertSee('Inactivo')
            ->assertSee('VENCIDO1')
            ->assertSee('Vencido')
            ->assertSee('Expiró')
            ->assertSee('AGOTADO1')
            ->assertSee('Agotado');
    }

    public function test_el_listado_muestra_la_vigencia_formateada(): void
    {
        $admin = $this->crearAdmin();
        Cupon::factory()->create([
            'codigo' => 'VIGENTE1',
            'inicio_en' => Carbon::parse('2026-01-10'),
            'fin_en' => Carbon::parse('2026-02-20'),
        ]);

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones')
            ->assertOk()
            ->assertSee('10/01/2026')
            ->assertSee('20/02/2026');
    }

    public function test_el_listado_muestra_el_estado_vacio_cuando_no_hay_cupones(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones')
            ->assertOk()
            ->assertSee('No se encontraron cupones');
    }

    public function test_el_buscador_filtra_cupones_por_codigo(): void
    {
        $admin = $this->crearAdmin();
        Cupon::factory()->create(['codigo' => 'BIENVENIDO10']);
        Cupon::factory()->create(['codigo' => 'OTROCODIGO']);

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones?buscar=BIENVENIDO')
            ->assertOk()
            ->assertSee('BIENVENIDO10')
            ->assertDontSee('OTROCODIGO');
    }

    // =====================================================================
    //  FORMULARIO — GET /admin/promociones/cupones/crear
    // =====================================================================

    public function test_el_formulario_de_crear_cupon_renderiza_los_campos(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones/crear')
            ->assertOk()
            ->assertSee('id="codigo"', false)
            ->assertSee('name="codigo"', false)
            ->assertSee('name="tipo"', false)
            ->assertSee('name="valor"', false)
            ->assertSee('name="monto_minimo"', false)
            ->assertSee('name="maximo_usos_total"', false)
            ->assertSee('name="usos_por_cliente"', false)
            ->assertSee('name="aplica_a"', false)
            ->assertSee('name="inicio_en"', false)
            ->assertSee('name="fin_en"', false);
    }

    public function test_editar_un_cupon_inexistente_redirige_con_error(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/promociones/cupones/999999/editar')
            ->assertRedirect(route('admin.promociones.cupones'))
            ->assertSessionHas('error');
    }

    // =====================================================================
    //  CREACIÓN — POST /admin/promociones/cupones (store)
    // =====================================================================

    public function test_un_administrador_puede_crear_un_cupon(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/promociones/cupones', $this->datosCuponValidos(['codigo' => 'bienvenido10']))
            ->assertRedirect(route('admin.promociones.cupones'))
            ->assertSessionHas('success');

        // El código se guarda en MAYÚSCULAS (mutador del modelo).
        $this->assertDatabaseHas('cupones', [
            'codigo' => 'BIENVENIDO10',
            'tipo' => 'porcentaje',
            'valor' => '10.00',
            'activo' => true,
        ]);
    }

    public function test_el_codigo_es_obligatorio_y_unico(): void
    {
        $admin = $this->crearAdmin();
        Cupon::factory()->create(['codigo' => 'EXISTENTE']);

        $this->actingAs($admin)
            ->from('/admin/promociones/cupones/crear')
            ->post('/admin/promociones/cupones', $this->datosCuponValidos(['codigo' => '']))
            ->assertSessionHasErrors('codigo');

        // Duplicado exacto (mismas mayúsculas) → regla unique.
        $this->actingAs($admin)
            ->from('/admin/promociones/cupones/crear')
            ->post('/admin/promociones/cupones', $this->datosCuponValidos(['codigo' => 'EXISTENTE']))
            ->assertSessionHasErrors('codigo');
    }

    public function test_el_tipo_debe_ser_uno_de_los_valores_permitidos(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/promociones/cupones/crear')
            ->post('/admin/promociones/cupones', $this->datosCuponValidos(['tipo' => 'otro']))
            ->assertSessionHasErrors('tipo');
    }

    public function test_el_valor_debe_ser_mayor_a_cero(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/promociones/cupones/crear')
            ->post('/admin/promociones/cupones', $this->datosCuponValidos(['valor' => 0]))
            ->assertSessionHasErrors('valor');
    }

    public function test_la_fecha_de_fin_debe_ser_posterior_o_igual_al_inicio(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/promociones/cupones/crear')
            ->post('/admin/promociones/cupones', $this->datosCuponValidos([
                'inicio_en' => '2026-05-01 00:00:00',
                'fin_en' => '2026-04-01 00:00:00',
            ]))
            ->assertSessionHasErrors('fin_en');
    }

    public function test_un_cupon_de_categoria_requiere_seleccionar_una_categoria(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/promociones/cupones/crear')
            ->post('/admin/promociones/cupones', $this->datosCuponValidos(['aplica_a' => 'categoria']))
            ->assertSessionHasErrors('categoria_id');
    }

    public function test_un_cupon_de_producto_requiere_seleccionar_un_producto(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/promociones/cupones/crear')
            ->post('/admin/promociones/cupones', $this->datosCuponValidos(['aplica_a' => 'producto']))
            ->assertSessionHasErrors('producto_id');
    }

    // =====================================================================
    //  ACTUALIZACIÓN — PUT /admin/promociones/cupones/{id}
    // =====================================================================

    public function test_un_administrador_puede_actualizar_un_cupon(): void
    {
        $admin = $this->crearAdmin();
        $cupon = Cupon::factory()->create(['codigo' => 'VIEJO', 'valor' => 5]);

        $respuesta = $this->actingAs($admin)
            ->put('/admin/promociones/cupones/' . $cupon->id, $this->datosCuponValidos([
                'codigo' => 'nuevo10',
                'valor' => 15,
            ]));

        $respuesta->assertRedirect(route('admin.promociones.cupones'));
        $respuesta->assertSessionHas('success');

        $cupon->refresh();
        $this->assertSame('NUEVO10', $cupon->codigo);
        $this->assertSame(15.0, $cupon->valor);
    }

    public function test_actualizar_un_cupon_inexistente_redirige_con_error(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->put('/admin/promociones/cupones/999999', $this->datosCuponValidos())
            ->assertRedirect(route('admin.promociones.cupones'))
            ->assertSessionHas('error');
    }

    // =====================================================================
    //  ESTADO — POST /admin/promociones/cupones/{id}/toggle
    // =====================================================================

    public function test_un_administrador_puede_cambiar_el_estado_de_un_cupon(): void
    {
        $admin = $this->crearAdmin();
        $cupon = Cupon::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->post('/admin/promociones/cupones/' . $cupon->id . '/toggle')
            ->assertSessionHas('success');

        $this->assertFalse($cupon->fresh()->activo);
    }

    public function test_el_toggle_de_cupon_responde_json_en_peticiones_ajax(): void
    {
        $admin = $this->crearAdmin();
        $cupon = Cupon::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->postJson('/admin/promociones/cupones/' . $cupon->id . '/toggle')
            ->assertOk()
            ->assertJson(['success' => true, 'activo' => false]);
    }

    // =====================================================================
    //  ELIMINACIÓN — DELETE /admin/promociones/cupones/{id}
    // =====================================================================

    public function test_un_administrador_puede_eliminar_un_cupon(): void
    {
        $admin = $this->crearAdmin();
        $cupon = Cupon::factory()->create();

        $this->actingAs($admin)
            ->delete('/admin/promociones/cupones/' . $cupon->id)
            ->assertRedirect(route('admin.promociones.cupones'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('cupones', ['id' => $cupon->id]);
    }

    public function test_eliminar_un_cupon_inexistente_redirige_con_error(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->delete('/admin/promociones/cupones/999999')
            ->assertRedirect(route('admin.promociones.cupones'))
            ->assertSessionHas('error');
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Datos válidos para crear/actualizar un cupón (campos del formulario admin).
     */
    protected function datosCuponValidos(array $sobrescribir = []): array
    {
        return array_merge([
            'codigo' => 'DESCUENTO-' . strtoupper(uniqid()),
            'tipo' => 'porcentaje',
            'valor' => 10,
            'monto_minimo' => 0,
            'maximo_usos_total' => 100,
            'usos_por_cliente' => 1,
            'activo' => 1,
            'aplica_a' => 'catalogo',
            'inicio_en' => now()->subDay()->format('Y-m-d H:i:s'),
            'fin_en' => now()->addMonth()->format('Y-m-d H:i:s'),
        ], $sobrescribir);
    }
}
