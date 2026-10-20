<?php

namespace App\Services\Pagos\Metodos;

use App\Models\Configuracion;
use App\Models\Pedido;
use App\Services\PagoService;
use App\Services\Pagos\Contracts\MetodoPagoInterface;
use App\Services\Pagos\DTOs\ResultadoPago;
use Illuminate\Http\Request;

class YappyMetodoPago implements MetodoPagoInterface
{
    public function __construct(
        protected PagoService $pagoService
    ) {}

    public function id(): string
    {
        return 'yappy';
    }

    public function nombre(): string
    {
        return 'Yappy';
    }

    public function descripcion(): string
    {
        return 'Paga rápido con tu directorio de Banco General';
    }

    public function icono(): string
    {
        return 'asset:images/pa-yappy.webp';
    }

    public function estaActivo(): bool
    {
        return Configuracion::obtenerBool('pagos.yappy.activo', true);
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
        $telefono = $contexto['telefono_yappy'] ?? '6000-0000';
        $total = (float) ($contexto['total'] ?? 0.0);

        $exito = $this->pagoService->procesarYappy($telefono, $total);

        if (!$exito) {
            return ResultadoPago::fallo('No se pudo procesar el pago con Yappy.');
        }

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
            'mensaje' => 'Yappy requiere realizar el reembolso manualmente desde la banca en línea de Banco General.',
            'refund_id' => null,
            'monto' => 0.0,
        ];
    }

    public function vistaSubformulario(): ?string
    {
        return null;
    }
}
