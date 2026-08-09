<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Models\VarianteProducto;
use Illuminate\Support\Facades\DB;

class CarritoService
{
    protected CuponService $cuponService;

    public function __construct(CuponService $cuponService)
    {
        $this->cuponService = $cuponService;
    }

    /**
     * Obtiene o crea un carrito persistente para el usuario o sesión actual.
     */
    public function obtenerOCrearCarrito(?int $usuarioId = null, ?string $sesionId = null): Carrito
    {
        if ($usuarioId) {
            $carrito = Carrito::with([
                'items.producto.imagenes',
                'items.variante.opciones',
                'cupon'
            ])->where('usuario_id', $usuarioId)->first();

            if (!$carrito) {
                $carrito = Carrito::create([
                    'usuario_id' => $usuarioId,
                    'sesion_id' => null,
                    'descuento_aplicado' => 0.00,
                ]);
                $carrito->load(['items.producto.imagenes', 'items.variante.opciones', 'cupon']);
            }

            return $carrito;
        }

        if ($sesionId) {
            $carrito = Carrito::with([
                'items.producto.imagenes',
                'items.variante.opciones',
                'cupon'
            ])->where('sesion_id', $sesionId)->first();

            if (!$carrito) {
                $carrito = Carrito::create([
                    'usuario_id' => null,
                    'sesion_id' => $sesionId,
                    'descuento_aplicado' => 0.00,
                ]);
                $carrito->load(['items.producto.imagenes', 'items.variante.opciones', 'cupon']);
            }

            return $carrito;
        }

        // Fallback en caso de que no haya sesión o usuario
        return new Carrito(['descuento_aplicado' => 0.00]);
    }

    /**
     * Fusiona el carrito de una sesión de visitante con el del usuario autenticado.
     */
    public function fusionarCarritos(string $sesionId, int $usuarioId): Carrito
    {
        return DB::transaction(function () use ($sesionId, $usuarioId) {
            $carritoSesion = Carrito::with(['items', 'cupon'])->where('sesion_id', $sesionId)->first();
            $carritoUsuario = $this->obtenerOCrearCarrito($usuarioId);

            if (!$carritoSesion || $carritoSesion->items->isEmpty()) {
                return $carritoUsuario;
            }

            foreach ($carritoSesion->items as $itemSesion) {
                $itemUsuario = ItemCarrito::where('carrito_id', $carritoUsuario->id)
                    ->where('producto_id', $itemSesion->producto_id)
                    ->where(function ($q) use ($itemSesion) {
                        if ($itemSesion->variante_producto_id) {
                            $q->where('variante_producto_id', $itemSesion->variante_producto_id);
                        } else {
                            $q->whereNull('variante_producto_id');
                        }
                    })
                    ->first();

                // Validar stock disponible
                $stockDisponible = $itemSesion->stock_disponible;

                if ($itemUsuario) {
                    $nuevaCantidad = min($itemUsuario->cantidad + $itemSesion->cantidad, $stockDisponible);
                    $itemUsuario->update(['cantidad' => $nuevaCantidad]);
                } else {
                    $cantidad = min($itemSesion->cantidad, $stockDisponible);
                    if ($cantidad > 0) {
                        ItemCarrito::create([
                            'carrito_id' => $carritoUsuario->id,
                            'producto_id' => $itemSesion->producto_id,
                            'variante_producto_id' => $itemSesion->variante_producto_id,
                            'cantidad' => $cantidad,
                            'precio_unitario' => $itemSesion->precio_unitario,
                        ]);
                    }
                }
            }

            // Transferir cupón si el usuario no tenía uno aplicado
            if ($carritoSesion->cupon_id && !$carritoUsuario->cupon_id) {
                $carritoUsuario->update([
                    'cupon_id' => $carritoSesion->cupon_id,
                    'descuento_aplicado' => $carritoSesion->descuento_aplicado,
                ]);
            }

            // Eliminar carrito temporal de la sesión
            $carritoSesion->items()->delete();
            $carritoSesion->delete();

            // Recalcular cupón y refrescar
            $this->recalcularDescuentoCupon($carritoUsuario, $usuarioId);

            return $carritoUsuario->fresh(['items.producto.imagenes', 'items.variante.opciones', 'cupon']);
        });
    }

