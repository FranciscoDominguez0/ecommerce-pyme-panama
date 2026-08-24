<?php

namespace App\Services;

use App\Models\Devolucion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\VarianteProducto;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StockMinimoNotification;
use Exception;

class InventarioService
{
    // ─── Consulta de stock ─────────────────────────────────────────────────────

    /**
     * Retorna el stock actual de un producto (o variante si aplica).
     */
    public function stockDisponible(Producto $producto, ?VarianteProducto $variante = null): int
    {
        if ($variante) {
            return (int) $variante->stock;
        }
        return (int) $producto->stock;
    }

    // ─── KPIs para la vista de inventario ─────────────────────────────────────

    /**
     * Calcula métricas del módulo de inventario.
     */
    public function calcularKpis(): array
    {
        // Productos sin variantes: stock en productos
        $totalSinVariante = (int) DB::table('productos')
            ->whereNull('eliminado_en')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('variantes_producto')->whereColumn('variantes_producto.producto_id', 'productos.id');
            })
            ->sum('stock');

        // Productos con variantes: stock en variantes
        $totalConVariante = (int) DB::table('variantes_producto')
            ->where('activo', true)
            ->sum('stock');

        $stockTotal = $totalSinVariante + $totalConVariante;

        // Productos con stock bajo (stock <= stock_minimo, sin variantes)
        $bajoPorProducto = (int) DB::table('productos')
            ->whereNull('eliminado_en')
            ->whereRaw('stock <= stock_minimo')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('variantes_producto')->whereColumn('variantes_producto.producto_id', 'productos.id');
            })
            ->count();

        // Variantes con stock bajo (stock <= stock_minimo del producto padre)
        $bajoPorVariante = (int) DB::table('variantes_producto')
            ->join('productos', 'variantes_producto.producto_id', '=', 'productos.id')
            ->whereNull('productos.eliminado_en')
            ->where('variantes_producto.activo', true)
            ->whereRaw('variantes_producto.stock <= productos.stock_minimo')
            ->count();

        $stockBajo = $bajoPorProducto + $bajoPorVariante;

        // Entradas en los últimos 7 días
        $entradasSiete = (int) MovimientoInventario::where('tipo', 'entrada')
            ->where('creado_en', '>=', now()->subDays(7))
            ->sum('cantidad');

        // Salidas en los últimos 7 días
        $salidasSiete = (int) MovimientoInventario::where('tipo', 'salida')
            ->where('creado_en', '>=', now()->subDays(7))
            ->sum('cantidad');

        return compact('stockTotal', 'stockBajo', 'entradasSiete', 'salidasSiete');
    }

    // ─── Registrar Entrada ─────────────────────────────────────────────────────

    /**
     * Aumenta el stock de un producto/variante y registra el movimiento.
     *
     * @throws Exception Si la cantidad no es positiva.
     */
    public function registrarEntrada(
        Producto $producto,
        ?VarianteProducto $variante,
        int $cantidad,
        string $motivo,
        ?string $proveedor = null,
        ?string $facturaProveedor = null,
        ?string $notas = null,
        ?int $usuarioId = null,
        ?int $pedidoId = null
    ): MovimientoInventario {
        if ($cantidad <= 0) {
            throw new Exception('La cantidad de entrada debe ser mayor que cero.');
        }

        return DB::transaction(function () use ($producto, $variante, $cantidad, $motivo, $proveedor, $facturaProveedor, $notas, $usuarioId, $pedidoId) {
            if ($variante) {
                $stockAntes = (int) $variante->fresh()->stock;
                $variante->increment('stock', $cantidad);
                $stockDespues = $stockAntes + $cantidad;
            } else {
                $stockAntes = (int) $producto->fresh()->stock;
                $producto->increment('stock', $cantidad);
                $stockDespues = $stockAntes + $cantidad;
            }

            return MovimientoInventario::create([
                'producto_id'          => $producto->id,
                'variante_producto_id' => $variante?->id,
                'usuario_id'           => $usuarioId,
                'pedido_id'            => $pedidoId,
                'tipo'                 => 'entrada',
                'cantidad'             => $cantidad,
                'stock_antes'          => $stockAntes,
                'stock_despues'        => $stockDespues,
                'motivo'               => $motivo,
                'proveedor'            => $proveedor,
                'factura_proveedor'    => $facturaProveedor,
                'notas'                => $notas,
            ]);
        });
    }

    // ─── Registrar Salida ──────────────────────────────────────────────────────

    /**
     * Disminuye el stock de un producto/variante y registra el movimiento.
     *
     * @throws Exception Si la cantidad no es positiva o supera el stock disponible.
     */
    public function registrarSalida(
        Producto $producto,
        ?VarianteProducto $variante,
        int $cantidad,
        string $motivo,
        ?string $notas = null,
        ?int $usuarioId = null,
        ?int $pedidoId = null
    ): MovimientoInventario {
        if ($cantidad <= 0) {
            throw new Exception('La cantidad de salida debe ser mayor que cero.');
        }

        return DB::transaction(function () use ($producto, $variante, $cantidad, $motivo, $notas, $usuarioId, $pedidoId) {
            if ($variante) {
                $varianteFresh = $variante->fresh();
                $stockAntes    = (int) $varianteFresh->stock;
                if ($cantidad > $stockAntes) {
                    throw new Exception("Stock insuficiente para la variante seleccionada. Disponible: {$stockAntes}.");
                }
                $variante->decrement('stock', $cantidad);
            } else {
                $productoFresh = $producto->fresh();
                $stockAntes    = (int) $productoFresh->stock;
                if ($cantidad > $stockAntes) {
                    throw new Exception("Stock insuficiente para '{$producto->nombre}'. Disponible: {$stockAntes}.");
                }
                $producto->decrement('stock', $cantidad);
            }

            $stockDespues = $stockAntes - $cantidad;

            $movimiento = MovimientoInventario::create([
                'producto_id'          => $producto->id,
                'variante_producto_id' => $variante?->id,
                'usuario_id'           => $usuarioId,
                'pedido_id'            => $pedidoId,
                'tipo'                 => 'salida',
                'cantidad'             => $cantidad,
                'stock_antes'          => $stockAntes,
                'stock_despues'        => $stockDespues,
                'motivo'               => $motivo,
                'notas'                => $notas,
            ]);

            // Notificar stock mínimo si aplica
            $stockMinimo = $producto->stock_minimo ?? 0;
            if ($stockAntes > $stockMinimo && $stockDespues <= $stockMinimo) {
                $admins = Usuario::role('super_admin')->get();
                Notification::send($admins, new StockMinimoNotification($producto, $variante));
            }

            return $movimiento;
        });
    }

    // ─── Registrar Ajuste Manual ───────────────────────────────────────────────

    /**
     * Fija el stock a una nueva cantidad y registra el ajuste.
     * La diferencia (positiva o negativa) se calcula automáticamente.
     *
     * @throws Exception Si el nuevo stock es negativo.
     */
    public function registrarAjuste(
        Producto $producto,
        ?VarianteProducto $variante,
        int $nuevoStock,
        string $motivo,
        ?string $notas = null,
        ?int $usuarioId = null
    ): MovimientoInventario {
        if ($nuevoStock < 0) {
            throw new Exception('El nuevo stock no puede ser negativo.');
        }

        return DB::transaction(function () use ($producto, $variante, $nuevoStock, $motivo, $notas, $usuarioId) {
            if ($variante) {
                $stockAntes = (int) $variante->fresh()->stock;
                $variante->update(['stock' => $nuevoStock]);
            } else {
                $stockAntes = (int) $producto->fresh()->stock;
                $producto->update(['stock' => $nuevoStock]);
            }

            $diferencia = $nuevoStock - $stockAntes;

            $movimiento = MovimientoInventario::create([
                'producto_id'          => $producto->id,
                'variante_producto_id' => $variante?->id,
                'usuario_id'           => $usuarioId,
                'pedido_id'            => null,
                'tipo'                 => 'ajuste',
                'cantidad'             => abs($diferencia),
                'stock_antes'          => $stockAntes,
                'stock_despues'        => $nuevoStock,
                'motivo'               => $motivo,
                'notas'                => $notas,
            ]);

            // Notificar stock mínimo si aplica (solo si disminuyó y cruzó el umbral)
            $stockMinimo = $producto->stock_minimo ?? 0;
            if ($stockAntes > $stockMinimo && $nuevoStock <= $stockMinimo) {
                $admins = Usuario::role('super_admin')->get();
                Notification::send($admins, new StockMinimoNotification($producto, $variante));
            }

            return $movimiento;
        });
    }

    // ─── Entrada Automática por Devolución Aprobada ────────────────────────────

    /**
     * Registra una entrada de inventario por cada ítem de una devolución aprobada.
     * Llama a registrarEntrada por ítem usando la misma lógica atómica.
     */
    public function registrarEntradaPorDevolucion(Devolucion $devolucion, ?int $adminId = null): void
    {
        $pedido = $devolucion->pedido()->with('items.producto', 'items.variante')->first();

        if (!$pedido) {
            return;
        }

        foreach ($pedido->items as $item) {
            $this->registrarEntrada(
                producto:         $item->producto,
                variante:         $item->variante,
                cantidad:         $item->cantidad,
                motivo:           'Devolución aprobada - Pedido ' . $pedido->numero_pedido,
                notas:            "Devolución ID #{$devolucion->id}",
                usuarioId:        $adminId,
                pedidoId:         $pedido->id,
            );
        }
    }
}
