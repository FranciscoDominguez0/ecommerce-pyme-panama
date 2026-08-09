<?php

namespace App\Services;

class PagoService
{
    /**
     * Procesa el pago con Stripe.
     * En un entorno real, esto interactuaría con el SDK de Stripe.
     */
    public function procesarStripe(array $datosTarjeta, float $monto): bool
    {
        // Simulación: asume que el pago siempre es exitoso si hay datos
        if (empty($datosTarjeta)) {
            return false;
        }

        // Lógica real de Stripe (creación de Intent, confirmación, etc.) iría aquí.
        // if ($stripeResponse->status === 'succeeded') return true;

        return true; 
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
}
