<?php

namespace Tests\Feature\Cliente;

use App\Models\Categoria;
use App\Models\EstadoPedido;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MisPedidosViewTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $cliente;
    protected Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->artisan('db:seed', ['--class' => 'RolesSeeder']);

        $this->cliente = Usuario::create([
            'nombre' => 'Carlos',
            'apellido' => 'Mendoza',
            'email' => 'carlos@example.com',
            'password_hash' => Hash::make('password123'),
        ]);
        $this->cliente->assignRole('cliente');

        $this->categoria = Categoria::create([
            'nombre' => 'Electrónica',
            'slug' => 'electronica',
            'activo' => true,
        ]);
    }

    public function test_usuario_autenticado_puede_ver_sus_pedidos_en_formato_tarjetas(): void
    {
        $producto = Producto::create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Teclado Mecánico RGB',
            'slug' => 'teclado-mecanico-rgb',
            'sku' => 'TEC-001',
            'precio' => 85.00,
            'stock' => 10,
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'usuario_id' => $this->cliente->id,
            'numero_pedido' => 'PED-2026-TEST01',
            'subtotal' => 85.00,
            'total' => 85.00,
            'costo_envio' => 0.00,
            'metodo_pago' => 'transferencia',
        ]);

        ItemPedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => 85.00,
            'subtotal' => 85.00,
        ]);

        EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'estado' => 'entregado',
            'creado_en' => now(),
        ]);

        $response = $this->actingAs($this->cliente)->get(route('cliente.perfil.pedidos.index'));

        $response->assertStatus(200);
        $response->assertSee('Mis Pedidos');
        $response->assertSee('#PED-2026-TEST01');
        $response->assertSee('Teclado Mecánico RGB');
        $response->assertSee('Entregado');
        $response->assertSee('$85.00');
    }

    public function test_busqueda_por_nombre_de_producto_en_pedidos(): void
    {
        $producto1 = Producto::create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Auriculares Inalámbricos',
            'slug' => 'auriculares-inalambricos',
            'sku' => 'AUR-001',
            'precio' => 45.00,
            'stock' => 10,
            'activo' => true,
        ]);

        $producto2 = Producto::create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Mouse Gamer Ergonómico',
            'slug' => 'mouse-gamer-ergonomico',
            'sku' => 'MOU-001',
            'precio' => 30.00,
            'stock' => 10,
            'activo' => true,
        ]);

        $pedido1 = Pedido::create([
            'usuario_id' => $this->cliente->id,
            'numero_pedido' => 'PED-AURICULARES',
            'subtotal' => 45.00,
            'total' => 45.00,
            'costo_envio' => 0.00,
            'metodo_pago' => 'transferencia',
        ]);
        ItemPedido::create([
            'pedido_id' => $pedido1->id,
            'producto_id' => $producto1->id,
            'cantidad' => 1,
            'precio_unitario' => 45.00,
            'subtotal' => 45.00,
        ]);
        EstadoPedido::create([
            'pedido_id' => $pedido1->id,
            'estado' => 'entregado',
            'creado_en' => now(),
        ]);

        $pedido2 = Pedido::create([
            'usuario_id' => $this->cliente->id,
            'numero_pedido' => 'PED-MOUSE',
            'subtotal' => 30.00,
            'total' => 30.00,
            'costo_envio' => 0.00,
            'metodo_pago' => 'transferencia',
        ]);
        ItemPedido::create([
            'pedido_id' => $pedido2->id,
            'producto_id' => $producto2->id,
            'cantidad' => 1,
            'precio_unitario' => 30.00,
            'subtotal' => 30.00,
        ]);
        EstadoPedido::create([
            'pedido_id' => $pedido2->id,
            'estado' => 'pendiente',
            'creado_en' => now(),
        ]);

        $response = $this->actingAs($this->cliente)->get(route('cliente.perfil.pedidos.index', ['buscar' => 'Auriculares']));

        $response->assertStatus(200);
        $response->assertSee('PED-AURICULARES');
        $response->assertDontSee('PED-MOUSE');
    }

    public function test_cliente_puede_ver_detalle_de_pedido_con_estilo_cards(): void
    {
        $producto = Producto::create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Smartphone Galaxy Ultra',
            'slug' => 'smartphone-galaxy-ultra',
            'sku' => 'PHO-001',
            'precio' => 999.00,
            'stock' => 5,
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'usuario_id' => $this->cliente->id,
            'numero_pedido' => 'PED-DETALLE-TEST',
            'subtotal' => 999.00,
            'total' => 999.00,
            'costo_envio' => 0.00,
            'metodo_pago' => 'stripe',
        ]);

        ItemPedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => 999.00,
            'subtotal' => 999.00,
        ]);

        EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'estado' => 'en_transito',
            'comentario' => 'El paquete salió de la bodega central.',
            'creado_en' => now(),
        ]);

        // Crear factura asociada para validar botón de descarga
        \App\Models\Factura::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $this->cliente->id,
            'numero' => 'FAC-TEST-001',
            'serie' => 'F',
            'total' => 999.00,
            'subtotal' => 999.00,
            'itbms' => 0.00,
            'estado' => 'emitida',
        ]);

        $response = $this->actingAs($this->cliente)->get(route('cliente.perfil.pedidos.detalle', $pedido->id));

        $response->assertStatus(200);
        $response->assertSee('Rastreo de tu Pedido');
        $response->assertSee('PED-DETALLE-TEST');
        $response->assertSee('Smartphone Galaxy Ultra');
        $response->assertSee('Progreso de Entrega');
        $response->assertSee('Historial del Pedido');
        $response->assertSee('Información de Rastreo');
        $response->assertSee('Productos del Pedido');
        $response->assertSee('$999.00');

        // Comprobar que no hay formulario de búsqueda de pedido
        $response->assertDontSee('Buscar Pedido');

        // Validar que todo esté en español
        $response->assertSee('Imprimir Recibo');
        $response->assertSee('Descargar Factura');
        $response->assertSee('Realizado');
        $response->assertSee('Confirmado');
        $response->assertSee('En camino');
        $response->assertSee('Entregado');
        $response->assertSee('Actual');
        $response->assertSee('Contactar Soporte');
        $response->assertSee('Seguir Comprando');
        $response->assertDontSee('>Ordered<', false);
        $response->assertDontSee('>Confirmed<', false);
        $response->assertDontSee('>Shipped<', false);
        $response->assertDontSee('>Delivered<', false);
        $response->assertDontSee('>Current<', false);
        $response->assertDontSee('Contact Support');

        // Validar que el botón Imprimir Recibo apunta al PDF oficial en modo inline para imprimir
        $response->assertSee('Imprimir Recibo');
        $response->assertSee(route('cliente.facturas.pdf', [$pedido->factura->id, 'modo' => 'inline']));

        // Validar logotipo de pago (Stripe / Visa / Mastercard)
        $response->assertSee('images/stripe.webp');

        // Al no estar entregado, NO debe mostrar ninguna sección ni aviso de reseña
        $response->assertDontSee('Escribir opinión');
        $response->assertDontSee('Las reseñas y comentarios');
        $response->assertDontSee('Entrega pendiente');

        // Cuando pasa a entregado, debe habilitar la opción de opinar
        EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'estado' => 'entregado',
            'comentario' => 'Paquete entregado al cliente.',
            'creado_en' => now(),
        ]);

        $responseEntregado = $this->actingAs($this->cliente)->get(route('cliente.perfil.pedidos.detalle', $pedido->id));
        $responseEntregado->assertStatus(200);
        $responseEntregado->assertSee('Escribir opinión');
    }

    public function test_cliente_puede_ver_formulario_de_devolucion_con_estilo_cards(): void
    {
        $producto = Producto::create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Monitor Gaming 144Hz',
            'slug' => 'monitor-gaming-144hz',
            'sku' => 'MON-001',
            'precio' => 250.00,
            'stock' => 8,
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'usuario_id' => $this->cliente->id,
            'numero_pedido' => 'PED-DEVOLUCION-TEST',
            'subtotal' => 250.00,
            'total' => 250.00,
            'costo_envio' => 0.00,
            'metodo_pago' => 'stripe',
        ]);

        ItemPedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => 250.00,
            'subtotal' => 250.00,
        ]);

        $response = $this->actingAs($this->cliente)->get(route('cliente.perfil.pedidos.devolucion.create', $pedido->id));

        $response->assertStatus(200);
        $response->assertSee('Solicitar Devolución');
        $response->assertSee('PED-DEVOLUCION-TEST');
        $response->assertSee('Monitor Gaming 144Hz');
        $response->assertSee('Garantía y Devolución Segura');
        $response->assertSee('Motivo de la Devolución');
        $response->assertSee('Explicación Detallada');
        $response->assertSee('Evidencia Fotográfica');
        $response->assertSee('Enviar Solicitud de Devolución');
    }
}


