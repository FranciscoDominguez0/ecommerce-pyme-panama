<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Livewire Release Token
    |--------------------------------------------------------------------------
    |
    | Este token se incluye en las instantáneas del cliente (wire:snapshot)
    | para invalidar estados cacheados cuando se despliega una nueva versión.
    |
    */
    'release_token' => env('APP_VERSION', '1.0.0'),
];
