<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Producto;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(
        protected BrandService $brandService
    ) {}

    // Muestra el listado de marcas con métricas, búsqueda y filtros
    public function index(Request $request): View
    {
        $busqueda = trim($request->input('buscar', ''));
        $filtroVerificada = $request->input('verificada', 'all');

        $totalMarcas = Brand::count();
        $totalVerificadas = Brand::where('verified', true)->count();
        $totalProductosConMarca = Producto::whereNotNull('brand_id')->orWhereNotNull('marca')->count();

        $query = Brand::withCount(['productos' => function ($q) {
            $q->whereNull('eliminado_en');
        }]);

        if (!empty($busqueda)) {
            $query->buscar($busqueda);
        }

        if ($filtroVerificada === 'yes') {
            $query->where('verified', true);
        } elseif ($filtroVerificada === 'no') {
            $query->where('verified', false);
        }

        $marcas = $query->orderBy('name', 'asc')
                        ->paginate(15)
                        ->withQueryString();

        return view('admin.brands.index', compact(
            'marcas',
            'totalMarcas',
            'totalVerificadas',
            'totalProductosConMarca',
            'busqueda',
            'filtroVerificada'
        ));
    }

    // Muestra el formulario para crear una nueva marca
    public function create(): View
    {
        $brand = new Brand();
        return view('admin.brands.create', compact('brand'));
    }

    // Almacena una nueva marca en la base de datos
    public function store(Request $request): RedirectResponse
    {
        $validados = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:brands,slug'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'verified' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre de la marca es obligatorio.',
            'slug.unique' => 'Ya existe una marca registrada con este slug.',
            'logo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'logo.max' => 'La imagen no debe superar los 4MB.',
        ]);

        $this->brandService->crear(
            $validados,
            $request->file('logo'),
            $request->boolean('verified', true)
        );

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marca registrada exitosamente.');
    }

    // Muestra el formulario para editar una marca
    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    // Actualiza una marca existente en la base de datos
    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validados = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:brands,slug,' . $brand->id],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'verified' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre de la marca es obligatorio.',
            'slug.unique' => 'Ya existe otra marca registrada con este slug.',
            'logo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'logo.max' => 'La imagen no debe superar los 4MB.',
        ]);

        $this->brandService->actualizar(
            $brand,
            $validados,
            $request->file('logo'),
            $request->boolean('verified')
        );

        return redirect()->route('admin.brands.index', request()->query())
            ->with('success', 'Marca actualizada exitosamente.');
    }

    // Elimina una marca de la base de datos
    public function destroy(Brand $brand): RedirectResponse|JsonResponse
    {
        $this->brandService->eliminar($brand);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Marca eliminada exitosamente.',
            ]);
        }

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marca eliminada exitosamente.');
    }
}
