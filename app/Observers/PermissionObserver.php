<?php

namespace App\Observers;

use App\Models\Permission;
use App\Services\AuditoriaService;

class PermissionObserver
{
    public function created(Permission $permission)
    {
        AuditoriaService::registrar('Roles y Permisos', 'creado', "Permiso '{$permission->name}' creado.", null, $permission->getAttributes());
    }

    public function updated(Permission $permission)
    {
        $cambios = $permission->getChanges();
        $original = array_intersect_key($permission->getOriginal(), $cambios);

        AuditoriaService::registrar('Roles y Permisos', 'actualizado', "Permiso '{$permission->name}' actualizado.", $original, $cambios);
    }

    public function deleted(Permission $permission)
    {
        AuditoriaService::registrar('Roles y Permisos', 'eliminado', "Permiso '{$permission->name}' eliminado.", $permission->getAttributes(), null);
    }
}
