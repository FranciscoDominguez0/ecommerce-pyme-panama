<?php

namespace App\Livewire;

use App\Models\Carrito;
use App\Models\ItemCarrito;
use App\Services\CarritoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

class CarritoDrawer extends Component
{
    public bool $abierto = false;
    public ?int $carritoId = null;
    public array $items = [];
    public float $totalParcial = 0.0;
    public int $cantidadTotal = 0;

    protected CarritoService $carritoService;

    public function boot(CarritoService $carritoService)
    {
        $this->carritoService = $carritoService;
    }

    public function mount()
    {
        $this->cargarCarrito();
    }

    #[On('abrir-carrito-drawer')]
    public function abrir()
    {
        $this->cargarCarrito();
        $this->abierto = true;
    }

    #[On('cerrar-carrito-drawer')]
    public function cerrar()
    {
        $this->abierto = false;
    }

    #[On('carrito-actualizado')]
    public function sincronizar()
    {
        $this->cargarCarrito();
    }

    public function cargarCarrito()
    {
        $usuarioId = Auth::id();
        $sesionId = Session::getId();

        $carrito = $this->carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);
        $carrito->load(['items.producto.imagenes', 'items.producto.brand', 'items.variante.opciones']);

        $this->carritoId = $carrito->id;
        $this->cantidadTotal = $carrito->cantidad_total;
        $this->totalParcial = (float) $carrito->subtotal;

        $this->items = $carrito->items->map(function (ItemCarrito $item) {
            $img = $item->imagen_url;
            $nombre = $item->producto->nombre ?? 'Producto';
            $precio = (float) $item->precio_unitario;
            $stockMax = $item->variante ? $item->variante->stock : ($item->producto->stock ?? 0);
            $slug = $item->producto->slug ?? '';

            $varianteInfo = '';
            if ($item->variante && $item->variante->opciones) {
                $varianteInfo = $item->variante->opciones->map(fn($o) => $o->valor)->join(' / ');
            }

            return [
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'nombre' => $nombre,
                'slug' => $slug,
                'imagen_url' => $img,
                'precio' => $precio,
                'cantidad' => $item->cantidad,
                'subtotal' => $item->subtotal,
                'stock_max' => $stockMax,
                'variante_info' => $varianteInfo,
            ];
        })->toArray();
    }

    public function incrementar(int $itemId)
    {
        $item = ItemCarrito::find($itemId);
        if (!$item) return;

        $res = $this->carritoService->actualizarCantidad($itemId, $item->cantidad + 1, Auth::id(), Session::getId());
        if ($res['exito']) {
            $this->cargarCarrito();
            $this->dispatch('carrito-actualizado');
        } else {
            $this->dispatch('mostrar-toast', ['tipo' => 'warning', 'mensaje' => $res['mensaje']]);
        }
    }

    public function decrementar(int $itemId)
    {
        $item = ItemCarrito::find($itemId);
        if (!$item) return;

        $res = $this->carritoService->actualizarCantidad($itemId, $item->cantidad - 1, Auth::id(), Session::getId());
        if ($res['exito']) {
            $this->cargarCarrito();
            $this->dispatch('carrito-actualizado');
        } else {
            $this->dispatch('mostrar-toast', ['tipo' => 'warning', 'mensaje' => $res['mensaje']]);
        }
    }

    public function eliminar(int $itemId)
    {
        $this->carritoService->eliminarItem($itemId, Auth::id(), Session::getId());
        $this->cargarCarrito();
        $this->dispatch('carrito-actualizado');
        $this->dispatch('mostrar-toast', ['tipo' => 'info', 'mensaje' => 'Producto removido del carrito']);
    }

    public function render()
    {
        return view('livewire.carrito-drawer');
    }
}
