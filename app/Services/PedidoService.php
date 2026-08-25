<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\EstadoPedido;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\ZonaEnvio;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NuevoPedidoNotification;
use App\Notifications\StockMinimoNotification;
use App\Mail\PedidoEntregadoMail;
use Illuminate\Support\Facades\Mail;
use Exception;

class PedidoService
{
    protected CuponService $cuponService;

    protected CarritoService $carritoService;

    public function __construct(CuponService $cuponService, CarritoService $carritoService)
    {
        $this->cuponService = $cuponService;
        $this->carritoService = $carritoService;
    }

    /**
     * Calcula los totales del pedido antes de procesarlo.
     */
    public function calcularTotales(Carrito $carrito, ?ZonaEnvio $zonaEnvio, ?Cupon $cupon): array
    {
        // Subtotal e ITBMS compartidos con el carrito (respeta aplica_itbms por producto).
        $desglose = $this->carritoService->calcularSubtotalEItbms($carrito);
        $subtotal = $desglose['subtotal'];
        $descuento = 0.00;
        $descuentoEnvio = 0.00;

        if ($cupon) {
            if ($cupon->tipo === 'porcentaje') {
                $descuento = $subtotal * ($cupon->valor / 100);
            } elseif ($cupon->tipo === 'monto_fijo') {
                $descuento = $cupon->valor;
            }
            // envio_gratis no genera descuento sobre productos
            if ($descuento > $subtotal) {
                $descuento = $subtotal;
            }
        } elseif ($carrito->descuento_aplicado > 0) {
            $descuento = $carrito->descuento_aplicado;
        }

        $costoEnvio = $zonaEnvio ? $zonaEnvio->costo : 0.00;

        if ($zonaEnvio && $this->cuponService->evaluarEnvioGratis($zonaEnvio->id, $subtotal)) {
            $descuentoEnvio = $costoEnvio;
            $costoEnvio = 0.00;
        }

        $itbmsMonto = $desglose['itbms'];
        $total = max(0.00, ($subtotal - $descuento) + $costoEnvio + $itbmsMonto);

        return [
            'subtotal' => round($subtotal, 2),
            'descuento' => round($descuento, 2),
            'descuento_envio' => round($descuentoEnvio, 2),
            'costo_envio' => round($costoEnvio, 2),
            'itbms_monto' => round($itbmsMonto, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Crea un pedido a partir del carrito. Se ejecuta dentro de una transacción en el controlador,
     * o maneja su propia transacción si falla la validación inicial.
     */
    public function crearDesdeCarrito(
        Carrito $carrito,
        int $direccionId,
        string $metodoPago,
        ?string $notasCliente,
        ?ZonaEnvio $zonaEnvio = null,
        ?string $comprobantePagoRuta = null
    ): Pedido {
        return DB::transaction(function () use ($carrito, $direccionId, $metodoPago, $notasCliente, $zonaEnvio, $comprobantePagoRuta) {
            // 1. Validar stock
            foreach ($carrito->items as $item) {
                $stockDisponible = $item->variante ? $item->variante->stock : $item->producto->stock;
                if ($item->cantidad > $stockDisponible) {
                    throw new Exception("Stock insuficiente para el producto: " . $item->producto->nombre);
                }
            }

            // 2. Calcular totales
            $cupon = $carrito->cupon;
            $totales = $this->calcularTotales($carrito, $zonaEnvio, $cupon);

            // 3. Crear registro de Pedido con número generado de forma atómica
            //    (ver generarNumeroPedido). Ya NO existe el trigger DB que lo llenaba.
            $pedido = Pedido::create([
                'usuario_id' => $carrito->usuario_id,
                'direccion_id' => $direccionId,
                'cupon_id' => $carrito->cupon_id,
                'zona_envio_id' => $zonaEnvio ? $zonaEnvio->id : null,
                'numero_pedido' => $this->generarNumeroPedido(),
                'metodo_pago' => $metodoPago,
                'subtotal' => $totales['subtotal'],
                'descuento' => $totales['descuento'],
                'costo_envio' => $totales['costo_envio'],
                'itbms_monto' => $totales['itbms_monto'],
                'total' => $totales['total'],
                'notas_cliente' => $notasCliente,
                'comprobante_pago_ruta' => $comprobantePagoRuta,
            ]);

            // 4. Copiar items, deducir stock y registrar el movimiento de inventario
            foreach ($carrito->items as $item) {
                ItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item->producto_id,
                    'variante_producto_id' => $item->variante_producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                ]);

                // Deducir stock (atómico dentro de la transacción) y capturar valores.
                if ($item->variante) {
                    $stockAntes = (int) $item->variante->stock;
                    $item->variante->decrement('stock', $item->cantidad);
                    $varianteId = $item->variante->id;
                } else {
                    $stockAntes = (int) $item->producto->stock;
                    $item->producto->decrement('stock', $item->cantidad);
                    $varianteId = null;
                }

                // Trazabilidad de inventario: salida por venta (misma transacción).
                DB::table('movimientos_inventario')->insert([
                    'producto_id' => $item->producto_id,
                    'variante_producto_id' => $varianteId,
                    'usuario_id' => $carrito->usuario_id,
                    'pedido_id' => $pedido->id,
                    'tipo' => 'salida',
                    'cantidad' => $item->cantidad,
                    'stock_antes' => $stockAntes,
                    'stock_despues' => $stockAntes - $item->cantidad,
                    'motivo' => 'Venta - Pedido ' . $pedido->numero_pedido,
                    'notas' => null,
                    'creado_en' => now(),
                ]);

                // Notificar stock mínimo si aplica
                $stockDespues = $stockAntes - $item->cantidad;
                $stockMinimo = $item->producto->stock_minimo ?? 0;
                if ($stockAntes > $stockMinimo && $stockDespues <= $stockMinimo) {
                    $admins = Usuario::role('super_admin')->get();
                    Notification::send($admins, new StockMinimoNotification($item->producto, $item->variante));
                    \App\Events\NuevaNotificacion::dispatch();
                }
            }

            // 5. Crear estado inicial
            $this->cambiarEstado($pedido, 'pendiente', $carrito->usuario_id, 'Pedido creado.');

            // 6. Limpiar carrito
            $carrito->items()->delete();
            $carrito->update([
                'cupon_id' => null,
                'descuento_aplicado' => 0.00,
            ]);

            // 7. Enviar notificaciones a los administradores
            $admins = Usuario::role('super_admin')->get();
            Notification::send($admins, new NuevoPedidoNotification($pedido));
            \App\Events\NuevaNotificacion::dispatch();

            return $pedido;
        });
    }

    /**
     * Registra un nuevo estado para el pedido.
     */
    public function cambiarEstado(Pedido $pedido, string $nuevoEstado, ?int $usuarioId = null, ?string $comentario = null): EstadoPedido
    {
        $estado = EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $usuarioId,
            'estado' => $nuevoEstado,
            'comentario' => $comentario,
        ]);

