<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Categoria extends Model
{
    use HasFactory;

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
        'exento_envio',
        'orden_visualizacion',
        'eliminado_en',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'exento_envio' => 'boolean',
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
     * Carga las categorías raíz con el conteo total de productos activos (propios + hijas)
     * en una sola query SQL optimizada, evitando N+1.
     */
    public static function raicesConConteoDeProductos(): Collection
    {
        // LEFT JOIN doble: primero a hijas, luego a productos de padre o hija.
        // Así contamos productos propios Y de subcategorías en una sola pasada.
        $sql = <<<SQL
            SELECT
                c.id,
                c.nombre,
                c.slug,
                c.imagen_ruta,
                c.activo,
                c.padre_id,
                c.orden_visualizacion,
                COUNT(DISTINCT p.id) AS total_productos_count
            FROM categorias c
            LEFT JOIN categorias hija
                ON hija.padre_id = c.id
                AND hija.eliminado_en IS NULL
            LEFT JOIN productos p
                ON (p.categoria_id = c.id OR p.categoria_id = hija.id)
                AND p.eliminado_en IS NULL
                AND p.activo = TRUE
            WHERE c.padre_id IS NULL
              AND c.eliminado_en IS NULL
            GROUP BY c.id, c.nombre, c.slug, c.imagen_ruta, c.activo, c.padre_id, c.orden_visualizacion
            ORDER BY c.nombre ASC
        SQL;

        $categorias = static::hydrate(
            array_map(fn($fila) => (array) $fila, DB::select($sql))
        );

        // Una query adicional para cargar hijas (necesaria para el sidebar).
        $categorias->load(['hijas' => fn($q) => $q->sinEliminar()->orderBy('nombre')]);

        return $categorias;
    }

    /**
     * Retorna si la categoría es raíz (nivel 0).
     */
    public function esPrincipal(): bool
    {
        return is_null($this->padre_id);
    }

    /**
     * Retorna el nivel de profundidad en el árbol jerárquico (0 = Raíz, 1 = Subcategoría, 2 = 3er Nivel, etc.).
     */
    public function getNivelAttribute(): int
    {
        $nivel = 0;
        $actual = $this->padre;
        while ($actual) {
            $nivel++;
            $actual = $actual->padre;
        }
        return $nivel;
    }

    /**
     * Retorna la cadena de ancestros superiores (ej: "Tecnología > Laptops").
     */
    public function getRutaPadresAttribute(): ?string
    {
        $ancestros = [];
        $actual = $this->padre;

        while ($actual) {
            array_unshift($ancestros, $actual->nombre);
            $actual = $actual->padre;
        }

        return !empty($ancestros) ? implode(' > ', $ancestros) : null;
    }

    /**
     * Retorna la ruta jerárquica completa incluyendo la propia categoría.
     */
    public function getRutaJerarquicaAttribute(): string
    {
        $padres = $this->ruta_padres;
        return $padres ? "{$padres} > {$this->nombre}" : $this->nombre;
    }

    /**
     * Determina si la categoría está exenta de cobro de envío.
     * Si la categoría padre tiene activo que no lleva envío, las hijas lo heredan automáticamente.
     */
    public function getExentoEnvioAttribute(): bool
    {
        if ((bool) ($this->attributes['exento_envio'] ?? false)) {
            return true;
        }

        // Si tiene categoría padre, hereda la exención del padre recursivamente
        return (bool) ($this->padre?->exento_envio ?? false);
    }

    /**
     * Retorna si la exención fue configurada directamente en esta categoría.
     */
    public function getExentoEnvioDirectoAttribute(): bool
    {
        return (bool) ($this->attributes['exento_envio'] ?? false);
    }

    /**
     * Retorna si la exención de envío proviene heredada de la categoría padre.
     */
    public function getExentoEnvioHeredadoAttribute(): bool
    {
        if ((bool) ($this->attributes['exento_envio'] ?? false)) {
            return false;
        }

        return (bool) ($this->padre?->exento_envio ?? false);
    }
}
