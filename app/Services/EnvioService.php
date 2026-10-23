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
    /**
     * Calcula el peso total del carrito en libras.
     */
    public function calcularPesoTotalLibras(\App\Models\Carrito $carrito): int
    {
        $pesoTotalKg = 0;
        foreach ($carrito->items as $item) {
            $peso = $item->producto?->peso ?? 0;
            $pesoTotalKg += ($peso * $item->cantidad);
        }

        return (int) ceil($pesoTotalKg * 2.20462);
    }

    /**
     * Calcula el costo final de envío dependiendo de la zona y el peso del carrito.
     */
    public function calcularCostoEnvio(?ZonaEnvio $zona, \App\Models\Carrito $carrito): float
    {
        if (!$zona || !$zona->activo) {
            return 0.00;
        }

        $baseCosto = (float) $zona->costo;
        $pesoTotalLbs = $this->calcularPesoTotalLibras($carrito);

        // Aplicar tarifa base hasta 10 libras, luego cobrar recargo por libra extra.
        // Se aplicará a todas las zonas de envío para que el cálculo sea global.
        if ($pesoTotalLbs > 10) {
            $librasExtras = $pesoTotalLbs - 10;
            // Recargo de $0.50 por cada libra adicional
            $recargo = $librasExtras * 0.50;
            return $baseCosto + $recargo;
        }

        // Si pesa menos de 10 libras, se cobra solo la tarifa base.
        return $baseCosto;
    }

    // Calcula la tarifa de sucursal de courier considerando peso y recargo por libra adicional
    public function calcularCostoCourier(?\App\Models\CourierSucursal $sucursal, \App\Models\Carrito $carrito): float
    {
        if (!$sucursal) {
            return 0.00;
        }

        $baseCosto = (float) $sucursal->tarifa_uno_hasta_7lb;
        $pesoTotalLbs = $this->calcularPesoTotalLibras($carrito);

        if ($pesoTotalLbs > 7) {
            $librasExtras = $pesoTotalLbs - 7;
            $recargo = $librasExtras * 0.50;
            return $baseCosto + $recargo;
        }

        return $baseCosto;
    }

    // Resuelve el costo especial de envío según el método de entrega (retiro local, courier, etc.)
    public function calcularCostoEspecial(string $metodoEntrega, ?int $courierSucursalId, \App\Models\Carrito $carrito): ?float
    {
        if ($metodoEntrega === 'retiro_local') {
            return 0.00;
        }

        if ($metodoEntrega === 'retiro_courier' && $courierSucursalId) {
            $sucursal = \App\Models\CourierSucursal::find($courierSucursalId);
            return $this->calcularCostoCourier($sucursal, $carrito);
        }

        return null;
    }
}
