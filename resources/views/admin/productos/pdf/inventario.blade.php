<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Valorización de Inventario - PayMe Panamá</title>
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
            margin: 10px 0 5px 0;
        }
        .report-date {
            text-align: center;
            font-size: 11px;
            color: #64748b;
            margin-bottom: 30px;
        }

        /* ----- RESUMEN EJECUTIVO (KPIs) ----- */
        .kpi-container {
            width: 100%;
            margin-bottom: 30px;
            border-spacing: 15px 0; /* Espaciado entre columnas */
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
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: 900;
            color: #059669;
        }

        /* ----- TABLA PRINCIPAL ----- */
        .table-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 10px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        table.data-table td {
            padding: 12px 8px;
            font-size: 10px;
            border-bottom: 1px dashed #e2e8f0;
            color: #1e293b;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) {
            background-color: #fbfcfd;
        }
        
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .product-name {
            font-weight: bold;
            color: #0f172a;
            display: block;
        }
        .product-sku {
            font-family: 'Courier New', Courier, monospace;
            color: #64748b;
            font-size: 9px;
            margin-top: 2px;
            display: block;
        }

        /* ----- BADGES ----- */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8px;
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
    <div class="report-title">Reporte de Valorización de Inventario</div>
    <div class="report-date">
        Fecha de Emisión: {{ \Carbon\Carbon::now()->format('d/m/Y \a \l\a\s H:i') }} | Generado por: Administrador
    </div>

    <!-- RESUMEN EJECUTIVO (KPIs) -->
    <table class="kpi-container">
        <tr>
            <td class="kpi-box">
                <div class="kpi-title">Artículos en Catálogo</div>
                <div class="kpi-value">{{ number_format($totalProductos) }} <span style="font-size: 10px; color: #64748b; font-weight: normal;">SKUs</span></div>
            </td>
            <td class="kpi-box">
                <div class="kpi-title">Unidades Físicas (Stock)</div>
                <div class="kpi-value">{{ number_format($totalStock) }} <span style="font-size: 10px; color: #64748b; font-weight: normal;">Unid.</span></div>
            </td>
            <td class="kpi-box" style="background-color: #ecfdf5; border-color: #a7f3d0;">
                <div class="kpi-title" style="color: #065f46;">Valorización Total</div>
                <div class="kpi-value" style="color: #047857;">${{ number_format($valorizacionTotal, 2) }}</div>
            </td>
        </tr>
    </table>

    <!-- TABLA DE DATOS -->
    <div class="table-title">Detalle de Inventario por Producto</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="45%">Producto y SKU</th>
                <th width="15%">Categoría</th>
                <th width="10%" class="text-center">Stock</th>
                <th width="15%" class="text-right">Precio Unit. (USD)</th>
                <th width="15%" class="text-right">Valor Total (USD)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productos as $prod)
                @php
                    $valor = $prod->stock * $prod->precio;
                @endphp
                <tr>
                    <td>
                        <span class="product-name">{{ Str::limit($prod->nombre, 60) }}</span>
                        <span class="product-sku">SKU: {{ $prod->sku ?? 'N/A' }}</span>
                    </td>
                    <td style="color: #64748b; font-weight: 500;">
                        {{ $prod->categoria->nombre ?? 'N/A' }}
                    </td>
                    <td class="text-center">
                        @if($prod->stock > ($prod->stock_minimo ?? 5))
                            <span class="badge badge-success">{{ $prod->stock }}</span>
                        @elseif($prod->stock > 0)
                            <span class="badge badge-warning">{{ $prod->stock }}</span>
                        @else
                            <span class="badge badge-danger">0</span>
                        @endif
                    </td>
                    <td class="text-right" style="font-weight: 500;">
                        ${{ number_format($prod->precio, 2) }}
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">
                        ${{ number_format($valor, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 30px; color: #94a3b8;">
                        No existen registros de productos en el catálogo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SECCIÓN DE FIRMAS PARA VALIDEZ -->
    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Elaborado por (Dpto. de Bodega)</div>
            </td>
            <td>
                <div class="signature-line">Aprobado por (Gerencia)</div>
            </td>
        </tr>
    </table>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        Documento Confidencial | PayMe Panamá, S.A. | RUC: 155698745-2-2023 DV 85 | Página <span class="page-number"></span>
    </div>
    
</body>
</html>
