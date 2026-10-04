<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImagenProducto;
use App\Models\Producto;
use App\Models\VarianteProducto;
use App\Models\Categoria;
use App\Services\ProductoService;
use App\Services\InventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\InventarioExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductoController extends Controller
{
    protected ProductoService $productoService;
    protected InventarioService $inventarioService;

    public function __construct(ProductoService $productoService, InventarioService $inventarioService)
    {
        $this->productoService = $productoService;
        $this->inventarioService = $inventarioService;
    }

    public function index(Request $request): View
    {
        $buscar = $request->input('buscar', '');
        $buscarSku = $request->input('sku', '');
        $categoriaId = $request->input('categoria_id', 'all');
        $filtroEstado = $request->input('estado', 'all');
        $filtroStock = $request->input('stock', 'all');

        $kpiTotal = Producto::sinEliminar()->count();
        $kpiEnStock = Producto::sinEliminar()->where('stock', '>', 5)->count();
        $kpiStockBajo = Producto::sinEliminar()->where('stock', '<=', 5)->count();
        $kpiVariantes = VarianteProducto::count();

        $categorias = Categoria::sinEliminar()->orderBy('nombre')->get();

        $query = Producto::with(['categoria', 'imagenes', 'variantes.opciones.tipo', 'brand'])
            ->withCount('variantes')
            ->sinEliminar();

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->whereRaw('unaccent(nombre) ILIKE unaccent(?)', ["%{$buscar}%"])
                    ->orWhereRaw('unaccent(descripcion_corta) ILIKE unaccent(?)', ["%{$buscar}%"])
                    ->orWhereRaw('unaccent(descripcion) ILIKE unaccent(?)', ["%{$buscar}%"]);
            });
        }

        if (!empty($buscarSku)) {
            $query->whereRaw('unaccent(sku) ILIKE unaccent(?)', ["%{$buscarSku}%"]);
        }

        if ($categoriaId !== 'all' && is_numeric($categoriaId)) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($filtroEstado !== 'all') {
            $query->where('activo', $filtroEstado === 'activo' || $filtroEstado === '1');
        }

        if ($filtroStock !== 'all') {
            if ($filtroStock === 'en_stock') $query->where('stock', '>', 5);
            elseif ($filtroStock === 'bajo_stock') $query->whereBetween('stock', [1, 5]);
            elseif ($filtroStock === 'agotado') $query->where('stock', '<=', 0);
        }

        $productos = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.productos.index', compact(
            'productos', 'kpiTotal', 'kpiEnStock', 'kpiStockBajo', 'kpiVariantes',
            'buscar', 'buscarSku', 'categoriaId', 'filtroEstado', 'filtroStock', 'categorias'
        ));
    }

    public function exportarPdf(Request $request)
    {
        $productos = Producto::with('categoria')->sinEliminar()->orderBy('nombre', 'asc')->get();
        $totalProductos = $productos->count();
        $totalStock = $productos->sum('stock');
        $valorizacionTotal = $productos->sum(fn($p) => $p->stock * $p->precio);

        $pdf = Pdf::loadView('admin.productos.pdf.inventario', compact('productos', 'totalProductos', 'totalStock', 'valorizacionTotal'));
        return $pdf->download("valorizacion_inventario_" . now()->format('Ymd') . ".pdf");
    }

    public function exportarExcel()
    {
        return Excel::download(new InventarioExport, "valorizacion_inventario_" . now()->format('Ymd') . ".xlsx");
    }

    public function create(): View
    {
        $producto = new Producto([
            'activo' => true,
            'destacado' => false,
            'aplica_itbms' => true,
            'oferta_activa' => false,
            'stock' => 0,
            'stock_minimo' => 3,
            'precio' => 0.00,
        ]);

        $datos = $this->productoService->obtenerDatosFormulario($producto, false);
        $datos['imagenes'] = collect();

        return view('admin.productos.form', $datos);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validarRequest($request);

        DB::transaction(function () use ($request) {
            list($brandId, $nombreMarca) = $this->productoService->resolverMarca($request);
            $datos = $this->productoService->prepararDatosProducto($request, $brandId, $nombreMarca);
            
            $producto = Producto::create($datos);
            
            $this->productoService->procesarImagenes($request, $producto);
            $this->productoService->guardarVariantes($request, $producto);
        });

        return redirect()->route('admin.productos.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(int $id): View
    {
        $producto = Producto::with(['imagenes', 'variantes.opciones.tipo', 'categoria', 'brand'])
            ->sinEliminar()
            ->findOrFail($id);

        $datos = $this->productoService->obtenerDatosFormulario($producto, true);
        $datos['imagenes'] = $producto->imagenes;
        $datos['id'] = $id;

        return view('admin.productos.form', $datos);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $producto = Producto::sinEliminar()->findOrFail($id);
        $this->validarRequest($request, $producto->id);

        DB::transaction(function () use ($request, $producto) {
            list($brandId, $nombreMarca) = $this->productoService->resolverMarca($request);
            $producto->update($this->productoService->prepararDatosProducto($request, $brandId, $nombreMarca));

            if ($request->has('imagenes_eliminar')) {
                ImagenProducto::whereIn('id', $request->imagenes_eliminar)->where('producto_id', $producto->id)->delete();
            }

            if ($request->has('orden_imagenes') && is_array($request->orden_imagenes)) {
                foreach ($request->orden_imagenes as $posicion => $imagenId) {
                    if (is_numeric($imagenId)) {
                        ImagenProducto::where('id', $imagenId)->where('producto_id', $producto->id)->update(['orden' => $posicion + 1]);
                    }
                }
            }

            if ($request->filled('imagen_principal_id')) {
                $producto->imagenes()->update(['es_principal' => false]);
                $producto->imagenes()->where('id', $request->imagen_principal_id)->update(['es_principal' => true]);
            }

            $this->productoService->procesarImagenes($request, $producto);
            $this->productoService->guardarVariantes($request, $producto);
        });

        $this->inventarioService->procesarNotificacionesStock($producto);

        return redirect()->route('admin.productos.edit', ['id' => $id] + request()->query())->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $producto = Producto::sinEliminar()->findOrFail($id);
        $producto->update(['eliminado_en' => now()]);

        return redirect()->route('admin.productos.index')->with('success', 'Producto eliminado del catálogo.');
    }

    private function validarRequest(Request $request, ?int $id = null): void
    {
        $slugRule = $id ? "unique:productos,slug,{$id}" : "unique:productos,slug";
        $skuRule = $id ? "unique:productos,sku,{$id}" : "unique:productos,sku";

        $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => "required|string|max:255|{$slugRule}",
            'sku' => "required|string|max:100|{$skuRule}",
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'slug.unique' => 'Ya existe otro producto con ese slug.',
            'sku.unique' => 'Ya existe otro producto con ese SKU.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'precio.required' => 'El precio es obligatorio.',
        ]);
    }
}
