<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Cupon;
use App\Models\LogAuditoria;
use App\Models\Producto;
use App\Models\UsoCupon;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CuponController extends Controller
{
    /**
     * Muestra el listado de cupones con métricas KPI, buscador y filtros.
     */
    public function index(Request $request): View
    {
        $busqueda = trim($request->input('buscar', ''));
        $filtroTipo = $request->input('tipo', 'all');

        // Métricas KPI
        $totalCupones = Cupon::count();
        $cuponesActivosCount = Cupon::where('activo', true)
            ->where(function ($q) {
                $q->whereNull('fin_en')->orWhere('fin_en', '>=', Carbon::now());
            })->count();
        $totalDescuentosMonto = (float) UsoCupon::sum('descuento_aplicado');

        // Query principal
        $query = Cupon::with(['categoria', 'producto']);

        if (!empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'LIKE', "%{$busqueda}%")
                  ->orWhere('tipo', 'LIKE', "%{$busqueda}%");
            });
        }

        if (in_array($filtroTipo, ['porcentaje', 'monto_fijo', 'envio_gratis'])) {
            $query->where('tipo', $filtroTipo);
        }

        $cupones = $query->orderBy('creado_en', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.promociones.cupones', compact(
            'cupones',
            'busqueda',
            'filtroTipo',
            'totalCupones',
            'cuponesActivosCount',
            'totalDescuentosMonto'
        ));
    }

    /**
     * Formulario para configurar un nuevo cupón.
     */
    public function create(): View
    {
        $cupon = new Cupon([
            'activo' => true,
            'tipo' => 'porcentaje',
            'valor' => 10,
            'aplica_a' => 'catalogo',
            'usos_por_cliente' => 1,
            'inicio_en' => Carbon::now(),
        ]);

        $categorias = Categoria::sinEliminar()->with('padre')->orderBy('nombre', 'asc')->get();
        $categoriasFormatted = $categorias->map(function ($cat) {
            return [
                'id' => $cat->id,
                'nombre' => $cat->nombre,
                'padre_nombre' => $cat->padre ? $cat->padre->nombre : null,
                'imagen_url' => $cat->imagen_ruta ? asset($cat->imagen_ruta) : null,
            ];
        });

        $productos = Producto::sinEliminar()->where('activo', true)->with('imagenes')->orderBy('nombre', 'asc')->get();
        $productosFormatted = $productos->map(function ($prod) {
            $img = $prod->imagenes->where('es_principal', true)->first() ?? $prod->imagenes->first();
            return [
                'id' => $prod->id,
                'nombre' => $prod->nombre,
                'sku' => $prod->sku ?? "PROD-{$prod->id}",
                'precio_base' => (float) $prod->precio,
                'imagen_url' => $img ? asset($img->ruta) : null,
            ];
        });

        $esEdicion = false;

        return view('admin.promociones.configurar-cupon', compact(
            'cupon',
            'categorias',
            'categoriasFormatted',
            'productos',
            'productosFormatted',
            'esEdicion'
        ));
    }

    /**
     * Guarda un nuevo cupón en la base de datos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:cupones,codigo',
            'tipo' => 'required|string|in:porcentaje,monto_fijo,envio_gratis',
            'valor' => 'required|numeric|min:0.01',
            'monto_minimo' => 'nullable|numeric|min:0',
            'maximo_usos_total' => 'nullable|integer|min:1',
            'usos_por_cliente' => 'nullable|integer|min:1',
            'activo' => 'nullable|boolean',
            'aplica_a' => 'required|string|in:catalogo,categoria,producto',
            'categoria_id' => 'nullable|required_if:aplica_a,categoria|exists:categorias,id',
            'producto_id' => 'nullable|required_if:aplica_a,producto|exists:productos,id',
            'inicio_en' => 'required|date',
            'fin_en' => 'nullable|date|after_or_equal:inicio_en',
        ], [
            'codigo.required' => 'El código del cupón es obligatorio.',
            'codigo.unique' => 'El código de cupón ingresado ya existe.',
            'tipo.in' => 'El tipo de descuento seleccionado no es válido.',
            'valor.required' => 'El valor del descuento es obligatorio.',
            'valor.min' => 'El valor debe ser mayor a 0.',
            'categoria_id.required_if' => 'Debes seleccionar una categoría.',
            'producto_id.required_if' => 'Debes seleccionar un producto.',
            'fin_en.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ]);

        $cupon = Cupon::create([
            'codigo' => strtoupper(trim($validated['codigo'])),
            'tipo' => $validated['tipo'],
            'valor' => (float) $validated['valor'],
            'monto_minimo' => isset($validated['monto_minimo']) ? (float) $validated['monto_minimo'] : 0.00,
            'maximo_usos_total' => !empty($validated['maximo_usos_total']) ? (int) $validated['maximo_usos_total'] : null,
            'usos_por_cliente' => !empty($validated['usos_por_cliente']) ? (int) $validated['usos_por_cliente'] : 1,
            'usos_actuales' => 0,
            'activo' => $request->has('activo') ? (bool) $request->input('activo') : true,
            'aplica_a' => $validated['aplica_a'],
            'categoria_id' => $validated['aplica_a'] === 'categoria' ? $validated['categoria_id'] : null,
            'producto_id' => $validated['aplica_a'] === 'producto' ? $validated['producto_id'] : null,
            'inicio_en' => Carbon::parse($validated['inicio_en']),
            'fin_en' => !empty($validated['fin_en']) ? Carbon::parse($validated['fin_en']) : null,
            'creado_en' => Carbon::now(),
            'actualizado_en' => Carbon::now(),
        ]);

        $this->registrarAuditoria('crear_cupon', "Cupón '{$cupon->codigo}' creado.", null, $cupon->toArray());

        return redirect()
            ->route('admin.promociones.cupones')
            ->with('success', "El cupón '{$cupon->codigo}' ha sido creado exitosamente.");
    }

    /**
     * Formulario para editar un cupón existente.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return redirect()
                ->route('admin.promociones.cupones')
                ->with('error', 'El cupón solicitado no fue encontrado.');
        }

        $categorias = Categoria::sinEliminar()->with('padre')->orderBy('nombre', 'asc')->get();
        $categoriasFormatted = $categorias->map(function ($cat) {
            return [
                'id' => $cat->id,
                'nombre' => $cat->nombre,
                'padre_nombre' => $cat->padre ? $cat->padre->nombre : null,
                'imagen_url' => $cat->imagen_ruta ? asset($cat->imagen_ruta) : null,
            ];
        });

        $productos = Producto::sinEliminar()->where('activo', true)->with('imagenes')->orderBy('nombre', 'asc')->get();
        $productosFormatted = $productos->map(function ($prod) {
            $img = $prod->imagenes->where('es_principal', true)->first() ?? $prod->imagenes->first();
            return [
                'id' => $prod->id,
                'nombre' => $prod->nombre,
                'sku' => $prod->sku ?? "PROD-{$prod->id}",
                'precio_base' => (float) $prod->precio,
                'imagen_url' => $img ? asset($img->ruta) : null,
            ];
        });

        $esEdicion = true;

        return view('admin.promociones.configurar-cupon', compact(
            'cupon',
            'categorias',
            'categoriasFormatted',
            'productos',
            'productosFormatted',
            'esEdicion'
        ));
    }

    /**
     * Actualiza un cupón existente.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return redirect()
                ->route('admin.promociones.cupones')
                ->with('error', 'El cupón no fue encontrado.');
        }

        $validated = $request->validate([
            'codigo' => "required|string|max:50|unique:cupones,codigo,{$cupon->id}",
            'tipo' => 'required|string|in:porcentaje,monto_fijo,envio_gratis',
            'valor' => 'required|numeric|min:0.01',
            'monto_minimo' => 'nullable|numeric|min:0',
            'maximo_usos_total' => 'nullable|integer|min:1',
            'usos_por_cliente' => 'nullable|integer|min:1',
            'activo' => 'nullable|boolean',
            'aplica_a' => 'required|string|in:catalogo,categoria,producto',
            'categoria_id' => 'nullable|required_if:aplica_a,categoria|exists:categorias,id',
            'producto_id' => 'nullable|required_if:aplica_a,producto|exists:productos,id',
            'inicio_en' => 'required|date',
            'fin_en' => 'nullable|date|after_or_equal:inicio_en',
        ]);

        $valorAnterior = $cupon->toArray();

        $cupon->update([
            'codigo' => strtoupper(trim($validated['codigo'])),
            'tipo' => $validated['tipo'],
            'valor' => (float) $validated['valor'],
            'monto_minimo' => isset($validated['monto_minimo']) ? (float) $validated['monto_minimo'] : 0.00,
            'maximo_usos_total' => !empty($validated['maximo_usos_total']) ? (int) $validated['maximo_usos_total'] : null,
            'usos_por_cliente' => !empty($validated['usos_por_cliente']) ? (int) $validated['usos_por_cliente'] : 1,
            'activo' => $request->has('activo') ? (bool) $request->input('activo') : false,
            'aplica_a' => $validated['aplica_a'],
            'categoria_id' => $validated['aplica_a'] === 'categoria' ? $validated['categoria_id'] : null,
            'producto_id' => $validated['aplica_a'] === 'producto' ? $validated['producto_id'] : null,
            'inicio_en' => Carbon::parse($validated['inicio_en']),
            'fin_en' => !empty($validated['fin_en']) ? Carbon::parse($validated['fin_en']) : null,
            'actualizado_en' => Carbon::now(),
        ]);

        $this->registrarAuditoria('actualizar_cupon', "Cupón '{$cupon->codigo}' actualizado.", $valorAnterior, $cupon->toArray());

        return redirect()
            ->route('admin.promociones.cupones')
            ->with('success', "El cupón '{$cupon->codigo}' fue actualizado correctamente.");
    }

    /**
     * Alterna el estado activo/inactivo de un cupón vía AJAX o POST.
     */
    public function toggleEstado(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cupón no encontrado.'], 404);
            }
            return back()->with('error', 'Cupón no encontrado.');
        }

        $cupon->activo = !$cupon->activo;
        $cupon->actualizado_en = Carbon::now();
        $cupon->save();

        $this->registrarAuditoria(
            'toggle_cupon',
            "Estado del cupón '{$cupon->codigo}' cambiado a " . ($cupon->activo ? 'Activo' : 'Inactivo'),
            ['activo' => !$cupon->activo],
            ['activo' => $cupon->activo]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo' => $cupon->activo,
                'message' => "Estado de '{$cupon->codigo}' actualizado a " . ($cupon->activo ? 'Activo' : 'Inactivo'),
            ]);
        }

        return back()->with('success', "Estado del cupón '{$cupon->codigo}' actualizado.");
    }

    /**
     * Elimina un cupón.
     */
    public function destroy(int $id): RedirectResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return redirect()
                ->route('admin.promociones.cupones')
                ->with('error', 'El cupón no fue encontrado.');
        }

        $codigo = $cupon->codigo;
        $cupon->delete();

        $this->registrarAuditoria('eliminar_cupon', "Cupón '{$codigo}' eliminado.", $cupon->toArray(), null);

        return redirect()
            ->route('admin.promociones.cupones')
            ->with('success', "El cupón '{$codigo}' ha sido eliminado.");
    }

    private function registrarAuditoria(string $accion, string $descripcion, ?array $anterior = null, ?array $nuevo = null): void
    {
        try {
            LogAuditoria::create([
                'usuario_id' => Auth::id(),
                'modulo' => 'Cupones y Promociones',
                'accion' => $accion,
                'descripcion' => $descripcion,
                'valor_anterior' => $anterior ? json_encode($anterior) : null,
                'valor_nuevo' => $nuevo ? json_encode($nuevo) : null,
                'ip' => request()->ip(),
                'agente_usuario' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Log fallback
        }
    }
}
