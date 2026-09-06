<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller
{
    protected PedidoService $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    /**
     * Procesa los eventos enviados por Stripe (webhooks).
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        $event = null;

        if (!empty($endpointSecret) && !empty($sigHeader)) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe Webhook: Firma inválida - ' . $e->getMessage());
                return response()->json(['error' => 'Firma inválida'], 400);
            } catch (Throwable $e) {
                Log::error('Stripe Webhook: Error al parsear evento - ' . $e->getMessage());
                return response()->json(['error' => 'Payload inválido'], 400);
            }
        } else {
            // En desarrollo o cuando aún no se ha configurado el secret de webhook
            $datos = json_decode($payload, true);
            if (!is_array($datos) || !isset($datos['type'])) {
                return response()->json(['error' => 'Payload inválido'], 400);
            }
            $event = (object) [
                'type' => $datos['type'],
                'data' => (object) [
                    'object' => json_decode(json_encode($datos['data']['object'] ?? [])),
                ],
            ];
        }

        $eventType = is_object($event) ? ($event->type ?? null) : ($event['type'] ?? null);

        Log::info("Stripe Webhook recibido: {$eventType}");

        switch ($eventType) {
            case 'charge.refunded':
            case 'charge.refund.updated':
                $this->handleChargeRefunded($event);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event);
                break;

            default:
                Log::info("Stripe Webhook: Evento ignorado ({$eventType})");
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Procesa un reembolso emitido en Stripe (charge.refunded).
     */
    protected function handleChargeRefunded(object|array $event): void
    {
        $dataObject = is_object($event) ? $event->data->object : (object) $event['data']['object'];
        
        $paymentIntentId = $dataObject->payment_intent ?? null;
        $chargeId = $dataObject->id ?? null;
        $amountRefunded = isset($dataObject->amount_refunded) ? ($dataObject->amount_refunded / 100) : null;
        $metadata = (array) ($dataObject->metadata ?? []);

        Log::info("Procesando reembolso Stripe para PI: {$paymentIntentId}, Charge: {$chargeId}, Monto: {$amountRefunded}");

        // 1. Buscar pedido por PaymentIntent
        $pedido = null;
        if (!empty($paymentIntentId)) {
            $pedido = Pedido::where('stripe_payment_intent_id', $paymentIntentId)->first();
            
            if (!$pedido) {
                $pedido = Pedido::where('notas_internas', 'like', '%' . $paymentIntentId . '%')->first();
            }
        }

        // 2. Si no se encuentra, buscar por ID o número de pedido en los metadatos de Stripe
        if (!$pedido && !empty($metadata['pedido_id'])) {
            $pedido = Pedido::find($metadata['pedido_id']);
        }
        if (!$pedido && !empty($metadata['numero_pedido'])) {
            $pedido = Pedido::where('numero_pedido', $metadata['numero_pedido'])->first();
        }

        if (!$pedido) {
            Log::warning("Stripe Webhook: No se encontró pedido para el reembolso (PI: {$paymentIntentId}, Charge: {$chargeId})");
            return;
        }

        $montoFinal = $amountRefunded ?: (float) $pedido->total;

        // Actualizar monto reembolsado en el pedido
        $pedido->update([
            'monto_reembolsado' => $montoFinal,
            'stripe_payment_intent_id' => $paymentIntentId ?: $pedido->stripe_payment_intent_id,
        ]);

        // Evitar duplicar el estado si ya estaba como reembolsado
        if ($pedido->ultimoEstado?->estado !== 'reembolsado') {
            $comentario = 'Reembolso procesado en Stripe por $' . number_format($montoFinal, 2) . ' USD.';
            $this->pedidoService->cambiarEstado($pedido, 'reembolsado', null, $comentario);
            Log::info("Pedido #{$pedido->id} marcado como REEMBOLSADO vía Stripe Webhook.");
        }
    }

    /**
     * Confirma el pago si se recibe payment_intent.succeeded
     */
    protected function handlePaymentIntentSucceeded(object|array $event): void
    {
        $dataObject = is_object($event) ? $event->data->object : (object) $event['data']['object'];
        $paymentIntentId = $dataObject->id ?? null;
        $metadata = (array) ($dataObject->metadata ?? []);

        if (empty($paymentIntentId)) {
            return;
        }

        $pedido = Pedido::where('stripe_payment_intent_id', $paymentIntentId)->first();
        if (!$pedido && !empty($metadata['pedido_id'])) {
            $pedido = Pedido::find($metadata['pedido_id']);
        }

        if ($pedido && $pedido->ultimoEstado?->estado === 'pendiente') {
            $this->pedidoService->cambiarEstado($pedido, 'pago_confirmado', null, 'Pago confirmado vía Stripe Webhook.');
        }
    }
}
