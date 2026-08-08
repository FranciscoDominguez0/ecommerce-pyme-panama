<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZonaEnvio extends Model
{
    use HasFactory;

    protected $table = 'zonas_envio';

    protected $fillable = [
        'nombre',
        'costo',
        'activo',
    ];

    protected $casts = [
        'costo' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Scope para filtrar únicamente zonas activas.
     */
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
