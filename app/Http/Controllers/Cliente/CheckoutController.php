<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Direccion;
use App\Models\ZonaEnvio;
use App\Services\CarritoService;
use App\Services\PagoService;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class CheckoutController extends Controller
{
    protected CarritoService $carritoService;
    protected PedidoService $pedidoService;
    protected PagoService $pagoService;

    public function __construct(CarritoService $carritoService, PedidoService $pedidoService, PagoService $pagoService)
    {
        $this->carritoService = $carritoService;
        $this->pedidoService = $pedidoService;
        $this->pagoService = $pagoService;
    }

    /**
     * Paso 1: Dirección de Envío
     * La selección/creación de la dirección y la continuación del flujo
     * las maneja el componente reutilizable App\Livewire\GestionDirecciones.
     */
    public function direccion(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login')->with('warning', 'Debes iniciar sesión para finalizar tu compra.');
        }

        $carrito = $this->carritoService->obtenerOCrearCarrito($usuario->id);
        if ($carrito->items->isEmpty()) {
            return redirect()->route('cliente.carrito')->with('warning', 'Tu carrito está vacío.');
        }

        $zonasEnvio = ZonaEnvio::activo()->get();

        return view('cliente.checkout.direccion', compact('zonasEnvio'));
    }

    /**
     * Paso 2: Método de Pago
     */
    public function pago(Request $request)
    {
        if (!session()->has('checkout_direccion_id')) {
            return redirect()->route('cliente.checkout.direccion')->with('warning', 'Selecciona una dirección de envío primero.');
        }

        return view('cliente.checkout.pago');
    }

    public function guardarPago(Request $request)
    {
        $request->validate([
            'metodo_pago' => 'required|in:stripe,yappy,transferencia,contra_entrega',
            'comprobante_pago' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $comprobanteRuta = null;
        if ($request->metodo_pago === 'transferencia' && $request->hasFile('comprobante_pago')) {
            $comprobanteRuta = $request->file('comprobante_pago')->store('comprobantes', 'public');
        }

        session([
            'checkout_metodo_pago' => $request->metodo_pago,
            'checkout_comprobante_ruta' => $comprobanteRuta,
        ]);

        return redirect()->route('cliente.checkout.confirmacion');
    }

    /**
     * Paso 3: Resumen y Confirmación
     */
    public function confirmacion(Request $request)
    {
        if (!session()->has('checkout_metodo_pago')) {
            return redirect()->route('cliente.checkout.pago')->with('warning', 'Selecciona un método de pago primero.');
        }

        $usuario = Auth::user();
        $carrito = $this->carritoService->obtenerOCrearCarrito($usuario->id);
        $direccion = Direccion::find(session('checkout_direccion_id'));
        $zonaEnvio = ZonaEnvio::find(session('checkout_zona_envio_id'));
        $metodoPago = session('checkout_metodo_pago');
        
        $totales = $this->pedidoService->calcularTotales($carrito, $zonaEnvio, $carrito->cupon);

        return view('cliente.checkout.confirmacion', compact('carrito', 'direccion', 'zonaEnvio', 'metodoPago', 'totales'));
    }

    /**
     * Procesar Orden
     */
    public function procesar(Request $request)
    {
        $request->validate([
            'notas_cliente' => 'nullable|string|max:500',
        ]);

        $usuario = Auth::user();
        $carrito = $this->carritoService->obtenerOCrearCarrito($usuario->id);

        if ($carrito->items->isEmpty()) {
            return redirect()->route('cliente.carrito')->with('warning', 'Tu pedido ya fue procesado o tu carrito está vacío.');
        }

        $direccionId = session('checkout_direccion_id');
        $zonaEnvio = ZonaEnvio::find(session('checkout_zona_envio_id'));
        $metodoPago = session('checkout_metodo_pago');
        $comprobanteRuta = session('checkout_comprobante_ruta');
        
        if (!$direccionId || !$metodoPago) {
            return redirect()->route('cliente.checkout.direccion')->with('error', 'Faltan datos para procesar tu pedido.');
        }

        $totales = $this->pedidoService->calcularTotales($carrito, $zonaEnvio, $carrito->cupon);
        $pagoExitoso = false;

        // Intentar procesar el pago antes de crear el pedido
        if ($metodoPago === 'stripe') {
            $pagoExitoso = $this->pagoService->procesarStripe(['simulacion' => true], $totales['total']);
        } elseif ($metodoPago === 'yappy') {
            $pagoExitoso = $this->pagoService->procesarYappy('6000-0000', $totales['total']);
        } elseif ($metodoPago === 'transferencia') {
            $pagoExitoso = $this->pagoService->procesarTransferencia($comprobanteRuta);
        } elseif ($metodoPago === 'contra_entrega') {
            $pagoExitoso = $this->pagoService->procesarContraEntrega();
        }

        if (!$pagoExitoso) {
            return redirect()->route('cliente.checkout.pago')->with('error', 'No se pudo procesar el pago. Por favor intenta con otro método.');
        }

        try {
            $pedido = $this->pedidoService->crearDesdeCarrito(
                $carrito, 
                $direccionId, 
                $metodoPago, 
                $request->notas_cliente,
                $zonaEnvio,
                $comprobanteRuta
            );

            // Limpiar sesión
            session()->forget(['checkout_direccion_id', 'checkout_zona_envio_id', 'checkout_metodo_pago', 'checkout_comprobante_ruta']);

            return redirect()->route('cliente.perfil.pedidos.detalle', $pedido->id)->with('toast_success', '¡Pedido procesado con éxito!');
        } catch (Exception $e) {
            return redirect()->route('cliente.checkout.confirmacion')->with('error', 'Error al procesar el pedido: ' . $e->getMessage());
        }
    }
}
