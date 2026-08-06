<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VarianteProducto extends Model
{
    protected $table = 'variantes_producto';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'producto_id',
        'sku',
        'precio',
        'stock',
        'imagen_ruta',
        'activo',
    ];

    protected $casts = [
        'producto_id' => 'integer',
        'precio' => 'decimal:2',
        'stock' => 'integer',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Producto padre de esta variante.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Opciones asociadas a esta variante (ej. Color: Negro, Talla: M).
     */
    public function opciones(): BelongsToMany
    {
        return $this->belongsToMany(
            OpcionVariante::class,
            'variante_opciones',
            'variante_producto_id',
            'opcion_variante_id'
        );
    }
}
