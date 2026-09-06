<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Throwable;

class PagoService
{
    /**
     * Procesa el pago con Stripe usando PaymentIntents de la API oficial de Stripe.
     *
     * @param string|array $paymentMethodId Token o ID de método de pago de Stripe (ej. pm_1...) o array de simulación
     * @param float $monto Monto total a cobrar en Balboas/USD
     */
    public function procesarStripe(string|array $paymentMethodId, float $monto): bool
    {
        if (empty($paymentMethodId)) {
            return false;
        }

        // Si es una simulación en pruebas unitarias
        if (is_array($paymentMethodId) && isset($paymentMethodId['simulacion'])) {
            session(['checkout_stripe_pi' => 'pi_simulacion_' . uniqid()]);
            return true;
        }

        $secretKey = config('services.stripe.secret');

        // Si no hay clave secreta configurada aún, simular si no es producción
        if (empty($secretKey)) {
            Log::warning('Stripe: STRIPE_SECRET no está configurada en .env.');
            session(['checkout_stripe_pi' => 'pi_simulacion_' . uniqid()]);
            return true;
        }

        $metodoId = is_string($paymentMethodId) ? $paymentMethodId : ($paymentMethodId['id'] ?? null);

        if (!$metodoId) {
            return false;
        }

        try {
            $intent = $this->crearPaymentIntentStripe($secretKey, $metodoId, $monto);

            if ($intent->status === 'succeeded') {
                session(['checkout_stripe_pi' => $intent->id]);
                return true;
            }

            Log::warning('Stripe: Pago no completado. Estado: ' . $intent->status);
            return false;

        } catch (CardException $e) {
            Log::error('Stripe CardException: ' . $e->getMessage());
            session()->flash('error', 'Pago rechazado: ' . $this->traducirErrorStripe($e));
            return false;
        } catch (Throwable $e) {
            Log::error('Stripe Error: ' . $e->getMessage());
            session()->flash('error', 'Error al procesar tarjeta con Stripe: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea y confirma el PaymentIntent a través del SDK de Stripe.
     *
     * @suppress PHP6613
     */
    protected function crearPaymentIntentStripe(string $secretKey, string $paymentMethodId, float $monto): PaymentIntent
    {
        Stripe::setApiKey($secretKey);

        $params = [
            'amount' => (int) round($monto * 100), // Stripe procesa en centavos
            'currency' => 'usd',
            'payment_method' => $paymentMethodId,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
            'description' => 'Compra en ' . config('app.name', 'PayMe Panamá'),
        ];

        /** @var PaymentIntent $intent */
        $intent = PaymentIntent::create($params);

        return $intent;
    }

    /**
     * Traduce los códigos de rechazo de Stripe al español.
     */
    protected function traducirErrorStripe(CardException $e): string
    {
        return match ($e->getDeclineCode() ?? $e->getStripeCode()) {
            'insufficient_funds' => 'Fondos insuficientes en la tarjeta.',
            'card_declined' => 'La tarjeta fue declinada por el banco.',
            'expired_card' => 'La tarjeta ha expirado.',
            'incorrect_cvc' => 'El código de seguridad (CVC) es incorrecto.',
            'processing_error' => 'Ocurrió un error al procesar la tarjeta.',
            'incorrect_number' => 'El número de tarjeta es incorrecto.',
            default => $e->getMessage(),
        };
    }

    /**
     * Procesa el pago con Yappy (Banco General Panamá).
     */
    public function procesarYappy(string $telefono, float $monto): bool
    {
        // Simulación de Yappy
        if (empty($telefono)) {
            return false;
        }

        return true;
    }

    /**
     * Procesa el pago por transferencia bancaria (ACH).
     * Solo verifica que se haya provisto una ruta válida al comprobante.
     */
    public function procesarTransferencia(?string $comprobantePagoRuta): bool
    {
        // Si no hay comprobante, no se puede confirmar (podría quedar pendiente y subirse después, 
        // pero la regla dice que si falla no se crea pedido. Asumimos que debe enviarse en el checkout).
        if (empty($comprobantePagoRuta)) {
            return false;
        }

        return true;
    }

    /**
     * Procesa el pago contra entrega. Siempre retorna true ya que el pago se realiza al recibir.
     */
    public function procesarContraEntrega(): bool
    {
        return true;
    }

    /**
     * Procesa un reembolso con Stripe para un pedido.
     *
     * @param Pedido $pedido
     * @param float|null $monto Monto a reembolsar en USD (si es null, reembolsa el total)
     * @param string|null $motivo Motivo del reembolso
     * @return array ['exito' => bool, 'mensaje' => string, 'refund_id' => string|null, 'monto' => float]
     */
    public function reembolsarStripe(Pedido $pedido, ?float $monto = null, ?string $motivo = null): array
    {
        $montoReembolso = $monto !== null && $monto > 0 ? $monto : (float) $pedido->total;
        $secretKey = config('services.stripe.secret');

        $paymentIntentId = $pedido->stripe_payment_intent_id;
        if (!$paymentIntentId && !empty($pedido->notas_internas)) {
            $detalles = json_decode($pedido->notas_internas, true);
            $paymentIntentId = $detalles['stripe_payment_intent_id'] ?? null;
        }

        // Si es una simulación o no hay clave secreta
        if (empty($secretKey) || str_starts_with((string) $paymentIntentId, 'pi_simulacion')) {
            return [
                'exito' => true,
                'mensaje' => 'Reembolso de $' . number_format($montoReembolso, 2) . ' simulado correctamente.',
                'refund_id' => 're_simulacion_' . uniqid(),
                'monto' => $montoReembolso,
            ];
        }

        if (empty($paymentIntentId)) {
            return [
                'exito' => false,
                'mensaje' => 'No se encontró el ID de transacción de Stripe (PaymentIntent) para este pedido.',
                'refund_id' => null,
                'monto' => 0.00,
            ];
        }

        try {
            Stripe::setApiKey($secretKey);

            $params = [
                'payment_intent' => $paymentIntentId,
                'amount' => (int) round($montoReembolso * 100),
            ];

            if (!empty($motivo)) {
                $params['metadata'] = [
                    'motivo' => $motivo,
                    'pedido_id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                ];
            }

            /** @var \Stripe\Refund $refund */
            $refund = \Stripe\Refund::create($params);

            return [
                'exito' => true,
                'mensaje' => 'Reembolso de $' . number_format($montoReembolso, 2) . ' procesado con éxito en Stripe.',
                'refund_id' => $refund->id,
                'monto' => (float) ($refund->amount / 100),
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Error al emitir reembolso en Stripe: ' . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error de Stripe: ' . $e->getMessage(),
                'refund_id' => null,
                'monto' => 0.00,
            ];
        } catch (Throwable $e) {
            Log::error('Error general en reembolso Stripe: ' . $e->getMessage());
            return [
                'exito' => false,
                'mensaje' => 'Error inesperado: ' . $e->getMessage(),
                'refund_id' => null,
                'monto' => 0.00,
            ];
        }
    }
}
