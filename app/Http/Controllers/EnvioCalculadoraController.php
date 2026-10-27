<?php

namespace App\Http\Controllers;

use App\Models\CourierSucursal;
use App\Models\ZonaEnvio;
use App\Services\EnvioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador reutilizable para cálculo de costos de envío y consultas logísticas
 * compartido entre usuarios (portal cliente) y agentes (administración / POS).
 */
class EnvioCalculadoraController extends Controller
{
    protected EnvioService $envioService;

    public function __construct(EnvioService $envioService)
    {
        $this->envioService = $envioService;
    }

    /**
     * Calcula la cotización dinámica de envío (delivery o courier) según el peso de los productos.
     */
    public function calcular(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metodo_entrega' => 'required|string|in:retiro_local,tienda_fisica,delivery,envio_domicilio,retiro_courier',
            'zona_envio_id' => 'nullable|exists:zonas_envio,id',
            'courier_sucursal_id' => 'nullable|exists:courier_sucursales,id',
            'items' => 'nullable|array',
            'items.*.producto_id' => 'nullable|integer',
            'items.*.peso' => 'nullable|numeric',
            'items.*.cantidad' => 'nullable|integer|min:1',
        ]);

        $metodo = $validated['metodo_entrega'];
        $items = $validated['items'] ?? [];
        $pesoLbs = $this->envioService->calcularPesoTotalLibras($items);

        $costo = 0.00;
        $detalles = [];

        if ($metodo === 'retiro_local' || $metodo === 'tienda_fisica') {
            $costo = 0.00;
            $retiroLocal = $this->envioService->obtenerDatosRetiroLocal();
            $detalles = [
                'tipo' => 'retiro_local',
                'sucursal' => $retiroLocal['sucursal'],
                'direccion' => $retiroLocal['direccion'],
                'horario' => $retiroLocal['horario'],
                'gratis' => true,
            ];
        } elseif ($metodo === 'retiro_courier') {
            $sucursal = null;
            if (!empty($validated['courier_sucursal_id'])) {
                $sucursal = CourierSucursal::find($validated['courier_sucursal_id']);
            }

            if ($sucursal) {
                $costo = $this->envioService->calcularCostoCourier($sucursal, $pesoLbs);
                $detalles = [
                    'tipo' => 'retiro_courier',
                    'zona' => $sucursal->zona,
                    'courier' => $sucursal->courier,
                    'sucursal' => $sucursal->sucursal,
                    'direccion' => $sucursal->direccion,
                    'tarifa_base' => (float) $sucursal->tarifa_uno_hasta_7lb,
                ];
            }
        } else {
            // delivery / envio_domicilio
            $zona = null;
            if (!empty($validated['zona_envio_id'])) {
                $zona = ZonaEnvio::find($validated['zona_envio_id']);
            }

            if ($zona) {
                $costo = $this->envioService->calcularCostoEnvio($zona, $pesoLbs);
                $detalles = [
                    'tipo' => 'delivery',
                    'zona_nombre' => $zona->nombre,
                    'tarifa_base' => (float) $zona->costo,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'metodo_entrega' => $metodo,
            'peso_total_lbs' => $pesoLbs,
            'costo_envio' => round($costo, 2),
            'detalles' => $detalles,
        ]);
    }

    /**
     * Devuelve las zonas de courier activas.
     */
    public function zonasCourier(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'zonas' => $this->envioService->obtenerZonasCourier(),
        ]);
    }

    /**
     * Devuelve los couriers disponibles en una zona.
     */
    public function couriersPorZona(Request $request): JsonResponse
    {
        $zona = (string) $request->query('zona', '');
        return response()->json([
            'success' => true,
            'couriers' => $this->envioService->obtenerCouriersPorZona($zona),
        ]);
    }

    /**
     * Devuelve las sucursales de un courier en una zona.
     */
    public function sucursales(Request $request): JsonResponse
    {
        $zona = (string) $request->query('zona', '');
        $courier = (string) $request->query('courier', '');

        return response()->json([
            'success' => true,
            'sucursales' => $this->envioService->obtenerSucursales($zona, $courier),
        ]);
    }
}
