<?php

namespace App\Services\Pagos\DTOs;

class ResultadoPago
{
    /**
     * @param bool $exitoso Indica si la transacción fue completada exitosamente
     * @param string|null $mensaje Mensaje descriptivo del resultado o causa de rechazo
     * @param array<string, mixed> $metadatos Metadatos para almacenar en las notas internas del pedido
     * @param string|null $referenciaTransaccion ID externo de la transacción (ej. PaymentIntent ID)
     */
    public function __construct(
        public readonly bool $exitoso,
        public readonly ?string $mensaje = null,
        public readonly array $metadatos = [],
        public readonly ?string $referenciaTransaccion = null
    ) {}

    public static function exito(array $metadatos = [], ?string $referenciaTransaccion = null): self
    {
        return new self(
            exitoso: true,
            mensaje: null,
            metadatos: $metadatos,
            referenciaTransaccion: $referenciaTransaccion
        );
    }

    public static function fallo(string $mensaje, array $metadatos = []): self
    {
        return new self(
            exitoso: false,
            mensaje: $mensaje,
            metadatos: $metadatos,
            referenciaTransaccion: null
        );
    }
}
