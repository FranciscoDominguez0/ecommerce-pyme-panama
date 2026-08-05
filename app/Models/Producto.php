<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'productos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'categoria_id',
        'nombre',
        'slug',
        'descripcion',
        'descripcion_corta',
        'sku',
        'precio',
        'precio_oferta',
        'oferta_activa',
        'oferta_inicio_en',
        'oferta_fin_en',
        'stock',
        'stock_minimo',
        'destacado',
        'activo',
        'aplica_itbms',
        'eliminado_en',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_oferta' => 'decimal:2',
        'oferta_activa' => 'boolean',
        'destacado' => 'boolean',
        'activo' => 'boolean',
        'aplica_itbms' => 'boolean',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function itemsPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class, 'producto_id');
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id');
    }
}
