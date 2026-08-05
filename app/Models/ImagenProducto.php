<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenProducto extends Model
{
    protected $table = 'imagenes_producto';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'producto_id',
        'ruta',
        'es_principal',
        'orden',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'orden' => 'integer',
        'creado_en' => 'datetime',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
