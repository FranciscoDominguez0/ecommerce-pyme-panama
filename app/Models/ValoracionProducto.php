<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoracionProducto extends Model
{
    use HasFactory;

    protected $table = 'valoraciones_producto';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'producto_id',
        'pedido_id',
        'usuario_id',
        'calificacion',
        'titulo',
        'comentario',
        'es_aprobado',
    ];

    protected $casts = [
        'calificacion' => 'integer',
        'es_aprobado' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    /**
     * Producto que recibe la valoración.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Pedido entregado donde se compró el producto.
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Cliente que realizó la valoración.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Scope para obtener únicamente valoraciones aprobadas o visibles.
     */
    public function scopeAprobadas(Builder $query): Builder
    {
        return $query->where('es_aprobado', true);
    }

    /**
     * Nombre amigable y protegido del autor (ej. Juan P.) para cuidar la privacidad.
     */
    public function getNombreAutorAttribute(): string
    {
        if (!$this->usuario) {
            return 'Cliente verificado';
        }

        $nombre = trim((string) $this->usuario->nombre);
        $apellido = trim((string) $this->usuario->apellido);

        if (!empty($nombre) && !empty($apellido)) {
            $inicialApellido = mb_strtoupper(mb_substr($apellido, 0, 1, 'UTF-8'), 'UTF-8');
            return "{$nombre} {$inicialApellido}.";
        }

        return $nombre ?: 'Cliente verificado';
    }
}
