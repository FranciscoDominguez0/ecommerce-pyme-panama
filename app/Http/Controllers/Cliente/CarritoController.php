<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Services\CarritoService;
use App\Services\EnvioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CarritoController extends Controller
{
    protected CarritoService $carritoService;
    protected EnvioService $envioService;

    public function __construct(CarritoService $carritoService, EnvioService $envioService)
    {
        $this->carritoService = $carritoService;
        $this->envioService = $envioService;
    }

    /**
     * Muestra la vista principal del Carrito de Compras.
     */
    public function index(Request $request): View
    {
        $usuarioId = Auth::id();
        $sesionId = $request->session()->getId();

        $carrito = $this->carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);
        $costoEnvioEstimado = 5.00; // Tarifa base estimada Panamá Centro
        $resumen = $this->carritoService->calcularTotal($carrito, $costoEnvioEstimado);

        // Obtener productos de la lista de deseos si el usuario está autenticado
        $productosDeseos = collect();
        if ($usuarioId) {
            $productosDeseos = Producto::with(['imagenes', 'variantes', 'brand', 'categoria'])
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

        return view('cliente.carrito', compact('carrito', 'resumen', 'costoEnvioEstimado', 'productosDeseos'));
    }

    /**
     * Agrega un producto al carrito (soporta peticiones web y AJAX).
     */
    public function agregar(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'variante_producto_id' => ['nullable', 'integer', 'exists:variantes_producto,id'],
            'cantidad' => ['nullable', 'integer', 'min:1'],
        ]);

        $usuarioId = Auth::id();
        $sesionId = $request->session()->getId();
        $cantidad = (int) ($request->input('cantidad', 1));

        $resultado = $this->carritoService->agregarProducto(
            (int) $request->producto_id,
            $request->filled('variante_producto_id') ? (int) $request->variante_producto_id : null,
            $cantidad,
            $usuarioId,
            $sesionId
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($resultado, $resultado['exito'] ? 200 : 422);
        }

        if (!$resultado['exito']) {
            return back()->with('error', $resultado['mensaje']);
        }

        return redirect()->route('cliente.carrito')->with('success', $resultado['mensaje']);
    }

    /**
     * Actualiza la cantidad de un artículo en el carrito.
     */
    public function actualizarCantidad(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'cantidad' => ['required', 'integer'],
        ]);

        $usuarioId = Auth::id();
        $resultado = $this->carritoService->actualizarCantidad($id, (int) $request->cantidad, $usuarioId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($resultado, $resultado['exito'] ? 200 : 422);
        }

        if (!$resultado['exito']) {
            return back()->with('error', $resultado['mensaje']);
        }

        return back()->with('success', $resultado['mensaje']);
    }

    /**
     * Elimina un item del carrito.
     */
    public function eliminar(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $usuarioId = Auth::id();
        $eliminado = $this->carritoService->eliminarItem($id, $usuarioId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'exito' => $eliminado,
                'mensaje' => $eliminado ? 'Producto removido del carrito.' : 'No se pudo eliminar el producto.',
            ], $eliminado ? 200 : 422);
        }

        if (!$eliminado) {
            return back()->with('error', 'No se pudo eliminar el artículo.');
        }

        return back()->with('success', 'Producto removido del carrito con éxito.');
    }

    /**
     * Aplica un cupón de descuento al carrito.
     */
    public function aplicarCupon(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'codigo' => ['required', 'string', 'max:50'],
        ], [
            'codigo.required' => 'Ingresa un código de cupón.',
        ]);

        $usuarioId = Auth::id();
        $sesionId = $request->session()->getId();
        $carrito = $this->carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);

        $resultado = $this->carritoService->aplicarCupon($carrito, $request->codigo, $usuarioId);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($resultado, $resultado['valido'] ? 200 : 422);
        }

        if (!$resultado['valido']) {
            return back()->with('error', $resultado['mensaje']);
        }

        return back()->with('success', $resultado['mensaje']);
    }

    /**
     * Remueve el cupón de descuento activo.
     */
    public function removerCupon(Request $request): JsonResponse|RedirectResponse
    {
        $usuarioId = Auth::id();
        $sesionId = $request->session()->getId();
        $carrito = $this->carritoService->obtenerOCrearCarrito($usuarioId, $sesionId);

        $this->carritoService->removerCupon($carrito);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'valido' => true,
                'mensaje' => 'Cupón removido exitosamente.',
            ]);
        }

        return back()->with('info', 'El cupón ha sido removido del carrito.');
    }
}
