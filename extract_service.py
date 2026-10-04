import re
import os

controller_path = r"C:\Users\Proyectos\ecommerce-pyme-panama\app\Http\Controllers\Admin\ProductoController.php"
service_path = r"C:\Users\Proyectos\ecommerce-pyme-panama\app\Services\ProductoService.php"

# Leer controlador actual
with open(controller_path, 'r', encoding='utf-8') as f:
    controller_content = f.read()

# Crear el Service
service_content = """<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Categoria;
use App\Models\ImagenProducto;
use App\Models\Producto;
use App\Models\TipoVariante;
use App\Models\OpcionVariante;
use App\Models\VarianteProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductoService
{
    public function obtenerDatosFormulario(Producto $producto, bool $esEdicion): array
    {
        $categorias = Categoria::sinEliminar()->orderBy('nombre')->get();
        $marcas = Brand::orderBy('name', 'asc')->get();
        $tiposVariante = TipoVariante::with('opciones')->get();

        $marcasData = $marcas->map(fn($m) => [
            'id' => $m->id,
            'nombre' => $m->name,
            'slug' => $m->slug,
            'url' => $m->logo_url,
            'verified' => (bool) $m->verified,
        ])->values()->toArray();

        $categoriasData = $categorias->map(fn($c) => [
            'id' => (string) $c->id,
            'nombre' => $c->nombre,
            'slug' => $c->slug ?? '',
            'imagen_ruta' => $c->imagen_ruta ?? '',
        ])->values()->toArray();

        $catalogoAtributos = [];
        foreach ($tiposVariante as $tipo) {
            $opcs = [];
            $hexs = [];
            foreach ($tipo->opciones as $opc) {
                $opcs[] = $opc->valor;
                if (!empty($opc->valor_hex)) {
                    $hexs[$opc->valor] = $opc->valor_hex;
                }
            }
            $catalogoAtributos[$tipo->nombre] = ['opciones' => $opcs, 'hex' => $hexs];
        }

        $atributosIniciales = [];
        $variantesExistentesData = [];

        if ($esEdicion && $producto->variantes && $producto->variantes->count() > 0) {
            $map = [];
            foreach ($producto->variantes as $variante) {
                $attrs = [];
                foreach ($variante->opciones as $opcion) {
                    $tipoNombre = $opcion->tipo->nombre;
                    $attrs[$tipoNombre] = $opcion->valor;

                    if (!isset($map[$tipoNombre])) {
                        $map[$tipoNombre] = [];
                    }
                    if (!in_array($opcion->valor, $map[$tipoNombre])) {
                        $map[$tipoNombre][] = $opcion->valor;
                    }
                }
                $variantesExistentesData[] = [
                    'sku' => $variante->sku,
                    'precio' => $variante->precio,
                    'stock' => $variante->stock,
                    'atributos' => $attrs,
                ];
            }
            foreach ($map as $nombre => $seleccionadas) {
                $atributosIniciales[] = ['nombre' => $nombre, 'seleccionadas' => $seleccionadas];
            }
        }

        return compact(
            'esEdicion',
            'producto',
            'categorias',
            'marcas',
            'marcasData',
            'categoriasData',
            'catalogoAtributos',
            'atributosIniciales',
            'variantesExistentesData'
        );
    }

    public function resolverMarca(Request $request): array
    {
        $brandId = null;
        $nombreMarca = null;

        if ($request->filled('brand_id') && is_numeric($request->brand_id)) {
            $brand = Brand::find($request->brand_id);
            if ($brand) {
                $brandId = $brand->id;
                $nombreMarca = $brand->name;
            }
        } elseif ($request->filled('marca')) {
            $nombreMarca = trim($request->marca);
            $brand = Brand::where('name', 'ILIKE', $nombreMarca)
                ->orWhere('slug', 'ILIKE', Str::slug($nombreMarca))
                ->first();
            if ($brand) {
                $brandId = $brand->id;
                $nombreMarca = $brand->name;
            }
        }
        return [$brandId, $nombreMarca];
    }

    public function calcularStockTotal(Request $request): int
    {
        if (!$request->boolean('tiene_variantes')) {
            return (int) ($request->stock ?? 0);
        }

        $stockTotal = 0;
        $variantesData = $request->input('variantes', []);

        if ($request->filled('variantes_json')) {
            $decoded = json_decode($request->input('variantes_json'), true);
            if (is_array($decoded) && count($decoded) > 0) {
                $variantesData = $decoded;
            }
        }

        foreach ($variantesData as $data) {
            $stockTotal += (int) ($data['stock'] ?? 0);
        }

        return $stockTotal;
    }

    public function prepararDatosProducto(Request $request, ?int $brandId, ?string $nombreMarca): array
    {
        return [
            'categoria_id' => $request->categoria_id,
            'brand_id' => $brandId,
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->slug),
            'descripcion' => $request->descripcion ?? '',
            'descripcion_corta' => $request->descripcion_corta ?? '',
            'sku' => strtoupper($request->sku),
            'marca' => $nombreMarca,
            'modelo' => $request->modelo ? trim($request->modelo) : null,
            'precio' => $request->precio,
            'precio_oferta' => $request->precio_oferta ?: null,
            'oferta_activa' => $request->boolean('oferta_activa'),
            'oferta_inicio_en' => $request->oferta_inicio_en ?: null,
            'oferta_fin_en' => $request->oferta_fin_en ?: null,
            'stock' => $this->calcularStockTotal($request),
            'stock_minimo' => (int) ($request->stock_minimo ?? 3),
            'destacado' => $request->boolean('destacado'),
            'activo' => $request->boolean('activo'),
            'aplica_itbms' => $request->boolean('aplica_itbms'),
        ];
    }

    public function procesarImagenes(Request $request, Producto $producto): void
    {
        $this->guardarImagenesUrl($request, $producto);
        $this->guardarImagenesArchivos($request, $producto);
        
        if ($producto->imagenes()->exists() && !$producto->imagenes()->where('es_principal', true)->exists()) {
            $primera = ImagenProducto::where('producto_id', $producto->id)->orderBy('orden')->first();
            if ($primera) {
                $primera->update(['es_principal' => true]);
            }
        }
    }

    private function guardarImagenesUrl(Request $request, Producto $producto): void
    {
        $urls = [];

        if ($request->has('imagenes_url') && is_array($request->imagenes_url)) {
            foreach ($request->imagenes_url as $url) {
                $url = trim($url);
                if (!empty($url)) $urls[] = $url;
            }
        }

        if ($request->filled('imagen_url')) {
            $lineas = array_filter(array_map('trim', explode("\\n", $request->imagen_url)));
            foreach ($lineas as $l) {
                if (!empty($l)) $urls[] = $l;
            }
        }

        if (empty($urls)) return;

        $tienePrincipal = $producto->imagenes()->where('es_principal', true)->exists();
        $maxOrden = (int) $producto->imagenes()->max('orden');

        foreach ($urls as $i => $url) {
            $esPrincipal = !$tienePrincipal && $i === 0;
            $producto->imagenes()->create([
                'ruta' => $url,
                'es_principal' => $esPrincipal,
                'orden' => $maxOrden + 1 + $i,
            ]);
            if ($esPrincipal) $tienePrincipal = true;
        }
    }

    private function guardarImagenesArchivos(Request $request, Producto $producto): void
    {
        if (!$request->hasFile('imagenes')) return;

        $tienePrincipal = $producto->imagenes()->where('es_principal', true)->exists();
        $maxOrden = (int) $producto->imagenes()->max('orden');

        foreach ($request->file('imagenes') as $i => $archivo) {
            if (!$archivo->isValid()) continue;

            $ruta = $archivo->store("productos/{$producto->id}", 'public');
            $esPrincipal = !$tienePrincipal && $i === 0;

            $producto->imagenes()->create([
                'ruta' => 'storage/' . $ruta,
                'es_principal' => $esPrincipal,
                'orden' => $maxOrden + 1 + $i,
            ]);

            if ($esPrincipal) $tienePrincipal = true;
        }
    }

    public function guardarVariantes(Request $request, Producto $producto): void
    {
        if (!$request->boolean('tiene_variantes')) {
            $producto->variantes()->delete();
            return;
        }

        $variantesData = $request->input('variantes', []);

        if ($request->filled('variantes_json')) {
            $decoded = json_decode($request->input('variantes_json'), true);
            if (is_array($decoded) && count($decoded) > 0) {
                $variantesData = $decoded;
            }
        }

        $producto->variantes()->delete();

        foreach ($variantesData as $data) {
            if (!isset($data['sku'])) continue;

            $variante = VarianteProducto::create([
                'producto_id' => $producto->id,
                'sku' => $data['sku'],
                'precio' => $data['precio'] ?? $producto->precio,
                'stock' => $data['stock'] ?? 0,
            ]);

            $opcionesIds = [];
            if (isset($data['atributos']) && is_array($data['atributos'])) {
                foreach ($data['atributos'] as $attrNombre => $opcionValor) {
                    $tipo = TipoVariante::firstOrCreate(['nombre' => $attrNombre]);
                    $opcion = OpcionVariante::firstOrCreate([
                        'tipo_variante_id' => $tipo->id,
                        'valor' => $opcionValor
                    ]);
                    $opcionesIds[] = $opcion->id;
                }
            }

            if (!empty($opcionesIds)) {
                $variante->opciones()->attach($opcionesIds);
            }
        }
    }
}
"""

with open(service_path, 'w', encoding='utf-8') as f:
    f.write(service_content)

# Refactorizar el Controlador para usar ProductoService
new_controller = """<?php

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
"""

with open(controller_path, 'w', encoding='utf-8') as f:
    f.write(new_controller)

