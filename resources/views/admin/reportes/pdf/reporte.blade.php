<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de {{ ucfirst($tipoReporte) }}</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Figtree', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #059669; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; color: #1F2937; }
        .header p { margin: 5px 0 0 0; color: #6B7280; }
        .stats { margin-bottom: 20px; }
        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .stats-table th, .stats-table td { padding: 8px; text-align: center; border: 1px solid #E5E7EB; }
        .stats-table th { background-color: #F9FAFB; font-weight: bold; color: #374151; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table th, table.data-table td { padding: 8px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        table.data-table th { background-color: #F3F4F6; font-weight: bold; color: #111827; }
        table.data-table tr:nth-child(even) { background-color: #F9FAFB; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-danger { background-color: #FEE2E2; color: #B91C1C; }
        .badge-warning { background-color: #FEF3C7; color: #B45309; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 30px; text-align: center; font-size: 10px; color: #9CA3AF; border-top: 1px solid #E5E7EB; padding-top: 10px; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h1>PayMe Panamá - Reporte de {{ ucfirst($tipoReporte) }}</h1>
        <p>Periodo: {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }}</p>
    </div>

    @if($tipoReporte === 'ventas')
        <table class="stats-table">
            <thead>
                <tr>
                    <th>Total Ventas</th>
                    <th>Pedidos</th>
                    <th>Ticket Promedio</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>${{ number_format($totalVentas, 2) }}</td>
                    <td>{{ $numeroPedidos }}</td>
                    <td>${{ number_format($ticketPromedio, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha / Periodo</th>
                    <th>Total Ventas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorPeriodo as $item)
                    <tr>
                        <td>{{ $item['etiqueta'] }}</td>
                        <td>${{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
                @if(empty($ventasPorPeriodo))
                    <tr><td colspan="2" style="text-align: center;">No hay datos para el periodo seleccionado.</td></tr>
                @endif
            </tbody>
        </table>

    @elseif($tipoReporte === 'productos')
        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th>Total Vendido</th>
                    <th>Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productosMasVendidos as $producto)
                    <tr>
                        <td>{{ $producto->sku }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->total_vendido }}</td>
                        <td>${{ number_format($producto->ingresos_generados, 2) }}</td>
                    </tr>
                @endforeach
                @if($productosMasVendidos->isEmpty())
                    <tr><td colspan="4" style="text-align: center;">No hay datos para el periodo seleccionado.</td></tr>
                @endif
            </tbody>
        </table>

    @elseif($tipoReporte === 'clientes')
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>Pedidos</th>
                    <th>Total Gastado</th>
                    <th>Último Pedido</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientesFrecuentes as $cliente)
                    <tr>
                        <td>{{ $cliente->nombre }} {{ $cliente->apellido }}</td>
                        <td>{{ $cliente->email }}</td>
                        <td>{{ $cliente->total_pedidos }}</td>
                        <td>${{ number_format($cliente->total_gastado, 2) }}</td>
                        <td>{{ \Carbon\Carbon::parse($cliente->ultimo_pedido_en)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
                @if($clientesFrecuentes->isEmpty())
                    <tr><td colspan="5" style="text-align: center;">No hay datos para el periodo seleccionado.</td></tr>
                @endif
            </tbody>
        </table>

    @elseif($tipoReporte === 'stock')
        <table class="data-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockCritico as $producto)
                    @php
                        $esCritico = $producto->stock == 0 || $producto->stock < ($producto->stock_minimo / 2);
                    @endphp
                    <tr>
                        <td>{{ $producto->sku }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->stock }}</td>
                        <td>{{ $producto->stock_minimo }}</td>
                        <td>
                            <span class="badge {{ $esCritico ? 'badge-danger' : 'badge-warning' }}">
                                {{ $esCritico ? 'Crítico' : 'Bajo' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                @if($stockCritico->isEmpty())
                    <tr><td colspan="5" style="text-align: center;">No hay productos con stock crítico.</td></tr>
                @endif
            </tbody>
        </table>
    @endif

    <div class="footer">
        Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} - Página <span class="page-number"></span>
    </div>
</body>
</html>
