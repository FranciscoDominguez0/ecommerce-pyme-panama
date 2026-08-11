<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Modelo Permission personalizado para adaptar Spatie Permission
 * a la tabla 'permisos' del proyecto, que usa creado_en
 * en lugar de created_at y no tiene columna de actualización.
 */
class Permission extends SpatiePermission
{
    protected $table = 'permisos';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'descripcion',
        'modulo',
        'name',
        'guard_name',
    ];
}
