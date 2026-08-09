<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvioPedido extends Model
{
    protected $table = 'envios_pedido';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'pedido_id',
        'empresa_mensajeria',
        'numero_guia',
        'url_rastreo',
        'fecha_estimada_entrega',
        'fecha_entrega_real',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'fecha_estimada_entrega' => 'datetime',
        'fecha_entrega_real' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
