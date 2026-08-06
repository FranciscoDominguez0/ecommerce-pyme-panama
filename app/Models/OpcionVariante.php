<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OpcionVariante extends Model
{
    protected $table = 'opciones_variante';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'tipo_variante_id',
        'valor',
        'valor_hex',
    ];

    protected $casts = [
        'tipo_variante_id' => 'integer',
        'creado_en' => 'datetime',
    ];

    /**
     * Tipo de variante al que pertenece esta opción (ej. Color, Talla).
     */
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoVariante::class, 'tipo_variante_id');
    }

    /**
     * Variantes de productos que contienen esta opción.
     */
    public function variantes(): BelongsToMany
    {
        return $this->belongsToMany(
            VarianteProducto::class,
            'variante_opciones',
            'opcion_variante_id',
            'variante_producto_id'
        );
    }
}
