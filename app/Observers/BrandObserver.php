<?php

namespace App\Observers;

use App\Models\Brand;
use App\Services\AuditoriaService;

class BrandObserver
{
    public function created(Brand $brand)
    {
        AuditoriaService::registrar('Catálogo', 'creado', "Marca '{$brand->nombre}' creada.", null, $brand->getAttributes());
    }

    public function updated(Brand $brand)
    {
        $cambios = $brand->getChanges();
        $original = array_intersect_key($brand->getOriginal(), $cambios);

        AuditoriaService::registrar('Catálogo', 'actualizado', "Marca '{$brand->nombre}' actualizada.", $original, $cambios);
    }

    public function deleted(Brand $brand)
    {
        AuditoriaService::registrar('Catálogo', 'eliminado', "Marca '{$brand->nombre}' eliminada.", $brand->getAttributes(), null);
    }
}
