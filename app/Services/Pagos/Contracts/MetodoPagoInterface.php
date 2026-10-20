<?php

namespace App\Services\Pagos\Contracts;

use App\Models\Pedido;
use App\Services\Pagos\DTOs\ResultadoPago;
use Illuminate\Http\Request;

interface MetodoPagoInterface
{
    /**
     * Identificador único del método de pago (ej: 'stripe', 'yappy', 'transferencia', 'contra_entrega').
     */
    public function id(): string;

    /**
     * Nombre público del método de pago mostrado al cliente y en administración.
     */
    public function nombre(): string;

    /**
     * Descripción breve mostrada debajo del nombre en el checkout.
     */
    public function descripcion(): string;

    /**
     * Nombre del icono de Material Symbols o clave de asset.
     */
    public function icono(): string;

    /**
     * Determina si el método de pago está habilitado en la configuración del comercio.
     */
    public function estaActivo(): bool;

    /**
     * Reglas de validación específicas para este método cuando se envía el formulario de pago.
     *
     * @return array<string, mixed>
     */
    public function reglasValidacion(Request $request): array;

    /**
     * Extrae los datos relevantes del request y los devuelve en un arreglo para almacenar en sesión.
     *
     * @return array<string, mixed>
     */
    public function extraerDatosSesion(Request $request): array;

    /**
     * Procesa la transacción de cobro con los datos del contexto del checkout.
     *
     * @param array<string, mixed> $contexto
     */
    public function procesar(array $contexto): ResultadoPago;

    /**
     * Indica si la pasarela permite reembolsos automáticos a través de API.
     */
    public function soportaReembolso(): bool;

    /**
     * Ejecuta el reembolso total o parcial para un pedido.
     *
     * @return array{exito: bool, mensaje: string, refund_id: ?string, monto: float}
     */
    public function reembolsar(Pedido $pedido, ?float $monto = null, ?string $motivo = null): array;

    /**
     * Nombre de la vista Blade que contiene el subformulario (ej. Stripe Elements o carga de comprobante), o null.
     */
    public function vistaSubformulario(): ?string;
}
