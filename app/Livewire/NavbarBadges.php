<?php

namespace App\Livewire;

use App\Models\ListaDeseos;
use App\Services\CarritoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

class NavbarBadges extends Component
{
    public int $cantidadCarrito = 0;
    public int $cantidadDeseos = 0;

    public function mount()
    {
        $this->actualizarCantidades();
    }

    #[On('carrito-actualizado')]
    public function actualizarCarrito()
    {
        $usuarioId = Auth::id();
        $sesionId = Session::getId();

        $carritoService = app(CarritoService::class);
        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);
        $this->cantidadCarrito = $carrito ? $carrito->cantidad_total : 0;
    }

    #[On('deseos-actualizado')]
    public function actualizarDeseos()
    {
        $usuarioId = Auth::id();
        $this->cantidadDeseos = $usuarioId ? ListaDeseos::where('usuario_id', $usuarioId)->count() : 0;
    }

    public function actualizarCantidades()
    {
        $this->actualizarCarrito();
        $this->actualizarDeseos();
    }

    public function render()
    {
        return view('livewire.navbar-badges');
    }
}