        // Regla de negocio: Generar factura cuando el pago es confirmado
        if ($nuevoEstado === 'pago_confirmado') {
            app(FacturaService::class)->generarFactura($pedido);
        }

        // Regla de negocio: Anular factura cuando el pedido se cancela
        if ($nuevoEstado === 'cancelado') {
            app(FacturaService::class)->anularFactura($pedido);
        }

        // Regla de negocio: Enviar email cuando el pedido es entregado
        if ($nuevoEstado === 'entregado') {
            Mail::to($pedido->usuario->email)->send(new PedidoEntregadoMail($pedido));
        }

        return $estado;
    }

    /**
     * Genera el siguiente número de pedido con el formato corto "#PM-XXXXXX".
     *
     * Única fuente de verdad (el trigger DB trg_numero_pedido fue eliminado en la
     * migración 2026_08_11_000000_drop_trigger_numero_pedido). Usa un correlativo
     * secuencial en la tabla "configuracion" con bloqueo de fila (lockForUpdate),
     * el mismo patrón atómico que facturas.numero (generar_numero_factura), por lo
     * que es seguro ante creación concurrente de pedidos.
     *
     * Debe invocarse DENTRO de una transacción de base de datos.
     */
    protected function generarNumeroPedido(): string
    {
        // Asegurar que exista la fila del correlativo (clave-valor de configuracion).
        // El valor inicial continúa la numeración id-base previa (#PM-260001 = 260000 + id).
        DB::table('configuracion')->insertOrIgnore([
            'clave' => 'pedido_correlativo',
            'valor' => (string) (Pedido::max('id') ?? 0),
            'grupo' => 'general',
            'descripcion' => 'Correlativo secuencial para el número de pedido (#PM-XXXXXX).',
            'actualizado_en' => now(),
        ]);

        // Bloquear la fila y leer el correlativo: dentro de la transacción, cualquier
        // otro pedido concurrente queda en espera hasta que esta transacción termina.
        $fila = DB::table('configuracion')
            ->where('clave', 'pedido_correlativo')
            ->lockForUpdate()
            ->first();

        $correlativo = $fila ? ((int) $fila->valor) + 1 : 1;

        if ($fila) {
            DB::table('configuracion')
                ->where('clave', 'pedido_correlativo')
                ->update(['valor' => (string) $correlativo, 'actualizado_en' => now()]);
        } else {
            // Caso borde defensivo: la fila no existía, se crea con el valor consumido.
            DB::table('configuracion')->insert([
                'clave' => 'pedido_correlativo',
                'valor' => (string) $correlativo,
                'grupo' => 'general',
                'descripcion' => 'Correlativo secuencial para el número de pedido (#PM-XXXXXX).',
                'actualizado_en' => now(),
            ]);
        }

        return '#PM-' . (260000 + $correlativo);
    }
}
