<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
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
        'subtotal',
        'descuento',
        'costo_envio',
        'itbms_monto',
        'total',
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
}
