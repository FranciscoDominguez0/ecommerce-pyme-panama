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
    #[On('deseos-actualizado')]
    public function actualizarCantidades()
    {
        $usuarioId = Auth::id();
        $sesionId = Session::getId();

        // Obtener cantidad de carrito
        $carritoService = app(CarritoService::class);
        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);
        $this->cantidadCarrito = $carrito ? $carrito->cantidad_total : 0;

        // Obtener cantidad de lista de deseos
        if ($usuarioId) {
            $this->cantidadDeseos = ListaDeseos::where('usuario_id', $usuarioId)->count();
        } else {
            $this->cantidadDeseos = 0;
        }
    }

    public function render()
    {
        return view('livewire.navbar-badges');
    }
}
