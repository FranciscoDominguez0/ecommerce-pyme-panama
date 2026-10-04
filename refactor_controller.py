import re
import os

path = r"C:\Users\Proyectos\ecommerce-pyme-panama\app\Http\Controllers\Admin\ProductoController.php"

with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

helpers = """
    private function resolverMarca(Request $request): array
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

    private function prepararDatosProducto(Request $request, ?int $brandId, ?string $nombreMarca): array
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

    private function asegurarImagenPrincipal(Producto $producto): void
    {
        if ($producto->imagenes()->exists() && !$producto->imagenes()->where('es_principal', true)->exists()) {
            $primera = ImagenProducto::where('producto_id', $producto->id)->orderBy('orden')->first();
            if ($primera) {
                $primera->update(['es_principal' => true]);
            }
        }
    }
"""

# Insert before 'private function guardarImagenesUrl'
content = content.replace('private function guardarImagenesUrl', helpers + '\n    private function guardarImagenesUrl')

# Replace store logic
store_pattern = r'\$brandId = null;.*?return \$producto;\n\s*\}\);'
store_replacement = """list($brandId, $nombreMarca) = $this->resolverMarca($request);

        $producto = DB::transaction(function () use ($request, $brandId, $nombreMarca) {
            $producto = Producto::create($this->prepararDatosProducto($request, $brandId, $nombreMarca));
            $this->guardarImagenesUrl($request, $producto);
            $this->guardarImagenesArchivos($request, $producto);
            $this->asegurarImagenPrincipal($producto);
            $this->guardarVariantes($request, $producto);
            return $producto;
        });"""

content = re.sub(store_pattern, store_replacement, content, count=1, flags=re.DOTALL)

# Replace update logic
update_pattern = r'\$brandId = null;.*?\n\s*\$this->guardarVariantes\(\$request, \$producto\);\n\s*\}\);'
update_replacement = """list($brandId, $nombreMarca) = $this->resolverMarca($request);

        DB::transaction(function () use ($request, $producto, $brandId, $nombreMarca) {
            $producto->update($this->prepararDatosProducto($request, $brandId, $nombreMarca));

            if ($request->has('imagenes_eliminar')) {
                ImagenProducto::whereIn('id', $request->imagenes_eliminar)
                    ->where('producto_id', $producto->id)
                    ->delete();
            }

            if ($request->has('orden_imagenes') && is_array($request->orden_imagenes)) {
                foreach ($request->orden_imagenes as $posicion => $imagenId) {
                    if (is_numeric($imagenId)) {
                        ImagenProducto::where('id', $imagenId)
                            ->where('producto_id', $producto->id)
                            ->update(['orden' => $posicion + 1]);
                    }
                }
            }

            if ($request->filled('imagen_principal_id')) {
                $producto->imagenes()->update(['es_principal' => false]);
                $producto->imagenes()->where('id', $request->imagen_principal_id)->update(['es_principal' => true]);
            }

            $this->guardarImagenesUrl($request, $producto);
            $this->guardarImagenesArchivos($request, $producto);
            $this->asegurarImagenPrincipal($producto);
            $this->guardarVariantes($request, $producto);
        });"""

content = re.sub(update_pattern, update_replacement, content, count=1, flags=re.DOTALL)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
