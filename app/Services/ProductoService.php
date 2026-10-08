<?php

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
            'especificaciones' => is_string($request->especificaciones) ? json_decode($request->especificaciones, true) : $request->especificaciones,
            'peso' => $request->peso,
            'dimension_largo' => $request->dimension_largo,
            'dimension_ancho' => $request->dimension_ancho,
            'dimension_alto' => $request->dimension_alto,
            'garantia_info' => is_string($request->garantia_info) ? json_decode($request->garantia_info, true) : $request->garantia_info,
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
            $lineas = array_filter(array_map('trim', explode("\n", $request->imagen_url)));
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
