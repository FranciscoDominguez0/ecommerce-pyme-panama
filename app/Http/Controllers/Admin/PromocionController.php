<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAuditoria;
use App\Models\Producto;
use App\Models\ProductoDelMes;
use App\Models\PromocionEnvioGratis;
use App\Models\ZonaEnvio;
use App\Services\CuponService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PromocionController extends Controller
{
    protected CuponService $cuponService;

    public function __construct(CuponService $cuponService)
    {
        $this->cuponService = $cuponService;
    }

    // ─────────────────────────────────────────────────────────
    // 1. GESTIÓN DE PROMOCIONES DE ENVÍO GRATIS
    // ─────────────────────────────────────────────────────────

    public function envioGratisIndex(): View
    {
        $promociones = PromocionEnvioGratis::with('zonaEnvio')
            ->orderBy('creado_en', 'desc')
            ->paginate(15);

        $zonasEnvio = ZonaEnvio::where('activo', true)->orderBy('nombre', 'asc')->get();

        return view('admin.promociones.envio-gratis', compact('promociones', 'zonasEnvio'));
    }

    public function envioGratisStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'zona_envio_id' => 'required|exists:zonas_envio,id',
            'monto_minimo' => 'required|numeric|min:0',
            'inicio_en' => 'required|date',
            'fin_en' => 'nullable|date|after_or_equal:inicio_en',
            'activo' => 'nullable|boolean',
        ], [
            'zona_envio_id.required' => 'Debes seleccionar una Zona de Envío.',
            'monto_minimo.required' => 'El monto mínimo de compra es obligatorio.',
            'fin_en.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ]);

        $promocion = PromocionEnvioGratis::create([
            'zona_envio_id' => $validated['zona_envio_id'],
            'monto_minimo' => (float) $validated['monto_minimo'],
            'inicio_en' => Carbon::parse($validated['inicio_en']),
            'fin_en' => !empty($validated['fin_en']) ? Carbon::parse($validated['fin_en']) : Carbon::now()->addYears(10),
            'activo' => $request->has('activo') ? (bool) $request->input('activo') : true,
            'creado_en' => Carbon::now(),
        ]);

        $this->registrarAuditoria('crear_envio_gratis', "Regla de envío gratis para zona #{$promocion->zona_envio_id} creada.", null, $promocion->toArray());

        return redirect()
            ->route('admin.promociones.envio-gratis')
            ->with('success', 'Regla de envío gratis creada correctamente.');
    }

    public function envioGratisUpdate(Request $request, int $id): RedirectResponse
    {
        $promocion = PromocionEnvioGratis::find($id);

        if (!$promocion) {
            return redirect()
                ->route('admin.promociones.envio-gratis')
                ->with('error', 'La regla de envío gratis no fue encontrada.');
        }

        $validated = $request->validate([
            'zona_envio_id' => 'required|exists:zonas_envio,id',
            'monto_minimo' => 'required|numeric|min:0',
            'inicio_en' => 'required|date',
            'fin_en' => 'nullable|date|after_or_equal:inicio_en',
            'activo' => 'nullable|boolean',
        ]);

        $valorAnterior = $promocion->toArray();

        $promocion->update([
            'zona_envio_id' => $validated['zona_envio_id'],
            'monto_minimo' => (float) $validated['monto_minimo'],
            'inicio_en' => Carbon::parse($validated['inicio_en']),
            'fin_en' => !empty($validated['fin_en']) ? Carbon::parse($validated['fin_en']) : Carbon::now()->addYears(10),
            'activo' => $request->has('activo') ? (bool) $request->input('activo') : false,
        ]);

        $this->registrarAuditoria('actualizar_envio_gratis', "Regla de envío gratis #{$promocion->id} actualizada.", $valorAnterior, $promocion->toArray());

        return redirect()
            ->route('admin.promociones.envio-gratis')
            ->with('success', 'Regla de envío gratis actualizada.');
    }

    public function envioGratisToggle(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $promocion = PromocionEnvioGratis::find($id);

        if (!$promocion) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Regla no encontrada.'], 404);
            }
            return back()->with('error', 'Regla no encontrada.');
        }

        $promocion->activo = !$promocion->activo;
        $promocion->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo' => $promocion->activo,
                'message' => 'Estado de envío gratis actualizado.',
            ]);
        }

        return back()->with('success', 'Estado de la regla de envío gratis actualizado.');
    }

    public function envioGratisDestroy(int $id): RedirectResponse
    {
        $promocion = PromocionEnvioGratis::find($id);
        if ($promocion) {
            $promocion->delete();
        }

        return redirect()
            ->route('admin.promociones.envio-gratis')
            ->with('success', 'Regla de envío gratis eliminada.');
    }

    // ─────────────────────────────────────────────────────────
    // 2. GESTIÓN DE PRODUCTO DEL MES
    // ─────────────────────────────────────────────────────────

    public function productoDelMesIndex(): View
    {
        $promocionActual = ProductoDelMes::with('producto.imagenes')
            ->orderBy('creado_en', 'desc')
            ->first();

        $productos = Producto::sinEliminar()
            ->where('activo', true)
            ->with('imagenes')
            ->orderBy('nombre', 'asc')
            ->get();

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

        return view('admin.promociones.producto-del-mes', compact('promocionActual', 'productos', 'productosFormatted'));
    }

    public function productoDelMesStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'descripcion_mes' => 'nullable|string|max:500',
            'descuento_especial' => 'required|numeric|min:1|max:99',
            'inicio_en' => 'required|date',
            'fin_en' => 'required|date|after_or_equal:inicio_en',
            'activo' => 'nullable|boolean',
            'imagen_banner' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'producto_id.required' => 'Debes seleccionar un producto.',
            'descuento_especial.required' => 'El porcentaje de descuento es obligatorio.',
            'fin_en.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ]);

        $imagenBannerRuta = null;
        if ($request->hasFile('imagen_banner') && $request->file('imagen_banner')->isValid()) {
            $file = $request->file('imagen_banner');
            $fileName = 'banner_pm_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/promociones');
            if (!File::isDirectory($destination)) {
                File::makeDirectory($destination, 0755, true, true);
            }
            $file->move($destination, $fileName);
            $imagenBannerRuta = 'uploads/promociones/' . $fileName;
        }

        // Si se marca activo, desactivar anteriores
        if ($request->has('activo')) {
            ProductoDelMes::where('activo', true)->update(['activo' => false]);
        }

        $promocion = ProductoDelMes::create([
            'producto_id' => $validated['producto_id'],
            'descripcion_mes' => $validated['descripcion_mes'] ?? null,
            'imagen_banner_ruta' => $imagenBannerRuta,
            'descuento_especial' => (float) $validated['descuento_especial'],
            'inicio_en' => Carbon::parse($validated['inicio_en']),
            'fin_en' => Carbon::parse($validated['fin_en']),
            'activo' => $request->has('activo') ? (bool) $request->input('activo') : true,
            'creado_en' => Carbon::now(),
        ]);

        $this->registrarAuditoria('crear_producto_del_mes', "Producto del Mes creado para el producto ID #{$promocion->producto_id}.", null, $promocion->toArray());

        return redirect()
            ->route('admin.promociones.producto-del-mes')
            ->with('success', 'El Producto del Mes ha sido configurado correctamente.');
    }

    public function productoDelMesToggle(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $promocion = ProductoDelMes::find($id);

        if (!$promocion) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Promoción no encontrada.'], 404);
            }
            return back()->with('error', 'Promoción no encontrada.');
        }

        $promocion->activo = !$promocion->activo;
        $promocion->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activo' => $promocion->activo,
                'message' => 'Estado de Producto del Mes actualizado.',
            ]);
        }

        return back()->with('success', 'Estado de Producto del Mes actualizado.');
    }

    public function productoDelMesDestroy(int $id): RedirectResponse
    {
        $promocion = ProductoDelMes::find($id);
        if ($promocion) {
            $promocion->delete();
        }

        return redirect()
            ->route('admin.promociones.producto-del-mes')
            ->with('success', 'Promoción de Producto del Mes eliminada.');
    }

    // ─────────────────────────────────────────────────────────
    // 3. CARRITO DE CLIENTE & ENDPOINTS AJAX DE CUPÓN
    // ─────────────────────────────────────────────────────────

    public function verCarrito(): View
    {
        $usuarioId = Auth::id();

        // En una implementación real se consulta el modelo Carrito o Sesión
        $cuponAplicado = session('cupon_aplicado');

        return view('cliente.carrito', compact('cuponAplicado'));
    }

    public function aplicarCuponCarrito(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'codigo' => 'required|string',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $codigo = trim($request->input('codigo'));
        $subtotal = (float) $request->input('subtotal', 150.00); // Subtotal de demostración
        $usuarioId = Auth::id();

        $resultado = $this->cuponService->validarCupon($codigo, $subtotal, $usuarioId);

        if (!$resultado['valido']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['mensaje'],
                ], 422);
            }
            return back()->with('error', $resultado['mensaje']);
        }

        $cupon = $resultado['cupon'];
        $datosCupon = [
            'id' => $cupon->id,
            'codigo' => $cupon->codigo,
            'tipo' => $cupon->tipo,
            'valor' => $cupon->valor,
            'descuento' => $resultado['descuento'],
        ];

        session(['cupon_aplicado' => $datosCupon]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $resultado['mensaje'],
                'cupon' => $datosCupon,
                'nuevo_descuento' => $resultado['descuento'],
                'nuevo_total' => max(0.0, round($subtotal - $resultado['descuento'], 2)),
            ]);
        }

        return back()->with('success', $resultado['mensaje']);
    }

    public function removerCuponCarrito(Request $request): JsonResponse|RedirectResponse
    {
        session()->forget('cupon_aplicado');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'El cupón ha sido removido de tu carrito.',
            ]);
        }

        return back()->with('info', 'Cupón removido.');
    }

    private function registrarAuditoria(string $accion, string $descripcion, ?array $anterior = null, ?array $nuevo = null): void
    {
        try {
            LogAuditoria::create([
                'usuario_id' => Auth::id(),
                'modulo' => 'Promociones & Envío',
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
