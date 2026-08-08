<?php

namespace App\Services;

use App\Models\ZonaEnvio;

class EnvioService
{
    /**
     * Obtiene la zona de envío correspondiente al nombre de provincia o zona.
     *
     * @param string|null $provincia
     * @return ZonaEnvio|null
     */
    public function obtenerZonaPorProvincia(?string $provincia): ?ZonaEnvio
    {
        if (empty($provincia)) {
            return null;
        }

        $provinciaNormalizada = trim(mb_strtolower($provincia));

        // 1. Coincidencia exacta
        $exacta = ZonaEnvio::all()->first(function ($zona) use ($provinciaNormalizada) {
            return mb_strtolower(trim($zona->nombre)) === $provinciaNormalizada;
        });

        if ($exacta) {
            return $exacta;
        }

        // 2. Coincidencia parcial (e.g. si busca "Chiriquí" y la zona es "Chiriquí (David / Boquete)")
        return ZonaEnvio::all()->first(function ($zona) use ($provinciaNormalizada) {
            $nombreZona = mb_strtolower(trim($zona->nombre));
            return str_contains($nombreZona, $provinciaNormalizada) || str_contains($provinciaNormalizada, $nombreZona);
        });
    }

    /**
     * Determina si la zona correspondiente a una provincia está configurada y activa.
     *
     * @param string|null $provincia
     * @return bool
     */
    public function esZonaActiva(?string $provincia): bool
    {
        $zona = $this->obtenerZonaPorProvincia($provincia);

        return $zona !== null && $zona->activo;
    }

    /**
     * Obtiene el costo de envío para la provincia especificada.
     * Si la zona no existe o está inactiva, se maneja correctamente retornando 0.00.
     *
     * @param string|null $provincia
     * @return float
     */
    public function obtenerCostoEnvio(?string $provincia): float
    {
        $zona = $this->obtenerZonaPorProvincia($provincia);

        if ($zona && $zona->activo) {
            return (float) $zona->costo;
        }

        return 0.00;
    }
}