    /**
     * Agrega un producto al carrito validando stock y congelando su precio unitario.
     */
    public function agregarProducto(
        int $productoId,
        ?int $varianteProductoId = null,
        int $cantidad = 1,
        ?int $usuarioId = null,
        ?string $sesionId = null
    ): array {
        if ($cantidad <= 0) {
            $cantidad = 1;
        }

        $producto = Producto::where('id', $productoId)
            ->whereNull('eliminado_en')
            ->where('activo', true)
            ->first();

        if (!$producto) {
            return [
                'exito' => false,
                'mensaje' => 'El producto seleccionado no se encuentra disponible.',
            ];
        }

        $variante = null;
        if ($varianteProductoId) {
            $variante = VarianteProducto::where('id', $varianteProductoId)
                ->where('producto_id', $productoId)
                ->where('activo', true)
                ->first();

            if (!$variante) {
                return [
                    'exito' => false,
                    'mensaje' => 'La variante seleccionada no está disponible.',
                ];
            }
        }

        // Obtener stock disponible
        $stockDisponible = $variante ? (int) $variante->stock : (int) $producto->stock;

        if ($stockDisponible <= 0) {
            return [
                'exito' => false,
                'mensaje' => 'Este producto se encuentra agotado actualmente.',
            ];
        }

        $carrito = $this->obtenerOCrearCarrito($usuarioId, $sesionId);

        // Buscar item existente respetando la restricción UNIQUE
        $itemExistente = ItemCarrito::where('carrito_id', $carrito->id)
            ->where('producto_id', $productoId)
            ->where(function ($q) use ($varianteProductoId) {
                if ($varianteProductoId) {
                    $q->where('variante_producto_id', $varianteProductoId);
                } else {
                    $q->whereNull('variante_producto_id');
                }
            })
            ->first();

        $cantidadActual = $itemExistente ? $itemExistente->cantidad : 0;
        $cantidadDeseada = $cantidadActual + $cantidad;

        if ($cantidadDeseada > $stockDisponible) {
            return [
                'exito' => false,
                'mensaje' => "No es posible agregar {$cantidad} unidades más. Solo quedan {$stockDisponible} unidades disponibles en stock.",
                'stock_disponible' => $stockDisponible,
            ];
        }

        // Determinar precio congelado
        $precioUnitario = $this->obtenerPrecioUnitario($producto, $variante);

        if ($itemExistente) {
            $itemExistente->update([
                'cantidad' => $cantidadDeseada,
                'precio_unitario' => $precioUnitario,
            ]);
            $item = $itemExistente;
        } else {
            $item = ItemCarrito::create([
                'carrito_id' => $carrito->id,
                'producto_id' => $productoId,
                'variante_producto_id' => $varianteProductoId,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
            ]);
        }

        // Recalcular cupón si existe
        $this->recalcularDescuentoCupon($carrito, $usuarioId);

        return [
            'exito' => true,
            'mensaje' => 'Producto añadido al carrito correctamente.',
            'item' => $item,
            'carrito' => $carrito->fresh(['items.producto.imagenes', 'items.variante.opciones', 'cupon']),
        ];
    }

    /**
     * Actualiza la cantidad de un item validando el stock disponible.
     */
    public function actualizarCantidad(int $itemCarritoId, int $cantidad, ?int $usuarioId = null): array
    {
        if ($cantidad <= 0) {
            $this->eliminarItem($itemCarritoId, $usuarioId);
            return [
                'exito' => true,
                'eliminado' => true,
                'mensaje' => 'El producto fue retirado del carrito.',
            ];
        }

        $item = ItemCarrito::with(['producto', 'variante', 'carrito'])->find($itemCarritoId);

        if (!$item) {
            return [
                'exito' => false,
                'mensaje' => 'El artículo no fue encontrado en el carrito.',
            ];
        }

        $stockDisponible = $item->stock_disponible;

        if ($cantidad > $stockDisponible) {
            return [
                'exito' => false,
                'mensaje' => "Solo quedan {$stockDisponible} unidades disponibles en inventario.",
                'stock_disponible' => $stockDisponible,
            ];
        }

        $item->update(['cantidad' => $cantidad]);

        // Recalcular cupón
        $this->recalcularDescuentoCupon($item->carrito, $usuarioId);

        return [
            'exito' => true,
            'eliminado' => false,
            'mensaje' => 'Cantidad actualizada correctamente.',
            'item' => $item,
        ];
    }

    /**
     * Elimina un item del carrito.
     */
    public function eliminarItem(int $itemCarritoId, ?int $usuarioId = null): bool
    {
        $item = ItemCarrito::with('carrito')->find($itemCarritoId);

        if (!$item) {
            return false;
        }

        $carrito = $item->carrito;
        $item->delete();

        if ($carrito) {
            if ($carrito->items()->count() === 0) {
                $this->removerCupon($carrito);
            } else {
                $this->recalcularDescuentoCupon($carrito, $usuarioId);
            }
        }

        return true;
    }

    /**
     * Calcula el subtotal bruto del carrito.
     */
    public function calcularSubtotal(Carrito $carrito): float
    {
        $items = $carrito->relationLoaded('items') ? $carrito->items : $carrito->items()->get();

        return round((float) $items->sum(function ($item) {
            return $item->cantidad * (float) $item->precio_unitario;
        }), 2);
    }

