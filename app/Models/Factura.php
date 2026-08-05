<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factura extends Model
{
    protected $table = 'facturas';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'numero',
        'metodo_pago',
        'referencia_pago_externo',
        'subtotal',
        'descuento',
        'costo_envio',
        'itbms_tasa',
        'itbms_monto',
        'total',
        'estado',
        'pdf_ruta',
        'emitida_en',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
