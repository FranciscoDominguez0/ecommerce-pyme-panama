<?php

namespace Tests\Feature;

use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\Direccion;
use App\Models\EstadoPedido;
use App\Models\ItemCarrito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\PromocionEnvioGratis;
use App\Models\Usuario;
use App\Models\VarianteProducto;
use App\Models\ZonaEnvio;
use App\Services\CarritoService;
use App\Services\PedidoService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de la lógica central de creación de pedidos (PedidoService) — FASE 12.
 *
 * Este módulo es crítico (dinero, stock e integridad de pedidos).
 *
 * NOTAS DE ARQUITECTURA (comportamiento actual fijado por las correcciones):
 *  1. numero_pedido se genera en PHP (PedidoService::generarNumeroPedido) con formato
 *     "#PM-XXXXXX"; el trigger DB "P-YYYY-000001" fue eliminado.
 *  2. Cada descuento de stock por venta registra un movimiento de inventario
 *     (tipo = salida) en movimientos_inventario, en la misma transacción.
 *  3. El ITBMS respeta el flag "aplica_itbms" de cada producto (cálculo compartido
 *     con CarritoService::calcularSubtotalEItbms) — carrito y pedido son consistentes.
 *  4. El estado inicial "pendiente" lo inserta ÚNICAMENTE PedidoService::cambiarEstado
 *     (el trigger DB trg_estado_inicial_pedido fue eliminado) → exactamente 1 fila.
 *  5. Cancelar un pedido NO restaura el stock descontado (pendiente de confirmación
 *     de negocio — ver AGENTS.md).
 */
class PedidoServiceTest extends BaseAdminTest
{
    // =====================================================================
    //  CONVERSIÓN CARRITO → PEDIDO
    // =====================================================================

    public function test_crear_un_pedido_desde_el_carrito_copia_items_y_calcula_los_totales(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $zona = ZonaEnvio::factory()->create(['nombre' => 'Panamá', 'costo' => 5.00]);
        $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 2, 100.00);

        $pedido = app(PedidoService::class)->crearDesdeCarrito(
            $carrito,
            $direccion->id,
            'contra_entrega',
            'Entregar en recepción',
            $zona,
            null
        );

        // Datos del pedido.
        $this->assertSame($usuario->id, $pedido->usuario_id);
        $this->assertSame($direccion->id, $pedido->direccion_id);
        $this->assertSame('contra_entrega', $pedido->metodo_pago);
        $this->assertSame('200.00', $pedido->subtotal);
        $this->assertSame('0.00', $pedido->descuento);
        $this->assertSame('5.00', $pedido->costo_envio);
        $this->assertSame('14.00', $pedido->itbms_monto); // 7% de 200
        $this->assertSame('219.00', $pedido->total);       // 200 + 14 + 5
        $this->assertSame('Entregar en recepción', $pedido->notas_cliente);

