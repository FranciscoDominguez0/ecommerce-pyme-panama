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

    // Icono representativo para el rol en paneles y formularios
    public function getIconoAttribute(): string
    {
        return in_array($this->name, ['super_admin', 'Administrador']) ? 'manage_accounts' : 'person';
    }

    // Color del icono según la jerarquía del rol
    public function getIconoColorAttribute(): string
    {
        return in_array($this->name, ['super_admin', 'Administrador']) 
            ? 'text-slate-900 dark:text-white' 
            : 'text-slate-500 dark:text-slate-400';
    }
}
