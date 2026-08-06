<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'oferta_inicio_en' => 'datetime',
        'oferta_fin_en' => 'datetime',
        'destacado' => 'boolean',
        'activo' => 'boolean',
        'aplica_itbms' => 'boolean',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
        'categoria_id' => 'integer',
        'eliminado_en' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Categoría a la que pertenece el producto.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Galería de imágenes del producto.
     */
    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id')
            ->orderBy('orden', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * Variantes del producto (ej. Color, Talla, Memoria).
     */
    public function variantes(): HasMany
    {
        return $this->hasMany(VarianteProducto::class, 'producto_id');
    }

    /**
     * Items de pedidos asociados a este producto.
     */
    public function itemsPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class, 'producto_id');
    }

    /**
     * Obtiene la imagen principal del producto.
     */
    public function imagenPrincipal()
    {
        return $this->imagenes->firstWhere('es_principal', true) ?? $this->imagenes->first();
    }

    /**
     * Scope para excluir eliminados suaves.
     */
    public function scopeSinEliminar(Builder $query): Builder
    {
        return $query->whereNull('eliminado_en');
    }

    /**
     * Scope para productos activos en tienda.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true)->whereNull('eliminado_en');
    }

    /**
     * Scope para productos destacados en portada.
     */
    public function scopeDestacados(Builder $query): Builder
    {
        return $query->where('destacado', true)->where('activo', true)->whereNull('eliminado_en');
    }

    /**
     * Retorna si el producto cuenta con oferta activa vigente.
     */
    public function tieneOfertaValida(): bool
    {
        if (!$this->oferta_activa || is_null($this->precio_oferta) || $this->precio_oferta <= 0) {
            return false;
        }

        $ahora = now();
        if ($this->oferta_inicio_en && $ahora->lt($this->oferta_inicio_en)) {
            return false;
        }
        if ($this->oferta_fin_en && $ahora->gt($this->oferta_fin_en)) {
            return false;
        }

        return true;
    }
}
