<?php

namespace App\Services\Pagos\Metodos;

use App\Models\Configuracion;
use App\Models\Pedido;
use App\Services\PagoService;
use App\Services\Pagos\Contracts\MetodoPagoInterface;
use App\Services\Pagos\DTOs\ResultadoPago;
use Illuminate\Http\Request;

class TransferenciaMetodoPago implements MetodoPagoInterface
{
    public function __construct(
        protected PagoService $pagoService
    ) {}

    public function id(): string
    {
        return 'transferencia';
    }

    public function nombre(): string
    {
        return 'Transferencia Bancaria (ACH)';
    }

    public function descripcion(): string
    {
        return 'Envía el comprobante para procesar tu pedido';
    }

    public function icono(): string
    {
        return 'account_balance';
    }

    public function estaActivo(): bool
    {
        return Configuracion::obtenerBool('pagos.transferencia.activo', true);
    }

    public function reglasValidacion(Request $request): array
    {
        return [
            'comprobante_pago' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ];
    }

    public function extraerDatosSesion(Request $request): array
    {
        $comprobanteRuta = null;
        if ($request->hasFile('comprobante_pago')) {
            $comprobanteRuta = $request->file('comprobante_pago')->store('comprobantes', 'public');
        }

        return [
            'checkout_comprobante_ruta' => $comprobanteRuta,
        ];
    }

    public function procesar(array $contexto): ResultadoPago
    {
        $comprobanteRuta = $contexto['comprobante_ruta'] ?? session('checkout_comprobante_ruta');

        $exito = $this->pagoService->procesarTransferencia($comprobanteRuta);

        if (!$exito) {
            return ResultadoPago::fallo('Es obligatorio adjuntar el comprobante de transferencia para continuar.');
        }

        return ResultadoPago::exito([
            'comprobante_ruta' => $comprobanteRuta,
        ]);
    }

    public function soportaReembolso(): bool
    {
        return false;
    }

    public function reembolsar(Pedido $pedido, ?float $monto = null, ?string $motivo = null): array
    {
        return [
            'exito' => false,
            'mensaje' => 'Las transferencias bancarias (ACH) deben reembolsarse manualmente mediante transferencia inversa al cliente.',
            'refund_id' => null,
            'monto' => 0.0,
        ];
    }

    public function vistaSubformulario(): ?string
    {
        return 'cliente.checkout.partials.subform-transferencia';
    }
}
