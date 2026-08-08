<?php

namespace App\Services;

use App\Models\Cupon;
use App\Models\ProductoDelMes;
use App\Models\PromocionEnvioGratis;
use App\Models\UsoCupon;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CuponService
{
    /**
     * Valida un código de cupón contra el carrito y el usuario actual.
     *
     * @param string $codigo Código del cupón
     * @param float $subtotal Subtotal del carrito
     * @param int|null $usuarioId ID del usuario autenticado
     * @param array $categoriaIds Lista de IDs de categorías presentes en el carrito
     * @param array $productoIds Lista de IDs de productos presentes en el carrito
     * @return array Respuesta formateada ['valido' => bool, 'descuento' => float, 'mensaje' => string, 'cupon' => Cupon|null]
     */
    public function validarCupon(
        string $codigo,
        float $subtotal,
        ?int $usuarioId = null,
        array $categoriaIds = [],
        array $productoIds = []
    ): array {
        $codigoLimpio = strtoupper(trim($codigo));

        if (empty($codigoLimpio)) {
            return [
                'valido' => false,
                'descuento' => 0.0,
                'mensaje' => 'Por favor, ingresa un código de cupón.',
                'cupon' => null,
            ];
        }

        $cupon = Cupon::where('codigo', $codigoLimpio)->first();

        if (!$cupon) {
            return [
                'valido' => false,
                'descuento' => 0.0,
                'mensaje' => 'El código de cupón ingresado no existe.',
                'cupon' => null,
            ];
        }

        // 1. Estado activo
        if (!$cupon->activo) {
            return [
                'valido' => false,
                'descuento' => 0.0,
                'mensaje' => 'Este cupón se encuentra inactivo.',
                'cupon' => $cupon,
            ];
        }

        // 2. Vigencia de fechas
        $ahora = Carbon::now();
        if ($cupon->inicio_en && $ahora->lt($cupon->inicio_en)) {
            return [
                'valido' => false,
                'descuento' => 0.0,
                'mensaje' => 'Este cupón aún no se encuentra vigente.',
                'cupon' => $cupon,
            ];
        }

        if ($cupon->fin_en && $ahora->gt($cupon->fin_en)) {
            return [
                'valido' => false,
                'descuento' => 0.0,
                'mensaje' => 'Este cupón ha expirado.',
                'cupon' => $cupon,
            ];
        }

        // 3. Límite de usos totales
        if ($cupon->maximo_usos_total !== null && $cupon->maximo_usos_total > 0) {
            if ($cupon->usos_actuales >= $cupon->maximo_usos_total) {
                return [
                    'valido' => false,
                    'descuento' => 0.0,
                    'mensaje' => 'Este cupón ha alcanzado el límite máximo de usos disponibles.',
                    'cupon' => $cupon,
                ];
            }
        }

        // 4. Monto mínimo del carrito
        if ($cupon->monto_minimo && $subtotal < (float) $cupon->monto_minimo) {
            $montoFaltante = number_format($cupon->monto_minimo - $subtotal, 2);
            return [
                'valido' => false,
                'descuento' => 0.0,
                'mensaje' => "El monto mínimo de compra para este cupón es de $" . number_format($cupon->monto_minimo, 2) . ". Agrega $" . $montoFaltante . " adicionales.",
                'cupon' => $cupon,
            ];
        }

        // 5. Alcance de aplicación (todo, categoría o producto)
        if ($cupon->aplica_a === 'categoria') {
            if ($cupon->categoria_id && !in_array($cupon->categoria_id, $categoriaIds)) {
                $nombreCat = $cupon->categoria ? $cupon->categoria->nombre : 'la categoría asignada';
                return [
                    'valido' => false,
                    'descuento' => 0.0,
                    'mensaje' => "Este cupón solo aplica para productos de la categoría '{$nombreCat}'.",
                    'cupon' => $cupon,
                ];
            }
        } elseif ($cupon->aplica_a === 'producto') {
            if ($cupon->producto_id && !in_array($cupon->producto_id, $productoIds)) {
                $nombreProd = $cupon->producto ? $cupon->producto->nombre : 'el producto asignado';
                return [
                    'valido' => false,
                    'descuento' => 0.0,
                    'mensaje' => "Este cupón solo aplica para el producto '{$nombreProd}'.",
                    'cupon' => $cupon,
                ];
            }
        }

        // 6. Límite de usos por cliente
        if ($cupon->usos_por_cliente !== null && $cupon->usos_por_cliente > 0 && $usuarioId) {
            $usosDelUsuario = UsoCupon::where('cupon_id', $cupon->id)
                ->where('usuario_id', $usuarioId)
                ->count();

            if ($usosDelUsuario >= $cupon->usos_por_cliente) {
                return [
                    'valido' => false,
                    'descuento' => 0.0,
                    'mensaje' => "Has alcanzado el límite máximo de {$cupon->usos_por_cliente} uso(s) permitido(s) para este cupón.",
                    'cupon' => $cupon,
                ];
            }
        }

        // 7. Cálculo del descuento
        $descuento = 0.0;
        if ($cupon->tipo === 'porcentaje') {
            $descuento = ($subtotal * (float) $cupon->valor) / 100;
        } elseif ($cupon->tipo === 'monto_fijo') {
            $descuento = (float) $cupon->valor;
        } elseif ($cupon->tipo === 'envio_gratis') {
            $descuento = 0.0; // El costo de envío se bonifica por separado
        }

        // Garantizar que el descuento no supere el subtotal
        $descuentoFinal = min($descuento, $subtotal);

        return [
            'valido' => true,
            'descuento' => round($descuentoFinal, 2),
            'mensaje' => 'Cupón aplicado exitosamente.',
            'cupon' => $cupon,
        ];
    }

    /**
     * Registra la utilización formal de un cupón al completar un pedido.
     */
    public function registrarUso(int $cuponId, ?int $usuarioId, int $pedidoId, float $descuentoAplicado): bool
    {
        return DB::transaction(function () use ($cuponId, $usuarioId, $pedidoId, $descuentoAplicado) {
            $cupon = Cupon::find($cuponId);
            if (!$cupon) {
                return false;
            }

            // Incrementar contador de usos del cupón
            $cupon->increment('usos_actuales');

            // Crear registro en tabla usos_cupon
            UsoCupon::create([
                'cupon_id' => $cuponId,
                'usuario_id' => $usuarioId,
                'pedido_id' => $pedidoId,
                'descuento_aplicado' => $descuentoAplicado,
                'creado_en' => Carbon::now(),
            ]);

            return true;
        });
    }

    /**
     * Evalúa si una zona de envío califica para promoción de envío gratis vigente.
     */
    public function evaluarEnvioGratis(?int $zonaEnvioId, float $subtotal): bool
    {
        if (!$zonaEnvioId || $subtotal <= 0) {
            return false;
        }

        $promocion = PromocionEnvioGratis::where('zona_envio_id', $zonaEnvioId)
            ->where('activo', true)
            ->get()
            ->first(function ($promo) use ($subtotal) {
                return $promo->aplicaParaMonto($subtotal);
            });

        return $promocion !== null;
    }

    /**
     * Obtiene el Producto del Mes activo y vigente.
     */
    public function obtenerProductoDelMesActivo(): ?ProductoDelMes
    {
        $promociones = ProductoDelMes::with('producto')
            ->where('activo', true)
            ->get();

        return $promociones->first(function ($promo) {
            return $promo->esVigente();
        });
    }
}
