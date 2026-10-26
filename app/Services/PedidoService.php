<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\EstadoPedido;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\ZonaEnvio;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NuevoPedidoNotification;
use App\Notifications\StockMinimoNotification;
use App\Mail\PedidoEntregadoMail;
use Illuminate\Support\Facades\Mail;
use Exception;

class PedidoService
{
    protected CuponService $cuponService;

    protected CarritoService $carritoService;

    protected EnvioService $envioService;

    public function __construct(CuponService $cuponService, CarritoService $carritoService, EnvioService $envioService)
    {
        $this->cuponService = $cuponService;
        $this->carritoService = $carritoService;
        $this->envioService = $envioService;
    }

    public function obtenerPedidosPaginadosAdmin(?string $estado = 'todos', ?string $busqueda = null)
    {
        $estado = $estado ?: 'todos';
        $busqueda = trim($busqueda ?? '');

        $query = Pedido::with(['usuario', 'vendedor', 'ultimoEstado']);

        if ($estado !== 'todos') {
            $query->whereHas('estados', function ($q) use ($estado) {
                $q->where('estado', $estado)
                  ->whereIn('id', function ($sub) {
                      $sub->selectRaw('MAX(id)')
                          ->from('estados_pedido')
                          ->groupBy('pedido_id');
                  });
            });
        }

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('unaccent(numero_pedido) ILIKE unaccent(?)', ["%{$busqueda}%"])
                  ->orWhereHas('usuario', function ($uq) use ($busqueda) {
                      $uq->whereRaw('unaccent(nombre) ILIKE unaccent(?)', ["%{$busqueda}%"])
                         ->orWhereRaw('unaccent(apellido) ILIKE unaccent(?)', ["%{$busqueda}%"]);
                  });
            });
        }

        return $query->orderByDesc('creado_en')->paginate(15)->withQueryString();
    }

    /**
     * Calcula los totales del pedido antes de procesarlo.
     */
    public function calcularTotales(Carrito $carrito, ?ZonaEnvio $zonaEnvio, ?Cupon $cupon, ?float $costoEnvioPersonalizado = null): array
    {
        $desglose = $this->carritoService->calcularSubtotalEItbms($carrito);
        $subtotal = $desglose['subtotal'];
        $descuento = 0.00;
        $descuentoEnvio = 0.00;

        if ($cupon) {
            if ($cupon->tipo === 'porcentaje') {
                $descuento = $subtotal * ($cupon->valor / 100);
            } elseif ($cupon->tipo === 'monto_fijo') {
                $descuento = $cupon->valor;
            }
            if ($descuento > $subtotal) {
                $descuento = $subtotal;
            }
        } elseif ($carrito->descuento_aplicado > 0) {
            $descuento = $carrito->descuento_aplicado;
        }

        $requiereEnvioFisico = $this->carritoRequiereEnvioFisico($carrito);

        if ($costoEnvioPersonalizado !== null) {
            $costoEnvio = $costoEnvioPersonalizado;
        } elseif (!$requiereEnvioFisico) {
            $costoEnvio = 0.00;
        } elseif ($zonaEnvio) {
            $costoEnvio = $this->envioService->calcularCostoEnvio($zonaEnvio, $carrito);
        } else {
            $costoEnvio = 0.00;
        }

        if (($zonaEnvio || $costoEnvioPersonalizado !== null) && $this->cuponService->evaluarEnvioGratis($zonaEnvio?->id, $subtotal)) {
            $descuentoEnvio = $costoEnvio;
            $costoEnvio = 0.00;
        }

        $itbmsMonto = $desglose['itbms'];
        $total = max(0.00, ($subtotal - $descuento) + $costoEnvio + $itbmsMonto);

        return [
            'subtotal' => round($subtotal, 2),
            'descuento' => round($descuento, 2),
            'descuento_envio' => round($descuentoEnvio, 2),
            'costo_envio' => round($costoEnvio, 2),
            'itbms_monto' => round($itbmsMonto, 2),
            'total' => round($total, 2),
            'requiere_envio' => $requiereEnvioFisico,
        ];
    }

    /**
     * Determina si el carrito contiene al menos un producto que requiere flete físico.
     */
    public function carritoRequiereEnvioFisico(Carrito $carrito): bool
    {
        if ($carrito->items->isEmpty()) {
            return false;
        }

        return $carrito->items->contains(function ($item) {
            return $item->producto?->requiere_envio ?? true;
        });
    }

    /**
     * Crea un pedido a partir del carrito.
     */
    public function crearDesdeCarrito(
        Carrito $carrito,
        ?int $direccionId,
        string $metodoPago,
        ?string $notasCliente,
        ?ZonaEnvio $zonaEnvio = null,
        ?string $comprobantePagoRuta = null,
        ?string $notasInternas = null,
        ?float $costoEnvioPersonalizado = null
    ): Pedido {
        return DB::transaction(function () use ($carrito, $direccionId, $metodoPago, $notasCliente, $zonaEnvio, $comprobantePagoRuta, $notasInternas, $costoEnvioPersonalizado) {
            // Validar stock disponible
            foreach ($carrito->items as $item) {
                $stockDisponible = $item->variante ? $item->variante->stock : $item->producto->stock;
                if ($item->cantidad > $stockDisponible) {
                    throw new Exception("Stock insuficiente para el producto: " . $item->producto->nombre);
                }
            }

            // Calcular totales
            $cupon = $carrito->cupon;
            $totales = $this->calcularTotales($carrito, $zonaEnvio, $cupon, $costoEnvioPersonalizado);

            // Guardar pedido
            $pedido = Pedido::create([
                'usuario_id' => $carrito->usuario_id,
                'direccion_id' => $direccionId,
                'cupon_id' => $carrito->cupon_id,
                'zona_envio_id' => $zonaEnvio ? $zonaEnvio->id : null,
                'numero_pedido' => $this->generarNumeroPedido(),
                'metodo_pago' => $metodoPago,
                'subtotal' => $totales['subtotal'],
                'descuento' => $totales['descuento'],
                'costo_envio' => $totales['costo_envio'],
                'itbms_monto' => $totales['itbms_monto'],
                'total' => $totales['total'],
                'notas_cliente' => $notasCliente,
                'notas_internas' => $notasInternas,
                'comprobante_pago_ruta' => $comprobantePagoRuta,
            ]);

            // Guardar items, descontar stock y registrar movimiento
            foreach ($carrito->items as $item) {
                ItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item->producto_id,
                    'variante_producto_id' => $item->variante_producto_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                ]);

                if ($item->variante) {
                    $stockAntes = (int) $item->variante->stock;
                    $item->variante->decrement('stock', $item->cantidad);
                    $varianteId = $item->variante->id;
                } else {
                    $stockAntes = (int) $item->producto->stock;
                    $item->producto->decrement('stock', $item->cantidad);
                    $varianteId = null;
                }

                DB::table('movimientos_inventario')->insert([
                    'producto_id' => $item->producto_id,
                    'variante_producto_id' => $varianteId,
                    'usuario_id' => $carrito->usuario_id,
                    'pedido_id' => $pedido->id,
                    'tipo' => 'salida',
                    'cantidad' => $item->cantidad,
                    'stock_antes' => $stockAntes,
                    'stock_despues' => $stockAntes - $item->cantidad,
                    'motivo' => 'Venta - Pedido ' . $pedido->numero_pedido,
                    'notas' => null,
                    'creado_en' => now(),
                ]);

                // Notificar si el stock llega al mínimo
                $stockDespues = $stockAntes - $item->cantidad;
                $stockMinimo = $item->producto->stock_minimo ?? 0;
                if ($stockAntes > $stockMinimo && $stockDespues <= $stockMinimo) {
                    $roles = ['super_admin'];
                    $rolesConfigurados = json_decode(\App\Models\Configuracion::obtener('notificaciones.stock.email.roles', '[]'), true) ?? [];
                    $roles = array_unique(array_merge($roles, $rolesConfigurados));
                    
                    $usuariosNotificar = Usuario::role($roles)->get();
                    Notification::send($usuariosNotificar, new StockMinimoNotification($item->producto, $item->variante));

                    if (\App\Models\Configuracion::obtenerBool('notificaciones.stock.email.activo', false)) {
                        $adicionales = \App\Models\Configuracion::obtener('notificaciones.stock.email.adicionales', '');
                        if (!empty(trim($adicionales))) {
                            $correos = array_map('trim', explode(',', $adicionales));
                            foreach ($correos as $correo) {
                                if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                                    Notification::route('mail', $correo)->notify(new StockMinimoNotification($item->producto, $item->variante));
                                }
                            }
                        }
                    }
                }
            }

            // Estado inicial y vaciado del carrito
            $this->cambiarEstado($pedido, 'pendiente', $carrito->usuario_id, 'Pedido creado.');

            $carrito->items()->delete();
            $carrito->update([
                'cupon_id' => null,
                'descuento_aplicado' => 0.00,
            ]);

            // Notificar a los administradores sin romper la transacción si el servicio de broadcasting/cola está inactivo
            try {
                $admins = Usuario::role('super_admin')->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new NuevoPedidoNotification($pedido));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo enviar la notificación de nuevo pedido: ' . $e->getMessage());
            }

            return $pedido;
        });
    }

    /**
     * Registra un nuevo estado para el pedido.
     */
    public function cambiarEstado(Pedido $pedido, string $nuevoEstado, ?int $usuarioId = null, ?string $comentario = null): EstadoPedido
    {
        // No permitir cambios si el pedido ya fue reembolsado
        if ($pedido->ultimoEstado?->estado === 'reembolsado' && $nuevoEstado !== 'reembolsado') {
            throw new Exception('Este pedido ya se encuentra reembolsado y su ciclo está cerrado. No se permiten más cambios de estado.');
        }

        // Los reembolsos deben procesarse formalmente mediante su botón de reembolso
        if ($nuevoEstado === 'reembolsado' && !str_contains($comentario ?? '', 'Stripe')) {
            throw new Exception('Los reembolsos financieros deben ejecutarse formalmente mediante el botón de Reembolso.');
        }

        if (!$comentario) {
            $empresa = $pedido->envio?->empresa_mensajeria ?? 'nuestra logística';
            $guia = ($pedido->envio && $pedido->envio->numero_guia) ? " (Referencia: {$pedido->envio->numero_guia})" : "";
            
            switch ($nuevoEstado) {
                case 'en_preparacion':
                    $comentario = 'El pedido ha comenzado a prepararse en bodega.';
                    break;
                case 'listo_para_envio':
                    $comentario = 'El pedido está empacado y listo para ser enviado.';
                    break;
                case 'enviado':
                    $comentario = "El pedido ha sido despachado a través de {$empresa}{$guia}.";
                    break;
                case 'en_transito':
                    $comentario = "El pedido se encuentra en ruta hacia su destino mediante {$empresa}.";
                    break;
                case 'entregado':
                    $comentario = "El pedido ha sido entregado exitosamente al destinatario.";
                    break;
                case 'problema_entrega':
                    $comentario = "Se ha reportado un inconveniente durante el proceso de entrega. Estamos revisando el caso.";
                    break;
                default:
                    $comentario = 'Estado actualizado a ' . str_replace('_', ' ', $nuevoEstado);
            }
        }

        $estado = EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $usuarioId,
            'estado' => $nuevoEstado,
            'comentario' => $comentario,
        ]);

        // Generar factura si el pago se confirma o avanza en el ciclo de despacho sin factura previa
        if (in_array($nuevoEstado, ['pago_confirmado', 'en_preparacion', 'listo_para_envio', 'enviado', 'entregado'])) {
            if (!$pedido->relationLoaded('factura')) {
                $pedido->load('factura');
            }
            if (!$pedido->factura) {
                app(FacturaService::class)->generarFactura($pedido);
            }
        }

        if (in_array($nuevoEstado, ['cancelado', 'reembolsado'])) {
            app(FacturaService::class)->anularFactura($pedido);
        }

        if ($nuevoEstado === 'entregado' && $pedido->envio) {
            $pedido->envio->update(['fecha_entrega_real' => now()]);
        }

        if ($nuevoEstado === 'entregado') {
            Mail::to($pedido->usuario->email)->send(new PedidoEntregadoMail($pedido));
        }

        return $estado;
    }

    /**
     * Genera el siguiente número secuencial de pedido (#PM-XXXXXX).
     */
    public function generarNumeroPedido(): string
    {
        DB::table('configuracion')->insertOrIgnore([
            'clave' => 'pedido_correlativo',
            'valor' => (string) (Pedido::max('id') ?? 0),
            'grupo' => 'general',
            'descripcion' => 'Correlativo secuencial para el número de pedido (#PM-XXXXXX).',
            'actualizado_en' => now(),
        ]);

        $fila = DB::table('configuracion')
            ->where('clave', 'pedido_correlativo')
            ->lockForUpdate()
            ->first();

        $correlativo = $fila ? ((int) $fila->valor) + 1 : 1;

        if ($fila) {
            DB::table('configuracion')
                ->where('clave', 'pedido_correlativo')
                ->update(['valor' => (string) $correlativo, 'actualizado_en' => now()]);
        } else {
            DB::table('configuracion')->insert([
                'clave' => 'pedido_correlativo',
                'valor' => (string) $correlativo,
                'grupo' => 'general',
                'descripcion' => 'Correlativo secuencial para el número de pedido (#PM-XXXXXX).',
                'actualizado_en' => now(),
            ]);
        }

        return '#PM-' . (260000 + $correlativo);
    }

    /**
     * Crea un pedido manual desde el panel de administración (Ventas Mostrador / POS / WhatsApp).
     *
     * @param array $datos
     * @param int $vendedorId
     * @return Pedido
     * @throws Exception
     */
    public function crearPedidoManual(array $datos, int $vendedorId): Pedido
    {
        return app(VentaManualService::class)->registrarVentaManual($datos, $vendedorId);
    }
}

