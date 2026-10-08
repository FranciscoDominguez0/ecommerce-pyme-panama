<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\CourierSucursal;
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
    public $metodo_entrega_modal = 'delivery';
    
    // Para Retiro Courier
    public $zonaSeleccionada = '';
    public $courierSeleccionado = '';
    public $sucursalSeleccionada = '';
    
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
        $this->metodo_entrega_modal = $this->metodo_entrega ?: 'delivery';
        $this->zonaSeleccionada = session('checkout_courier_zona', '');
        $this->courierSeleccionado = session('checkout_courier_courier', '');
        $this->sucursalSeleccionada = session('checkout_courier_sucursal', '');
    }

    public function abrirModalMetodo($metodo)
    {
        $this->metodo_entrega_modal = $metodo;
        $this->dispatch('open-modal', 'modal-checkout-envio');
    }

    #[On('direccionSeleccionadaParaCheckout')]
    public function confirmarDelivery()
    {
        $this->metodo_entrega = 'delivery';
        session(['checkout_metodo_entrega' => 'delivery']);
    }

    public function updatedMetodoEntrega($value)
    {
        session(['checkout_metodo_entrega' => $value]);
    }
    
    public function updatedZonaSeleccionada()
    {
        $this->courierSeleccionado = '';
        $this->sucursalSeleccionada = '';
    }

    public function updatedCourierSeleccionado()
    {
        $this->sucursalSeleccionada = '';
    }

    public function getZonasCourierProperty()
    {
        return CourierSucursal::where('activo', true)->distinct()->pluck('zona')->sort();
    }

    public function getCouriersProperty()
    {
        if (!$this->zonaSeleccionada) return collect();
        return CourierSucursal::where('activo', true)->where('zona', $this->zonaSeleccionada)->distinct()->pluck('courier')->sort();
    }

    public function getSucursalesCourierProperty()
    {
        if (!$this->zonaSeleccionada || !$this->courierSeleccionado) return collect();
        return CourierSucursal::where('activo', true)
            ->where('zona', $this->zonaSeleccionada)
            ->where('courier', $this->courierSeleccionado)
            ->get();
    }

    public function continuarCourier()
    {
        $this->validate([
            'zonaSeleccionada' => 'required',
            'courierSeleccionado' => 'required',
            'sucursalSeleccionada' => 'required',
        ]);

        $sucursal = CourierSucursal::find($this->sucursalSeleccionada);
        if (!$sucursal) {
            $this->addError('sucursalSeleccionada', 'Sucursal inválida');
            return;
        }

        session([
            'checkout_metodo_entrega' => 'retiro_courier',
            'checkout_courier_zona' => $this->zonaSeleccionada,
            'checkout_courier_courier' => $this->courierSeleccionado,
            'checkout_courier_sucursal' => $this->sucursalSeleccionada,
            'checkout_direccion_id' => null,
            'checkout_zona_envio_id' => null,
        ]);
        $this->metodo_entrega = 'retiro_courier';
        
        $this->dispatch('close-modal', 'modal-checkout-envio');
    }

    public function continuarRetiroLocal()
    {
        session([
            'checkout_metodo_entrega' => 'retiro_local',
            'checkout_direccion_id' => null,
            'checkout_zona_envio_id' => null,
            'checkout_courier_sucursal' => null,
        ]);

        $this->metodo_entrega = 'retiro_local';

        $this->dispatch('close-modal', 'modal-checkout-envio');
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

    public function render()
    {
        return view('livewire.checkout-envio');
    }
}
