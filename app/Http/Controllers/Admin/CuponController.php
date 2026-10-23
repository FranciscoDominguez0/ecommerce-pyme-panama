<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use App\Services\CuponService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CuponController extends Controller
{
    public function __construct(
        protected CuponService $cuponService
    ) {}

    // Muestra el listado de cupones con métricas KPI y filtros
    public function index(Request $request): View
    {
        $busqueda = trim($request->input('buscar', ''));
        $filtroTipo = $request->input('tipo', 'all');

        $metricas = $this->cuponService->obtenerMetricasAdmin($busqueda, $filtroTipo);

        return view('admin.promociones.cupones', array_merge([
            'busqueda' => $busqueda,
            'filtroTipo' => $filtroTipo,
        ], $metricas));
    }

    // Formulario para configurar un nuevo cupón
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

        $datosFormulario = $this->cuponService->obtenerDatosFormulario();
        $esEdicion = false;

        return view('admin.promociones.configurar-cupon', array_merge(
            compact('cupon', 'esEdicion'),
            $datosFormulario
        ));
    }

    // Guarda un nuevo cupón en la base de datos
    public function store(Request $request): RedirectResponse
    {
        $validados = $request->validate([
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

        $activo = $request->has('activo') ? (bool) $request->input('activo') : true;
        $this->cuponService->crear($validados, $activo);

        return redirect()->route('admin.promociones.cupones')
            ->with('success', 'Cupón creado exitosamente.');
    }

    // Formulario para editar un cupón existente
    public function edit(int $id): View|RedirectResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return redirect()
                ->route('admin.promociones.cupones')
                ->with('error', 'El cupón solicitado no fue encontrado.');
        }

        $datosFormulario = $this->cuponService->obtenerDatosFormulario();
        $esEdicion = true;

        return view('admin.promociones.configurar-cupon', array_merge(
            compact('cupon', 'esEdicion'),
            $datosFormulario
        ));
    }

    // Actualiza un cupón existente
    public function update(Request $request, int $id): RedirectResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return redirect()
                ->route('admin.promociones.cupones')
                ->with('error', 'El cupón no fue encontrado.');
        }

        $validados = $request->validate([
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

        $activo = $request->has('activo') ? (bool) $request->input('activo') : false;
        $this->cuponService->actualizar($cupon, $validados, $activo);

        return redirect()->route('admin.promociones.cupones')
            ->with('success', 'Cupón actualizado exitosamente.');
    }

    // Alterna el estado activo/inactivo de un cupón vía AJAX o petición web
    public function alternarEstado(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cupón no encontrado.'], 404);
            }
            return back()->with('error', 'Cupón no encontrado.');
        }

        $activo = $this->cuponService->alternarEstado($cupon);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo' => $activo,
                'message' => "Estado de '{$cupon->codigo}' actualizado a " . ($activo ? 'Activo' : 'Inactivo'),
            ]);
        }

        return back()->with('success', 'Estado del cupón actualizado exitosamente.');
    }

    // Elimina un cupón
    public function destroy(int $id): RedirectResponse
    {
        $cupon = Cupon::find($id);

        if (!$cupon) {
            return redirect()
                ->route('admin.promociones.cupones')
                ->with('error', 'El cupón no fue encontrado.');
        }

        $this->cuponService->eliminar($cupon);

        return redirect()->route('admin.promociones.cupones')
            ->with('success', 'Cupón eliminado exitosamente.');
    }
}
