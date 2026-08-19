<?php

namespace App\Observers;

use App\Models\MovimientoInventario;
use App\Services\AuditoriaService;

class MovimientoInventarioObserver
{
    public function created(MovimientoInventario $movimiento)
    {
        $tipo = ucfirst($movimiento->tipo); // 'Entrada' o 'Salida'
        $producto = $movimiento->producto ? $movimiento->producto->nombre : 'Desconocido';
        
        $desc = "Movimiento de inventario ($tipo) registrado para el producto '$producto'. Cantidad: {$movimiento->cantidad}.";
        
        AuditoriaService::registrar('Inventario', 'creado', $desc, null, $movimiento->getAttributes());
    }

    public function updated(MovimientoInventario $movimiento)
    {
        $cambios = $movimiento->getChanges();
        $original = array_intersect_key($movimiento->getOriginal(), $cambios);

        AuditoriaService::registrar('Inventario', 'actualizado', "Movimiento de inventario ID {$movimiento->id} actualizado.", $original, $cambios);
    }

    public function deleted(MovimientoInventario $movimiento)
    {
        AuditoriaService::registrar('Inventario', 'eliminado', "Movimiento de inventario ID {$movimiento->id} eliminado.", $movimiento->getAttributes(), null);
    }
}
