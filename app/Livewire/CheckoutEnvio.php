<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\CourierSucursal;
use App\Models\Direccion;
use App\Services\CarritoService;
use App\Services\PedidoService;
use Illuminate\Support\Facades\Auth;

class CheckoutEnvio extends Component
{
    // Datos de Contacto
    public $contacto_nombre = '';
    public $contacto_apellido = '';
    public $contacto_email = '';
    public $contacto_telefono1 = '';
    public $contacto_telefono2 = '';

    public $metodo_entrega = 'delivery'; // 'delivery', 'retiro_local', 'retiro_courier'
    
    // Zonas de Envio para Delivery
    public $zonasEnvio = [];
    public $requiereEnvio = true;

    public function mount($zonasEnvio = [], $requiereEnvio = true)
    {
        $this->zonasEnvio = $zonasEnvio;
        $this->requiereEnvio = $requiereEnvio;
        
        $usuario = Auth::user();
        if ($usuario) {
            $this->contacto_nombre = session('checkout_contacto_nombre', $usuario->nombre);
            $this->contacto_apellido = session('checkout_contacto_apellido', $usuario->apellido);
            $this->contacto_email = session('checkout_contacto_email', $usuario->email);
            $this->contacto_telefono1 = session('checkout_contacto_telefono1', $usuario->telefono);
            $this->contacto_telefono2 = session('checkout_contacto_telefono2', $usuario->telefono2);
        } else {
            $this->contacto_nombre = session('checkout_contacto_nombre', '');
            $this->contacto_apellido = session('checkout_contacto_apellido', '');
            $this->contacto_email = session('checkout_contacto_email', '');
            $this->contacto_telefono1 = session('checkout_contacto_telefono1', '');
            $this->contacto_telefono2 = session('checkout_contacto_telefono2', '');
        }

        $this->metodo_entrega = session('checkout_metodo_entrega', 'delivery');
    }

    #[On('envioActualizado')]
    public function actualizarTotalesDesdeCalculadora()
    {
        $this->metodo_entrega = session('checkout_metodo_entrega', 'delivery');
    }

    public function continuarCheckout()
    {
        $this->validate([
            'contacto_nombre' => 'required|string|max:100',
            'contacto_apellido' => 'required|string|max:100',
            'contacto_email' => 'required|email|max:255',
            'contacto_telefono1' => 'required|string|max:30',
            'contacto_telefono2' => 'nullable|string|max:30',
        ]);

        session([
            'checkout_contacto_nombre' => $this->contacto_nombre,
            'checkout_contacto_apellido' => $this->contacto_apellido,
            'checkout_contacto_email' => $this->contacto_email,
            'checkout_contacto_telefono1' => $this->contacto_telefono1,
            'checkout_contacto_telefono2' => $this->contacto_telefono2,
            'checkout_metodo_entrega' => $this->metodo_entrega,
        ]);

        // Asegurar que tengan un método de entrega válido
        if ($this->metodo_entrega === 'delivery' && !session('checkout_direccion_id')) {
            $this->addError('metodo_entrega', 'Debes completar tu dirección de envío en el modal.');
            return;
        }
        if ($this->metodo_entrega === 'retiro_courier' && !session('checkout_courier_sucursal')) {
            $this->addError('metodo_entrega', 'Debes completar tu sucursal de courier en el modal.');
            return;
        }

        return redirect()->route('cliente.checkout.pago');
    }

    public function resolverUbicacionEnvio(): array
    {
        $metodo = session('checkout_metodo_entrega', 'delivery');
        
        if ($metodo === 'retiro_local') {
            return [
                'costo' => 0.00,
                'ubicacion' => 'Retiro en Sucursal',
                'zona_id' => null,
            ];
        } elseif ($metodo === 'retiro_courier') {
            if (session('checkout_courier_sucursal')) {
                $suc = CourierSucursal::find(session('checkout_courier_sucursal'));
                if ($suc) {
                    return [
                        'costo' => (float)$suc->tarifa_uno_hasta_7lb,
                        'ubicacion' => 'Courier: ' . $suc->sucursal,
                        'zona_id' => null,
                    ];
                }
            }
            return [
                'costo' => 0.00,
                'ubicacion' => 'Por calcular (Courier)',
                'zona_id' => null,
            ];
        } else {
            // delivery
            if (session()->has('checkout_direccion_id')) {
                $direccion = Direccion::with('zonaEnvio')->find(session('checkout_direccion_id'));
                if ($direccion && $direccion->zonaEnvio) {
                    $zona = $direccion->zonaEnvio;
                    $nombreUbicacion = $direccion->provincia;
                    if (!empty($direccion->distrito)) {
                        $nombreUbicacion .= " ({$direccion->distrito})";
                    }
                    return [
                        'costo' => (float) $zona->costo,
                        'zona_id' => (int) $zona->id,
                        'ubicacion' => 'Delivery: ' . $nombreUbicacion,
                        'direccion_id' => (int) $direccion->id,
                    ];
                }
            }
            return [
                'costo' => 0.00,
                'ubicacion' => 'No seleccionado',
                'zona_id' => null,
                'direccion_id' => null,
            ];
        }
    }

    public function render(CarritoService $carritoService)
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();
        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);

        $ubicacion = $this->resolverUbicacionEnvio();
        $resumen = $carritoService->calcularTotal($carrito, $ubicacion['costo'], $ubicacion['zona_id']);

        return view('livewire.checkout-envio', compact('carrito', 'resumen', 'ubicacion'));
    }
}
