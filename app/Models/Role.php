<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Modelo Role personalizado para adaptar Spatie Permission
 * a la tabla 'roles' del proyecto, que usa creado_en / actualizado_en
 * en lugar de created_at / updated_at.
 */
class Role extends SpatieRole
{
    protected $table = 'roles';

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'name',
        'guard_name',
    ];
}
