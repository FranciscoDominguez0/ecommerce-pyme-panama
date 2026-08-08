<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsoCupon extends Model
{
    use HasFactory;

    protected $table = 'usos_cupon';

    public $timestamps = false;

    const CREATED_AT = 'creado_en';

    protected $fillable = [
        'cupon_id',
        'usuario_id',
        'pedido_id',
        'descuento_aplicado',
        'creado_en',
    ];

    protected $casts = [
        'descuento_aplicado' => 'float',
        'creado_en' => 'datetime',
    ];

    public function cupon(): BelongsTo
    {
        return $this->belongsTo(Cupon::class, 'cupon_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
