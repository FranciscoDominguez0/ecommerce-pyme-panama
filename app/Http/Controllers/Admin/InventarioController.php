<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\VarianteProducto;
use App\Services\InventarioService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventarioController extends Controller
{
    public function __construct(protected InventarioService $inventario) {}

    // ─── 1. Historial de Movimientos (index) ──────────────────────────────────

    public function index(Request $request)
    {
        $query = MovimientoInventario::with(['producto', 'variante.opciones.tipo', 'usuario', 'pedido'])
            ->orderByDesc('creado_en');

        // Filtro: tipo
        if ($request->filled('tipo') && in_array($request->tipo, ['entrada', 'salida', 'ajuste'])) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro: búsqueda por motivo o producto
        if ($request->filled('q')) {
            $buscar = $request->q;
            $query->where(function ($q) use ($buscar) {
                $q->where('motivo', 'ilike', "%{$buscar}%")
                  ->orWhereHas('producto', fn($p) => $p->where('nombre', 'ilike', "%{$buscar}%")
                      ->orWhere('sku', 'ilike', "%{$buscar}%"));
            });
        }

        // Filtro: fechas
        if ($request->filled('desde')) {
            $query->whereDate('creado_en', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('creado_en', '<=', $request->hasta);
        }

        $movimientos = $query->paginate(20)->withQueryString();
        $kpis        = $this->inventario->calcularKpis();

        return view('admin.inventario.index', compact('movimientos', 'kpis'));
    }

    // ─── 2. Stock Actual ───────────────────────────────────────────────────────

    public function stock(Request $request)
    {
        // Obtener productos sin variantes
        $qProductos = Producto::with(['categoria', 'imagenes'])
            ->sinEliminar()
            ->whereDoesntHave('variantes')
            ->orderBy('nombre');

        if ($request->filled('q')) {
            $buscar = $request->q;
            $qProductos->where(function ($q) use ($buscar) {
                $q->where('nombre', 'ilike', "%{$buscar}%")
                  ->orWhere('sku', 'ilike', "%{$buscar}%");
            });
        }

        if ($request->filled('categoria')) {
            $qProductos->where('categoria_id', $request->categoria);
        }

        if ($request->boolean('stock_bajo')) {
            $qProductos->whereRaw('stock <= stock_minimo');
        }

        // Obtener variantes con su producto
        $qVariantes = VarianteProducto::with(['producto.imagenes', 'producto.categoria', 'opciones.tipo'])
            ->whereHas('producto', fn($p) => $p->sinEliminar())
            ->where('activo', true)
            ->orderBy('id');

        if ($request->filled('q')) {
            $buscar = $request->q;
            $qVariantes->where(function ($q) use ($buscar) {
                $q->where('sku', 'ilike', "%{$buscar}%")
                  ->orWhereHas('producto', fn($p) => $p->where('nombre', 'ilike', "%{$buscar}%"));
            });
        }

        if ($request->filled('categoria')) {
            $qVariantes->whereHas('producto', fn($p) => $p->where('categoria_id', $request->categoria));
        }

        if ($request->boolean('stock_bajo')) {
            $qVariantes->whereHas('producto', fn($p) => $p->whereRaw('variantes_producto.stock <= productos.stock_minimo'));
        }

        // Unificamos en una lista paginada de forma simple: products first, then variants
        $productos = $qProductos->paginate(15, ['*'], 'p_page')->withQueryString();
        $variantes = $qVariantes->paginate(15, ['*'], 'v_page')->withQueryString();

        $kpis       = $this->inventario->calcularKpis();
        $categorias = \App\Models\Categoria::orderBy('nombre')->get(['id', 'nombre']);

        return view('admin.inventario.index', compact('productos', 'variantes', 'kpis', 'categorias'))
            ->with('vista', 'stock');
    }

    // ─── 3. Registrar Entrada ──────────────────────────────────────────────────

    public function entradaForm()
    {
        $productos = Producto::sinEliminar()->orderBy('nombre')->get(['id', 'nombre', 'sku', 'stock']);
        return view('admin.inventario.entrada', compact('productos'));
    }

    public function entrada(Request $request)
    {
        $data = $request->validate([
            'producto_id'       => ['required', 'integer', 'exists:productos,id'],
            'variante_id'       => ['nullable', 'integer', 'exists:variantes_producto,id'],
            'cantidad'          => ['required', 'integer', 'min:1'],
            'motivo'            => ['required', 'string', 'max:255'],
            'proveedor'         => ['nullable', 'string', 'max:200'],
            'factura_proveedor' => ['nullable', 'string', 'max:100'],
            'notas'             => ['nullable', 'string', 'max:1000'],
        ]);

        $producto = Producto::findOrFail($data['producto_id']);
        $variante = isset($data['variante_id']) ? VarianteProducto::findOrFail($data['variante_id']) : null;

        try {
            $this->inventario->registrarEntrada(
                producto:         $producto,
                variante:         $variante,
                cantidad:         (int) $data['cantidad'],
                motivo:           $data['motivo'],
                proveedor:        $data['proveedor'] ?? null,
                facturaProveedor: $data['factura_proveedor'] ?? null,
                notas:            $data['notas'] ?? null,
                usuarioId:        Auth::id(),
            );

            return redirect()->route('admin.inventario.index')
                ->with('success', "✓ Entrada de {$data['cantidad']} unidades registrada.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ─── 4. Registrar Salida ───────────────────────────────────────────────────

    public function salidaForm()
    {
        $productos = Producto::sinEliminar()->orderBy('nombre')->get(['id', 'nombre', 'sku', 'stock']);
        return view('admin.inventario.salida', compact('productos'));
    }

    public function salida(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'variante_id' => ['nullable', 'integer', 'exists:variantes_producto,id'],
            'cantidad'    => ['required', 'integer', 'min:1'],
            'motivo'      => ['required', 'string', 'max:255'],
            'notas'       => ['nullable', 'string', 'max:1000'],
        ]);

        $producto = Producto::findOrFail($data['producto_id']);
        $variante = isset($data['variante_id']) ? VarianteProducto::findOrFail($data['variante_id']) : null;

        try {
            $this->inventario->registrarSalida(
                producto:  $producto,
                variante:  $variante,
                cantidad:  (int) $data['cantidad'],
                motivo:    $data['motivo'],
                notas:     $data['notas'] ?? null,
                usuarioId: Auth::id(),
            );

            return redirect()->route('admin.inventario.index')
                ->with('success', "✓ Salida de {$data['cantidad']} unidades registrada.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ─── 5. Ajuste Manual ─────────────────────────────────────────────────────

    public function ajusteForm()
    {
        $productos = Producto::sinEliminar()->orderBy('nombre')->get(['id', 'nombre', 'sku', 'stock', 'stock_minimo']);
        return view('admin.inventario.ajuste', compact('productos'));
    }

    public function ajuste(Request $request)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'variante_id' => ['nullable', 'integer', 'exists:variantes_producto,id'],
            'nuevo_stock' => ['required', 'integer', 'min:0'],
            'motivo'      => ['required', 'string', 'max:255'],
            'notas'       => ['nullable', 'string', 'max:1000'],
        ]);

        $producto = Producto::findOrFail($data['producto_id']);
        $variante = isset($data['variante_id']) ? VarianteProducto::findOrFail($data['variante_id']) : null;

        try {
            $this->inventario->registrarAjuste(
                producto:   $producto,
                variante:   $variante,
                nuevoStock: (int) $data['nuevo_stock'],
                motivo:     $data['motivo'],
                notas:      $data['notas'] ?? null,
                usuarioId:  Auth::id(),
            );

            return redirect()->route('admin.inventario.index')
                ->with('success', "✓ Stock ajustado a {$data['nuevo_stock']} unidades.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ─── AJAX: variantes por producto ─────────────────────────────────────────

    public function variantesPorProducto(int $productoId)
    {
        $variantes = VarianteProducto::with('opciones.tipo')
            ->where('producto_id', $productoId)
            ->where('activo', true)
            ->get()
            ->map(fn($v) => [
                'id'     => $v->id,
                'label'  => $v->opciones->map(fn($o) => $o->tipo->nombre . ': ' . $o->valor)->join(' / '),
                'sku'    => $v->sku,
                'stock'  => $v->stock,
                'precio' => $v->precio,
            ]);

        return response()->json($variantes);
    }

    // ─── AJAX: stock actual de un producto/variante ────────────────────────────

    public function stockProducto(int $productoId, ?int $varianteId = null)
    {
        if ($varianteId) {
            $variante = VarianteProducto::findOrFail($varianteId);
            return response()->json(['stock' => $variante->stock]);
        }

        $producto = Producto::findOrFail($productoId);
        return response()->json(['stock' => $producto->stock]);
    }

    // 🔥 7. Exportación de Stock 🔥

    protected function obtenerDatosStockFiltrado(Request $request)
    {
        // 1. Productos sin variantes
        $qProductos = Producto::with(['categoria'])
            ->sinEliminar()
            ->whereDoesntHave('variantes')
            ->orderBy('nombre');

        if ($request->filled('q')) {
            $buscar = $request->q;
            $qProductos->where(function ($q) use ($buscar) {
                $q->where('nombre', 'ilike', "%{$buscar}%")
                  ->orWhere('sku', 'ilike', "%{$buscar}%");
            });
        }
        if ($request->filled('categoria')) {
            $qProductos->where('categoria_id', $request->categoria);
        }
        if ($request->boolean('stock_bajo')) {
            $qProductos->whereRaw('stock <= stock_minimo');
        }

        // 2. Variantes
        $qVariantes = VarianteProducto::with(['producto.categoria', 'opciones.tipo'])
            ->where('variantes_producto.activo', true)
            ->whereHas('producto', fn($q) => $q->sinEliminar())
            ->join('productos', 'variantes_producto.producto_id', '=', 'productos.id')
            ->select('variantes_producto.*')
            ->orderBy('productos.nombre');

        if ($request->filled('q')) {
            $buscar = $request->q;
            $qVariantes->where(function ($q) use ($buscar) {
                $q->where('variantes_producto.sku', 'ilike', "%{$buscar}%")
                  ->orWhereHas('producto', fn($p) => $p->where('nombre', 'ilike', "%{$buscar}%"));
            });
        }
        if ($request->filled('categoria')) {
            $qVariantes->whereHas('producto', fn($p) => $p->where('categoria_id', $request->categoria));
        }
        if ($request->boolean('stock_bajo')) {
            // Nota: En la BD, stock_minimo está en la tabla productos. 
            // Para ser precisos en la vista original se compara variantes_producto.stock con productos.stock_minimo
            $qVariantes->whereRaw('variantes_producto.stock <= productos.stock_minimo');
        }

        $productos = $qProductos->get()->map(function ($p) {
            $p->is_variante = false;
            $p->nombre_completo = $p->nombre;
            return $p;
        });

        $variantes = $qVariantes->get()->map(function ($v) {
            $v->is_variante = true;
            $label = $v->opciones->map(fn($o) => $o->valor)->join(' - ');
            $v->nombre_completo = $v->producto->nombre . ' (' . $label . ')';
            $v->categoria = $v->producto->categoria;
            $v->stock_minimo = $v->producto->stock_minimo;
            return $v;
        });

        return $productos->concat($variantes)->sortBy('nombre_completo')->values();
    }

    public function exportarStockExcel(Request $request, \App\Services\AuditoriaService $auditoria)
    {
        $items = $this->obtenerDatosStockFiltrado($request);
        
        $auditoria->registrar('Inventario', 'Exportación Excel', 'Exportación de reporte de stock actual filtrado');
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StockActualExport($items), 'stock_actual_' . date('Y-m-d') . '.xlsx');
    }

    public function exportarStockPdf(Request $request, \App\Services\AuditoriaService $auditoria)
    {
        $items = $this->obtenerDatosStockFiltrado($request);
        
        $auditoria->registrar('Inventario', 'Exportación PDF', 'Exportación de reporte de stock actual filtrado');
        
        $totalStock = $items->sum('stock');
        $valorizacionTotal = $items->sum(function ($item) {
            return $item->stock * $item->precio;
        });
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.inventario.pdf.stock', [
            'items' => $items,
            'totalItems' => $items->count(),
            'totalStock' => $totalStock,
            'valorizacionTotal' => $valorizacionTotal
        ]);
        
        return $pdf->download('stock_actual_' . date('Y-m-d') . '.pdf');
    }
}
