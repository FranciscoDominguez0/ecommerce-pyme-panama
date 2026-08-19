<?php

namespace App\Observers;

use App\Models\Pedido;
use App\Services\AuditoriaService;

class PedidoObserver
{
    public function created(Pedido $pedido)
    {
        AuditoriaService::registrar('Pedidos', 'creado', "Pedido {$pedido->numero_pedido} creado.", null, $pedido->getAttributes());
    }

    public function updated(Pedido $pedido)
    {
        $cambios = $pedido->getChanges();
        $original = array_intersect_key($pedido->getOriginal(), $cambios);

        $desc = "Pedido #{$pedido->numero_pedido} actualizado.";

        if (isset($cambios['estado'])) {
            $estadoStr = match($cambios['estado']) {
                'cancelado' => 'CANCELADO',
                'pago_aprobado' => 'PAGO CONFIRMADO',
                'enviado' => 'ENVIADO',
                'entregado' => 'ENTREGADO',
                default => strtoupper($cambios['estado'])
            };
            $desc = "Pedido #{$pedido->numero_pedido} cambió de estado a $estadoStr.";
        }

        AuditoriaService::registrar('Pedidos', 'actualizado', $desc, $original, $cambios);
    }

    public function deleted(Pedido $pedido)
    {
        AuditoriaService::registrar('Pedidos', 'eliminado', "Pedido {$pedido->numero_pedido} eliminado.", $pedido->getAttributes(), null);
    }
}
