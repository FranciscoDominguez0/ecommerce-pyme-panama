<?php

namespace App\Livewire;

use App\Models\Direccion;
use App\Models\Producto;
use App\Models\ZonaEnvio;
use App\Services\CarritoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CarritoWidget extends Component
{
    public string $codigoCupon = '';
    public float $costoEnvio = 5.00;
    public string $nombreUbicacion = 'Panamá Centro';
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

    /**
     * Resuelve la ubicación y tarifa de envío a partir de la dirección configurada del usuario o sesión.
     */
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
                $suc = \App\Models\CourierSucursal::find(session('checkout_courier_sucursal'));
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
                $direccion = \App\Models\Direccion::with('zonaEnvio')->find(session('checkout_direccion_id'));
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
            
            // Fallback: Si no ha elegido nada, se pone costo en 0 para no asustar con "Tarifa Estimada" 
            // ya que ahora el usuario elige antes del checkout
            return [
                'costo' => 0.00,
                'ubicacion' => 'No seleccionado',
                'zona_id' => null,
                'direccion_id' => null,
            ];
        }
    }

    #[\Livewire\Attributes\On('envioActualizado')]
    public function render(CarritoService $carritoService)
    {
        $usuarioId = Auth::id();
        $sesionId = session()->getId();

        $carrito = $carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);
        $items = $carrito->items()->with([
            'producto.imagenes',
            'producto.categoria.padre',
            'variante.opciones',
        ])->get();

        // Asegurar que el carrito tenga asignados los items con sus relaciones cargadas
        $carrito->setRelation('items', $items);

        $ubicacion = $this->resolverUbicacionEnvio();
        $this->costoEnvio = $ubicacion['costo'];
        $this->nombreUbicacion = $ubicacion['ubicacion'];

        $resumen = $carritoService->calcularTotal($carrito, $this->costoEnvio, $ubicacion['zona_id']);

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

        // Obtener productos relacionados (de la misma categoría de los que están en el carrito)
        $productosRelacionados = collect();
        if ($items->count() > 0) {
            $categoriasIds = $items->pluck('producto.categoria_id')->unique()->filter();
            $productosIds = $items->pluck('producto.id');
            
            $productosRelacionados = Producto::with(['imagenes', 'categoria', 'brand'])
                ->sinEliminar()
                ->where('activo', true)
                ->whereIn('categoria_id', $categoriasIds)
                ->whereNotIn('id', $productosIds)
                ->inRandomOrder()
                ->take(3)
                ->get();
                
            // Fallback si no hay suficientes
            if ($productosRelacionados->count() < 3) {
                $mas = Producto::with(['imagenes', 'categoria', 'brand'])
                    ->sinEliminar()
                    ->where('activo', true)
                    ->whereNotIn('id', $productosIds->concat($productosRelacionados->pluck('id')))
                    ->inRandomOrder()
                    ->take(3 - $productosRelacionados->count())
                    ->get();
                $productosRelacionados = $productosRelacionados->concat($mas);
            }
        } else {
            $productosRelacionados = Producto::with(['imagenes', 'categoria', 'brand'])
                ->sinEliminar()
                ->where('activo', true)
                ->inRandomOrder()
                ->take(3)
                ->get();
        }

        return view('livewire.carrito-widget', compact('carrito', 'items', 'resumen', 'productosDeseos', 'productosRelacionados', 'ubicacion'));
    }
}
