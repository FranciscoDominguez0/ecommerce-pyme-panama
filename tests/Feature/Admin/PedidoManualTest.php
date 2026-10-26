<?php

namespace Tests\Feature\Admin;

use App\Models\Factura;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\VarianteProducto;
use App\Models\ZonaEnvio;

class PedidoManualTest extends BaseAdminTest
{
    public function test_invitado_no_puede_acceder_a_crear_pedido_manual(): void
    {
        $this->get('/admin/pedidos/crear')->assertRedirect('/login');
    }

    public function test_cliente_no_puede_acceder_a_crear_pedido_manual(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)->get('/admin/pedidos/crear')->assertForbidden();
    }

    public function test_admin_puede_ver_pantalla_de_crear_pedido_manual(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get('/admin/pedidos/crear');

        $response->assertOk();
        $response->assertViewIs('admin.pedidos.crear');
        $response->assertSee('Nueva Venta');
    }

    public function test_admin_puede_registrar_venta_manual_con_cliente_existente(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();

        $producto = Producto::factory()->create([
            'precio' => 50.00,
            'stock' => 10,
            'activo' => true,
        ]);

        $payload = [
            'tipo_cliente' => 'existente',
            'usuario_id' => $cliente->id,
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 2,
                    'precio_unitario' => 50.00,
                ],
            ],
            'tipo_entrega' => 'retiro_local',
            'metodo_pago' => 'efectivo',
            'descuento_manual' => 0.00,
            'marcar_pagado' => '1',
            'emitir_factura' => '1',
            'notas_cliente' => 'Cliente retira en sucursal',
            'notas_internas' => 'Atendido en mostrador',
        ];

        $response = $this->actingAs($admin)->post('/admin/pedidos/crear', $payload);
        $pedido = Pedido::where('usuario_id', $cliente->id)->latest('id')->first();

        $this->assertNotNull($pedido);
        $response->assertRedirect(route('admin.pedidos.detalle', $pedido->id));
        $response->assertSessionHas('toast_success');

        // Verificar vendedor asignado y correlativo oficial (#PM-XXXXXX)
        $this->assertEquals($admin->id, $pedido->vendedor_id);
        $this->assertStringStartsWith('#PM-', $pedido->numero_pedido);

        // Subtotal = 100, ITBMS 7% = 7, Total = 107
        $this->assertEquals(100.00, (float) $pedido->subtotal);
        $this->assertEquals(7.00, (float) $pedido->itbms_monto);
        $this->assertEquals(107.00, (float) $pedido->total);

        // Verificar que el stock se descontó correctamente
        $this->assertEquals(8, $producto->fresh()->stock);

        // Verificar movimiento de inventario
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'tipo' => 'salida',
            'cantidad' => 2,
        ]);

        // Verificar estado pago_confirmado
        $this->assertDatabaseHas('estados_pedido', [
            'pedido_id' => $pedido->id,
            'estado' => 'pago_confirmado',
        ]);

        // Verificar factura emitida
        $this->assertDatabaseHas('facturas', [
            'pedido_id' => $pedido->id,
            'estado' => 'emitida',
        ]);
    }

    public function test_admin_puede_registrar_venta_manual_creando_cliente_nuevo(): void
    {
        $admin = $this->crearAdmin();

        $producto = Producto::factory()->create([
            'precio' => 20.00,
            'stock' => 5,
            'activo' => true,
        ]);

        $payload = [
            'tipo_cliente' => 'nuevo',
            'nuevo_nombre' => 'María',
            'nuevo_apellido' => 'González',
            'nuevo_email' => 'maria.pos@example.com',
            'nuevo_telefono' => '67891234',
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 1,
                    'precio_unitario' => 20.00,
                ],
            ],
            'tipo_entrega' => 'retiro_local',
            'metodo_pago' => 'punto_venta',
            'marcar_pagado' => '1',
            'emitir_factura' => '0',
        ];

        $response = $this->actingAs($admin)->post('/admin/pedidos/crear', $payload);

        $clienteNuevo = Usuario::where('email', 'maria.pos@example.com')->first();
        $this->assertNotNull($clienteNuevo);
        $this->assertEquals('María', $clienteNuevo->nombre);

        $pedido = Pedido::where('usuario_id', $clienteNuevo->id)->first();
        $this->assertNotNull($pedido);
        $this->assertEquals($admin->id, $pedido->vendedor_id);
        $this->assertEquals(4, $producto->fresh()->stock);
    }

    public function test_admin_puede_registrar_venta_manual_con_variante_de_producto(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();

        $producto = Producto::factory()->create([
            'precio' => 30.00,
            'stock' => 20,
            'activo' => true,
        ]);

        $variante = VarianteProducto::create([
            'producto_id' => $producto->id,
            'nombre' => 'Talla M - Azul',
            'sku' => 'VAR-TEST-001',
            'precio' => 35.00,
            'stock' => 8,
            'activo' => true,
        ]);

        $payload = [
            'tipo_cliente' => 'existente',
            'usuario_id' => $cliente->id,
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'variante_id' => $variante->id,
                    'cantidad' => 3,
                    'precio_unitario' => 35.00,
                ],
            ],
            'tipo_entrega' => 'retiro_local',
            'metodo_pago' => 'yappy',
            'marcar_pagado' => '1',
        ];

        $response = $this->actingAs($admin)->post('/admin/pedidos/crear', $payload);

        $pedido = Pedido::where('usuario_id', $cliente->id)->latest('id')->first();
        $this->assertNotNull($pedido);

        // Variante stock descontada
        $this->assertEquals(5, $variante->fresh()->stock);

        $this->assertDatabaseHas('items_pedido', [
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'variante_producto_id' => $variante->id,
            'cantidad' => 3,
            'precio_unitario' => 35.00,
        ]);
    }

    public function test_venta_manual_falla_si_stock_insuficiente(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();

        $producto = Producto::factory()->create([
            'precio' => 15.00,
            'stock' => 2,
            'activo' => true,
        ]);

        $payload = [
            'tipo_cliente' => 'existente',
            'usuario_id' => $cliente->id,
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 10, // Más que el stock disponible
                    'precio_unitario' => 15.00,
                ],
            ],
            'tipo_entrega' => 'retiro_local',
            'metodo_pago' => 'efectivo',
        ];

        $response = $this->actingAs($admin)->from('/admin/pedidos/crear')->post('/admin/pedidos/crear', $payload);

        $response->assertRedirect('/admin/pedidos/crear');
        $response->assertSessionHas('toast_error');

        // El stock no debe haber cambiado
        $this->assertEquals(2, $producto->fresh()->stock);
        $this->assertDatabaseMissing('pedidos', [
            'usuario_id' => $cliente->id,
        ]);
    }

    public function test_super_admin_puede_registrar_venta_manual_sin_depender_de_rol_vendedor(): void
    {
        $superAdmin = $this->crearSuperAdmin();
        $cliente = $this->crearCliente();

        $producto = Producto::factory()->create([
            'precio' => 45.00,
            'stock' => 10,
            'activo' => true,
        ]);

        $payload = [
            'tipo_cliente' => 'existente',
            'usuario_id' => $cliente->id,
            'items' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 1,
                    'precio_unitario' => 45.00,
                ],
            ],
            'tipo_entrega' => 'retiro_local',
            'metodo_pago' => 'efectivo',
            'marcar_pagado' => '1',
        ];

        $response = $this->actingAs($superAdmin)->post('/admin/pedidos/crear', $payload);

        $pedido = Pedido::where('usuario_id', $cliente->id)->latest('id')->first();
        $this->assertNotNull($pedido);
        $this->assertEquals($superAdmin->id, $pedido->vendedor_id);
        $response->assertRedirect(route('admin.pedidos.detalle', $pedido->id));
    }

    public function test_admin_puede_registrar_cliente_rapido_desde_modal(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->postJson('/admin/pedidos/cliente-rapido', [
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'email' => 'carlos.mendoza@ejemplo.com',
            'telefono' => '+507 6890-1122',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'cliente' => [
                'nombre' => 'Carlos',
                'apellido' => 'Mendoza',
                'email' => 'carlos.mendoza@ejemplo.com',
                'telefono' => '+507 6890-1122',
            ],
        ]);

        $this->assertDatabaseHas('usuarios', [
            'email' => 'carlos.mendoza@ejemplo.com',
            'nombre' => 'Carlos',
        ]);

        $usuario = Usuario::where('email', 'carlos.mendoza@ejemplo.com')->first();
        $this->assertTrue($usuario->hasRole('cliente'));
    }

    public function test_cliente_rapido_reutiliza_cliente_si_email_ya_existe(): void
    {
        $admin = $this->crearAdmin();
        $existente = $this->crearCliente(['email' => 'existente@ejemplo.com', 'nombre' => 'Ana']);

        $response = $this->actingAs($admin)->postJson('/admin/pedidos/cliente-rapido', [
            'nombre' => 'Ana Nuevo Nombre',
            'email' => 'existente@ejemplo.com',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'cliente' => [
                'id' => $existente->id,
                'email' => 'existente@ejemplo.com',
            ],
        ]);
    }
}
