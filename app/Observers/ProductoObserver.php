<?php

namespace App\Observers;

use App\Models\Producto;
use App\Services\AuditoriaService;

class ProductoObserver
{
    public function created(Producto $producto)
    {
        AuditoriaService::registrar('Productos', 'creado', "Producto '{$producto->nombre}' creado.", null, $producto->getAttributes());
    }

    public function updated(Producto $producto)
    {
        $cambios = $producto->getChanges();
        $original = array_intersect_key($producto->getOriginal(), $cambios);

        $desc = "Producto '{$producto->nombre}' actualizado.";
        
        if (isset($cambios['precio_base'])) {
            $desc .= " Precio base modificado de {$original['precio_base']} a {$cambios['precio_base']}.";
        }
        if (isset($cambios['stock'])) {
            $desc .= " Stock modificado de {$original['stock']} a {$cambios['stock']}.";
        }

        AuditoriaService::registrar('Productos', 'actualizado', $desc, $original, $cambios);
    }

    public function deleted(Producto $producto)
    {
        AuditoriaService::registrar('Productos', 'eliminado', "Producto '{$producto->nombre}' eliminado.", $producto->getAttributes(), null);
    }
}