    /**
     * Calcula el total desglosado con ITBMS (7%), envío y descuentos aplicados.
     */
    public function calcularTotal(Carrito $carrito, ?float $costoEnvio = 0.0, ?int $zonaEnvioId = null): array
    {
        $items = $carrito->relationLoaded('items')
            ? $carrito->items
            : $carrito->items()->with('producto')->get();

        $subtotal = 0.0;
        $baseImponibleItbms = 0.0;

        foreach ($items as $item) {
            $lineaTotal = $item->cantidad * (float) $item->precio_unitario;
            $subtotal += $lineaTotal;

            if ($item->producto && $item->producto->aplica_itbms) {
                $baseImponibleItbms += $lineaTotal;
            }
        }

        $subtotal = round($subtotal, 2);
        $itbms = round($baseImponibleItbms * 0.07, 2);
        $descuento = round((float) $carrito->descuento_aplicado, 2);
        $envio = round((float) ($costoEnvio ?? 0.0), 2);

        // Si la promoción de envío gratis aplica para esta zona, anular costo de envío
        if ($zonaEnvioId && $this->cuponService->evaluarEnvioGratis($zonaEnvioId, $subtotal)) {
            $envio = 0.0;
        }

        $total = max(0.00, round($subtotal + $itbms + $envio - $descuento, 2));

        return [
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'itbms' => $itbms,
            'envio' => $envio,
            'total' => $total,
            'cantidad_items' => (int) $items->sum('cantidad'),
        ];
    }

    /**
     * Aplica un código de cupón al carrito validando todas las reglas de negocio.
     */
    public function aplicarCupon(Carrito $carrito, string $codigo, ?int $usuarioId = null): array
    {
        $subtotal = $this->calcularSubtotal($carrito);

        if ($subtotal <= 0) {
            return [
                'valido' => false,
                'mensaje' => 'El carrito se encuentra vacío.',
            ];
        }

        $items = $carrito->relationLoaded('items') ? $carrito->items : $carrito->items()->with('producto')->get();
        $categoriaIds = $items->pluck('producto.categoria_id')->filter()->unique()->values()->all();
        $productoIds = $items->pluck('producto_id')->filter()->unique()->values()->all();

        $resultado = $this->cuponService->validarCupon(
            $codigo,
            $subtotal,
            $usuarioId,
            $categoriaIds,
            $productoIds
        );

        if (!$resultado['valido']) {
            return [
                'valido' => false,
                'mensaje' => $resultado['mensaje'],
            ];
        }

        $cupon = $resultado['cupon'];
        $descuento = (float) $resultado['descuento'];

        $carrito->update([
            'cupon_id' => $cupon ? $cupon->id : null,
            'descuento_aplicado' => $descuento,
        ]);

        return [
            'valido' => true,
            'descuento' => $descuento,
            'mensaje' => $resultado['mensaje'],
            'cupon' => $cupon,
        ];
    }

    /**
     * Remueve el cupón del carrito.
     */
    public function removerCupon(Carrito $carrito): bool
    {
        $carrito->update([
            'cupon_id' => null,
            'descuento_aplicado' => 0.00,
        ]);

        return true;
    }

    /**
     * Recalcula el descuento del cupón activo si cambia el subtotal del carrito.
     */
    public function recalcularDescuentoCupon(Carrito $carrito, ?int $usuarioId = null): void
    {
        if (!$carrito->cupon_id) {
            return;
        }

        $subtotal = $this->calcularSubtotal($carrito);

        if ($subtotal <= 0) {
            $this->removerCupon($carrito);
            return;
        }

        $cupon = Cupon::find($carrito->cupon_id);
        if (!$cupon || !$cupon->esVigente()) {
            $this->removerCupon($carrito);
            return;
        }

        $items = $carrito->relationLoaded('items') ? $carrito->items : $carrito->items()->with('producto')->get();
        $categoriaIds = $items->pluck('producto.categoria_id')->filter()->unique()->values()->all();
        $productoIds = $items->pluck('producto_id')->filter()->unique()->values()->all();

        $resultado = $this->cuponService->validarCupon(
            $cupon->codigo,
            $subtotal,
            $usuarioId,
            $categoriaIds,
            $productoIds
        );

        if ($resultado['valido']) {
            $carrito->update([
                'descuento_aplicado' => (float) $resultado['descuento'],
            ]);
        } else {
            $this->removerCupon($carrito);
        }
    }

    /**
     * Determina el precio unitario a congelar en el item.
     */
    protected function obtenerPrecioUnitario(Producto $producto, ?VarianteProducto $variante = null): float
    {
        if ($variante && $variante->precio !== null && (float) $variante->precio > 0) {
            return (float) $variante->precio;
        }

        if ($producto->oferta_activa && $producto->precio_oferta !== null) {
            $ahora = now();
            $inicioValido = !$producto->oferta_inicio_en || $ahora->gte($producto->oferta_inicio_en);
            $finValido = !$producto->oferta_fin_en || $ahora->lte($producto->oferta_fin_en);

            if ($inicioValido && $finValido) {
                return (float) $producto->precio_oferta;
            }
        }

        return (float) $producto->precio;
    }
}
