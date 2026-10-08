<?php

namespace Tests\Feature\Admin;

use App\Models\CourierSucursal;
use App\Models\ZonaEnvio;

/**
 * Pruebas del módulo ADMIN de Zonas de Envío (FASE 9).
 *
 * Cubre las rutas reales:
 *   GET    /admin/zonas-envio             → index
 *   POST   /admin/zonas-envio             → store
 *   PUT    /admin/zonas-envio/{zonaEnvio} → update
 *   POST   /admin/zonas-envio/{zonaEnvio}/toggle → toggle
 *   DELETE /admin/zonas-envio/{zonaEnvio} → destroy
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
        $this->get('/admin/zonas-envio')
            ->assertRedirect('/login');
    }

    public function test_un_cliente_no_puede_acceder_a_las_zonas_de_envio(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/admin/zonas-envio')
            ->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_a_las_zonas_de_envio(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/zonas-envio')
            ->assertOk();
    }

    // =====================================================================
    //  LISTADO — GET /admin/zonas-envio
    // =====================================================================

    public function test_el_listado_muestra_el_estado_vacio_cuando_no_hay_zonas(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/zonas-envio')
            ->assertOk();
    }

    public function test_el_listado_muestra_sucursales_courier(): void
    {
        $admin = $this->crearAdmin();
        CourierSucursal::factory()->create(['zona' => 'Panamá', 'courier' => 'Uno Express', 'sucursal' => 'Miraflores', 'tarifa_uno_hasta_7lb' => 6.50, 'activo' => true]);
        CourierSucursal::factory()->create(['zona' => 'Chiriquí', 'courier' => 'Fletes Chavale', 'sucursal' => 'David', 'tarifa_uno_hasta_7lb' => 8.50, 'activo' => false]);

        $this->actingAs($admin)
            ->get('/admin/zonas-envio')
            ->assertOk()
            ->assertSee('Panamá')
            ->assertSee('Chiriquí');
    }

    // =====================================================================
    //  FORMULARIO — Campos del modal crear/editar (siempre presente en la página)
    // =====================================================================

    public function test_el_formulario_renderiza_campos_de_courier(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/zonas-envio')
            ->assertOk()
            ->assertSee('name="zona"', false)
            ->assertSee('name="courier"', false)
            ->assertSee('name="sucursal"', false)
            ->assertSee('name="tarifa_uno_hasta_7lb"', false);
    }

    public function test_el_formulario_no_administra_provincias_ni_tiempo_estimado(): void
    {
        // La vista de zonas-envio gestiona CourierSucursal, no ZonaEnvio.
        // No hay campos de provincias ni tiempo_estimado en el formulario actual.
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/zonas-envio')
            ->assertOk()
            ->assertDontSee('name="provincias"', false)
            ->assertDontSee('name="tiempo_estimado"', false);
    }

    // =====================================================================
    //  CREACIÓN — POST /admin/zonas-envio (store)
    // =====================================================================

    public function test_un_administrador_puede_crear_una_sucursal_courier(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/zonas-envio', [
                'zona'                 => 'Panamá Oeste',
                'courier'             => 'Uno Express',
                'sucursal'            => 'La Chorrera',
                'tarifa_uno_hasta_7lb' => 6.75,
            ])
            ->assertRedirect(route('admin.zonas-envio.index'))
            ->assertSessionHas('success');

        $sucursal = CourierSucursal::where('zona', 'Panamá Oeste')->first();
        $this->assertNotNull($sucursal);
        $this->assertSame('6.75', (string) $sucursal->tarifa_uno_hasta_7lb);
    }

    public function test_una_sucursal_courier_se_crea_activa_por_defecto(): void
    {
        $admin = $this->crearAdmin();
        $this->withoutExceptionHandling();

        $this->actingAs($admin)
            ->post('/admin/zonas-envio', [
                'zona'                 => 'Coclé',
                'courier'             => 'Fletes Chavale',
                'sucursal'            => 'Penonomé',
                'tarifa_uno_hasta_7lb' => 7.00,
            ]);

        $sucursal = CourierSucursal::where('zona', 'Coclé')->first();
        $this->assertNotNull($sucursal);
        $this->assertTrue($sucursal->activo);
    }

    public function test_una_sucursal_courier_se_puede_crear_como_inactiva(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/zonas-envio', [
                'zona'                 => 'Darién',
                'courier'             => 'Uno Express',
                'sucursal'            => 'La Palma',
                'tarifa_uno_hasta_7lb' => 12.00,
                'activo'              => 0,
            ]);

        $sucursal = CourierSucursal::where('zona', 'Darién')->first();
        $this->assertNotNull($sucursal);
        $this->assertFalse($sucursal->activo);
    }

    public function test_la_creacion_requiere_campos_obligatorios_del_courier(): void
    {
        $admin = $this->crearAdmin();

        // Sin campos obligatorios debe fallar la validación
        $this->actingAs($admin)
            ->from('/admin/zonas-envio')
            ->post('/admin/zonas-envio', [])
            ->assertSessionHasErrors(['zona', 'courier', 'sucursal', 'tarifa_uno_hasta_7lb']);
    }

    // =====================================================================
    //  VALIDACIÓN — nombre obligatorio, costo >= 0 (CHECK zonas_envio_costo_check)
    // =====================================================================

    public function test_la_zona_es_obligatoria_al_crear_courier(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/zonas-envio')
            ->post('/admin/zonas-envio', [
                'zona'                 => '',
                'courier'             => 'Uno Express',
                'sucursal'            => 'Miraflores',
                'tarifa_uno_hasta_7lb' => 6.50,
            ])
            ->assertSessionHasErrors('zona');
    }

    public function test_la_tarifa_es_obligatoria_al_crear_courier(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/zonas-envio')
            ->post('/admin/zonas-envio', [
                'zona'                 => 'Veraguas',
                'courier'             => 'Uno Express',
                'sucursal'            => 'Santiago',
                'tarifa_uno_hasta_7lb' => '',
            ])
            ->assertSessionHasErrors('tarifa_uno_hasta_7lb');
    }

    public function test_la_tarifa_no_puede_ser_negativa(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->from('/admin/zonas-envio')
            ->post('/admin/zonas-envio', [
                'zona'                 => 'Herrera',
                'courier'             => 'Uno Express',
                'sucursal'            => 'Chitré',
                'tarifa_uno_hasta_7lb' => -5,
            ])
            ->assertSessionHasErrors('tarifa_uno_hasta_7lb');

        $this->assertDatabaseMissing('courier_sucursales', ['zona' => 'Herrera']);
    }

    public function test_la_tarifa_puede_ser_cero(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/zonas-envio', [
                'zona'                 => 'Los Santos',
                'courier'             => 'Uno Express',
                'sucursal'            => 'Las Tablas',
                'tarifa_uno_hasta_7lb' => 0,
            ]);

        $this->assertDatabaseHas('courier_sucursales', ['zona' => 'Los Santos', 'tarifa_uno_hasta_7lb' => '0.00']);
    }

    // =====================================================================
    //  ACTUALIZACIÓN — PUT /admin/zonas-envio/{zonaEnvio}
    // =====================================================================

    public function test_un_administrador_puede_actualizar_una_sucursal_courier(): void
    {
        $admin = $this->crearAdmin();
        $sucursal = CourierSucursal::factory()->create([
            'zona'                 => 'Panamá',
            'courier'             => 'Uno Express',
            'sucursal'            => 'Miraflores',
            'tarifa_uno_hasta_7lb' => 6.50,
            'activo'              => true,
        ]);

        $respuesta = $this->actingAs($admin)
            ->put('/admin/zonas-envio/' . $sucursal->id, [
                'zona'                 => 'Panamá y Colón',
                'courier'             => 'Uno Express',
                'sucursal'            => 'Miraflores',
                'tarifa_uno_hasta_7lb' => 7.50,
                'activo'              => 1,
            ]);

        $respuesta->assertRedirect(route('admin.zonas-envio.index'));
        $respuesta->assertSessionHas('success');

        $sucursal->refresh();
        $this->assertSame('Panamá y Colón', $sucursal->zona);
        $this->assertSame('7.50', (string) $sucursal->tarifa_uno_hasta_7lb);
        $this->assertTrue($sucursal->activo);
    }

    public function test_la_actualizacion_sin_campo_activo_desactiva_la_zona(): void
    {
        // OJO (hallazgo): el checkbox desmarcado no envía el campo "activo" y en
        // "update" el fallback es `false`, por lo que guardar sin el campo la desactiva.
        $admin = $this->crearAdmin();
        $sucursal = CourierSucursal::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->put('/admin/zonas-envio/' . $sucursal->id, [
                'zona' => $sucursal->zona,
                'courier' => $sucursal->courier,
                'sucursal' => $sucursal->sucursal,
                'tarifa_uno_hasta_7lb' => $sucursal->tarifa_uno_hasta_7lb,
            ]);

        $this->assertFalse($sucursal->fresh()->activo);
    }

    // =====================================================================
    //  ESTADO — POST /admin/zonas-envio/{zonaEnvio}/toggle
    // =====================================================================

    public function test_un_administrador_puede_activar_y_desactivar_una_zona(): void
    {
        $admin = $this->crearAdmin();
        $sucursal = CourierSucursal::factory()->create(['activo' => true]);

        $this->actingAs($admin)
            ->post('/admin/zonas-envio/' . $sucursal->id . '/toggle')
            ->assertRedirect(route('admin.zonas-envio.index'))
            ->assertSessionHas('success');

        $this->assertFalse($sucursal->fresh()->activo);

        $this->actingAs($admin)
            ->post('/admin/zonas-envio/' . $sucursal->id . '/toggle')
            ->assertRedirect(route('admin.zonas-envio.index'))
            ->assertSessionHas('success');

        $this->assertTrue($sucursal->fresh()->activo);
    }

    // =====================================================================
    //  ELIMINACIÓN — DELETE /admin/zonas-envio/{zonaEnvio}
    // =====================================================================

    public function test_un_administrador_puede_eliminar_una_zona_de_envio(): void
    {
        $admin = $this->crearAdmin();
        $sucursal = CourierSucursal::factory()->create(['zona' => 'Veraguas']);

        $respuesta = $this->actingAs($admin)
            ->delete('/admin/zonas-envio/' . $sucursal->id);

        $respuesta->assertRedirect(route('admin.zonas-envio.index'));
        $respuesta->assertSessionHas('success');

        $this->assertDatabaseMissing('courier_sucursales', ['id' => $sucursal->id]);
    }
}
