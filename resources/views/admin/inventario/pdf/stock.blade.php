<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Stock Actual (Filtrado) - PayMe Panamá</title>
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
        .table-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
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
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
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
    <div class="report-title">Reporte de Stock Actual (Filtrado)</div>
    <div class="report-date">
        Fecha de Emisión: {{ \Carbon\Carbon::now()->format('d/m/Y \a \l\a\s H:i') }} | Generado por: Administrador
    </div>

    <!-- RESUMEN EJECUTIVO (KPIs) -->
    <table class="kpi-container">
        <tr>
            <td class="kpi-box">
                <div class="kpi-title">Items Mostrados</div>
                <div class="kpi-value">{{ number_format($totalItems) }} <span style="font-size: 10px; color: #64748b; font-weight: normal;">Líneas</span></div>
            </td>
            <td class="kpi-box">
                <div class="kpi-title">Unidades (Stock)</div>
                <div class="kpi-value">{{ number_format($totalStock) }} <span style="font-size: 10px; color: #64748b; font-weight: normal;">Unid.</span></div>
            </td>
            <td class="kpi-box" style="background-color: #ecfdf5; border-color: #a7f3d0;">
                <div class="kpi-title" style="color: #065f46;">Valorización (Filtrado)</div>
                <div class="kpi-value" style="color: #047857;">${{ number_format($valorizacionTotal, 2) }}</div>
            </td>
        </tr>
    </table>

    <!-- TABLA DE DATOS -->
    <div class="table-title">Detalle de Stock Actual</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="45%">Producto / Variante</th>
                <th width="15%">Categoría</th>
                <th width="10%" class="text-center">Stock</th>
                <th width="15%" class="text-right">Precio Unit. (USD)</th>
                <th width="15%" class="text-right">Valor Total (USD)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $minimo = $item->stock_minimo ?? 5;
                    $esCritico = $item->stock == 0 || $item->stock < ($minimo / 2);
                    $esBajo = $item->stock > 0 && $item->stock <= $minimo;
                @endphp
                <tr>
                    <td>
                        <strong style="color: #0f172a;">{{ $item->nombre_completo }}</strong><br>
                        <span style="font-size: 9px; color: #64748b; font-family: monospace;">SKU: {{ $item->sku ?? 'N/A' }}</span>
                    </td>
                    <td style="color: #475569;">{{ $item->categoria->nombre ?? 'N/A' }}</td>
                    <td class="text-center">
                        @if($esCritico)
                            <span class="badge badge-danger">{{ $item->stock }}</span>
                        @elseif($esBajo)
                            <span class="badge badge-warning">{{ $item->stock }}</span>
                        @else
                            <span class="badge badge-success">{{ $item->stock }}</span>
                        @endif
                    </td>
                    <td class="text-right" style="color: #475569;">${{ number_format($item->precio, 2) }}</td>
                    <td class="text-right"><strong style="color: #059669;">${{ number_format($item->stock * $item->precio, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">No se encontraron registros de stock para los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- FIRMAS DE RESPONSABILIDAD -->
    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Jefe de Bodega / Inventario</div>
            </td>
            <td>
                <div class="signature-line">Gerencia / Finanzas</div>
            </td>
        </tr>
    </table>

    <!-- FOOTER PÁGINAS -->
    <div class="footer">
        Documento Confidencial | PAYME PANAMÁ, S.A. | RUC: 155698745-2-2023 DV 85 | Página <span class="page-number"></span>
    </div>

</body>
</html>
