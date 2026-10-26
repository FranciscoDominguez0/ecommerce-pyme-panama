<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedidos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'direccion_id',
        'cupon_id',
        'zona_envio_id',
        'numero_pedido',
        'metodo_pago',
        'stripe_payment_intent_id',
        'subtotal',
        'descuento',
        'costo_envio',
        'itbms_monto',
        'total',
        'monto_reembolsado',
        'notas_cliente',
        'notas_internas',
        'comprobante_pago_ruta',
        'leido_en',
        'eliminado_en',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'itbms_monto' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_reembolsado' => 'decimal:2',
        'leido_en' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemPedido::class, 'pedido_id');
    }

    public function estados(): HasMany
    {
        return $this->hasMany(EstadoPedido::class, 'pedido_id')->orderByDesc('creado_en');
    }

    public function ultimoEstado(): HasOne
    {
        return $this->hasOne(EstadoPedido::class, 'pedido_id')->latestOfMany('creado_en');
    }

    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class, 'pedido_id');
    }

    public function direccion(): BelongsTo
    {
        return $this->belongsTo(Direccion::class, 'direccion_id');
    }

    public function zonaEnvio(): BelongsTo
    {
        return $this->belongsTo(ZonaEnvio::class, 'zona_envio_id');
    }

    public function cupon(): BelongsTo
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }

    public function envio(): HasOne
    {
        return $this->hasOne(EnvioPedido::class, 'pedido_id');
    }

    public function devolucion(): HasOne
    {
        return $this->hasOne(Devolucion::class, 'pedido_id');
    }

    public function valoraciones(): HasMany
    {
        return $this->hasMany(ValoracionProducto::class, 'pedido_id');
    }

    public function evaluacionUsabilidad(): HasOne
    {
        return $this->hasOne(EvaluacionUsabilidad::class, 'pedido_id');
    }

    /**
     * Obtiene los metadatos de tarjeta guardados en notas_internas (JSON).
     */
    public function getDetallesTarjetaAttribute(): array
    {
        if (!empty($this->notas_internas)) {
            $datos = json_decode($this->notas_internas, true);
            if (is_array($datos) && (isset($datos['tarjeta_marca']) || isset($datos['tarjeta_last4']))) {
                return $datos;
            }
        }

        return [];
    }

    public function getTarjetaMarcaAttribute(): ?string
    {
        return $this->detalles_tarjeta['tarjeta_marca'] ?? null;
    }

    public function getTarjetaLast4Attribute(): ?string
    {
        return $this->detalles_tarjeta['tarjeta_last4'] ?? null;
    }

    public function getEsReembolsadoAttribute(): bool
    {
        return $this->ultimoEstado?->estado === 'reembolsado' || (float) $this->monto_reembolsado > 0;
    }

    /**
     * Retorna la configuración visual (badge, punto de color y barra de progreso) según el estado actual.
     */
    public function getConfigEstadoAttribute(): array
    {
        $estado = $this->ultimoEstado?->estado ?? 'pendiente';

        return match ($estado) {
            'entregado' => [
                'label' => 'Entregado',
                'dot' => 'bg-emerald-500',
                'badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60',
                'progress_step' => 4,
                'progress_pct' => 100,
            ],
            'enviado', 'en_transito' => [
                'label' => 'En camino',
                'dot' => 'bg-blue-500',
                'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/60',
                'progress_step' => 3,
                'progress_pct' => 75,
            ],
            'listo_para_envio', 'en_preparacion' => [
                'label' => 'En preparación',
                'dot' => 'bg-amber-500',
                'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60',
                'progress_step' => 2,
                'progress_pct' => 50,
            ],
            'pago_confirmado' => [
                'label' => 'Pago confirmado',
                'dot' => 'bg-teal-500',
                'badge' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border border-teal-200/80 dark:border-teal-800/60',
                'progress_step' => 2,
                'progress_pct' => 50,
            ],
            'cancelado' => [
                'label' => 'Cancelado',
                'dot' => 'bg-rose-500',
                'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60',
                'progress_step' => 1,
                'progress_pct' => 15,
            ],
            default => [
                'label' => 'Pendiente',
                'dot' => 'bg-slate-400',
                'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
                'progress_step' => 1,
                'progress_pct' => 25,
            ],
        };
    }

    /**
     * Retorna la información visual de la tarjeta de crédito usada para el pago.
     */
    public function getInfoTarjetaAttribute(): array
    {
        $detalles = $this->detalles_tarjeta ?? [];
        $marca = strtolower($detalles['tarjeta_marca'] ?? '');
        $last4 = $detalles['tarjeta_last4'] ?? null;

        if (str_contains($marca, 'visa')) {
            $logo = asset('images/VISA-Logo.webp');
            $nombre = 'Visa';
        } elseif (str_contains($marca, 'mastercard') || str_contains($marca, 'master')) {
            $logo = asset('images/mastercard-logo.png');
            $nombre = 'Mastercard';
        } else {
            $logo = asset('images/stripe.webp');
            $nombre = !empty($marca) ? ucfirst($marca) : 'Stripe';
        }

        return [
            'nombre' => $nombre,
            'logo'   => $logo,
            'last4'  => $last4,
        ];
    }
}