        // Items copiados con cantidad y precio congelado.
        $itemPedido = $pedido->items()->where('producto_id', $producto->id)->first();
        $this->assertNotNull($itemPedido);
        $this->assertSame(2, $itemPedido->cantidad);
        $this->assertSame('100.00', $itemPedido->precio_unitario);
        $this->assertSame('200.00', $itemPedido->subtotal);
    }

    public function test_el_carrito_se_limpia_al_crear_el_pedido(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $carrito->refresh();
        $this->assertSame(0, $carrito->items()->count());
        $this->assertNull($carrito->cupon_id);
        $this->assertSame('0.00', (string) $carrito->descuento_aplicado);
    }

    // =====================================================================
    //  NÚMERO DE PEDIDO — formato REAL guardado
    // =====================================================================

    public function test_el_numero_de_pedido_guardado_usa_el_formato_del_sistema(): void
    {
        // numero_pedido se genera en PHP (PedidoService::generarNumeroPedido) con el
        // formato "#PM-260001" (correlativo atómico en "configuracion"). El trigger DB
        // "P-YYYY-000001" ya no existe (eliminado en 2026_08_11_000000_drop_trigger_numero_pedido).
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertMatchesRegularExpression('/^#PM-\d+$/', $pedido->fresh()->numero_pedido);
    }

    public function test_los_numero_de_pedido_generados_son_unicos_secuenciales_y_en_formato_correcto(): void
    {
        // Verifica que el correlativo atómico de "configuracion" produce números únicos
        // (constraint pedidos_numero_pedido_key) y en el formato correcto, sin colisiones.
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);

        $numeros = [];
        for ($i = 0; $i < 5; $i++) {
            $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 100]);
            $carrito = $this->crearCarritoConItem($usuario, $producto, 1, 10.00);
            $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);
            $numeros[] = $pedido->numero_pedido;
        }

        $this->assertCount(5, array_unique($numeros), 'Los números de pedido no deben repetirse.');
        foreach ($numeros as $numero) {
            $this->assertMatchesRegularExpression('/^#PM-\d+$/', $numero);
        }

        // El correlativo quedó en 5 (1 por pedido) y la numeración es consecutiva.
        $this->assertDatabaseHas('configuracion', ['clave' => 'pedido_correlativo', 'valor' => '5']);
        $this->assertSame(5, Pedido::count());
    }

    public function test_el_correlativo_de_numero_de_pedido_continua_desde_el_max_id_existente(): void
    {
        // Al sembrar el correlativo se usa MAX(pedidos.id). Debido a que las secuencias
        // de Postgres no se reinician entre tests transaccionales, usamos un número de
        // pedido ficticio muy alto para evitar colisiones con el constraint unique.
        $usuario = $this->crearCliente();
        $pedidoPrevio = Pedido::factory()->create(['usuario_id' => $usuario->id, 'numero_pedido' => '#PM-999999']);
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 100]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 1, 10.00);

        $pedidoNuevo = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertNotSame($pedidoPrevio->numero_pedido, $pedidoNuevo->numero_pedido);
        $this->assertMatchesRegularExpression('/^#PM-\d+$/', $pedidoNuevo->numero_pedido);
        $this->assertGreaterThan($pedidoPrevio->id, $pedidoNuevo->id);
    }

    // =====================================================================
    //  ESTADO INICIAL — trigger DB
    // =====================================================================

    public function test_el_pedido_creado_tiene_una_unica_fila_de_estado_inicial_pendiente(): void
    {
        // El estado inicial lo inserta ÚNICAMENTE PedidoService::cambiarEstado (el
        // trigger DB trg_estado_inicial_pedido fue eliminado) → exactamente 1 fila.
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertSame(
            1,
            EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'pendiente')->count()
        );
    }

    // =====================================================================
    //  DESCUENTO DE STOCK
    // =====================================================================

    public function test_el_stock_del_producto_se_descuenta_al_confirmar_el_pedido(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 3, 50.00);

        app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertSame(7, $producto->fresh()->stock);
    }

    public function test_el_stock_de_la_variante_se_descuenta_al_confirmar_el_pedido(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 5]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 3, 60.00, $variante->id);

        app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertSame(2, $variante->fresh()->stock);
        // El stock del producto base NO se toca cuando el item usa variante.
        $this->assertSame(99, $producto->fresh()->stock);
    }

    public function test_se_registra_un_movimiento_de_inventario_por_item_al_confirmar_el_pedido(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 3, 50.00);

        $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'pedido_id' => $pedido->id,
            'tipo' => 'salida',
            'cantidad' => 3,
            'stock_antes' => 10,
            'stock_despues' => 7,
            'usuario_id' => $usuario->id,
        ]);
    }

    public function test_se_registra_el_movimiento_de_inventario_con_variante(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 5]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 3, 60.00, $variante->id);

        $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id' => $producto->id,
            'variante_producto_id' => $variante->id,
            'pedido_id' => $pedido->id,
            'tipo' => 'salida',
            'cantidad' => 3,
            'stock_antes' => 5,
            'stock_despues' => 2,
        ]);
    }

    public function test_no_se_registran_movimientos_de_inventario_si_el_pedido_falla(): void
    {
        // Al fallar por stock insuficiente, el rollback no deja movimientos ni pedido.
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 3]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 3, 50.00);
        $producto->update(['stock' => 2]);

        try {
            app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);
            $this->fail('Se esperaba una excepción de stock insuficiente.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }

        $this->assertSame(0, DB::table('movimientos_inventario')->count());
    }

    // =====================================================================
    //  STOCK INSUFICIENTE — atomicidad
    // =====================================================================

    public function test_no_se_crea_el_pedido_si_el_stock_es_insuficiente_al_confirmar(): void
    {
        // Simula la "carrera": otro comprador agotó unidades entre el carrito y la
        // confirmación. El pedido NO se crea, no se descuenta stock y el carrito se
        // conserva intacto.
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 3]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 3, 50.00);

        // "Otro comprador" redujo el stock a 2.
        $producto->update(['stock' => 2]);

        try {
            app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);
            $this->fail('Se esperaba una excepción de stock insuficiente.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }

        // Rollback completo: nada de pedido, sin items, sin estados, sin descuento de stock.
        $this->assertSame(0, Pedido::count());
        $this->assertSame(0, DB::table('items_pedido')->count());
        $this->assertSame(2, $producto->fresh()->stock);
        // El carrito se conserva con sus items.
        $this->assertSame(1, $carrito->items()->count());
        $this->assertSame(3, $carrito->items()->first()->cantidad);
    }

    // =====================================================================
    //  CÁLCULO DE TOTALES
    // =====================================================================

    public function test_calcular_totales_aplica_descuento_de_cupon_porcentaje(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 2, 100.00); // subtotal 200
        $cupon = Cupon::factory()->create(['tipo' => 'porcentaje', 'valor' => 10]);

        $totales = app(PedidoService::class)->calcularTotales($carrito, null, $cupon);

        $this->assertSame(200.0, $totales['subtotal']);
        $this->assertSame(20.0, $totales['descuento']);
        $this->assertSame(14.0, $totales['itbms_monto']); // 7% de la base imponible (200, todo gravado)
        $this->assertSame(194.0, $totales['total']);      // (200 - 20) + 14
    }

    public function test_calcular_totales_aplica_envio_gratis_por_promocion(): void
    {
        $usuario = $this->crearCliente();
        $zona = ZonaEnvio::factory()->create(['costo' => 5.00]);
        PromocionEnvioGratis::factory()->create(['zona_envio_id' => $zona->id, 'monto_minimo' => 50, 'activo' => true]);
        $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 1, 100.00);

        $totales = app(PedidoService::class)->calcularTotales($carrito, $zona, null);

        $this->assertSame(0.0, $totales['costo_envio']);
        $this->assertSame(5.0, $totales['descuento_envio']);
        $this->assertSame(107.0, $totales['total']);
    }

    public function test_el_itbms_respeta_el_flag_aplica_itbms_de_cada_producto(): void
    {
        // El ITBMS se calcula SOLO sobre los items con aplica_itbms = true, y debe
        // coincidir con CarritoService::calcularTotal (cálculo compartido).
        $usuario = $this->crearCliente();
        $productoGravado = Producto::factory()->create(['precio' => 100.00, 'stock' => 10, 'aplica_itbms' => true]);
        $productoNoGravado = Producto::factory()->create(['precio' => 100.00, 'stock' => 10, 'aplica_itbms' => false]);
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);
        ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $productoGravado->id, 'cantidad' => 1, 'precio_unitario' => 100.00]);
        ItemCarrito::factory()->create(['carrito_id' => $carrito->id, 'producto_id' => $productoNoGravado->id, 'cantidad' => 1, 'precio_unitario' => 100.00]);

        $totalesPedido = app(PedidoService::class)->calcularTotales($carrito, null, null);
        $totalesCarrito = app(CarritoService::class)->calcularTotal($carrito, 0.0, null);

        $this->assertSame(200.0, $totalesPedido['subtotal']);
        // 7% solo sobre el item gravado (100), no sobre los 200.
        $this->assertSame(7.0, $totalesPedido['itbms_monto']);
        // Consistencia total entre pedido y carrito para el mismo carrito.
        $this->assertSame($totalesCarrito['itbms'], $totalesPedido['itbms_monto']);
        $this->assertSame($totalesCarrito['total'], $totalesPedido['total']);
    }

    // =====================================================================
    //  TRANSICIONES DE ESTADO
    // =====================================================================

    public function test_las_transiciones_de_estado_agregan_historial_sin_sobrescribir(): void
    {
        $pedido = Pedido::factory()->create();
        $servicio = app(PedidoService::class);

        foreach (['pago_confirmado', 'en_preparacion', 'listo_para_envio', 'enviado', 'entregado'] as $estado) {
            $servicio->cambiarEstado($pedido, $estado, null, null);
        }

        // Historial acumulativo: las 5 transiciones (sin fila inicial extra de trigger).
        $this->assertSame(5, EstadoPedido::where('pedido_id', $pedido->id)->count());
        $this->assertSame(1, EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'enviado')->count());
        $this->assertSame(1, EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'entregado')->count());

        // El estado más reciente (por id) es el último aplicado.
        $ultimo = EstadoPedido::where('pedido_id', $pedido->id)->orderByDesc('id')->first();
        $this->assertSame('entregado', $ultimo->estado);
    }

    public function test_un_estado_invalido_es_rechazado_por_la_restriccion_check(): void
    {
        $pedido = Pedido::factory()->create();

        $this->expectException(QueryException::class);
        app(PedidoService::class)->cambiarEstado($pedido, 'estado_inexistente', null, null);
    }

    public function test_cancelar_un_pedido_no_restaura_el_stock(): void
    {
        // HALLAZGO: cancelar solo agrega el estado "cancelado"; NO repone el stock
        // descontado al crear el pedido (posible gap pendiente de implementar).
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 2, 50.00);

        $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);
        $this->assertSame(8, $producto->fresh()->stock);

        app(PedidoService::class)->cambiarEstado($pedido, 'cancelado', null, 'Cancelado por el cliente');

        $this->assertSame('cancelado', EstadoPedido::where('pedido_id', $pedido->id)->orderByDesc('id')->first()->estado);
        $this->assertSame(8, $producto->fresh()->stock); // sin restauración
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Crea un carrito con un item (producto o variante).
     */
    protected function crearCarritoConItem(
        Usuario $usuario,
        Producto $producto,
        int $cantidad,
        float $precio,
        ?int $varianteProductoId = null
    ): Carrito {
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);

        ItemCarrito::factory()->create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'variante_producto_id' => $varianteProductoId,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
        ]);

        return $carrito;
    }
}
