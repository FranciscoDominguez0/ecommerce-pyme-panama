<?php

namespace Tests\Feature\Inventario;

use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de acceso al módulo de inventario (FASE 14).
 *
 * Rutas cubiertas (todas tras el middleware 'role:admin|super_admin|Admin'):
 *   GET /admin/inventario             → index (historial de movimientos)
 *   GET /admin/inventario/stock       → stock actual
 *   GET /admin/inventario/entrada     → formulario de entrada
 *   GET /admin/inventario/salida      → formulario de salida
 *   GET /admin/inventario/ajuste      → formulario de ajuste
 */
class AccesoInventarioTest extends BaseAdminTest
{
    #[Test]
    public function un_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $this->get('/admin/inventario')
            ->assertRedirect('/login');
    }

    #[Test]
    public function un_cliente_no_puede_acceder_al_historial_de_movimientos(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get('/admin/inventario')
            ->assertForbidden();
    }

    #[Test]
    public function un_cliente_no_puede_acceder_al_stock_ni_a_los_formularios(): void
    {
        $cliente = $this->crearCliente();

        foreach (['/admin/inventario/stock', '/admin/inventario/entrada', '/admin/inventario/salida', '/admin/inventario/ajuste'] as $ruta) {
            $this->actingAs($cliente)
                ->get($ruta)
                ->assertForbidden();
        }
    }

    #[Test]
    public function un_administrador_puede_acceder_al_historial_de_movimientos(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/inventario')
            ->assertOk();
    }

    #[Test]
    public function un_administrador_puede_acceder_al_stock_actual(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->get('/admin/inventario/stock')
            ->assertOk();
    }

    #[Test]
    public function un_administrador_puede_acceder_a_los_formularios_de_entrada_salida_y_ajuste(): void
    {
        $admin = $this->crearAdmin();

        foreach (['/admin/inventario/entrada', '/admin/inventario/salida', '/admin/inventario/ajuste'] as $ruta) {
            $this->actingAs($admin)
                ->get($ruta)
                ->assertOk();
        }
    }
}
