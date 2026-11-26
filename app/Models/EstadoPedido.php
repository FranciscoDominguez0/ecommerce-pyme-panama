<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstadoPedido extends Model
{
    use HasFactory;
    protected $table = 'estados_pedido';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'estado',
        'comentario',
    ];

    /**
     * Sincronización automática: anula la factura asociada si el pedido entra en estado cancelado o afín.
     */
    protected static function booted()
    {
        static::created(function (EstadoPedido $estadoPedido) {
            if (in_array($estadoPedido->estado, ['cancelado', 'reembolsado', 'pago_rechazado', 'devolucion_aprobada'])) {
                if ($estadoPedido->pedido) {
                    app(\App\Services\FacturaService::class)->anularFactura($estadoPedido->pedido);
                }
            }
        });
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
