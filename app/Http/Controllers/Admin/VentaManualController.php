<?php

namespace App\Http\Controllers\Admin;

use App\Models\Direccion;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\ZonaEnvio;
use App\Services\PedidoService;
use App\Services\VentaManualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Controlador especializado para el Punto de Venta (POS) y registro
 * de ventas manuales / mostrador.
 *
 * Hereda de PedidoController para mantener cohesión de módulo y reutilizar
 * servicios comunes sin sobrecargar la gestión general de pedidos.
 */
class VentaManualController extends PedidoController
{
    protected VentaManualService $ventaManualService;

    public function __construct(
        PedidoService $pedidoService,
        VentaManualService $ventaManualService
    ) {
        parent::__construct($pedidoService);
        $this->ventaManualService = $ventaManualService;
    }

    /**
     * Muestra la interfaz del Punto de Venta (POS) y selección de productos.
     */
    public function crear()
    {
        $clientes = Usuario::whereHas('roles', function ($q) {
                $q->where('name', 'cliente');
            })
            ->select('id', 'nombre', 'apellido', 'email', 'telefono')
            ->orderBy('nombre')
            ->get();

        $productos = Producto::with(['imagenes', 'variantes.opciones.tipo'])
            ->sinEliminar()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'precio' => (float) $p->precio,
                    'stock' => (int) $p->stock,
                    'imagen' => $p->imagen_url,
                    'es_digital' => (bool) $p->es_digital,
                    'variantes' => $p->variantes->map(function ($v) {
                        return [
                            'id' => $v->id,
                            'nombre' => $v->nombre,
                            'precio' => (float) $v->precio,
                            'stock' => (int) $v->stock,
                        ];
                    }),
                ];
            });

        $zonasEnvio = ZonaEnvio::where('activo', true)->orderBy('nombre')->get();

        return view('admin.pedidos.crear', compact('clientes', 'productos', 'zonasEnvio'));
    }

    /**
     * Procesa y registra la venta manual en el sistema.
     */
    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'tipo_cliente' => 'required|in:existente,nuevo',
            'usuario_id' => 'required_if:tipo_cliente,existente|nullable|exists:usuarios,id',
            'nuevo_nombre' => 'required_if:tipo_cliente,nuevo|nullable|string|max:100',
            'nuevo_apellido' => 'nullable|string|max:100',
            'nuevo_email' => 'required_if:tipo_cliente,nuevo|nullable|email|max:150',
            'nuevo_telefono' => 'nullable|string|max:25',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.variante_id' => 'nullable|exists:variantes_producto,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|string|in:efectivo,yappy,punto_venta,transferencia,contra_entrega',
            'tipo_entrega' => 'required|string|in:retiro_local,envio_domicilio',
            'zona_envio_id' => 'nullable|exists:zonas_envio,id',
            'costo_envio' => 'nullable|numeric|min:0',
            'direccion_exacta' => 'nullable|string|max:255',
            'descuento_manual' => 'nullable|numeric|min:0',
            'notas_cliente' => 'nullable|string|max:500',
            'notas_internas' => 'nullable|string|max:500',
            'marcar_pagado' => 'nullable',
            'emitir_factura' => 'nullable',
        ]);

        try {
            $usuarioId = $validated['usuario_id'] ?? null;

            if ($validated['tipo_cliente'] === 'nuevo') {
                $email = strtolower(trim($validated['nuevo_email']));
                $existente = Usuario::where('email', $email)->first();

                if ($existente) {
                    $usuarioId = $existente->id;
                } else {
                    $clienteNuevo = Usuario::create([
                        'nombre' => trim($validated['nuevo_nombre']),
                        'apellido' => trim($validated['nuevo_apellido'] ?? ''),
                        'email' => $email,
                        'telefono' => trim($validated['nuevo_telefono'] ?? '00000000'),
                        'password_hash' => Hash::make(Str::random(16)),
                    ]);
                    $clienteNuevo->assignRole('cliente');
                    $usuarioId = $clienteNuevo->id;
                }
            }

            $direccionId = null;
            if ($validated['tipo_entrega'] === 'envio_domicilio' && !empty($validated['direccion_exacta'])) {
                $dir = Direccion::create([
                    'usuario_id' => $usuarioId,
                    'alias' => 'Envío mostrador',
                    'nombre_receptor' => $validated['nuevo_nombre'] ?? 'Cliente mostrador',
                    'telefono' => $validated['nuevo_telefono'] ?? '00000000',
                    'provincia' => 'Panamá',
                    'distrito' => 'Panamá',
                    'corregimiento' => 'San Francisco',
                    'direccion_exacta' => $validated['direccion_exacta'],
                    'zona_envio_id' => $validated['zona_envio_id'] ?? null,
                ]);
                $direccionId = $dir->id;
            }

            $datosVenta = [
                'usuario_id' => $usuarioId,
                'items' => $validated['items'],
                'metodo_pago' => $validated['metodo_pago'],
                'direccion_id' => $direccionId,
                'zona_envio_id' => $validated['tipo_entrega'] === 'envio_domicilio' ? ($validated['zona_envio_id'] ?? null) : null,
                'costo_envio' => $validated['tipo_entrega'] === 'envio_domicilio' ? (float) ($validated['costo_envio'] ?? 0.00) : 0.00,
                'descuento_manual' => (float) ($validated['descuento_manual'] ?? 0.00),
                'notas_cliente' => $validated['notas_cliente'] ?? null,
                'notas_internas' => $validated['notas_internas'] ?? 'Pedido registrado manualmente desde panel de administración.',
                'marcar_pagado' => $request->boolean('marcar_pagado'),
                'emitir_factura' => $request->boolean('emitir_factura'),
            ];

            // Cualquier administrador o superadmin autenticado queda registrado como el vendedor/autor
            $vendedorId = Auth::id() ?? 1;
            $pedido = $this->ventaManualService->registrarVentaManual($datosVenta, $vendedorId);

            return redirect()->route('admin.pedidos.detalle', $pedido->id)
                ->with('toast_success', "Venta {$pedido->numero_pedido} registrada exitosamente.");
        } catch (\Exception $e) {
            return back()->withInput()->with('toast_error', $e->getMessage());
        }
    }

    /**
     * Creación rápida de cliente desde el modal de Venta Manual / POS.
     */
    public function clienteRapido(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'nullable|string|max:100',
            'email' => 'required|email|max:150',
            'telefono' => 'nullable|string|max:25',
        ]);

        $email = strtolower(trim($validated['email']));
        $cliente = Usuario::where('email', $email)->first();

        if (!$cliente) {
            $cliente = Usuario::create([
                'nombre' => trim($validated['nombre']),
                'apellido' => trim($validated['apellido'] ?? ''),
                'email' => $email,
                'telefono' => trim($validated['telefono'] ?? '00000000'),
                'password_hash' => Hash::make(Str::random(16)),
            ]);
            $cliente->assignRole('cliente');
        }

        return response()->json([
            'success' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'apellido' => $cliente->apellido,
                'email' => $cliente->email,
                'telefono' => $cliente->telefono,
            ],
            'mensaje' => 'Cliente seleccionado exitosamente.',
        ]);
    }
}
