<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Direccion;
use App\Models\Pedido;
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
     * Calcula el peso total en libras a partir de un Carrito o una lista de items/productos.
     *
     * @param mixed $origen \App\Models\Carrito|iterable|null
     * @return int
     */
    public function calcularPesoTotalLibras(mixed $origen): int
    {
        if (!$origen) {
            return 0;
        }

        if ($origen instanceof \App\Models\Carrito) {
            $items = $origen->items;
        } elseif (is_iterable($origen)) {
            $items = $origen;
        } else {
            return 0;
        }

        $pesoTotalKg = 0;
        foreach ($items as $item) {
            $cantidad = is_array($item) ? ($item['cantidad'] ?? 1) : ($item->cantidad ?? 1);
            $peso = 0;

            if (is_array($item)) {
                $peso = (float) ($item['peso'] ?? 0);
                if ($peso <= 0 && !empty($item['producto_id'])) {
                    $prod = \App\Models\Producto::find($item['producto_id']);
                    $peso = (float) ($prod?->peso ?? 0);
                }
            } elseif (is_object($item)) {
                $peso = (float) ($item->producto?->peso ?? $item->peso ?? 0);
            }

            $pesoTotalKg += ($peso * $cantidad);
        }

        return (int) ceil($pesoTotalKg * 2.20462);
    }

    /**
     * Calcula el costo final de envío delivery dependiendo de la zona y el peso del carrito o items.
     *
     * @param ZonaEnvio|null $zona
     * @param mixed $origen Carrito, iterable de items o peso numérico en libras
     * @return float
     */
    public function calcularCostoEnvio(?ZonaEnvio $zona, mixed $origen = null): float
    {
        if (!$zona || !$zona->activo) {
            return 0.00;
        }

        $baseCosto = (float) $zona->costo;
        $pesoTotalLbs = is_numeric($origen) ? (int) ceil($origen) : $this->calcularPesoTotalLibras($origen);

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

    /**
     * Calcula la tarifa de sucursal de courier considerando peso y recargo por libra adicional.
     *
     * @param \App\Models\CourierSucursal|null $sucursal
     * @param mixed $origen Carrito, iterable de items o peso numérico en libras
     * @return float
     */
    public function calcularCostoCourier(?\App\Models\CourierSucursal $sucursal, mixed $origen = null): float
    {
        if (!$sucursal) {
            return 0.00;
        }

        $baseCosto = (float) $sucursal->tarifa_uno_hasta_7lb;
        $pesoTotalLbs = is_numeric($origen) ? (int) ceil($origen) : $this->calcularPesoTotalLibras($origen);

        if ($pesoTotalLbs > 7) {
            $librasExtras = $pesoTotalLbs - 7;
            $recargo = $librasExtras * 0.50;
            return $baseCosto + $recargo;
        }

        return $baseCosto;
    }

    /**
     * Resuelve el costo especial de envío según el método de entrega (retiro local, courier, etc.)
     */
    public function calcularCostoEspecial(string $metodoEntrega, ?int $courierSucursalId, mixed $origen = null): ?float
    {
        if ($metodoEntrega === 'retiro_local' || $metodoEntrega === 'tienda_fisica') {
            return 0.00;
        }

        if ($metodoEntrega === 'retiro_courier' && $courierSucursalId) {
            $sucursal = \App\Models\CourierSucursal::find($courierSucursalId);
            return $this->calcularCostoCourier($sucursal, $origen);
        }

        return null;
    }

    /**
     * Obtiene los datos reales de la sucursal física configurada de la empresa para retiro en tienda.
     */
    public function obtenerDatosRetiroLocal(): array
    {
        $nombre = Configuracion::obtener('empresa.nombre', 'PyME Panamá');
        $sucursalNombre = Configuracion::obtener('empresa.sucursal_nombre', 'Sucursal ' . $nombre . ' - San Francisco');
        $direccion = Configuracion::obtener('empresa.direccion', 'Local Pyme Panamá, Planta Baja, Calle 67 Este, San Francisco, Ciudad de Panamá');
        $horario = Configuracion::obtener('empresa.horario', 'Lunes a Sábado, 9:00 AM - 6:00 PM');
        $telefono = Configuracion::obtener('empresa.telefono', '');

        return [
            'nombre' => $nombre,
            'sucursal' => $sucursalNombre,
            'direccion' => $direccion,
            'horario' => $horario,
            'telefono' => $telefono,
        ];
    }

    /**
     * Obtiene las zonas de envío autorizadas para Delivery urbano/metropolitano (las mismas de los usuarios: Panamá y Panamá Oeste).
     */
    public function obtenerZonasDelivery(): \Illuminate\Support\Collection
    {
        $zonas = ZonaEnvio::where('activo', true)
            ->where(function ($q) {
                $q->whereIn('nombre', ['Panamá', 'Panamá Oeste', 'Ciudad de Panamá'])
                  ->orWhere('nombre', 'ilike', '%Panamá%');
            })
            ->orderBy('id', 'asc')
            ->get();

        if ($zonas->isEmpty()) {
            return ZonaEnvio::where('activo', true)->orderBy('id', 'asc')->get();
        }

        return $zonas;
    }

    /**
     * Obtiene el listado de zonas disponibles para Courier.
     */
    public function obtenerZonasCourier(): \Illuminate\Support\Collection
    {
        return \App\Models\CourierSucursal::where('activo', true)
            ->select('zona')
            ->distinct()
            ->orderBy('zona')
            ->pluck('zona');
    }

    /**
     * Obtiene los couriers disponibles en una zona específica.
     */
    public function obtenerCouriersPorZona(string $zona): \Illuminate\Support\Collection
    {
        return \App\Models\CourierSucursal::where('zona', $zona)
            ->where('activo', true)
            ->select('courier')
            ->distinct()
            ->orderBy('courier')
            ->pluck('courier');
    }

    /**
     * Obtiene las sucursales disponibles de un courier en una zona.
     */
    public function obtenerSucursales(string $zona, string $courier): \Illuminate\Support\Collection
    {
        return \App\Models\CourierSucursal::where('zona', $zona)
            ->where('courier', $courier)
            ->where('activo', true)
            ->orderBy('sucursal')
            ->get();
    }

    /**
     * Resuelve la dirección inicial para el checkout del usuario.
     * Prioridad:
     * 1. Lo último que eligió en un pedido previo.
     * 2. Dirección predeterminada o última dirección guardada en DB.
     */
    public function resolverDireccionInicialCheckout(int $usuarioId): ?Direccion
    {
        // 1. Lo último que eligió en un pedido previo
        $ultimoPedidoConDireccion = Pedido::where('usuario_id', $usuarioId)
            ->whereNotNull('direccion_id')
            ->latest('creado_en')
            ->first();

        if ($ultimoPedidoConDireccion && $ultimoPedidoConDireccion->direccion_id) {
            $dirPedido = Direccion::with('zonaEnvio')
                ->where('id', $ultimoPedidoConDireccion->direccion_id)
                ->sinEliminar()
                ->first();

            if ($dirPedido) {
                return $dirPedido;
            }
        }

        // 2. Dirección predeterminada o la última dirección guardada
        return Direccion::with('zonaEnvio')
            ->where('usuario_id', $usuarioId)
            ->sinEliminar()
            ->orderByDesc('es_predeterminada')
            ->latest('creado_en')
            ->first();
    }

    /**
     * Sincroniza la dirección de checkout en la sesión si está vacía o inválida.
     */
    public function sincronizarDireccionCheckoutSiVacio(int $usuarioId): ?Direccion
    {
        $direccionActualId = session('checkout_direccion_id');

        if ($direccionActualId) {
            $direccionValida = Direccion::with('zonaEnvio')
                ->where('id', $direccionActualId)
                ->where('usuario_id', $usuarioId)
                ->sinEliminar()
                ->first();

            if ($direccionValida) {
                return $direccionValida;
            }

            session()->forget(['checkout_direccion_id', 'checkout_zona_envio_id']);
        }

        $direccion = $this->resolverDireccionInicialCheckout($usuarioId);

        if ($direccion) {
            $zona = $direccion->zonaEnvioCalculada;

            // Si la dirección no tenía la zona asociada permanentemente, la actualizamos
            if ($zona && !$direccion->zona_envio_id) {
                $direccion->update(['zona_envio_id' => $zona->id]);
            }

            session([
                'checkout_direccion_id' => (int) $direccion->id,
                'checkout_zona_envio_id' => $zona ? (int) $zona->id : null,
                'checkout_metodo_entrega' => session('checkout_metodo_entrega', 'delivery'),
            ]);
        }

        return $direccion;
    }
}
