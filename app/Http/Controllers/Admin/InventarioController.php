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
        $query = MovimientoInventario::with(['producto.imagenes', 'variante.opciones.tipo', 'usuario', 'pedido.usuario'])
            ->orderByDesc('creado_en');

        // Filtro: tipo
        if ($request->filled('tipo') && in_array($request->tipo, ['entrada', 'salida', 'ajuste'])) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro: búsqueda por motivo o producto
        if ($request->filled('q')) {
            $buscar = $request->q;
            $query->where(function ($q) use ($buscar) {
                $q->whereRaw('unaccent(motivo) ILIKE unaccent(?)', ["%{$buscar}%"])
                  ->orWhereHas('producto', fn($p) => $p->whereRaw('unaccent(nombre) ILIKE unaccent(?)', ["%{$buscar}%"])
                      ->orWhereRaw('unaccent(sku) ILIKE unaccent(?)', ["%{$buscar}%"]));
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

        $movimientos->getCollection()->transform(function ($mov) {
            $mov->payload_modal  = $this->formatearMovimientoModal($mov);
            $mov->texto_variante = $mov->variante 
                ? $mov->variante->opciones->map(fn($o) => ($o->tipo?->nombre ? $o->tipo->nombre . ': ' : '') . $o->valor)->join(' / ') 
                : null;
            return $mov;
        });

        $kpis = $this->inventario->calcularKpis();

        return view('admin.inventario.index', compact('movimientos', 'kpis'));
    }

    /**
     * Prepara el payload estructurado para el modal de detalle de movimiento.
     */
    protected function formatearMovimientoModal(MovimientoInventario $mov): array
    {
        $usuarioResp = $mov->usuario ?? $mov->pedido?->usuario;
        $esPorPedido = !empty($mov->pedido_id);

        if ($esPorPedido) {
            $numPedido = $mov->pedido?->numero_pedido ?? ('#' . $mov->pedido_id);
            $respTipo = 'Compra de Cliente (Online)';
            $respDetalle = 'Movimiento originado automáticamente por la compra del cliente en el Pedido ' . $numPedido;
            $respBadge = 'Cliente';
            $respBadgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60';
        } elseif ($mov->usuario) {
            $respTipo = 'Manual de Administración';
            $respDetalle = 'Movimiento registrado manualmente por el usuario desde el panel administrativo';
            $respBadge = 'Administración';
            $respBadgeClass = 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800/60';
        } else {
            $respTipo = 'Sistema Automático';
            $respDetalle = 'Movimiento generado automáticamente por proceso interno del sistema';
            $respBadge = 'Sistema';
            $respBadgeClass = 'bg-slate-100 text-slate-700 dark:bg-gray-800 dark:text-slate-300 border border-slate-200 dark:border-gray-700';
        }

        $labelVariante = $mov->variante ? $mov->variante->opciones->map(fn($o) => ($o->tipo?->nombre ? $o->tipo->nombre . ': ' : '') . $o->valor)->join(' / ') : null;

        return [
            'id'                => $mov->id,
            'fecha'             => $mov->creado_en->format('d/m/Y h:i A'),
            'tipo'              => $mov->tipo,
            'cantidad'          => $mov->cantidad,
            'stock_antes'       => $mov->stock_antes,
            'stock_despues'     => $mov->stock_despues,
            'motivo'            => $mov->motivo,
            'proveedor'         => $mov->proveedor,
            'factura_proveedor' => $mov->factura_proveedor,
            'notas'             => $mov->notas,
            'responsable'       => [
                'nombre'      => $usuarioResp ? $usuarioResp->nombre_completo : 'Sistema',
                'email'       => $usuarioResp?->email,
                'url'         => $usuarioResp ? route('admin.usuarios.show', $usuarioResp->id) : null,
                'tipo'        => $respTipo,
                'detalle'     => $respDetalle,
                'badge'       => $respBadge,
                'badge_class' => $respBadgeClass,
                'es_cliente'  => $esPorPedido,
            ],
            'producto_nombre'   => $mov->producto?->nombre ?? 'Producto no disponible',
            'producto_sku'      => $mov->variante?->sku ?? ($mov->producto?->sku ?? '—'),
            'producto_imagen'   => $mov->producto?->imagen_url ?? null,
            'variante'          => $labelVariante,
            'pedido_numero'     => $mov->pedido?->numero_pedido,
            'pedido_url'        => $mov->pedido_id ? route('admin.pedidos.detalle', $mov->pedido_id) : null,
        ];
    }

    // ─── 2. Stock Actual ───────────────────────────────────────────────────────

    public function stock(Request $request)
    {
        $qProductos = Producto::with(['categoria', 'imagenes', 'variantes' => function($q) use ($request) {
                $q->where('variantes_producto.activo', true)->with('opciones.tipo');
                if ($request->boolean('stock_bajo')) {
                    $q->join('productos', 'variantes_producto.producto_id', '=', 'productos.id')
                      ->whereRaw('variantes_producto.stock <= productos.stock_minimo')
                      ->select('variantes_producto.*');
                }
            }])
            ->sinEliminar()
            ->orderBy('nombre');

        if ($request->filled('q')) {
            $buscar = $request->q;
            $qProductos->where(function ($q) use ($buscar) {
                $q->whereRaw('unaccent(nombre) ILIKE unaccent(?)', ["%{$buscar}%"])
                  ->orWhereRaw('unaccent(sku) ILIKE unaccent(?)', ["%{$buscar}%"])
                  ->orWhereHas('variantes', function($qv) use($buscar) {
                      $qv->whereRaw('unaccent(sku) ILIKE unaccent(?)', ["%{$buscar}%"]);
                  });
            });
        }

        if ($request->filled('categoria')) {
            $qProductos->where('categoria_id', $request->categoria);
        }

        if ($request->boolean('stock_bajo')) {
            $qProductos->where(function ($q) {
                $q->whereDoesntHave('variantes', function($qv) {
                      $qv->where('activo', true);
                  })
                  ->whereRaw('stock <= stock_minimo')
                  ->orWhereHas('variantes', function($qv) {
                      $qv->where('activo', true)
                         ->whereRaw('variantes_producto.stock <= productos.stock_minimo');
                  });
            });
        }

        // Unificamos en una lista paginada por producto
        $productos = $qProductos->paginate(20)->withQueryString();

        $kpis       = $this->inventario->calcularKpis();
        $categorias = \App\Models\Categoria::orderBy('nombre')->get(['id', 'nombre']);

        return view('admin.inventario.index', compact('productos', 'kpis', 'categorias'))
            ->with('vista', 'stock');
    }

    // ─── 3. Registrar Entrada ──────────────────────────────────────────────────

    public function formularioEntrada()
    {
        $productos = Producto::sinEliminar()->orderBy('nombre')->get(['id', 'nombre', 'sku', 'stock']);
        $productosDataJson = $this->formatearProductosParaSelector($productos);
        return view('admin.inventario.entrada', compact('productos', 'productosDataJson'));
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

    public function formularioSalida()
    {
        $productos = Producto::sinEliminar()->orderBy('nombre')->get(['id', 'nombre', 'sku', 'stock']);
        $productosDataJson = $this->formatearProductosParaSelector($productos);
        return view('admin.inventario.salida', compact('productos', 'productosDataJson'));
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

    public function formularioAjuste()
    {
        $productos = Producto::sinEliminar()->orderBy('nombre')->get(['id', 'nombre', 'sku', 'stock', 'stock_minimo']);
        $productosDataJson = $this->formatearProductosParaSelector($productos, true);
        return view('admin.inventario.ajuste', compact('productos', 'productosDataJson'));
    }

    /**
     * Da formato a la lista de productos para el modal selector en JavaScript.
     */
    private function formatearProductosParaSelector($productos, bool $incluirMinimo = false)
    {
        return $productos->map(function ($p) use ($incluirMinimo) {
            $item = [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'sku' => $p->sku,
                'stock' => $p->stock,
                'imagen_ruta' => $p->imagen_url,
                'subtitulo' => $p->sku ? 'SKU: ' . $p->sku . ' | Stock: ' . $p->stock : 'Stock: ' . $p->stock,
                'icono' => 'inventory_2'
            ];

            if ($incluirMinimo) {
                $item['minimo'] = $p->stock_minimo;
            }

            return $item;
        })->values();
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

    // ─── 7. Exportación de Stock ───────────────────────────────────────────────

    public function exportarStockExcel(Request $request, \App\Services\AuditoriaService $auditoria)
    {
        $items = $this->inventario->obtenerDatosStockFiltrado($request);
        
        $auditoria->registrar('Inventario', 'Exportación Excel', 'Exportación de reporte de stock actual filtrado');
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StockActualExport($items), 'stock_actual_' . date('Y-m-d') . '.xlsx');
    }

    public function exportarStockPdf(Request $request, \App\Services\AuditoriaService $auditoria)
    {
        $items = $this->inventario->obtenerDatosStockFiltrado($request);
        
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
