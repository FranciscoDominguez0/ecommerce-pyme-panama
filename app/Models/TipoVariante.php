<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoVariante extends Model
{
    protected $table = 'tipos_variante';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
    ];

    /**
     * Opciones disponibles para este tipo de variante (ej. Negro, Blanco para Color).
     */
    public function opciones(): HasMany
    {
        return $this->hasMany(OpcionVariante::class, 'tipo_variante_id');
    }
}
