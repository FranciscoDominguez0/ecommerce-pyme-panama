<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Métodos de Pago Registrados (Open/Closed Principle)
    |--------------------------------------------------------------------------
    |
    | Para agregar una nueva pasarela de pago en el futuro (ej. PayPal,
    | PagueloFacil, Cuanto), solo debes crear la clase correspondiente
    | implementando MetodoPagoInterface y registrarla en este arreglo.
    |
    */
    'metodos' => [
        'stripe' => \App\Services\Pagos\Metodos\StripeMetodoPago::class,
        'yappy' => \App\Services\Pagos\Metodos\YappyMetodoPago::class,
        'transferencia' => \App\Services\Pagos\Metodos\TransferenciaMetodoPago::class,
        'contra_entrega' => \App\Services\Pagos\Metodos\ContraEntregaMetodoPago::class,
    ],
];
