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
        'eliminado_en',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'itbms_monto' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_reembolsado' => 'decimal:2',
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
}
