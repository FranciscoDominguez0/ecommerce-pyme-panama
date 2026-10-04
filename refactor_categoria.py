import re
import os

path = r"C:\Users\Proyectos\ecommerce-pyme-panama\app\Http\Controllers\Admin\CategoriaController.php"

with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Remove unnecessary docblocks
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Muestra el listado de categorías.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Muestra el formulario para crear.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Almacena una nueva categoría.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Muestra el formulario para editar.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Actualiza una categoría existente.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Elimina \(soft-delete\) una categoría.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Alterna el estado activo\/inactivo.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Alterna la exención de cobro.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Obtiene recursivamente todos los IDs.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Genera la lista formateada de categorías.*?\n\s*\*\/\s*\n', '', content)
content = re.sub(r'\/\*\*\s*\n\s*\*\s*Helper para registrar auditoría.*?\n\s*\*\/\s*\n', '', content)

# Remove simple comments
content = re.sub(r'\s*\/\/ Métricas KPI de cabecera\n', '\n', content)
content = re.sub(r'\s*\/\/ Query principal\n', '\n', content)
content = re.sub(r'\s*\/\/ Filtro por búsqueda\n', '\n', content)
content = re.sub(r'\s*\/\/ Filtro por estado\n', '\n', content)
content = re.sub(r'\s*\/\/ Orden jerárquico inteligente:.*?\n\s*\/\/.*?\n', '\n', content)
content = re.sub(r'\s*\/\/ Generar slug automático si no fue provisto\n', '\n', content)
content = re.sub(r'\s*\/\/ Manejo de carga de imagen\n', '\n', content)
content = re.sub(r'\s*\/\/ Registro de Auditoría\n', '\n', content)
content = re.sub(r'\s*\/\/ Excluir la categoría actual y todas sus descendientes para prevenir ciclos\n', '\n', content)
content = re.sub(r'\s*\/\/ Validar prevención de ciclos\n', '\n', content)
content = re.sub(r'\s*\/\/ Slug\n', '\n', content)
content = re.sub(r'\s*\/\/ Eliminar imagen si se marcó la opción\n', '\n', content)
content = re.sub(r'\s*\/\/ Manejo de nueva imagen\n', '\n', content)
content = re.sub(r'\s*\/\/ Eliminar imagen anterior\n', '\n', content)
content = re.sub(r'\s*\/\/ Auditoría\n', '\n', content)
content = re.sub(r'\s*\/\/ Validación 1: Verificar si tiene productos asociados\n', '\n', content)
content = re.sub(r'\s*\/\/ Validación 2: Verificar si tiene subcategorías hijas\n', '\n', content)
content = re.sub(r'\s*\/\/ Soft delete manual consistente\n', '\n', content)
content = re.sub(r'\s*\/\/ Agrupar categorías por su padre_id\n', '\n', content)
content = re.sub(r'\s*\/\/ Ordenar cada grupo alfabéticamente por nombre\n', '\n', content)
content = re.sub(r'\s*\/\/ Recorrido recursivo en profundidad \(DFS\)\n', '\n', content)
content = re.sub(r'\s*\/\/ Comenzar por las categorías raíz\n', '\n', content)
content = re.sub(r'\s*\/\/ Incluir categorías cuya categoría padre haya sido excluida.*?\n', '\n', content)
content = re.sub(r'\s*\/\/ No romper el flujo principal si el log de auditoría falla\n', '\n', content)


helpers = """
    private function procesarImagen(Request $request, ?string $rutaAnterior = null): ?string
    {
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            if ($rutaAnterior && File::exists(public_path($rutaAnterior))) {
                File::delete(public_path($rutaAnterior));
            }
            $file = $request->file('imagen');
            $fileName = 'cat_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/categorias');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }
            $file->move($destinationPath, $fileName);
            return 'uploads/categorias/' . $fileName;
        }
        return $rutaAnterior;
    }

    private function generarSlug(string $nombre, ?string $slugBase, ?int $ignorarId = null): string
    {
        $slug = !empty($slugBase) ? Str::slug($slugBase) : Str::slug($nombre);
        $slugOriginal = $slug;
        $contador = 1;
        $query = Categoria::where('slug', $slug);
        if ($ignorarId) {
            $query->where('id', '!=', $ignorarId);
        }
        while ($query->exists()) {
            $slug = "{$slugOriginal}-{$contador}";
            $contador++;
            $query = Categoria::where('slug', $slug);
            if ($ignorarId) {
                $query->where('id', '!=', $ignorarId);
            }
        }
        return $slug;
    }
"""

content = content.replace('private function obtenerIdsDescendientes', helpers + '\n    private function obtenerIdsDescendientes')

store_pattern = r'\$slug = !empty.*?\$imagenRuta = \'uploads/categorias/\' \. \$fileName;\n\s*\}'
store_replacement = """$slug = $this->generarSlug($validated['nombre'], $validated['slug'] ?? null);
        $imagenRuta = $this->procesarImagen($request);"""
content = re.sub(store_pattern, store_replacement, content, count=1, flags=re.DOTALL)


update_pattern = r'\$slug = !empty.*?\$imagenRuta = \'uploads/categorias/\' \. \$fileName;\n\s*\}'
update_replacement = """$slug = $this->generarSlug($validated['nombre'], $validated['slug'] ?? null, $categoria->id);
        $imagenRuta = $categoria->imagen_ruta;

        if ($request->boolean('eliminar_imagen')) {
            if ($imagenRuta && File::exists(public_path($imagenRuta))) {
                File::delete(public_path($imagenRuta));
            }
            $imagenRuta = null;
        }

        if ($request->hasFile('imagen')) {
            $imagenRuta = $this->procesarImagen($request, $imagenRuta);
        }"""
content = re.sub(update_pattern, update_replacement, content, count=1, flags=re.DOTALL)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
