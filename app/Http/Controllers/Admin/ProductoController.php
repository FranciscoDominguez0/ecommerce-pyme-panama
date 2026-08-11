<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Categoria;
use App\Models\ImagenProducto;
use App\Models\Producto;
use App\Models\TipoVariante;
use App\Models\OpcionVariante;
use App\Models\VarianteProducto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Muestra el listado de productos con filtros, métricas KPI y tabla interactiva desde la BD.
     */
    public function index(Request $request): View
    {
        $buscar = $request->input('buscar', '');
        $buscarSku = $request->input('sku', '');
        $categoriaId = $request->input('categoria_id', 'all');
        $filtroEstado = $request->input('estado', 'all');
        $filtroStock = $request->input('stock', 'all');

        // Métricas KPI
        $kpiTotal = Producto::sinEliminar()->count();
        $kpiEnStock = Producto::sinEliminar()->where('stock', '>', 5)->count();
        $kpiStockBajo = Producto::sinEliminar()->where('stock', '<=', 5)->count();
        $kpiVariantes = VarianteProducto::count();

        // Categorías para el filtro
        $categorias = Categoria::sinEliminar()->orderBy('nombre')->get();

        // Consulta base con eager loading
        $query = Producto::with(['categoria', 'imagenes', 'variantes.opciones.tipo', 'brand'])
            ->withCount('variantes')
            ->sinEliminar();

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('descripcion_corta', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        if (!empty($buscarSku)) {
            $query->where('sku', 'like', "%{$buscarSku}%");
        }

        if ($categoriaId !== 'all' && is_numeric($categoriaId)) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($filtroEstado !== 'all') {
            $query->where('activo', $filtroEstado === 'activo' || $filtroEstado === '1');
        }

        if ($filtroStock !== 'all') {
            if ($filtroStock === 'en_stock') {
                $query->where('stock', '>', 5);
            } elseif ($filtroStock === 'bajo_stock') {
                $query->whereBetween('stock', [1, 5]);
            } elseif ($filtroStock === 'agotado') {
                $query->where('stock', '<=', 0);
            }
        }

        $productos = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.productos.index', compact(
            'productos',
            'kpiTotal',
            'kpiEnStock',
            'kpiStockBajo',
            'kpiVariantes',
            'buscar',
            'buscarSku',
            'categoriaId',
            'filtroEstado',
            'filtroStock',
            'categorias'
        ));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
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

        $datos = $this->obtenerDatosFormulario($producto, false);
        $datos['imagenes'] = collect();

        return view('admin.productos.form', $datos);
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:productos,slug',
            'sku' => 'required|string|max:100|unique:productos,sku',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'slug.unique' => 'Ya existe otro producto con ese slug.',
            'sku.unique' => 'Ya existe otro producto con ese SKU.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'precio.required' => 'El precio es obligatorio.',
        ]);

        // Resolver marca y brand_id
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

        $producto = DB::transaction(function () use ($request, $brandId, $nombreMarca) {
            $producto = Producto::create([
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
                'stock' => (int) ($request->stock ?? 0),
                'stock_minimo' => (int) ($request->stock_minimo ?? 3),
                'destacado' => $request->boolean('destacado'),
                'activo' => $request->boolean('activo'),
                'aplica_itbms' => $request->boolean('aplica_itbms'),
            ]);

            // Imágenes por URL
            $this->guardarImagenesUrl($request, $producto);

            // Imágenes por archivo
            $this->guardarImagenesArchivos($request, $producto);

            // Asegurar al menos una imagen principal si existen imágenes
            if ($producto->imagenes()->exists() && !$producto->imagenes()->where('es_principal', true)->exists()) {
                $primera = ImagenProducto::where('producto_id', $producto->id)->orderBy('orden')->first();
                if ($primera) {
                    $primera->update(['es_principal' => true]);
                }
            }

            // Guardar variantes si aplica
            $this->guardarVariantes($request, $producto);

            return $producto;
        });

        return redirect()
            ->route('admin.productos.index')
            ->with('success', 'Producto creado exitosamente y publicado en la tienda.');
    }

    /**
     * Muestra el formulario para editar un producto existente.
     */
    public function edit(int $id): View
    {
        $producto = Producto::with(['imagenes', 'variantes.opciones.tipo', 'categoria', 'brand'])
            ->sinEliminar()
            ->findOrFail($id);

        $datos = $this->obtenerDatosFormulario($producto, true);
        $datos['imagenes'] = $producto->imagenes;
        $datos['id'] = $id;

        return view('admin.productos.form', $datos);
    }

    /**
     * Centraliza las consultas a la BD y el formateo de datos para el formulario (MVC estricto).
     */
    private function obtenerDatosFormulario(Producto $producto, bool $esEdicion): array
    {
        $categorias = Categoria::sinEliminar()->orderBy('nombre')->get();
        $marcas = Brand::orderBy('name', 'asc')->get();
        $tiposVariante = TipoVariante::with('opciones')->get();

        $marcasData = $marcas->map(function ($m) {
            return [
                'id' => $m->id,
                'nombre' => $m->name,
                'slug' => $m->slug,
                'url' => $m->logo_url,
                'verified' => (bool) $m->verified,
            ];
        })->values()->toArray();

        $categoriasData = $categorias->map(function ($c) {
            return [
                'id' => (string) $c->id,
                'nombre' => $c->nombre,
                'slug' => $c->slug ?? '',
                'imagen_ruta' => $c->imagen_ruta ?? '',
            ];
        })->values()->toArray();

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
            $catalogoAtributos[$tipo->nombre] = [
                'opciones' => $opcs,
                'hex' => $hexs,
            ];
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
                $atributosIniciales[] = [
                    'nombre' => $nombre,
                    'seleccionadas' => $seleccionadas,
                ];
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

    /**
     * Actualiza un producto existente en la base de datos.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $producto = Producto::sinEliminar()->findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'slug' => "required|string|max:255|unique:productos,slug,{$producto->id}",
            'sku' => "required|string|max:100|unique:productos,sku,{$producto->id}",
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'slug.unique' => 'Ya existe otro producto con ese slug.',
            'sku.unique' => 'Ya existe otro producto con ese SKU.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'precio.required' => 'El precio es obligatorio.',
        ]);

        // Resolver marca y brand_id
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

        DB::transaction(function () use ($request, $producto, $brandId, $nombreMarca) {
            $producto->update([
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
                'stock' => (int) ($request->stock ?? 0),
                'stock_minimo' => (int) ($request->stock_minimo ?? 3),
                'destacado' => $request->boolean('destacado'),
                'activo' => $request->boolean('activo'),
                'aplica_itbms' => $request->boolean('aplica_itbms'),
            ]);

            // Eliminar imágenes marcadas para borrar
            if ($request->has('imagenes_eliminar')) {
                ImagenProducto::whereIn('id', $request->imagenes_eliminar)
                    ->where('producto_id', $producto->id)
                    ->delete();
            }

            // Actualizar el orden de las imágenes existentes según el orden en que se enviaron
            if ($request->has('orden_imagenes') && is_array($request->orden_imagenes)) {
                foreach ($request->orden_imagenes as $posicion => $imagenId) {
                    if (is_numeric($imagenId)) {
                        ImagenProducto::where('id', $imagenId)
                            ->where('producto_id', $producto->id)
                            ->update(['orden' => $posicion + 1]);
                    }
                }
            }

            // Actualizar imagen principal si se seleccionó una existente
            if ($request->filled('imagen_principal_id')) {
                $producto->imagenes()->update(['es_principal' => false]);
                $producto->imagenes()->where('id', $request->imagen_principal_id)->update(['es_principal' => true]);
            }

            // Agregar nuevas imágenes por URL
            $this->guardarImagenesUrl($request, $producto);

            // Agregar nuevas imágenes por archivo
            $this->guardarImagenesArchivos($request, $producto);

            // Asegurar que al menos una imagen sea principal si existen imágenes
            if ($producto->imagenes()->exists() && !$producto->imagenes()->where('es_principal', true)->exists()) {
                $primera = ImagenProducto::where('producto_id', $producto->id)->orderBy('orden')->first();
                if ($primera) {
                    $primera->update(['es_principal' => true]);
                }
            }

            // Guardar variantes si aplica
            $this->guardarVariantes($request, $producto);
        });

        return redirect()
            ->route('admin.productos.edit', $id)
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Elimina suavemente un producto (soft delete).
     */
    public function destroy(int $id): RedirectResponse
    {
        $producto = Producto::sinEliminar()->findOrFail($id);
        $producto->update(['eliminado_en' => now()]);

        return redirect()
            ->route('admin.productos.index')
            ->with('success', 'Producto eliminado del catálogo.');
    }

    // ─── Helpers Privados ─────────────────────────────────────────────────────

    /**
     * Guarda imágenes enviadas como URL (array imagenes_url[] o texto imagen_url).
     */
    private function guardarImagenesUrl(Request $request, Producto $producto): void
    {
        $urls = [];

        // 1. Array de URLs desde las tarjetas dinámicas de la galería
        if ($request->has('imagenes_url') && is_array($request->imagenes_url)) {
            foreach ($request->imagenes_url as $url) {
                $url = trim($url);
                if (!empty($url)) {
                    $urls[] = $url;
                }
            }
        }

        // 2. Campo de texto directo (compatibilidad)
        if ($request->filled('imagen_url')) {
            $lineas = array_filter(array_map('trim', explode("\n", $request->imagen_url)));
            foreach ($lineas as $l) {
                if (!empty($l)) {
                    $urls[] = $l;
                }
            }
        }

        if (empty($urls)) {
            return;
        }

        $tienePrincipal = $producto->imagenes()->where('es_principal', true)->exists();
        $maxOrden = (int) $producto->imagenes()->max('orden');

        foreach ($urls as $i => $url) {
            $esPrincipal = !$tienePrincipal && $i === 0;
            $producto->imagenes()->create([
                'ruta' => $url,
                'es_principal' => $esPrincipal,
                'orden' => $maxOrden + 1 + $i,
            ]);
            if ($esPrincipal) {
                $tienePrincipal = true;
            }
        }
    }

    /**
     * Guarda imágenes subidas como archivos físicos en storage.
     */
    private function guardarImagenesArchivos(Request $request, Producto $producto): void
    {
        if (!$request->hasFile('imagenes')) {
            return;
        }

        $tienePrincipal = $producto->imagenes()->where('es_principal', true)->exists();
        $maxOrden = (int) $producto->imagenes()->max('orden');

        foreach ($request->file('imagenes') as $i => $archivo) {
            if (!$archivo->isValid())
                continue;

            $ruta = $archivo->store("productos/{$producto->id}", 'public');
            $esPrincipal = !$tienePrincipal && $i === 0;

            $producto->imagenes()->create([
                'ruta' => 'storage/' . $ruta,
                'es_principal' => $esPrincipal,
                'orden' => $maxOrden + 1 + $i,
            ]);

            if ($esPrincipal)
                $tienePrincipal = true;
        }
    }

    /**
     * Procesa y guarda las variantes y opciones del producto.
     */
    private function guardarVariantes(Request $request, Producto $producto): void
    {
        if (!$request->boolean('tiene_variantes')) {
            // Eliminar variantes existentes si se desactivó
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

        // Estrategia simple: limpiar y recrear las variantes
        $producto->variantes()->delete();

        foreach ($variantesData as $data) {
            if (!isset($data['sku']))
                continue;

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
