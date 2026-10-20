<?php

namespace App\Services\Pagos\Metodos;

use App\Models\Configuracion;
use App\Models\Pedido;
use App\Services\PagoService;
use App\Services\Pagos\Contracts\MetodoPagoInterface;
use App\Services\Pagos\DTOs\ResultadoPago;
use Illuminate\Http\Request;

class StripeMetodoPago implements MetodoPagoInterface
{
    public function __construct(
        protected PagoService $pagoService
    ) {}

    public function id(): string
    {
        return 'stripe';
    }

    public function nombre(): string
    {
        return 'Tarjeta de Crédito / Débito';
    }

    public function descripcion(): string
    {
        return 'Pago seguro y validación en tiempo real con Stripe';
    }

    public function icono(): string
    {
        return 'credit_card';
    }

    public function estaActivo(): bool
    {
        return Configuracion::obtenerBool('pagos.stripe.activo', true);
    }

    public function reglasValidacion(Request $request): array
    {
        return [];
    }

    public function extraerDatosSesion(Request $request): array
    {
        return [
            'checkout_stripe_pm' => $request->input('stripe_payment_method_id'),
            'checkout_stripe_last4' => $request->input('stripe_last4'),
            'checkout_stripe_brand' => $request->input('stripe_brand'),
        ];
    }

    public function procesar(array $contexto): ResultadoPago
    {
        $stripePm = $contexto['stripe_pm'] ?? session('checkout_stripe_pm') ?: ['simulacion' => true];
        $total = (float) ($contexto['total'] ?? 0.0);

        $exito = $this->pagoService->procesarStripe($stripePm, $total);

        if (!$exito) {
            $errorSession = session('error');
            $mensaje = is_array($errorSession)
                ? ($errorSession['message'] ?? 'El pago con tarjeta fue rechazado.')
                : ($errorSession ?: 'El pago con tarjeta no pudo ser procesado.');

            return ResultadoPago::fallo($mensaje);
        }

        $stripePiId = session('checkout_stripe_pi');
        $stripeBrand = session('checkout_stripe_brand');
        $stripeLast4 = session('checkout_stripe_last4');

        $metadatos = [
            'tarjeta_marca' => $stripeBrand,
            'tarjeta_last4' => $stripeLast4,
            'stripe_pm' => is_string($stripePm) ? $stripePm : ($stripePm['id'] ?? 'simulacion'),
            'stripe_payment_intent_id' => $stripePiId,
        ];

        return ResultadoPago::exito($metadatos, $stripePiId);
    }

    public function soportaReembolso(): bool
    {
        return true;
    }

    public function reembolsar(Pedido $pedido, ?float $monto = null, ?string $motivo = null): array
    {
        return $this->pagoService->reembolsarStripe($pedido, $monto, $motivo);
    }

    public function vistaSubformulario(): ?string
    {
        return 'cliente.checkout.partials.subform-stripe';
    }
}
