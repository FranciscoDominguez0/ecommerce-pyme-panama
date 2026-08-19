<?php

namespace App\Observers;

use App\Models\Categoria;
use App\Services\AuditoriaService;

class CategoriaObserver
{
    public function created(Categoria $categoria)
    {
        AuditoriaService::registrar('Catálogo', 'creado', "Categoría '{$categoria->nombre}' creada.", null, $categoria->getAttributes());
    }

    public function updated(Categoria $categoria)
    {
        $cambios = $categoria->getChanges();
        $original = array_intersect_key($categoria->getOriginal(), $cambios);

        AuditoriaService::registrar('Catálogo', 'actualizado', "Categoría '{$categoria->nombre}' actualizada.", $original, $cambios);
    }

    public function deleted(Categoria $categoria)
    {
        AuditoriaService::registrar('Catálogo', 'eliminado', "Categoría '{$categoria->nombre}' eliminada.", $categoria->getAttributes(), null);
    }
}
