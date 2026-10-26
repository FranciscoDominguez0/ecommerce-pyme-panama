<?php

namespace App\Services;

use App\Models\EstadoPedido;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\VarianteProducto;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Servicio especializado en el registro y procesamiento de ventas manuales
 * presenciales (Mostrador / POS), cotizaciones de WhatsApp y pedidos telefónicos.
 */
class VentaManualService
{
    protected FacturaService $facturaService;
    protected PedidoService $pedidoService;

    public function __construct(FacturaService $facturaService, PedidoService $pedidoService)
    {
        $this->facturaService = $facturaService;
        $this->pedidoService = $pedidoService;
    }

    /**
     * Registra una venta manual en una transacción atómica verificando inventario,
     * descontando existencias, registrando movimientos y emitiendo factura fiscal si aplica.
     */
    public function registrarVentaManual(array $datos, int $vendedorId): Pedido
    {
        return DB::transaction(function () use ($datos, $vendedorId) {
            $subtotal = 0.00;
            $itemsValidados = [];

            // 1. Validar y bloquear existencias de cada producto/variante
            foreach ($datos['items'] as $item) {
                $producto = Producto::where('id', $item['producto_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $variante = null;
                if (!empty($item['variante_id'])) {
                    $variante = VarianteProducto::where('id', $item['variante_id'])
                        ->where('producto_id', $producto->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                }

                $stockDisponible = $variante ? (int) $variante->stock : (int) $producto->stock;
                $cantidad = (int) $item['cantidad'];

                if ($stockDisponible < $cantidad) {
                    $nombreItem = $variante ? "{$producto->nombre} ({$variante->nombre})" : $producto->nombre;
                    throw new Exception("Stock insuficiente para: {$nombreItem}. Disponible: {$stockDisponible}, solicitado: {$cantidad}.");
                }

                $precioUnitario = isset($item['precio_unitario']) && is_numeric($item['precio_unitario'])
                    ? (float) $item['precio_unitario']
                    : ($variante ? (float) $variante->precio : (float) $producto->precio);

                $itemSubtotal = round($precioUnitario * $cantidad, 2);
                $subtotal += $itemSubtotal;

                $itemsValidados[] = [
                    'producto' => $producto,
                    'variante' => $variante,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // 2. Cálculos financieros (Panamá ITBMS 7%)
            $descuento = round((float) ($datos['descuento_manual'] ?? 0.00), 2);
            $baseImponible = max(0, $subtotal - $descuento);
            $itbmsMonto = round($baseImponible * 0.07, 2);
            $costoEnvio = round((float) ($datos['costo_envio'] ?? 0.00), 2);
            $total = round($baseImponible + $itbmsMonto + $costoEnvio, 2);

            // 3. Crear el Pedido
            $pedido = Pedido::create([
                'usuario_id' => $datos['usuario_id'],
                'vendedor_id' => $vendedorId,
                'direccion_id' => $datos['direccion_id'] ?? null,
                'zona_envio_id' => $datos['zona_envio_id'] ?? null,
                'cupon_id' => null,
                'numero_pedido' => $this->pedidoService->generarNumeroPedido(),
                'metodo_pago' => $datos['metodo_pago'] ?? 'efectivo',
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'costo_envio' => $costoEnvio,
                'itbms_monto' => $itbmsMonto,
                'total' => $total,
                'notas_cliente' => $datos['notas_cliente'] ?? null,
                'notas_internas' => $datos['notas_internas'] ?? 'Venta manual registrada desde el panel de administración.',
                'comprobante_pago_ruta' => $datos['comprobante_pago_ruta'] ?? null,
                'leido_en' => now(),
            ]);

            // 4. Registrar items e impacto en inventario
            foreach ($itemsValidados as $iv) {
                ItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $iv['producto']->id,
                    'variante_producto_id' => $iv['variante']?->id,
                    'cantidad' => $iv['cantidad'],
                    'precio_unitario' => $iv['precio_unitario'],
                    'subtotal' => $iv['subtotal'],
                ]);

                if ($iv['variante']) {
                    $stockAntes = (int) $iv['variante']->stock;
                    $iv['variante']->decrement('stock', $iv['cantidad']);
                    $varianteId = $iv['variante']->id;
                } else {
                    $stockAntes = (int) $iv['producto']->stock;
                    $iv['producto']->decrement('stock', $iv['cantidad']);
                    $varianteId = null;
                }

                DB::table('movimientos_inventario')->insert([
                    'producto_id' => $iv['producto']->id,
                    'variante_producto_id' => $varianteId,
                    'usuario_id' => $vendedorId,
                    'pedido_id' => $pedido->id,
                    'tipo' => 'salida',
                    'cantidad' => $iv['cantidad'],
                    'stock_antes' => $stockAntes,
                    'stock_despues' => $stockAntes - $iv['cantidad'],
                    'motivo' => 'Venta Mostrador/POS - Pedido ' . $pedido->numero_pedido,
                    'notas' => 'Venta manual procesada por usuario ID ' . $vendedorId,
                    'creado_en' => now(),
                ]);
            }

            // 5. Estado inicial del pedido
            $marcarPagado = !empty($datos['marcar_pagado']);
            $estadoInicial = $marcarPagado ? 'pago_confirmado' : 'pendiente';
            $comentarioInicial = $marcarPagado
                ? "Venta en mostrador cobrada ({$pedido->metodo_pago})."
                : 'Venta manual registrada, pendiente de cobro.';

            EstadoPedido::create([
                'pedido_id' => $pedido->id,
                'usuario_id' => $vendedorId,
                'estado' => $estadoInicial,
                'comentario' => $comentarioInicial,
                'creado_en' => now(),
            ]);

            // 6. Generación de factura fiscal si fue solicitada y cobrada
            if (!empty($datos['emitir_factura'])) {
                try {
                    $this->facturaService->generarFactura($pedido);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("No se pudo emitir la factura automática para el pedido {$pedido->numero_pedido}: " . $e->getMessage());
                }
            }

            return $pedido;
        });
    }
}
