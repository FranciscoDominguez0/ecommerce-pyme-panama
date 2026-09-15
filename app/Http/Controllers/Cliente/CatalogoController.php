<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Categoria;
use App\Models\NotificacionStock;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    /**
     * Precio máximo del slider de filtro. Debe coincidir con el valor máximo de la vista.
     */
    private const PRECIO_MAXIMO_FILTRO = 2000;

    /**
     * Relaciones necesarias para mostrar una tarjeta de producto en el listado.
     */
    private const RELACIONES_LISTADO = ['categoria', 'imagenes', 'promocionesProductoDelMes'];

    /**
     * Relaciones necesarias para la página de detalle de un producto.
     */
    private const RELACIONES_DETALLE = ['categoria', 'imagenes', 'variantes.opciones.tipo', 'promocionesProductoDelMes'];

    /**
     * Muestra el catálogo público de productos con filtros y paginación.
     */
    public function index(Request $request): View
    {
        $buscar        = $request->input('buscar', '');
        $categoriaSlug = $request->input('categoria', 'all');
        $precioMin     = (float) $request->input('min_precio', 0);
        $precioMax     = (float) $request->input('max_precio', self::PRECIO_MAXIMO_FILTRO);
        $orden         = $request->input('orden', 'relevancia');

        $categorias    = Categoria::raicesConConteoDeProductos();
        $marcas        = Brand::where('verified', true)->orderBy('name')->get();

        // Resolvemos la categoría activa una sola vez para evitar queries duplicadas.
        $categoriaActiva = $categoriaSlug !== 'all'
            ? Categoria::where('slug', $categoriaSlug)->first()
            : null;

        $categoriasPills = $this->resolverCategoriasPills($categoriaSlug, $categorias, $categoriaActiva);

        $query = Producto::with(self::RELACIONES_DETALLE)
            ->withCount('variantes')
            ->sinEliminar()
            ->activos();

        $this->aplicarFiltros($query, $buscar, $categoriaActiva, $request->input('marca', []), $precioMin, $precioMax);
        $this->aplicarOrden($query, $orden);

        $productos = $query->paginate(12)->withQueryString();

        return view('cliente.catalogo.listado', compact(
            'productos',
            'buscar',
            'categoriaSlug',
            'precioMin',
            'precioMax',
            'orden',
            'categorias',
            'categoriasPills',
            'marcas'
        ));
    }

    /**
     * Muestra la página de detalle de un producto.
     * Solo productos activos son accesibles; los inactivos devuelven 404.
     */
    public function show(string $slug): View
    {
        $producto = Producto::with(self::RELACIONES_DETALLE)
            ->sinEliminar()
            ->where('activo', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $relacionados = $this->obtenerProductosRelacionados($producto);
        $categorias   = Categoria::sinEliminar()->orderBy('nombre')->get();

        return view('cliente.catalogo.detalle', compact('producto', 'relacionados', 'categorias'));
    }

    /**
     * Registra el email de un cliente para avisarle cuando el producto vuelva a tener stock.
     */
    public function solicitarNotificacionStock(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'email'       => 'required|email|max:255',
        ], [
            'email.required'       => 'Por favor ingresa tu correo electrónico.',
            'email.email'          => 'Ingresa una dirección de correo válida.',
            'producto_id.required' => 'El producto es requerido.',
        ]);

        $producto = Producto::sinEliminar()->findOrFail($validated['producto_id']);

        // Buscamos por producto+email únicamente para no crear duplicados si el cliente
        // ya fue notificado antes y vuelve a registrarse.
        NotificacionStock::updateOrCreate(
            [
                'producto_id' => $producto->id,
                'email'       => strtolower(trim($validated['email'])),
            ],
            ['notificado' => false]
        );

        $mensaje = "¡Listo! Te enviaremos un correo a {$validated['email']} en cuanto tengamos stock de {$producto->nombre}.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
            ]);
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Determina qué categorías mostrar como pills según el slug activo.
     */
    private function resolverCategoriasPills(string $categoriaSlug, Collection $categorias, ?Categoria $categoriaActiva): Collection
    {
        if ($categoriaSlug === 'all' || ! $categoriaActiva) {
            return $categorias;
        }

        $subcategorias = Categoria::sinEliminar()
            ->where('padre_id', $categoriaActiva->id)
            ->withCount(['productos' => fn($q) => $q->sinEliminar()->activos()])
            ->orderBy('nombre')
            ->get();

        if ($subcategorias->isNotEmpty()) {
            return $subcategorias;
        }

        // Si es hija sin sub-hijas, mostramos sus hermanas para mantener el contexto de navegación.
        if ($categoriaActiva->padre_id) {
            return Categoria::sinEliminar()
                ->where('padre_id', $categoriaActiva->padre_id)
                ->withCount(['productos' => fn($q) => $q->sinEliminar()->activos()])
                ->orderBy('nombre')
                ->get();
        }

        return $categorias;
    }

    /**
     * Aplica los filtros de búsqueda, categoría, marca y precio al query de productos.
     */
    private function aplicarFiltros(Builder $query, ?string $buscar, ?Categoria $categoriaActiva, array $marcas, float $precioMin, float $precioMax): void
    {
        if (! empty($buscar)) {
            $query->where(function (Builder $q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion_corta', 'like', "%{$buscar}%")
                  ->orWhere('sku', 'like', "%{$buscar}%");
            });
        }

        if ($categoriaActiva) {
            $hijas        = Categoria::sinEliminar()->where('padre_id', $categoriaActiva->id)->pluck('id');
            $idsCategoria = collect([$categoriaActiva->id])->merge($hijas);
            $query->whereIn('categoria_id', $idsCategoria);
        }

        if (count($marcas) > 0) {
            $query->whereIn('brand_id', $marcas);
        }

        if ($precioMin > 0) {
            $query->where('precio', '>=', $precioMin);
        }

        if ($precioMax > 0 && $precioMax < self::PRECIO_MAXIMO_FILTRO) {
            $query->where('precio', '<=', $precioMax);
        }
    }

    /**
     * Aplica el orden seleccionado al query de productos.
     */
    private function aplicarOrden(Builder $query, string $orden): void
    {
        match ($orden) {
            'precio_asc'              => $query->orderBy('precio', 'asc'),
            'precio_desc'             => $query->orderBy('precio', 'desc'),
            'nombre_asc'              => $query->orderBy('nombre', 'asc'),
            'relevancia'              => $query->orderBy('destacado', 'desc')->orderBy('id', 'desc'),
            default                   => $query->orderBy('destacado', 'desc')->orderBy('id', 'desc'),
        };
    }

    /**
     * Obtiene hasta $cantidad productos relacionados de la misma categoría.
     * Si no hay suficientes, hace fallback a cualquier producto activo.
     */
    private function obtenerProductosRelacionados(Producto $producto, int $cantidad = 4): Collection
    {
        $relacionados = Producto::with(self::RELACIONES_LISTADO)
            ->sinEliminar()
            ->activos()
            ->where('id', '!=', $producto->id)
            ->where('categoria_id', $producto->categoria_id)
            ->take($cantidad)
            ->get();

        if ($relacionados->isNotEmpty()) {
            return $relacionados;
        }

        // Fallback: si no hay de la misma categoría, mostramos otros productos activos.
        return Producto::with(self::RELACIONES_LISTADO)
            ->sinEliminar()
            ->activos()
            ->where('id', '!=', $producto->id)
            ->take($cantidad)
            ->get();
    }
}
