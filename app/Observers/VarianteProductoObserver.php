<?php

namespace App\Observers;

use App\Models\VarianteProducto;
use App\Services\AuditoriaService;

class VarianteProductoObserver
{
    public function created(VarianteProducto $variante)
    {
        $producto = $variante->producto ? $variante->producto->nombre : 'Desconocido';
        AuditoriaService::registrar('Catálogo', 'creado', "Variante de producto creada para '$producto' (SKU: {$variante->sku}).", null, $variante->getAttributes());
    }

    public function updated(VarianteProducto $variante)
    {
        $cambios = $variante->getChanges();
        $original = array_intersect_key($variante->getOriginal(), $cambios);
        
        $producto = $variante->producto ? $variante->producto->nombre : 'Desconocido';
        $desc = "Variante '{$variante->sku}' del producto '$producto' actualizada.";

        // Añadir detalles si cambia precio o stock
        if (isset($cambios['precio'])) {
            $desc .= " Precio cambiado de {$original['precio']} a {$cambios['precio']}.";
        }
        if (isset($cambios['stock'])) {
            $desc .= " Stock cambiado de {$original['stock']} a {$cambios['stock']}.";
        }

        AuditoriaService::registrar('Catálogo', 'actualizado', $desc, $original, $cambios);
    }

    public function deleted(VarianteProducto $variante)
    {
        AuditoriaService::registrar('Catálogo', 'eliminado', "Variante '{$variante->sku}' eliminada.", $variante->getAttributes(), null);
    }
}
