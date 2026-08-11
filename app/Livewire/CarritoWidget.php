<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Services\CarritoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CarritoWidget extends Component
{
    public string $codigoCupon = '';
    public float $costoEnvio = 5.00;
    public array $stockAdvertencias = [];
    public ?string $mensajeCupon = null;
    public ?string $tipoMensajeCupon = null;

    /**
     * Incrementa en 1 la cantidad de un producto en el carrito validando stock.
     */
    public function incrementar(int $itemId, CarritoService $carritoService): void
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();
        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);
        $item = $carrito->items()->with(['producto', 'variante'])->find($itemId);

        if (!$item) {
            return;
        }

        $stockDisponible = $item->stock_disponible;
        $nuevaCantidad = $item->cantidad + 1;

        if ($nuevaCantidad > $stockDisponible) {
            $this->stockAdvertencias[$itemId] = "Solo quedan {$stockDisponible} unidades disponibles";
            $this->dispatch('mostrar-toast', [
                'tipo' => 'warning',
                'mensaje' => "Stock insuficiente: solo quedan {$stockDisponible} unidades disponibles.",
            ]);
            return;
        }

        unset($this->stockAdvertencias[$itemId]);
        $carritoService->actualizarCantidad($itemId, $nuevaCantidad, $usuarioId, $sesionId);
        $this->dispatch('carrito-actualizado');
    }

    /**
     * Decrementa en 1 la cantidad o elimina si llega a 0.
     */
    public function decrementar(int $itemId, CarritoService $carritoService): void
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();
        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);
        $item = $carrito->items()->find($itemId);

        if (!$item) {
            return;
        }

        unset($this->stockAdvertencias[$itemId]);

        if ($item->cantidad > 1) {
            $carritoService->actualizarCantidad($itemId, $item->cantidad - 1, $usuarioId, $sesionId);
        } else {
            $carritoService->eliminarItem($itemId, $usuarioId, $sesionId);
        }

        $this->dispatch('carrito-actualizado');
    }

    /**
     * Elimina un producto del carrito.
     */
    public function eliminar(int $itemId, CarritoService $carritoService): void
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();
        unset($this->stockAdvertencias[$itemId]);
        $carritoService->eliminarItem($itemId, $usuarioId, $sesionId);

        $this->dispatch('mostrar-toast', [
            'tipo' => 'info',
            'mensaje' => 'Producto retirado del carrito.',
        ]);

        $this->dispatch('carrito-actualizado');
    }

    /**
     * Aplica un cupón promocional al carrito.
     */
    public function aplicarCupon(CarritoService $carritoService): void
    {
        $this->reset(['mensajeCupon', 'tipoMensajeCupon']);

        if (empty(trim($this->codigoCupon))) {
            $this->mensajeCupon = 'Por favor ingresa un código de cupón.';
            $this->tipoMensajeCupon = 'error';
            return;
        }

        $usuarioId = Auth::id();
        $sesionId = session()->getId();
        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);

        $resultado = $carritoService->aplicarCupon($carrito, $this->codigoCupon, $usuarioId);

        if ($resultado['valido']) {
            $this->mensajeCupon = $resultado['mensaje']; // Mostrar mensaje inline para éxito
            $this->tipoMensajeCupon = 'success';
            $this->codigoCupon = '';
            // No mostrar toast para éxito de cupón, solo mensaje inline
        } else {
            $this->mensajeCupon = $resultado['mensaje'];
            $this->tipoMensajeCupon = 'error';
            // No mostrar toast para errores de cupón, solo mensaje inline
        }
    }

    /**
     * Remueve el cupón de descuento activo.
     */
    public function removerCupon(CarritoService $carritoService): void
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();
        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);

        $carritoService->removerCupon($carrito);
        $this->reset(['mensajeCupon', 'tipoMensajeCupon']);

        $this->dispatch('mostrar-toast', [
            'tipo' => 'info',
            'mensaje' => 'Cupón removido del carrito.',
        ]);
    }

    /**
     * Mueve un producto desde la lista de deseos al carrito.
     */
    public function moverDeseoAlCarrito(int $productoId, CarritoService $carritoService): void
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();

        $resultado = $carritoService->agregarProducto($productoId, null, 1, $usuarioId, $sesionId);

        if ($resultado['exito']) {
            if ($usuarioId) {
                DB::table('lista_deseos')
                    ->where('usuario_id', $usuarioId)
                    ->where('producto_id', $productoId)
                    ->delete();
                $this->dispatch('deseos-actualizado');
            }

            $this->dispatch('mostrar-toast', [
                'tipo' => 'success',
                'mensaje' => 'Producto movido al carrito.',
            ]);
            
            $this->dispatch('carrito-actualizado');
        } else {
            $this->dispatch('mostrar-toast', [
                'tipo' => 'warning',
                'mensaje' => $resultado['mensaje'],
            ]);
        }
    }

    /**
     * Elimina un producto de la lista de deseos.
     */
    public function eliminarDeseo(int $productoId): void
    {
        $usuarioId = Auth::id();
        if ($usuarioId) {
            DB::table('lista_deseos')
                ->where('usuario_id', $usuarioId)
                ->where('producto_id', $productoId)
                ->delete();

            $this->dispatch('deseos-actualizado');

            $this->dispatch('mostrar-toast', [
                'tipo' => 'info',
                'mensaje' => 'Producto retirado de la lista de deseos.',
            ]);
        }
    }

    public function render(CarritoService $carritoService)
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();

        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);
        $items = $carrito->items()->with([
            'producto.imagenes',
            'producto.categoria',
            'variante.opciones',
        ])->get();

        $resumen = $carritoService->calcularTotal($carrito, $this->costoEnvio, null);

        // Obtener productos de la lista de deseos
        $productosDeseos = collect();
        if ($usuarioId) {
            $productosDeseos = Producto::with(['imagenes', 'categoria', 'brand'])
                ->sinEliminar()
                ->where('activo', true)
                ->whereIn('id', function ($query) use ($usuarioId) {
                    $query->select('producto_id')
                        ->from('lista_deseos')
                        ->where('usuario_id', $usuarioId);
                })
                ->take(4)
                ->get();
        }

        return view('livewire.carrito-widget', compact('carrito', 'items', 'resumen', 'productosDeseos'));
    }
}
