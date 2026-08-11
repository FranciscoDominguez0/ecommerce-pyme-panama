<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'usuario_id',
        'alias',
        'nombre_receptor',
        'provincia',
        'distrito',
        'corregimiento',
        'direccion_exacta',
        'referencia',
        'es_predeterminada',
        'eliminado_en',
    ];

    protected $casts = [
        'es_predeterminada' => 'boolean',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
        'eliminado_en' => 'datetime',
    ];

    public function scopeSinEliminar($query)
    {
        return $query->whereNull('eliminado_en');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
