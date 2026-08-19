<?php

namespace App\Observers;

use App\Models\Role;
use App\Services\AuditoriaService;

class RoleObserver
{
    public function created(Role $role)
    {
        AuditoriaService::registrar('Roles y Permisos', 'creado', "Rol '{$role->name}' creado.", null, $role->getAttributes());
    }

    public function updated(Role $role)
    {
        $cambios = $role->getChanges();
        $original = array_intersect_key($role->getOriginal(), $cambios);

        AuditoriaService::registrar('Roles y Permisos', 'actualizado', "Rol '{$role->name}' actualizado.", $original, $cambios);
    }

    public function deleted(Role $role)
    {
        AuditoriaService::registrar('Roles y Permisos', 'eliminado', "Rol '{$role->name}' eliminado.", $role->getAttributes(), null);
    }
}
