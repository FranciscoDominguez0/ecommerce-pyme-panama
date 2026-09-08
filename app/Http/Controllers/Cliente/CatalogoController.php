<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    /**
     * Muestra el catálogo público de productos con filtros y paginación desde la BD.
     */
    public function index(Request $request): View
    {
        $buscar        = $request->input('buscar', '');
        $categoriaSlug = $request->input('categoria', 'all');
        $precioMin     = (float) $request->input('min_precio', 0);
        $precioMax     = (float) $request->input('max_precio', 2000);
        $orden         = $request->input('orden', 'relevancia');

        // Cargamos categorías raíz con conteos y pills según el filtro activo.
        $categorias      = $this->cargarCategoriasConConteo();
        $categoriasPills = $this->resolverCategoriasPills($categoriaSlug, $categorias);

        // Marcas verificadas para el sidebar.
        $marcas = \App\Models\Brand::where('verified', true)->orderBy('name')->get();

        // Construimos la query de productos y aplicamos filtros y orden.
        $query = Producto::with(['categoria', 'imagenes', 'variantes.opciones.tipo', 'promocionesProductoDelMes'])
            ->withCount('variantes')
            ->sinEliminar()
            ->activos();

        $this->aplicarFiltros($query, $buscar, $categoriaSlug, $request->input('marca', []), $precioMin, $precioMax);
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
     * Carga las categorías raíz con hijas y el conteo total de productos en una sola query SQL.
     */
    private function cargarCategoriasConConteo()
    {
        // Una sola query con LEFT JOIN reemplaza las 3 queries anteriores.
        // Cuenta productos activos propios + los de cada hija directa por categoría padre.
        $sql = <<<SQL
            SELECT
                c.id,
                c.nombre,
                c.slug,
                c.imagen_ruta,
                c.activo,
                c.padre_id,
                c.orden_visualizacion,
                COALESCE(SUM(
                    CASE
                        WHEN p.eliminado_en IS NULL AND p.activo = TRUE THEN 1
                        ELSE 0
                    END
                ), 0) AS total_productos_count
            FROM categorias c
            LEFT JOIN categorias hija
                ON hija.padre_id = c.id
                AND hija.eliminado_en IS NULL
            LEFT JOIN productos p
                ON (p.categoria_id = c.id OR p.categoria_id = hija.id)
                AND p.eliminado_en IS NULL
                AND p.activo = TRUE
            WHERE c.padre_id IS NULL
              AND c.eliminado_en IS NULL
            GROUP BY c.id, c.nombre, c.slug, c.imagen_ruta, c.activo, c.padre_id, c.orden_visualizacion
            ORDER BY c.nombre ASC
        SQL;

        $filas = DB::select($sql);

        // Hidratamos objetos Categoria para que las vistas y relaciones funcionen igual que antes.
        $categorias = Categoria::hydrate(
            array_map(fn ($fila) => (array) $fila, $filas)
        );

        // Cargamos las hijas con eager loading (una sola query adicional, necesaria para el sidebar).
        $categorias->load(['hijas' => fn ($q) => $q->sinEliminar()->orderBy('nombre')]);

        return $categorias;
    }

    /**
     * Determina qué categorías mostrar como pills según el slug activo.
     */
    private function resolverCategoriasPills(string $categoriaSlug, $categorias)
    {
        if ($categoriaSlug === 'all') {
            return $categorias;
        }

        $categoriaActual = Categoria::where('slug', $categoriaSlug)->first();

        if (! $categoriaActual) {
            return $categorias;
        }

        $subcategorias = Categoria::sinEliminar()
            ->where('padre_id', $categoriaActual->id)
            ->withCount(['productos' => fn ($q) => $q->sinEliminar()->activos()])
            ->orderBy('nombre')
            ->get();

        if ($subcategorias->isNotEmpty()) {
            return $subcategorias;
        }

        // Si es hija y no tiene sub-hijas, mostramos sus hermanas.
        if ($categoriaActual->padre_id) {
            return Categoria::sinEliminar()
                ->where('padre_id', $categoriaActual->padre_id)
                ->withCount(['productos' => fn ($q) => $q->sinEliminar()->activos()])
                ->orderBy('nombre')
                ->get();
        }

        return $categorias;
    }

    /**
     * Aplica los filtros de búsqueda, categoría, marca y precio al query de productos.
     */
    private function aplicarFiltros($query, string $buscar, string $categoriaSlug, array $marcas, float $precioMin, float $precioMax): void
    {
        if (! empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('descripcion_corta', 'like', "%{$buscar}%")
                  ->orWhere('sku', 'like', "%{$buscar}%");
            });
        }

        if ($categoriaSlug !== 'all') {
            $categoriaFiltro = Categoria::where('slug', $categoriaSlug)->first();

            if ($categoriaFiltro) {
                $hijas        = Categoria::sinEliminar()->where('padre_id', $categoriaFiltro->id)->pluck('id');
                $idsCategoria = collect([$categoriaFiltro->id])->merge($hijas);
                $query->whereIn('categoria_id', $idsCategoria);
            }
        }

        if (count($marcas) > 0) {
            $query->whereIn('brand_id', $marcas);
        }

        if ($precioMin > 0) {
            $query->where('precio', '>=', $precioMin);
        }

        if ($precioMax > 0 && $precioMax < 2000) {
            $query->where('precio', '<=', $precioMax);
        }
    }

    /**
     * Aplica el orden seleccionado al query de productos.
     */
    private function aplicarOrden($query, string $orden): void
    {
        match ($orden) {
            'precio_asc'  => $query->orderBy('precio', 'asc'),
            'precio_desc' => $query->orderBy('precio', 'desc'),
            'nombre_asc'  => $query->orderBy('nombre', 'asc'),
            default       => $query->orderBy('destacado', 'desc')->orderBy('id', 'desc'),
        };
    }

    /**
     * Muestra la página de detalle de un producto específico desde la base de datos.
     */
    public function show(string $slug): View
    {
        $producto = Producto::with(['categoria', 'imagenes', 'variantes.opciones.tipo', 'promocionesProductoDelMes'])
            ->sinEliminar()
            ->where('slug', $slug)
            ->firstOrFail();

        // Productos relacionados de la misma categoría.
        $relacionados = Producto::with(['categoria', 'imagenes', 'promocionesProductoDelMes'])
            ->sinEliminar()
            ->activos()
            ->where('id', '!=', $producto->id)
            ->where('categoria_id', $producto->categoria_id)
            ->take(4)
            ->get();

        if ($relacionados->isEmpty()) {
            $relacionados = Producto::with(['categoria', 'imagenes', 'promocionesProductoDelMes'])
                ->sinEliminar()
                ->activos()
                ->where('id', '!=', $producto->id)
                ->take(4)
                ->get();
        }

        $categorias = Categoria::sinEliminar()->orderBy('nombre')->get();

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

        \App\Models\NotificacionStock::firstOrCreate([
            'producto_id' => $producto->id,
            'email'       => strtolower(trim($validated['email'])),
            'notificado'  => false,
        ]);

        $mensaje = "¡Listo! Te enviaremos un correo a {$validated['email']} en cuanto tengamos stock de {$producto->nombre}.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
            ]);
        }

        return back()->with('success', $mensaje);
    }
}
