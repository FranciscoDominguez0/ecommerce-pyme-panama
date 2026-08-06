<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    /**
     * Muestra el listado de marcas con métricas, búsqueda y filtros.
     */
    public function index(Request $request): View
    {
        $busqueda = trim($request->input('buscar', ''));
        $filtroSugerida = $request->input('sugerida', 'all');
        $filtroVerificada = $request->input('verificada', 'all');

        // Métricas de cabecera
        $totalMarcas = Brand::count();
        $totalSugeridas = Brand::where('is_suggested', true)->count();
        $totalVerificadas = Brand::where('verified', true)->count();
        $totalProductosConMarca = Producto::whereNotNull('brand_id')->orWhereNotNull('marca')->count();

        // Query principal
        $query = Brand::withCount(['productos' => function ($q) {
            $q->whereNull('eliminado_en');
        }]);

        if (!empty($busqueda)) {
            $query->search($busqueda);
        }

        if ($filtroSugerida === 'yes') {
            $query->where('is_suggested', true);
        } elseif ($filtroSugerida === 'no') {
            $query->where('is_suggested', false);
        }

        if ($filtroVerificada === 'yes') {
            $query->where('verified', true);
        } elseif ($filtroVerificada === 'no') {
            $query->where('verified', false);
        }

        $marcas = $query->orderBy('is_suggested', 'desc')
                        ->orderBy('name', 'asc')
                        ->paginate(15)
                        ->withQueryString();

        return view('admin.brands.index', compact(
            'marcas',
            'totalMarcas',
            'totalSugeridas',
            'totalVerificadas',
            'totalProductosConMarca',
            'busqueda',
            'filtroSugerida',
            'filtroVerificada'
        ));
    }

    /**
     * Muestra el formulario para crear una nueva marca.
     */
    public function create(): View
    {
        $brand = new Brand();
        return view('admin.brands.create', compact('brand'));
    }

    /**
     * Almacena una nueva marca en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:brands,slug'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'is_suggested' => ['nullable', 'boolean'],
            'verified' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre de la marca es obligatorio.',
            'slug.unique' => 'Ya existe una marca registrada con este slug.',
            'logo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'logo.max' => 'La imagen no debe superar los 4MB.',
        ]);

        $name = trim($request->input('name'));
        $slug = !empty($request->input('slug')) ? Str::slug($request->input('slug')) : Str::slug($name);

        // Asegurar unicidad de slug
        $contador = 1;
        $slugOriginal = $slug;
        while (Brand::where('slug', $slug)->exists()) {
            $slug = "{$slugOriginal}-{$contador}";
            $contador++;
        }

        $imageResource = null;
        $imageMime = null;
        $imagePath = null;

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $file = $request->file('logo');
            $imageMime = $file->getMimeType();
            $ext = $file->getClientOriginalExtension() ?: 'webp';
            
            // Guardar copia opcional en public/images/Marcas
            $filename = Str::slug($name) . '.' . $ext;
            $destinationPath = public_path('images/Marcas');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }
            $file->move($destinationPath, $filename);
            $imagePath = 'images/Marcas/' . $filename;

            // Stream en memoria para PostgreSQL bytea
            $rawBytes = File::get($destinationPath . DIRECTORY_SEPARATOR . $filename);
            $imageResource = fopen('php://memory', 'r+');
            fwrite($imageResource, $rawBytes);
            rewind($imageResource);
        }

        $brand = Brand::create([
            'name' => $name,
            'slug' => $slug,
            'image' => $imageResource,
            'image_mime' => $imageMime,
            'image_path' => $imagePath,
            'is_suggested' => $request->boolean('is_suggested'),
            'verified' => $request->boolean('verified', true),
        ]);

        if (is_resource($imageResource)) {
            fclose($imageResource);
        }

        // Vincular productos que tengan el nombre de esta marca escrito como texto
        Producto::whereNull('brand_id')
            ->where(function ($q) use ($name, $slug) {
                $q->where('marca', 'ILIKE', $name)
                  ->orWhere('marca', 'ILIKE', $slug);
            })
            ->update(['brand_id' => $brand->id, 'marca' => $name]);

        return redirect()->route('admin.brands.index')
            ->with('toast_success', "Marca «{$brand->name}» registrada exitosamente en el catálogo.");
    }

    /**
     * Muestra el formulario para editar una marca.
     */
    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Actualiza una marca existente en la base de datos.
     */
    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:brands,slug,' . $brand->id],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:4096'],
            'is_suggested' => ['nullable', 'boolean'],
            'verified' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre de la marca es obligatorio.',
            'slug.unique' => 'Ya existe otra marca registrada con este slug.',
            'logo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'logo.max' => 'La imagen no debe superar los 4MB.',
        ]);

        $name = trim($request->input('name'));
        $slug = !empty($request->input('slug')) ? Str::slug($request->input('slug')) : Str::slug($name);

        $updateData = [
            'name' => $name,
            'slug' => $slug,
            'is_suggested' => $request->boolean('is_suggested'),
            'verified' => $request->boolean('verified'),
        ];

        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $file = $request->file('logo');
            $imageMime = $file->getMimeType();
            $ext = $file->getClientOriginalExtension() ?: 'webp';
            
            $filename = Str::slug($name) . '.' . $ext;
            $destinationPath = public_path('images/Marcas');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true, true);
            }
            $file->move($destinationPath, $filename);
            $imagePath = 'images/Marcas/' . $filename;

            $rawBytes = File::get($destinationPath . DIRECTORY_SEPARATOR . $filename);
            $imageResource = fopen('php://memory', 'r+');
            fwrite($imageResource, $rawBytes);
            rewind($imageResource);

            $updateData['image'] = $imageResource;
            $updateData['image_mime'] = $imageMime;
            $updateData['image_path'] = $imagePath;
        }

        $brand->update($updateData);

        if (isset($imageResource) && is_resource($imageResource)) {
            fclose($imageResource);
        }

        // Sincronizar el nombre en los productos asociados
        Producto::where('brand_id', $brand->id)->update(['marca' => $name]);

        return redirect()->route('admin.brands.index')
            ->with('toast_success', "Marca «{$brand->name}» actualizada correctamente.");
    }

    /**
     * Elimina una marca de la base de datos.
     */
    public function destroy(Brand $brand): RedirectResponse|JsonResponse
    {
        $nombre = $brand->name;
        $totalProductos = $brand->productos()->count();

        // Desvincular productos antes de eliminar
        Producto::where('brand_id', $brand->id)->update(['brand_id' => null]);

        $brand->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "La marca «{$nombre}» ha sido eliminada correctamente.",
            ]);
        }

        return redirect()->route('admin.brands.index')
            ->with('toast_success', "Marca «{$nombre}» eliminada del sistema." . ($totalProductos > 0 ? " Se desvincularon {$totalProductos} producto(s)." : ''));
    }

    /**
     * Alterna rápidamente el estado de marca sugerida vía AJAX.
     */
    public function toggleSuggested(Brand $brand): JsonResponse
    {
        $brand->is_suggested = !$brand->is_suggested;
        $brand->save();

        return response()->json([
            'success' => true,
            'is_suggested' => $brand->is_suggested,
            'message' => $brand->is_suggested ? "Marca «{$brand->name}» marcada como sugerida." : "Marca «{$brand->name}» retirada de sugeridas.",
        ]);
    }
}
