<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte Ejecutivo - PayMe Panamá</title>
    <style>
        /* Tipografías limpias y profesionales */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        /* ----- HEADER ----- */
        .header-table {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #059669;
            padding-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo {
            max-width: 160px;
            height: auto;
        }
        .company-details {
            text-align: right;
            font-size: 10px;
            color: #475569;
            line-height: 1.4;
        }
        .company-details strong {
            font-size: 14px;
            color: #0f172a;
            display: block;
            margin-bottom: 3px;
        }

        /* ----- TÍTULO ----- */
        .report-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .report-date {
            text-align: center;
            font-size: 10px;
            color: #64748b;
            margin-bottom: 25px;
        }

        /* ----- KPIs (Resumen Ejecutivo) ----- */
        .kpi-container {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 30px;
        }
        .kpi-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            width: 33.33%;
        }
        .kpi-title {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
        }

        /* ----- TABLAS DE DATOS ----- */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            padding: 10px 12px;
            border-bottom: 1px dashed #cbd5e1;
            text-align: left;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e1;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        /* ----- BADGES ----- */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-warning { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* ----- FOOTER Y FIRMAS ----- */
        .signatures {
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signatures td {
            width: 50%;
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #94a3b8;
            margin: 0 auto;
            margin-top: 40px;
            padding-top: 5px;
            font-weight: bold;
            font-size: 10px;
            color: #334155;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    @php
        // Extraer la imagen a Base64 para garantizar compatibilidad con DomPDF
        $logoPath = public_path('images/logo.png');
        $base64Logo = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $base64Logo = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
    @endphp

    <!-- HEADER: Logo y Datos de la Empresa -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                @if($base64Logo)
                    <img src="{{ $base64Logo }}" class="logo" alt="PayMe Panamá Logo">
                @else
                    <h1 style="color: #059669; margin: 0; font-size: 24px;">PAYME PANAMÁ</h1>
                @endif
            </td>
            <td class="company-details">
                <strong>PAYME PANAMÁ, S.A.</strong><br>
                RUC: 155698745-2-2023 DV 85<br>
                Edificio Century Tower, Piso 4, Oficina 402<br>
                Ciudad de Panamá, Panamá<br>
                Tel: +507 830-5555 | hola@paymepanama.com
            </td>
        </tr>
    </table>

    <!-- TÍTULO DEL REPORTE -->
    <div class="report-title">Reporte Ejecutivo de {{ $tipoReporte === 'completo' ? 'Inteligencia de Negocio' : ucfirst($tipoReporte) }}</div>
    <div class="report-date">
        Periodo: {{ $fechaInicio->format('d/m/Y') }} al {{ $fechaFin->format('d/m/Y') }} | Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>

    <!-- RESUMEN EJECUTIVO (KPIs) -->
    @if(in_array($tipoReporte, ['ventas', 'completo']))
    <table class="kpi-container">
        <tr>
            <td class="kpi-box" style="background-color: #ecfdf5; border-color: #a7f3d0;">
                <div class="kpi-title" style="color: #065f46;">Ingresos Totales</div>
                <div class="kpi-value" style="color: #047857;">${{ number_format($totalVentas, 2) }}</div>
            </td>
            <td class="kpi-box">
                <div class="kpi-title">Total de Pedidos</div>
                <div class="kpi-value">{{ number_format($numeroPedidos) }}</div>
            </td>
            <td class="kpi-box">
                <div class="kpi-title">Ticket Promedio</div>
                <div class="kpi-value">${{ number_format($ticketPromedio, 2) }}</div>
            </td>
        </tr>
    </table>
    @endif

    <!-- SECCIÓN: VENTAS POR PERIODO -->
    @if(in_array($tipoReporte, ['ventas', 'completo']))
    <div class="section-title">Resumen de Ingresos por Periodo</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="40%">Fecha / Periodo</th>
                <th width="30%" class="text-right">Total Descuentos</th>
                <th width="30%" class="text-right">Total Ventas (USD)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventasPorPeriodo as $item)
                <tr>
                    <td><strong>{{ $item['etiqueta'] }}</strong></td>
                    <td class="text-right" style="color: #64748b;">${{ number_format($item['descuentos'], 2) }}</td>
                    <td class="text-right"><strong>${{ number_format($item['total'], 2) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">No hay datos para el periodo seleccionado.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- SECCIÓN: TOP PRODUCTOS -->
    @if(in_array($tipoReporte, ['productos', 'completo']))
    <div class="section-title">Top Productos Más Vendidos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">SKU</th>
                <th width="45%">Producto</th>
                <th width="20%" class="text-center">Unid. Vendidas</th>
                <th width="20%" class="text-right">Ingresos (USD)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productosMasVendidos as $producto)
                <tr>
                    <td style="color: #64748b; font-size: 10px;">{{ $producto->sku }}</td>
                    <td><strong>{{ $producto->nombre }}</strong></td>
                    <td class="text-center">{{ $producto->total_vendido }}</td>
                    <td class="text-right" style="color: #059669; font-weight: bold;">${{ number_format($producto->ingresos_generados, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No hay datos para el periodo seleccionado.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- SECCIÓN: MEJORES CLIENTES -->
    @if(in_array($tipoReporte, ['clientes', 'completo']))
    <div class="section-title">Mejores Clientes</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="30%">Cliente</th>
                <th width="30%">Email</th>
                <th width="15%" class="text-center">Pedidos</th>
                <th width="25%" class="text-right">Total Gastado (USD)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clientesFrecuentes as $cliente)
                <tr>
                    <td><strong>{{ $cliente->nombre }} {{ $cliente->apellido }}</strong></td>
                    <td style="color: #64748b;">{{ $cliente->email }}</td>
                    <td class="text-center">{{ $cliente->total_pedidos }}</td>
                    <td class="text-right" style="color: #7c3aed; font-weight: bold;">${{ number_format($cliente->total_gastado, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">No hay datos para el periodo seleccionado.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- SECCIÓN: STOCK CRÍTICO -->
    @if(in_array($tipoReporte, ['stock', 'completo']))
    <div class="section-title">Alerta de Stock Crítico</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">SKU</th>
                <th width="45%">Producto</th>
                <th width="15%" class="text-center">Stock Actual</th>
                <th width="15%" class="text-center">Mínimo Req.</th>
                <th width="10%" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockCritico as $producto)
                @php
                    $esCritico = $producto->stock == 0 || $producto->stock < ($producto->stock_minimo / 2);
                @endphp
                <tr>
                    <td style="color: #64748b; font-size: 10px;">{{ $producto->sku }}</td>
                    <td><strong>{{ $producto->nombre }}</strong></td>
                    <td class="text-center" style="color: {{ $esCritico ? '#e11d48' : '#d97706' }}; font-weight: bold;">{{ $producto->stock }}</td>
                    <td class="text-center" style="color: #64748b;">{{ $producto->stock_minimo }}</td>
                    <td class="text-center">
                        <span class="badge {{ $esCritico ? 'badge-danger' : 'badge-warning' }}">
                            {{ $esCritico ? 'Crítico' : 'Bajo' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No hay productos con stock crítico en este momento.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- FIRMAS DE VALIDEZ -->
    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Departamento de Finanzas</div>
            </td>
            <td>
                <div class="signature-line">Gerencia General</div>
            </td>
        </tr>
    </table>

    <!-- FOOTER PÁGINAS -->
    <div class="footer">
        Documento Confidencial | PAYME PANAMÁ, S.A. | RUC: 155698745-2-2023 DV 85 | Página <span class="page-number"></span>
    </div>

</body>
</html>
