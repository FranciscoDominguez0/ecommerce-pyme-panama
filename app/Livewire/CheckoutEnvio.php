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
            if (!session()->has('checkout_direccion_id')) {
                app(\App\Services\EnvioService::class)->sincronizarDireccionCheckoutSiVacio($usuario->id);
            }

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
        $validator = \Illuminate\Support\Facades\Validator::make([
            'contacto_nombre' => $this->contacto_nombre,
            'contacto_apellido' => $this->contacto_apellido,
            'contacto_email' => $this->contacto_email,
            'contacto_telefono1' => $this->contacto_telefono1,
            'contacto_telefono2' => $this->contacto_telefono2,
        ], [
            'contacto_nombre' => 'required|string|max:100',
            'contacto_apellido' => 'required|string|max:100',
            'contacto_email' => 'required|email|max:255',
            'contacto_telefono1' => 'required|string|max:30',
            'contacto_telefono2' => 'nullable|string|max:30',
        ], [
            'contacto_nombre.required' => 'El nombre es obligatorio.',
            'contacto_apellido.required' => 'El apellido es obligatorio.',
            'contacto_email.required' => 'El correo electrónico es obligatorio.',
            'contacto_email.email' => 'El correo electrónico no es válido.',
            'contacto_telefono1.required' => 'El número de teléfono es obligatorio.',
        ]);

        $faltaDireccion = ($this->metodo_entrega === 'delivery' && !session('checkout_direccion_id'));
        $faltaCourier = ($this->metodo_entrega === 'retiro_courier' && !session('checkout_courier_sucursal'));

        if ($validator->fails() || $faltaDireccion || $faltaCourier) {
            foreach ($validator->errors()->messages() as $campo => $mensajes) {
                foreach ($mensajes as $msg) {
                    $this->addError($campo, $msg);
                }
            }

            if ($validator->errors()->has('contacto_telefono1')) {
                $this->dispatch('mostrar-toast', [
                    'tipo' => 'warning',
                    'mensaje' => 'Falta ingresar el número de teléfono.',
                ]);
            } elseif ($validator->errors()->has('contacto_nombre') || $validator->errors()->has('contacto_apellido')) {
                $this->dispatch('mostrar-toast', [
                    'tipo' => 'warning',
                    'mensaje' => 'Falta ingresar nombre y apellido de contacto.',
                ]);
            } elseif ($validator->errors()->has('contacto_email')) {
                $this->dispatch('mostrar-toast', [
                    'tipo' => 'warning',
                    'mensaje' => 'Falta ingresar un correo electrónico válido.',
                ]);
            }

            if ($faltaDireccion) {
                $this->addError('metodo_entrega', 'Falta seleccionar la dirección.');
                $this->dispatch('mostrar-toast', [
                    'tipo' => 'warning',
                    'mensaje' => 'Falta seleccionar la dirección. <button type="button" onclick="window.dispatchEvent(new CustomEvent(\'open-modal\', {detail: \'modal-calculadora-envio\'}))" class="underline hover:text-emerald-300 ml-1">Haz clic aquí para Cambiar.</button>',
                ]);
                $this->dispatch('open-modal', 'modal-calculadora-envio');
            } elseif ($faltaCourier) {
                $this->addError('metodo_entrega', 'Falta seleccionar la sucursal de courier.');
                $this->dispatch('mostrar-toast', [
                    'tipo' => 'warning',
                    'mensaje' => 'Falta seleccionar la sucursal de courier. <button type="button" onclick="window.dispatchEvent(new CustomEvent(\'open-modal\', {detail: \'modal-calculadora-envio\'}))" class="underline hover:text-emerald-300 ml-1">Haz clic aquí para Cambiar.</button>',
                ]);
                $this->dispatch('open-modal', 'modal-calculadora-envio');
            }

            return;
        }

        session([
            'checkout_contacto_nombre' => $this->contacto_nombre,
            'checkout_contacto_apellido' => $this->contacto_apellido,
            'checkout_contacto_email' => $this->contacto_email,
            'checkout_contacto_telefono1' => $this->contacto_telefono1,
            'checkout_contacto_telefono2' => $this->contacto_telefono2,
            'checkout_metodo_entrega' => $this->metodo_entrega,
        ]);

        return redirect()->route('cliente.checkout.pago');
    }

    public function resolverUbicacionEnvio(\App\Models\Carrito $carrito, \App\Services\EnvioService $envioService): array
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
                    $costoFinal = $envioService->calcularCostoCourier($suc, $carrito);

                    return [
                        'costo' => $costoFinal,
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
                        'costo' => (float) $envioService->calcularCostoEnvio($zona, $carrito),
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

    public function render(CarritoService $carritoService, \App\Services\EnvioService $envioService)
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();
        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);

        // Asegurar que el carrito tenga asignados los items con sus relaciones cargadas para el peso
        $items = $carrito->items()->with([
            'producto.imagenes',
            'producto.categoria.padre',
            'variante.opciones',
        ])->get();
        $carrito->setRelation('items', $items);

        $ubicacion = $this->resolverUbicacionEnvio($carrito, $envioService);
        $resumen = $carritoService->calcularTotal($carrito, $ubicacion['costo'], $ubicacion['zona_id']);

        return view('livewire.checkout-envio', compact('carrito', 'resumen', 'ubicacion'));
    }
}
