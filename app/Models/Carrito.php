<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrito extends Model
{
    use HasFactory;
    protected $table = 'carritos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'cupon_id',
        'sesion_id',
        'descuento_aplicado',
    ];

    protected $casts = [
        'usuario_id' => 'integer',
        'cupon_id' => 'integer',
        'descuento_aplicado' => 'decimal:2',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Usuario propietario del carrito (si está autenticado).
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Cupón de descuento aplicado a este carrito.
     */
    public function cupon(): BelongsTo
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }

    /**
     * Items o productos contenidos en el carrito.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemCarrito::class, 'carrito_id');
    }

    /**
     * Cantidad total de unidades de productos en el carrito.
     */
    public function getCantidadTotalAttribute(): int
    {
        return (int) $this->items->sum('cantidad');
    }

    /**
     * Subtotal bruto del carrito sumando cada item.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->subtotal;
        });
    }
}
