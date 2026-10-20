<?php

namespace App\Services\Pagos\Metodos;

use App\Models\Configuracion;
use App\Models\Pedido;
use App\Services\PagoService;
use App\Services\Pagos\Contracts\MetodoPagoInterface;
use App\Services\Pagos\DTOs\ResultadoPago;
use Illuminate\Http\Request;

class ContraEntregaMetodoPago implements MetodoPagoInterface
{
    public function __construct(
        protected PagoService $pagoService
    ) {}

    public function id(): string
    {
        return 'contra_entrega';
    }

    public function nombre(): string
    {
        return 'Pago Contra Entrega';
    }

    public function descripcion(): string
    {
        return 'Paga en efectivo o tarjeta al recibir tu pedido';
    }

    public function icono(): string
    {
        return 'local_shipping';
    }

    public function estaActivo(): bool
    {
        return Configuracion::obtenerBool('pagos.contra_entrega.activo', true);
    }

    public function reglasValidacion(Request $request): array
    {
        return [];
    }

    public function extraerDatosSesion(Request $request): array
    {
        return [];
    }

    public function procesar(array $contexto): ResultadoPago
    {
        $this->pagoService->procesarContraEntrega();

        return ResultadoPago::exito();
    }

    public function soportaReembolso(): bool
    {
        return false;
    }

    public function reembolsar(Pedido $pedido, ?float $monto = null, ?string $motivo = null): array
    {
        return [
            'exito' => false,
            'mensaje' => 'Los pedidos con pago contra entrega se cobran al recibir; el reembolso se efectúa en efectivo o nota de crédito.',
            'refund_id' => null,
            'monto' => 0.0,
        ];
    }

    public function vistaSubformulario(): ?string
    {
        return null;
    }
}
