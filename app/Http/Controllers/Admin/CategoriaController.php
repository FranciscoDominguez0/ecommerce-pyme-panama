<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\LogAuditoria;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoriaController extends Controller
{
        public function index(Request $request): View
    {
        $busqueda = trim($request->input('buscar', ''));
        $filtroEstado = $request->input('estado', 'all');
        $totalCategorias = Categoria::sinEliminar()->count();
        $categoriasPrincipalesCount = Categoria::principales()->count();
        $subcategoriasCount = Categoria::sinEliminar()->whereNotNull('padre_id')->count();
        $totalProductosAsignados = Producto::whereNotNull('categoria_id')->whereNull('eliminado_en')->count();
        $query = Categoria::sinEliminar()
            ->with(['padre', 'hijas'])
            ->withCount(['productos' => function ($q) {
                $q->whereNull('eliminado_en');
            }]);
        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->whereRaw('unaccent(nombre) ILIKE unaccent(?)', ["%{$busqueda}%"])
                  ->orWhereRaw('unaccent(slug) ILIKE unaccent(?)', ["%{$busqueda}%"])
                  ->orWhereRaw('unaccent(descripcion) ILIKE unaccent(?)', ["%{$busqueda}%"]);
            });
        }
        if ($filtroEstado === 'active') {
            $query->where('activo', true);
        } elseif ($filtroEstado === 'inactive') {
            $query->where('activo', false);
        }
        if (empty($busqueda)) {
            $query->orderByRaw('COALESCE(padre_id, id), padre_id IS NOT NULL, orden_visualizacion ASC, nombre ASC');
        } else {
            $query->orderBy('orden_visualizacion', 'asc')->orderBy('nombre', 'asc');
        }

        $categorias = $query->paginate(15)->withQueryString();

        return view('admin.categorias.index', compact(
            'categorias',
            'busqueda',
            'filtroEstado',
            'totalCategorias',
            'categoriasPrincipalesCount',
            'subcategoriasCount',
            'totalProductosAsignados'
        ));
    }

        public function create(): View
    {
        $categoria = new Categoria([
            'activo' => true,
            'orden_visualizacion' => 0,
        ]);

        $padres = Categoria::sinEliminar()
            ->with('padre')
            ->orderBy('nombre', 'asc')
            ->get();

        $padresFormatted = $this->formatearPadresJerarquicos($padres);

        $esEdicion = false;

        return view('admin.categorias.form', compact('categoria', 'padres', 'padresFormatted', 'esEdicion'));
    }

        public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'slug' => 'nullable|string|max:120|unique:categorias,slug',
            'padre_id' => 'nullable|integer|exists:categorias,id',
            'descripcion' => 'nullable|string|max:1000',
            'orden_visualizacion' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'exento_envio' => 'nullable|boolean',
            'imagen' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max' => 'El nombre no debe superar los 100 caracteres.',
            'slug.unique' => 'El slug ingresado ya está en uso por otra categoría.',
            'padre_id.exists' => 'La categoría padre seleccionada no es válida.',
            'imagen.mimes' => 'El formato de imagen/ícono debe ser SVG, PNG, JPG o WEBP.',
            'imagen.max' => 'El archivo no debe pesar más de 2MB.',
        ]);
        $slug = $this->generarSlug($validated['nombre'], $validated['slug'] ?? null);
        $imagenRuta = $this->procesarImagen($request);

        $categoria = Categoria::create([
            'nombre' => $validated['nombre'],
            'slug' => $slug,
            'padre_id' => !empty($validated['padre_id']) ? (int) $validated['padre_id'] : null,
            'descripcion' => $validated['descripcion'] ?? null,
            'imagen_ruta' => $imagenRuta,
            'activo' => $request->has('activo') ? (bool) $request->input('activo') : true,
            'exento_envio' => $request->boolean('exento_envio'),
            'orden_visualizacion' => isset($validated['orden_visualizacion']) ? (int) $validated['orden_visualizacion'] : 0,
        ]);
        $this->registrarAuditoria(
            'crear',
            "Categoría '{$categoria->nombre}' creada con éxito.",
            null,
            $categoria->toArray()
        );

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', "Categoría creada exitosamente.");
    }

        public function edit(int $id): View|RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            return redirect()
                ->route('admin.categorias.index')
                ->with('error', 'La categoría solicitada no existe o fue eliminada.');
        }
        $descendientesIds = $this->obtenerIdsDescendientes($categoria);
        $excluirIds = array_merge([$categoria->id], $descendientesIds);

        $padres = Categoria::sinEliminar()
            ->with('padre')
            ->whereNotIn('id', $excluirIds)
            ->orderBy('nombre', 'asc')
            ->get();

        $padresFormatted = $this->formatearPadresJerarquicos($padres);

        $esEdicion = true;

        return view('admin.categorias.form', compact('categoria', 'padres', 'padresFormatted', 'esEdicion'));
    }

        public function update(Request $request, int $id): RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            return redirect()
                ->route('admin.categorias.index')
                ->with('error', 'La categoría no existe o fue eliminada.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'slug' => "nullable|string|max:120|unique:categorias,slug,{$categoria->id}",
            'padre_id' => 'nullable|integer|exists:categorias,id',
            'descripcion' => 'nullable|string|max:1000',
            'orden_visualizacion' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'exento_envio' => 'nullable|boolean',
            'imagen' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'eliminar_imagen' => 'nullable|boolean',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max' => 'El nombre no debe superar los 100 caracteres.',
            'slug.unique' => 'El slug ingresado ya está en uso por otra categoría.',
            'padre_id.exists' => 'La categoría padre seleccionada no es válida.',
            'imagen.mimes' => 'El formato de imagen/ícono debe ser SVG, PNG, JPG o WEBP.',
            'imagen.max' => 'El archivo no debe pesar más de 2MB.',
        ]);

        $padreId = !empty($validated['padre_id']) ? (int) $validated['padre_id'] : null;
        if ($padreId !== null) {
            if ($padreId === $categoria->id) {
                return back()
                    ->withInput()
                    ->withErrors(['padre_id' => 'Una categoría no puede ser padre de sí misma.']);
            }

            $descendientesIds = $this->obtenerIdsDescendientes($categoria);
            if (in_array($padreId, $descendientesIds)) {
                return back()
                    ->withInput()
                    ->withErrors(['padre_id' => 'No puedes seleccionar una subcategoría hija como categoría padre.']);
            }
        }
        $slug = $this->generarSlug($validated['nombre'], $validated['slug'] ?? null, $categoria->id);
        $imagenRuta = $categoria->imagen_ruta;

        if ($request->boolean('eliminar_imagen')) {
            if ($imagenRuta && File::exists(public_path($imagenRuta))) {
                File::delete(public_path($imagenRuta));
            }
            $imagenRuta = null;
        }

        if ($request->hasFile('imagen')) {
            $imagenRuta = $this->procesarImagen($request, $imagenRuta);
        }

        $valorAnterior = $categoria->toArray();

        $categoria->update([
            'nombre' => $validated['nombre'],
            'slug' => $slug,
            'padre_id' => $padreId,
            'descripcion' => $validated['descripcion'] ?? null,
            'imagen_ruta' => $imagenRuta,
            'activo' => $request->has('activo') ? (bool) $request->input('activo') : false,
            'exento_envio' => $request->boolean('exento_envio'),
            'orden_visualizacion' => isset($validated['orden_visualizacion']) ? (int) $validated['orden_visualizacion'] : 0,
        ]);
        $this->registrarAuditoria(
            'actualizar',
            "Categoría '{$categoria->nombre}' actualizada.",
            $valorAnterior,
            $categoria->toArray()
        );

        return redirect()
            ->route('admin.categorias.index', request()->query())
            ->with('success', "Categoría actualizada exitosamente.");
    }

        public function destroy(int $id): RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            return redirect()
                ->route('admin.categorias.index')
                ->with('error', 'La categoría no fue encontrada.');
        }
        $productosAsociados = $categoria->productos()->whereNull('eliminado_en')->count();
        if ($productosAsociados > 0) {
            return redirect()
                ->route('admin.categorias.index')
                ->with('error', "No se puede eliminar '{$categoria->nombre}' porque tiene {$productosAsociados} producto(s) asignado(s). Reasigna los productos primero.");
        }
        $subcategoriasAsociadas = $categoria->hijas()->count();
        if ($subcategoriasAsociadas > 0) {
            return redirect()
                ->route('admin.categorias.index')
                ->with('error', "No se puede eliminar '{$categoria->nombre}' porque tiene {$subcategoriasAsociadas} subcategoría(s) dependiente(s). Reasigna o elimina las subcategorías primero.");
        }

        $valorAnterior = $categoria->toArray();
        $categoria->update([
            'eliminado_en' => Carbon::now(),
            'activo' => false,
        ]);
        $this->registrarAuditoria(
            'eliminar',
            "Categoría '{$categoria->nombre}' (ID #{$categoria->id}) eliminada.",
            $valorAnterior,
            null
        );

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', "Categoría eliminada exitosamente.");
    }

        public function toggleEstado(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Categoría no encontrada.'], 404);
            }
            return redirect()->route('admin.categorias.index')->with('error', 'Categoría no encontrada.');
        }

        $categoria->activo = !$categoria->activo;
        $categoria->save();

        $this->registrarAuditoria(
            'actualizar_estado',
            "Estado de categoría '{$categoria->nombre}' cambiado a: " . ($categoria->activo ? 'Activo' : 'Inactivo'),
            ['activo' => !$categoria->activo],
            ['activo' => $categoria->activo]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo' => $categoria->activo,
                'message' => "Estado actualizado a " . ($categoria->activo ? 'Activo' : 'Inactivo'),
            ]);
        }

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', "Estado actualizado exitosamente.");
    }

    /**
     * Alterna la exención de cobro de envío de una categoría mediante AJAX o formulario rápido.
     * Si la categoría padre tiene activo que no lleva envío, las hijas lo heredan automáticamente.
     */
    public function toggleExentoEnvio(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Categoría no encontrada.'], 404);
            }
            return redirect()->route('admin.categorias.index')->with('error', 'Categoría no encontrada.');
        }

        $nuevoEstado = !$categoria->exento_envio_directo;
        $categoria->exento_envio = $nuevoEstado;
        $categoria->save();

        $descripcion = $nuevoEstado
            ? ($categoria->esPrincipal()
                ? "Categoría '{$categoria->nombre}' marcada como sin envío (aplicado a todas sus subcategorías hijas)."
                : "Subcategoría '{$categoria->nombre}' marcada como sin envío.")
            : ($categoria->esPrincipal()
                ? "Categoría '{$categoria->nombre}' configurada con cobro de envío."
                : "Subcategoría '{$categoria->nombre}' configurada con cobro de envío.");

        $this->registrarAuditoria(
            'actualizar_exento_envio',
            $descripcion,
            ['exento_envio' => !$nuevoEstado],
            ['exento_envio' => $nuevoEstado]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'exento_envio' => $nuevoEstado,
                'message' => $descripcion,
            ]);
        }

        return redirect()
            ->route('admin.categorias.index', request()->query())
            ->with('success', $descripcion);
    }

        
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

    private function obtenerIdsDescendientes(Categoria $categoria): array
    {
        $ids = [];
        $hijas = Categoria::sinEliminar()->where('padre_id', $categoria->id)->get();

        foreach ($hijas as $hija) {
            $ids[] = $hija->id;
            $ids = array_merge($ids, $this->obtenerIdsDescendientes($hija));
        }

        return $ids;
    }

    /**
     * Genera la lista formateada de categorías padre con su ruta jerárquica completa y nivel,
     * ordenadas en estructura de árbol (Padres primero, luego sus hijas/subcategorías agrupadas alfabéticamente).
     */
    private function formatearPadresJerarquicos($padresCollection)
    {
        $mapaCategorias = Categoria::sinEliminar()->get()->keyBy('id');
        $porPadre = $padresCollection->groupBy(function ($cat) {
            return $cat->padre_id ?? 'root';
        });
        $porPadre->transform(function ($grupo) {
            return $grupo->sortBy(function ($item) {
                return strtolower($item->nombre);
            });
        });

        $ordenadoJerarquico = collect();
        $agregarConHijos = function ($padreId) use (&$agregarConHijos, $porPadre, &$ordenadoJerarquico) {
            $key = $padreId ?? 'root';
            if ($porPadre->has($key)) {
                foreach ($porPadre->get($key) as $categoria) {
                    $ordenadoJerarquico->push($categoria);
                    $agregarConHijos($categoria->id);
                }
            }
        };
        $agregarConHijos(null);
        $incluidosIds = $ordenadoJerarquico->pluck('id')->all();
        $huerfanas = $padresCollection->reject(function ($cat) use ($incluidosIds) {
            return in_array($cat->id, $incluidosIds);
        })->sortBy(function ($item) {
            return strtolower($item->nombre);
        });

        $coleccionFinal = $ordenadoJerarquico->concat($huerfanas);

        return $coleccionFinal->values()->map(function ($cat) use ($mapaCategorias) {
            $ancestros = [];
            $actualId = $cat->padre_id;

            while ($actualId && isset($mapaCategorias[$actualId])) {
                $padreObj = $mapaCategorias[$actualId];
                array_unshift($ancestros, $padreObj->nombre);
                $actualId = $padreObj->padre_id;
            }

            $rutaPadres = !empty($ancestros) ? implode(' > ', $ancestros) : null;
            $nivel = count($ancestros);

            return [
                'id' => (int) $cat->id,
                'nombre' => $cat->nombre,
                'padre_nombre' => $cat->padre ? $cat->padre->nombre : null,
                'ruta_jerarquica' => $rutaPadres ? "{$rutaPadres} > {$cat->nombre}" : $cat->nombre,
                'ruta_padres' => $rutaPadres,
                'nivel' => $nivel,
                'activo' => (bool) $cat->activo,
            ];
        });
    }

        private function registrarAuditoria(string $accion, string $descripcion, ?array $valorAnterior = null, ?array $valorNuevo = null): void
    {
        try {
            LogAuditoria::create([
                'usuario_id' => Auth::id(),
                'modulo' => 'Categorías',
                'accion' => $accion,
                'descripcion' => $descripcion,
                'valor_anterior' => $valorAnterior ? json_encode($valorAnterior) : null,
                'valor_nuevo' => $valorNuevo ? json_encode($valorNuevo) : null,
                'ip' => request()->ip(),
                'agente_usuario' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
        }
    }
}
