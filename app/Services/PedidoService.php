<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\EstadoPedido;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\ZonaEnvio;
use Illuminate\Support\Facades\DB;
use Exception;

class PedidoService
{
    /**
     * Calcula los totales del pedido antes de procesarlo.
     */
    public function calcularTotales(Carrito $carrito, ?ZonaEnvio $zonaEnvio, ?Cupon $cupon): array
    {
        $subtotal = $carrito->subtotal;
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

        if ($cupon && $cupon->tipo === 'envio_gratis') {
            $descuentoEnvio = $costoEnvio;
            $costoEnvio = 0.00;
        }
        
        $montoBaseItbms = max(0, $subtotal - $descuento);
        $itbmsMonto = $montoBaseItbms * 0.07;

        $total = $montoBaseItbms + $costoEnvio + $itbmsMonto;

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

            // 3. Crear registro de Pedido
            $pedido = Pedido::create([
                'usuario_id' => $carrito->usuario_id,
                'direccion_id' => $direccionId,
                'cupon_id' => $carrito->cupon_id,
                'zona_envio_id' => $zonaEnvio ? $zonaEnvio->id : null,
                'numero_pedido' => '',
                'metodo_pago' => $metodoPago,
                'subtotal' => $totales['subtotal'],
                'descuento' => $totales['descuento'],
                'costo_envio' => $totales['costo_envio'],
                'itbms_monto' => $totales['itbms_monto'],
                'total' => $totales['total'],
                'notas_cliente' => $notasCliente,
                'comprobante_pago_ruta' => $comprobantePagoRuta,
            ]);

            $pedido->update([
                'numero_pedido' => '#PM-' . (260000 + $pedido->id),
            ]);

            // 4. Copiar items y deducir stock
            foreach ($carrito->items as $item) {
                ItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item->producto_id,
                    'variante_producto_id' => $item->variante_producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                ]);

                // Deducir stock
                if ($item->variante) {
                    $item->variante->decrement('stock', $item->cantidad);
                } else {
                    $item->producto->decrement('stock', $item->cantidad);
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

            return $pedido;
        });
    }

    /**
     * Registra un nuevo estado para el pedido.
     */
    public function cambiarEstado(Pedido $pedido, string $nuevoEstado, ?int $usuarioId = null, ?string $comentario = null): EstadoPedido
    {
        return EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $usuarioId,
            'estado' => $nuevoEstado,
            'comentario' => $comentario,
        ]);
    }
}
