<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    /**
     * Muestra el catálogo público de productos con filtros y paginación desde la BD.
     */
    public function index(Request $request): View
    {
        $buscar = $request->input('buscar', '');
        $categoriaSlug = $request->input('categoria', 'all');
        $precioMin = (float) $request->input('min_precio', 0);
        $precioMax = (float) $request->input('max_precio', 2000);
        $orden = $request->input('orden', 'relevancia');

        // Categorías con conteo de productos activos
        $categorias = Categoria::sinEliminar()
            ->withCount(['productos' => function ($q) {
                $q->sinEliminar()->activos();
            }])
            ->orderBy('nombre')
            ->get();

        // Consulta de productos con eager loading
        $query = Producto::with(['categoria', 'imagenes', 'variantes.opciones.tipo'])
            ->withCount('variantes')
            ->sinEliminar()
            ->activos();

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion_corta', 'like', "%{$buscar}%")
                  ->orWhere('sku', 'like', "%{$buscar}%");
            });
        }

        if ($categoriaSlug !== 'all') {
            $query->whereHas('categoria', function ($q) use ($categoriaSlug) {
                $q->where('slug', $categoriaSlug);
            });
        }

        if ($precioMin > 0) {
            $query->where('precio', '>=', $precioMin);
        }

        if ($precioMax > 0 && $precioMax < 2000) {
            $query->where('precio', '<=', $precioMax);
        }

        switch ($orden) {
            case 'precio_asc':
                $query->orderBy('precio', 'asc');
                break;
            case 'precio_desc':
                $query->orderBy('precio', 'desc');
                break;
            case 'nombre_asc':
                $query->orderBy('nombre', 'asc');
                break;
            default:
                $query->orderBy('destacado', 'desc')->orderBy('id', 'desc');
                break;
        }

        $productos = $query->paginate(12)->withQueryString();

        return view('cliente.catalogo.listado', compact(
            'productos',
            'buscar',
            'categoriaSlug',
            'precioMin',
            'precioMax',
            'orden',
            'categorias'
        ));
    }

    /**
     * Muestra la página de detalle de un producto específico desde la base de datos.
     */
    public function show(string $slug): View
    {
        $producto = Producto::with(['categoria', 'imagenes', 'variantes.opciones.tipo'])
            ->sinEliminar()
            ->where('slug', $slug)
            ->firstOrFail();

        // Productos relacionados
        $relacionados = Producto::with(['categoria', 'imagenes'])
            ->sinEliminar()
            ->activos()
            ->where('id', '!=', $producto->id)
            ->where('categoria_id', $producto->categoria_id)
            ->take(4)
            ->get();

        if ($relacionados->isEmpty()) {
            $relacionados = Producto::with(['categoria', 'imagenes'])
                ->sinEliminar()
                ->activos()
                ->where('id', '!=', $producto->id)
                ->take(4)
                ->get();
        }

        $categorias = Categoria::sinEliminar()->orderBy('nombre')->get();

        return view('cliente.catalogo.detalle', compact('producto', 'relacionados', 'categorias'));
    }
}
