<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\CourierSucursal;
use App\Models\Direccion;
use App\Models\ZonaEnvio;

class CalculadoraEnvio extends Component
{
    public $metodo_entrega = 'delivery'; // 'delivery', 'retiro_local', 'retiro_courier'
    public $metodo_entrega_modal = 'delivery';
    
    // Para Retiro Courier
    public $zonaSeleccionada = '';
    public $courierSeleccionado = '';
    public $sucursalSeleccionada = '';
    
    // Zonas de Envio para Delivery
    public $zonasEnvio = [];
    public $requiereEnvio = true;

    public function mount($requiereEnvio = true)
    {
        $this->requiereEnvio = $requiereEnvio;
        $this->zonasEnvio = ZonaEnvio::where('activo', true)->orderBy('nombre')->get();

        $this->metodo_entrega = session('checkout_metodo_entrega', 'delivery');
        $this->metodo_entrega_modal = $this->metodo_entrega ?: 'delivery';
        $this->zonaSeleccionada = session('checkout_courier_zona', '');
        $this->courierSeleccionado = session('checkout_courier_courier', '');
        $this->sucursalSeleccionada = session('checkout_courier_sucursal', '');
    }

    public function abrirModalMetodo($metodo)
    {
        $this->metodo_entrega_modal = $metodo;
        $this->dispatch('open-modal', 'modal-calculadora-envio');
    }

    #[On('direccionSeleccionadaParaCheckout')]
    public function confirmarDelivery()
    {
        $this->metodo_entrega = 'delivery';
        session(['checkout_metodo_entrega' => 'delivery']);
        $this->dispatch('envioActualizado');
    }

    public function updatedMetodoEntrega($value)
    {
        if ($value === 'delivery') {
            $this->abrirModalMetodo('delivery');
        } elseif ($value === 'retiro_courier') {
            $this->abrirModalMetodo('retiro_courier');
        } elseif ($value === 'retiro_local') {
            session(['checkout_metodo_entrega' => 'retiro_local']);
            $this->dispatch('envioActualizado');
        }
    }

    public function confirmarSeleccion()
    {
        $this->validate([
            'metodo_entrega_modal' => 'required|in:delivery,retiro_local,retiro_courier',
        ]);

        if ($this->metodo_entrega_modal === 'retiro_courier') {
            $this->validate([
                'zonaSeleccionada' => 'required',
                'courierSeleccionado' => 'required',
                'sucursalSeleccionada' => 'required',
            ]);
            
            session([
                'checkout_metodo_entrega' => 'retiro_courier',
                'checkout_courier_zona' => $this->zonaSeleccionada,
                'checkout_courier_courier' => $this->courierSeleccionado,
                'checkout_courier_sucursal' => $this->sucursalSeleccionada,
            ]);
            $this->metodo_entrega = 'retiro_courier';
        }

        if ($this->metodo_entrega_modal === 'retiro_local') {
            session(['checkout_metodo_entrega' => 'retiro_local']);
            $this->metodo_entrega = 'retiro_local';
        }

        $this->dispatch('close-modal', 'modal-calculadora-envio');
        $this->dispatch('envioActualizado');
    }

    public function getCouriersProperty()
    {
        if (!$this->zonaSeleccionada) return [];
        return CourierSucursal::where('zona', $this->zonaSeleccionada)
            ->where('activo', true)
            ->select('courier')
            ->distinct()
            ->pluck('courier');
    }

    public function getSucursalesProperty()
    {
        if (!$this->zonaSeleccionada || !$this->courierSeleccionado) return [];
        return CourierSucursal::where('zona', $this->zonaSeleccionada)
            ->where('courier', $this->courierSeleccionado)
            ->where('activo', true)
            ->get();
    }

    public function render()
    {
        return view('livewire.calculadora-envio');
    }
}
