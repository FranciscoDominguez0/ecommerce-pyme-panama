<?php

namespace Tests\Feature\Cliente;

use App\Models\Categoria;
use App\Models\EstadoPedido;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\ValoracionProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ValoracionProductoTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $cliente;
    protected Producto $producto;
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

        $this->producto = Producto::create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Teclado Mecánico RGB',
            'slug' => 'teclado-mecanico-rgb',
            'sku' => 'TEC-001',
            'precio' => 75.00,
            'stock' => 10,
            'activo' => true,
        ]);
    }

    private function crearPedido(Usuario $usuario, string $estado = 'entregado', ?Producto $prod = null): Pedido
    {
        $pedido = Pedido::create([
            'usuario_id' => $usuario->id,
            'numero_pedido' => 'PM-' . strtoupper(uniqid()),
            'metodo_pago' => 'transferencia',
            'subtotal' => 75.00,
            'total' => 75.00,
        ]);

        EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $usuario->id,
            'estado' => $estado,
            'comentario' => 'Estado: ' . $estado,
        ]);

        $productoAsociado = $prod ?? $this->producto;

        ItemPedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $productoAsociado->id,
            'cantidad' => 1,
            'precio_unitario' => 75.00,
            'subtotal' => 75.00,
        ]);

        return $pedido;
    }

    public function test_cliente_puede_valorar_producto_de_pedido_entregado(): void
    {
        $pedido = $this->crearPedido($this->cliente, 'entregado');

        $response = $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedido->id), [
                'producto_id' => $this->producto->id,
                'calificacion' => 5,
                'titulo' => 'Increíble teclado',
                'comentario' => 'Las teclas se sienten muy suaves y los colores son espectaculares.',
            ]);

        $response->assertSessionHas('toast_success');

        $this->assertDatabaseHas('valoraciones_producto', [
            'pedido_id' => $pedido->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->cliente->id,
            'calificacion' => 5,
            'titulo' => 'Increíble teclado',
            'es_aprobado' => true,
        ]);
    }

    public function test_comentario_y_titulo_son_sanitizados_contra_xss_y_codigo_malicioso(): void
    {
        $pedido = $this->crearPedido($this->cliente, 'entregado');

        $payloadMalicioso = [
            'producto_id' => $this->producto->id,
            'calificacion' => 4,
            'titulo' => '<script>alert("xss")</script>Título seguro<b> negrita</b>',
            'comentario' => '<img src=x onerror=alert(1)>Comentario con <a href="http://malicioso.com">enlace</a> y código.',
        ];

        $response = $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedido->id), $payloadMalicioso);

        $response->assertSessionHas('toast_success');

        $valoracion = ValoracionProducto::where('pedido_id', $pedido->id)
            ->where('producto_id', $this->producto->id)
            ->first();

        $this->assertNotNull($valoracion);

        // Comprobar que no existen etiquetas script, img, ni enlaces
        $this->assertStringNotContainsString('<script>', $valoracion->titulo);
        $this->assertStringNotContainsString('</script>', $valoracion->titulo);
        $this->assertStringNotContainsString('<b>', $valoracion->titulo);
        $this->assertEquals('Título seguro negrita', $valoracion->titulo);

        $this->assertStringNotContainsString('<img', $valoracion->comentario);
        $this->assertStringNotContainsString('onerror', $valoracion->comentario);
        $this->assertStringNotContainsString('<a', $valoracion->comentario);
        $this->assertEquals('Comentario con enlace y código.', $valoracion->comentario);
    }

    public function test_cliente_no_puede_valorar_pedido_no_entregado(): void
    {
        $pedidoEnCamino = $this->crearPedido($this->cliente, 'enviado');

        $response = $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedidoEnCamino->id), [
                'producto_id' => $this->producto->id,
                'calificacion' => 5,
                'titulo' => 'Aún no llega pero creo que está bien',
            ]);

        $response->assertSessionHasErrors('producto_id');
        $this->assertDatabaseEmpty('valoraciones_producto');
    }

    public function test_cliente_no_puede_valorar_producto_que_no_pertenece_al_pedido(): void
    {
        $otroProducto = Producto::create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Monitor Gamer 144Hz',
            'slug' => 'monitor-gamer-144hz',
            'sku' => 'MON-001',
            'precio' => 200.00,
            'stock' => 5,
            'activo' => true,
        ]);

        $pedido = $this->crearPedido($this->cliente, 'entregado', $this->producto);

        $response = $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedido->id), [
                'producto_id' => $otroProducto->id,
                'calificacion' => 5,
                'titulo' => 'No compré esto aquí pero quiero valorarlo',
            ]);

        $response->assertSessionHasErrors('producto_id');
        $this->assertDatabaseEmpty('valoraciones_producto');
    }

    public function test_cliente_no_puede_valorar_pedido_de_otro_usuario(): void
    {
        $otroUsuario = Usuario::create([
            'nombre' => 'María',
            'apellido' => 'Pérez',
            'email' => 'maria@example.com',
            'password_hash' => Hash::make('password123'),
        ]);
        $otroUsuario->assignRole('cliente');

        $pedidoDeOtro = $this->crearPedido($otroUsuario, 'entregado');

        $response = $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedidoDeOtro->id), [
                'producto_id' => $this->producto->id,
                'calificacion' => 5,
                'titulo' => 'Intentando calificar compra ajena',
            ]);

        $response->assertNotFound();
        $this->assertDatabaseEmpty('valoraciones_producto');
    }

    public function test_cliente_puede_actualizar_su_valoracion_sin_duplicar_registros(): void
    {
        $pedido = $this->crearPedido($this->cliente, 'entregado');

        // Primera valoración
        $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedido->id), [
                'producto_id' => $this->producto->id,
                'calificacion' => 3,
                'titulo' => 'Regular',
                'comentario' => 'Llegó bien pero una tecla chirriaba.',
            ]);

        $this->assertDatabaseCount('valoraciones_producto', 1);

        // Actualización de la misma valoración
        $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedido->id), [
                'producto_id' => $this->producto->id,
                'calificacion' => 5,
                'titulo' => 'Excelente soporte',
                'comentario' => 'Cambié el switch y ahora va perfecto.',
            ]);

        $this->assertDatabaseCount('valoraciones_producto', 1);
        $this->assertDatabaseHas('valoraciones_producto', [
            'pedido_id' => $pedido->id,
            'producto_id' => $this->producto->id,
            'calificacion' => 5,
            'titulo' => 'Excelente soporte',
            'comentario' => 'Cambié el switch y ahora va perfecto.',
        ]);
    }

    public function test_validacion_de_estrellas_entre_1_y_5(): void
    {
        $pedido = $this->crearPedido($this->cliente, 'entregado');

        // Menor que 1
        $response1 = $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedido->id), [
                'producto_id' => $this->producto->id,
                'calificacion' => 0,
            ]);
        $response1->assertSessionHasErrors('calificacion');

        // Mayor que 5
        $response2 = $this->actingAs($this->cliente)
            ->post(route('cliente.perfil.pedidos.valorar', $pedido->id), [
                'producto_id' => $this->producto->id,
                'calificacion' => 6,
            ]);
        $response2->assertSessionHasErrors('calificacion');
    }

    public function test_detalle_de_producto_muestra_promedio_y_opiniones_aprobadas(): void
    {
        $pedido = $this->crearPedido($this->cliente, 'entregado');

        ValoracionProducto::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $this->producto->id,
            'usuario_id' => $this->cliente->id,
            'calificacion' => 5,
            'titulo' => 'Totalmente recomendado',
            'comentario' => 'Excelente acabado y materiales resistentes.',
            'es_aprobado' => true,
        ]);

        $response = $this->get(route('cliente.producto.detalle', $this->producto->slug));

        $response->assertOk();
        $response->assertSee('Totalmente recomendado');
        $response->assertSee('Excelente acabado y materiales resistentes.');
        $response->assertSee('Carlos M.');
        $response->assertSee('Compra verificada');
        $response->assertSee('Opiniones de Clientes');
    }
}
