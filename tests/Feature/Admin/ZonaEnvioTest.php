<?php

namespace Tests\Feature\Admin;

use App\Models\ZonaEnvio;

/**
 * Pruebas del módulo ADMIN de Zonas de Envío (FASE 9).
 *
 * Cubre las rutas reales:
 *   GET    /admin/configuracion/zonas-envio             → index
 *   POST   /admin/configuracion/zonas-envio             → store
 *   PUT    /admin/configuracion/zonas-envio/{zonaEnvio} → update
 *   POST   /admin/configuracion/zonas-envio/{zonaEnvio}/toggle → toggle
 *   DELETE /admin/configuracion/zonas-envio/{zonaEnvio} → destroy
 *
 * Esquema verificado: la tabla `zonas_envio` tiene `nombre`, `provincias` (text,
 * nullable), `costo` (con CHECK `costo >= 0`), `tiempo_estimado` y `activo`.
 * HALLAZGO: la app SOLO administra "nombre/costo/activo"; las columnas
 * `provincias` y `tiempo_estimado` existen en el esquema pero no se gestionan
 * (ni en $fillable del modelo, ni en el controlador, ni en la vista).
 */
class ZonaEnvioTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — Solo administradores pueden acceder
    // =====================================================================

    public function test_el_acceso_a_las_zonas_de_envio_requiere_iniciar_sesion(): void
    {
        $this->get('/admin/configuracion/zonas-envio')
            ->assertRedirect('/login');
    }

    public function test_un_cliente_no_puede_acceder_a_las_zonas_de_envio(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/admin/configuracion/zonas-envio')
            ->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_a_las_zonas_de_envio(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/configuracion/zonas-envio')
            ->assertOk();
    }

    // =====================================================================
    //  LISTADO — GET /admin/configuracion/zonas-envio
    // =====================================================================

    public function test_el_listado_muestra_el_estado_vacio_cuando_no_hay_zonas(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/configuracion/zonas-envio')
            ->assertOk()
            ->assertSee('Todavía no existen zonas de envío')
            ->assertSee('Nueva zona de envío');
    }

    public function test_el_listado_muestra_nombre_costo_y_estado_de_cada_zona(): void
    {
        $admin = $this->crearAdmin();
        ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00, 'activo' => true]);
        ZonaEnvio::factory()->create(['nombre' => 'Chiriquí', 'costo' => 8.50, 'activo' => false]);

        $this->actingAs($admin)
            ->get('/admin/configuracion/zonas-envio')
            ->assertOk()
            ->assertSee('Panamá')
            ->assertSee('$5.00', false)
            ->assertSee('Chiriquí')
            ->assertSee('$8.50', false)
            ->assertSee('<span>Activa</span>', false)
            ->assertSee('<span>Inactiva</span>', false);
    }

    // =====================================================================
    //  FORMULARIO — Campos del modal crear/editar (siempre presente en la página)
    // =====================================================================

    public function test_el_formulario_renderiza_los_campos_nombre_costo_y_activo(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/configuracion/zonas-envio')
            ->assertOk()
            ->assertSee('id="input-zona-nombre"', false)
            ->assertSee('name="nombre"', false)
            ->assertSee('id="input-zona-costo"', false)
            ->assertSee('name="costo"', false)
            ->assertSee('id="input-zona-activo"', false)
            ->assertSee('name="activo"', false)
            ->assertSee('id="modal-zona-envio"', false);
    }

    public function test_el_formulario_no_administra_provincias_ni_tiempo_estimado(): void
    {
        // HALLAZGO: aunque el esquema tiene las columnas "provincias" (texto, puede
        // listar varias provincias por zona) y "tiempo_estimado", la vista NO las
        // expone. El modelo solo gestiona "nombre/costo/activo".
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/configuracion/zonas-envio')
            ->assertOk()
            ->assertDontSee('name="provincias"', false)
            ->assertDontSee('name="tiempo_estimado"', false);
    }

    // =====================================================================
    //  CREACIÓN — POST /admin/configuracion/zonas-envio (store)
    // =====================================================================

    public function test_un_administrador_puede_crear_una_zona_de_envio(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/configuracion/zonas-envio', ['nombre' => 'Panamá Oeste', 'costo' => 6.75])
            ->assertRedirect(route('admin.zonas-envio.index'))
            ->assertSessionHas('success');

        $zona = ZonaEnvio::where('nombre', 'Panamá Oeste')->first();
        $this->assertNotNull($zona);
        $this->assertSame('6.75', $zona->costo);
    }

    public function test_una_zona_se_crea_activa_por_defecto(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/configuracion/zonas-envio', ['nombre' => 'Coclé', 'costo' => 4.00]);

        $this->assertDatabaseHas('zonas_envio', ['nombre' => 'Coclé', 'activo' => true]);
    }

    public function test_una_zona_se_puede_crear_como_inactiva(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/configuracion/zonas-envio', ['nombre' => 'Darién', 'costo' => 10.00, 'activo' => 0]);

        $this->assertDatabaseHas('zonas_envio', ['nombre' => 'Darién', 'activo' => false]);
    }

    public function test_la_creacion_ignora_las_columnas_no_administradas(): void
    {
        // HALLAZGO: el campo "provincias" no está en $fillable del modelo, así que
        // aunque se envíe en el request, no se persiste (columna muerta).
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/configuracion/zonas-envio', [
                'nombre' => 'Multi Provincia',
                'costo' => 5.00,
                'provincias' => 'Panamá, Colón',
            ]);

        $zona = ZonaEnvio::where('nombre', 'Multi Provincia')->first();
        $this->assertNotNull($zona);
        $this->assertNull($zona->provincias);
    }

    // =====================================================================
    //  VALIDACIÓN — nombre obligatorio, costo >= 0 (CHECK zonas_envio_costo_check)
    // =====================================================================

    public function test_el_nombre_es_obligatorio_al_crear_una_zona(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/configuracion/zonas-envio')
            ->post('/admin/configuracion/zonas-envio', ['nombre' => '', 'costo' => 5.00])
            ->assertSessionHasErrors('nombre');
    }

    public function test_el_costo_es_obligatorio_al_crear_una_zona(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/configuracion/zonas-envio')
            ->post('/admin/configuracion/zonas-envio', ['nombre' => 'Veraguas', 'costo' => ''])
            ->assertSessionHasErrors('costo');
    }

    public function test_el_costo_no_puede_ser_negativo(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/configuracion/zonas-envio')
            ->post('/admin/configuracion/zonas-envio', ['nombre' => 'Herrera', 'costo' => -5])
            ->assertSessionHasErrors('costo');

        $this->assertDatabaseMissing('zonas_envio', ['nombre' => 'Herrera']);
    }

    public function test_el_costo_puede_ser_cero_envio_gratis(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/configuracion/zonas-envio', ['nombre' => 'Los Santos', 'costo' => 0]);

        $this->assertDatabaseHas('zonas_envio', ['nombre' => 'Los Santos', 'costo' => '0.00']);
    }

    // =====================================================================
    //  ACTUALIZACIÓN — PUT /admin/configuracion/zonas-envio/{zonaEnvio}
    // =====================================================================

    public function test_un_administrador_puede_actualizar_una_zona_de_envio(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00, 'activo' => true]);

        $respuesta = $this->actingAs($admin)
            ->put('/admin/configuracion/zonas-envio/' . $zona->id, [
                'nombre' => 'Panamá y Colón',
                'costo' => 7.50,
                'activo' => 1,
            ]);

        $respuesta->assertRedirect(route('admin.zonas-envio.index'));
        $respuesta->assertSessionHas('success');

        $zona->refresh();
        $this->assertSame('Panamá y Colón', $zona->nombre);
        $this->assertSame('7.50', $zona->costo);
        $this->assertTrue($zona->activo);
    }

    public function test_la_actualizacion_sin_campo_activo_desactiva_la_zona(): void
    {
        // OJO (hallazgo): el checkbox desmarcado no envía el campo "activo" y en
        // "update" el fallback es `false`, por lo que guardar sin el campo la desactiva.
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->put('/admin/configuracion/zonas-envio/' . $zona->id, [
                'nombre' => $zona->nombre,
                'costo' => $zona->costo,
            ]);

        $this->assertFalse($zona->fresh()->activo);
    }

    // =====================================================================
    //  ESTADO — POST /admin/configuracion/zonas-envio/{zonaEnvio}/toggle
    // =====================================================================

    public function test_un_administrador_puede_activar_y_desactivar_una_zona(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->post('/admin/configuracion/zonas-envio/' . $zona->id . '/toggle')
            ->assertRedirect(route('admin.zonas-envio.index'))
            ->assertSessionHas('success');

        $this->assertFalse($zona->fresh()->activo);

        $this->actingAs($admin)
            ->post('/admin/configuracion/zonas-envio/' . $zona->id . '/toggle')
            ->assertRedirect(route('admin.zonas-envio.index'))
            ->assertSessionHas('success');

        $this->assertTrue($zona->fresh()->activo);
    }

    // =====================================================================
    //  ELIMINACIÓN — DELETE /admin/configuracion/zonas-envio/{zonaEnvio}
    // =====================================================================

    public function test_un_administrador_puede_eliminar_una_zona_de_envio(): void
    {
        $admin = $this->crearAdmin();
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Veraguas']);

        $respuesta = $this->actingAs($admin)
            ->delete('/admin/configuracion/zonas-envio/' . $zona->id);

        $respuesta->assertRedirect(route('admin.zonas-envio.index'));
        $respuesta->assertSessionHas('success');

        $this->assertDatabaseMissing('zonas_envio', ['id' => $zona->id]);
    }
}
