<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\ListaDeseos;
use App\Models\Producto;
use App\Services\CarritoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ListaDeseosController extends Controller
{
    protected CarritoService $carritoService;

    public function __construct(CarritoService $carritoService)
    {
        $this->carritoService = $carritoService;
    }

    /**
     * Muestra la lista de deseos del usuario autenticado.
     */
    public function index(): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Inicia sesión para ver tu lista de deseos.');
        }

        $usuarioId = Auth::id();

        $productos = Producto::with(['imagenes', 'variantes', 'brand', 'categoria'])
            ->sinEliminar()
            ->where('activo', true)
            ->whereIn('id', function ($query) use ($usuarioId) {
                $query->select('producto_id')
                    ->from('lista_deseos')
                    ->where('usuario_id', $usuarioId);
            })
            ->get();

        return view('cliente.lista-deseos', compact('productos'));
    }

    /**
     * Agrega un producto a la lista de deseos.
     */
    public function agregar(Request $request, int $productoId): JsonResponse|RedirectResponse
    {
        if (!Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'exito' => false,
                    'requiere_auth' => true,
                    'mensaje' => 'Debes iniciar sesión para guardar productos en tu lista de deseos.',
                ], 401);
            }
            return redirect()->route('login')->with('info', 'Debes iniciar sesión para guardar productos en tu lista de deseos.');
        }

        $usuarioId = Auth::id();
        $producto = Producto::sinEliminar()->where('activo', true)->find($productoId);

        if (!$producto) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['exito' => false, 'mensaje' => 'Producto no encontrado.'], 404);
            }
            return back()->with('error', 'El producto no existe.');
        }

        // Insertar o ignorar si ya existe
        DB::table('lista_deseos')->insertOrIgnore([
            'usuario_id' => $usuarioId,
            'producto_id' => $productoId,
            'creado_en' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'exito' => true,
                'mensaje' => 'Producto agregado a tu lista de deseos.',
            ]);
        }

        return back()->with('success', 'Producto guardado en tu lista de deseos.');
    }

    /**
     * Mueve un producto desde la lista de deseos hacia el carrito de compras.
     */
    public function moverAlCarrito(Request $request, int $productoId): JsonResponse|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'Inicia sesión para gestionar tus compras.');
        }

        $usuarioId = Auth::id();
        $sesionId = $request->session()->getId();

        // 1. Agregar al carrito
        $resultado = $this->carritoService->agregarProducto(
            $productoId,
            null,
            1,
            $usuarioId,
            $sesionId
        );

        if (!$resultado['exito']) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($resultado, 422);
            }
            return back()->with('error', $resultado['mensaje']);
        }

        // 2. Eliminar de lista de deseos
        DB::table('lista_deseos')
            ->where('usuario_id', $usuarioId)
            ->where('producto_id', $productoId)
            ->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'exito' => true,
                'mensaje' => 'Producto movido al carrito exitosamente.',
            ]);
        }

        return redirect()->route('cliente.carrito')->with('success', 'Producto movido al carrito con éxito.');
    }

    /**
     * Elimina un producto de la lista de deseos.
     */
    public function eliminar(Request $request, int $productoId): JsonResponse|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuarioId = Auth::id();

        DB::table('lista_deseos')
            ->where('usuario_id', $usuarioId)
            ->where('producto_id', $productoId)
            ->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'exito' => true,
                'mensaje' => 'Producto removido de tu lista de deseos.',
            ]);
        }

        return back()->with('success', 'Producto retirado de tu lista de deseos.');
    }
}
