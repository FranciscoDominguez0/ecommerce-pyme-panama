<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Services\CategoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function __construct(
        protected CategoriaService $categoriaService
    ) {}

    // Muestra el listado de categorías con métricas y filtros
    public function index(Request $request): View
    {
        $busqueda = trim($request->input('buscar', ''));
        $filtroEstado = $request->input('estado', 'all');

        $metricas = $this->categoriaService->obtenerMetricas();
        $categorias = $this->categoriaService->obtenerCategoriasListado($busqueda, $filtroEstado, 15);

        return view('admin.categorias.index', array_merge([
            'categorias' => $categorias,
            'busqueda' => $busqueda,
            'filtroEstado' => $filtroEstado,
        ], $metricas));
    }

    // Muestra el formulario para crear una nueva categoría
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

        $padresFormatted = $this->categoriaService->formatearPadresJerarquicos($padres);
        $esEdicion = false;

        return view('admin.categorias.form', compact('categoria', 'padres', 'padresFormatted', 'esEdicion'));
    }

    // Guarda una nueva categoría
    public function store(Request $request): RedirectResponse
    {
        $validados = $request->validate([
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

        $this->categoriaService->crear($validados, $request->file('imagen'));

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    // Muestra el formulario para editar una categoría existente
    public function edit(int $id): View|RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            return redirect()
                ->route('admin.categorias.index')
                ->with('error', 'La categoría solicitada no existe o fue eliminada.');
        }

        $descendientesIds = $this->categoriaService->obtenerIdsDescendientes($categoria);
        $excluirIds = array_merge([$categoria->id], $descendientesIds);

        $padres = Categoria::sinEliminar()
            ->with('padre')
            ->whereNotIn('id', $excluirIds)
            ->orderBy('nombre', 'asc')
            ->get();

        $padresFormatted = $this->categoriaService->formatearPadresJerarquicos($padres);
        $esEdicion = true;

        return view('admin.categorias.form', compact('categoria', 'padres', 'padresFormatted', 'esEdicion'));
    }

    // Actualiza los datos de la categoría
    public function update(Request $request, int $id): RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            return redirect()
                ->route('admin.categorias.index')
                ->with('error', 'La categoría no existe o fue eliminada.');
        }

        $validados = $request->validate([
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

        $padreId = !empty($validados['padre_id']) ? (int) $validados['padre_id'] : null;
        if ($padreId !== null) {
            if ($padreId === $categoria->id) {
                return back()
                    ->withInput()
                    ->withErrors(['padre_id' => 'Una categoría no puede ser padre de sí misma.']);
            }

            $descendientesIds = $this->categoriaService->obtenerIdsDescendientes($categoria);
            if (in_array($padreId, $descendientesIds, true)) {
                return back()
                    ->withInput()
                    ->withErrors(['padre_id' => 'No puedes seleccionar una subcategoría hija como categoría padre.']);
            }
        }

        $this->categoriaService->actualizar(
            $categoria,
            $validados,
            $request->file('imagen'),
            $request->boolean('eliminar_imagen')
        );

        return redirect()
            ->route('admin.categorias.index', request()->query())
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    // Elimina lógicamente la categoría si no posee productos o subcategorías
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

        $this->categoriaService->eliminar($categoria);

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    // Alterna el estado activo/inactivo vía petición estándar o AJAX
    public function alternarEstado(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Categoría no encontrada.'], 404);
            }
            return redirect()->route('admin.categorias.index')->with('error', 'Categoría no encontrada.');
        }

        $activo = $this->categoriaService->alternarEstado($categoria);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo' => $activo,
                'message' => 'Estado actualizado a ' . ($activo ? 'Activo' : 'Inactivo'),
            ]);
        }

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Estado actualizado exitosamente.');
    }

    // Alterna la configuración de cobro de envío para la categoría y sus hijas
    public function alternarExentoEnvio(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $categoria = Categoria::sinEliminar()->find($id);

        if (!$categoria) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Categoría no encontrada.'], 404);
            }
            return redirect()->route('admin.categorias.index')->with('error', 'Categoría no encontrada.');
        }

        $nuevoEstado = $this->categoriaService->alternarExentoEnvio($categoria);

        $mensaje = $nuevoEstado
            ? "Categoría '{$categoria->nombre}' marcada como sin envío."
            : "Categoría '{$categoria->nombre}' configurada con cobro de envío.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'exento_envio' => $nuevoEstado,
                'message' => $mensaje,
            ]);
        }

        return redirect()
            ->route('admin.categorias.index', request()->query())
            ->with('success', $mensaje);
    }
}
