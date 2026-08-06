<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Categoria extends Model
{
    protected $table = 'categorias';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'padre_id',
        'nombre',
        'slug',
        'descripcion',
        'imagen_ruta',
        'activo',
        'orden_visualizacion',
        'eliminado_en',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden_visualizacion' => 'integer',
        'padre_id' => 'integer',
        'eliminado_en' => 'datetime',
    ];

    /**
     * Categoría padre a la que pertenece esta categoría.
     */
    public function padre(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'padre_id');
    }

    /**
     * Subcategorías directas (hijas) de esta categoría.
     */
    public function hijas(): HasMany
    {
        return $this->hasMany(Categoria::class, 'padre_id')
            ->whereNull('eliminado_en')
            ->orderBy('orden_visualizacion', 'asc')
            ->orderBy('nombre', 'asc');
    }

    /**
     * Productos directamente asociados a esta categoría.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    /**
     * Scope para excluir registros marcados como eliminados (Soft Delete manual).
     */
    public function scopeSinEliminar(Builder $query): Builder
    {
        return $query->whereNull('eliminado_en');
    }

    /**
     * Scope para categorías activas y no eliminadas.
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('activo', true)->whereNull('eliminado_en');
    }

    /**
     * Scope para categorías raíz (sin padre).
     */
    public function scopePrincipales(Builder $query): Builder
    {
        return $query->whereNull('padre_id')->whereNull('eliminado_en');
    }

    /**
     * Retorna si la categoría es raíz (nivel 0).
     */
    public function esPrincipal(): bool
    {
        return is_null($this->padre_id);
    }
}
