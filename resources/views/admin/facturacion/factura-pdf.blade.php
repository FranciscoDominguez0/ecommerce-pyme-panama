<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
        }
        .header td {
            vertical-align: top;
        }
        .logo {
            max-width: 110px;
            margin-bottom: 15px;
        }
        .company-info {
            color: #555;
            line-height: 1.6;
        }
        .invoice-title-container {
            text-align: right;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #002349;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .invoice-meta {
            font-size: 13px;
            color: #555;
            line-height: 1.6;
        }
        .billing-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .billing-section td {
            vertical-align: top;
            width: 50%;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #002349;
            border-bottom: 2px solid #002349;
            padding-bottom: 5px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .customer-info {
            line-height: 1.6;
            color: #444;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f4f6f8;
            color: #002349;
            font-weight: bold;
            text-align: left;
            padding: 12px 10px;
            border-bottom: 2px solid #002349;
            font-size: 12px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            color: #333;
            vertical-align: top;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .items-table tr:nth-child(even) td {
            background-color: #fcfcfc;
        }
        .totals-container {
            width: 100%;
        }
        .totals-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px 12px;
            color: #333;
        }
        .totals-table .label {
            font-weight: bold;
            color: #555;
        }
        .totals-table .amount {
            text-align: right;
        }
        .totals-table .total-row td {
            font-size: 18px;
            font-weight: bold;
            color: #002349;
            border-top: 2px solid #002349;
            padding-top: 15px;
            margin-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .badge {
            font-size: 12px;
            font-weight: bold;
        }
        .notes {
            margin-top: 40px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #002349;
            font-size: 12px;
            color: #555;
            width: 50%;
            float: left;
        }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td style="width: 50%;">
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="PyME Panamá">
                <div class="company-info">
                    <strong>PyME Panamá</strong><br>
                    PH Obarrio 60, Piso 8, Oficina 802<br>
                    Bella Vista, Ciudad de Panamá<br>
                    RUC: 155698745-2-2023 DV 45<br>
                    info@pymepanama.com | +507 830-4500
                </div>
            </td>
            <td style="width: 50%;" class="invoice-title-container">
                <div class="invoice-title">Factura</div>
                <div class="invoice-meta">
                    @php
                        $colorEstado = match($factura->estado) {
                            'emitida' => '#006c47', // Verde secundario
                            'anulada' => '#ba1a1a', // Rojo error
                            default => '#43474e',
                        };
                    @endphp
                    <strong>Nº de Factura:</strong> {{ $factura->numero }}<br>
                    <strong>Fecha de Emisión:</strong> {{ $factura->emitida_en->format('d M, Y') }}<br>
                    <strong>Vencimiento:</strong> {{ $factura->emitida_en->addDays(15)->format('d M, Y') }}<br>
                    <strong>Pedido Relacionado:</strong> {{ $factura->pedido->numero_pedido }}<br>
                    <strong>Estado:</strong> <span class="badge" style="color: {{ $colorEstado }};">{{ strtoupper(str_replace('_', ' ', $factura->estado)) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="billing-section">
        <tr>
            <td style="padding-right: 30px;">
                <div class="section-title">Facturar a</div>
                <div class="customer-info">
                    <strong>{{ $factura->usuario->nombre }} {{ $factura->usuario->apellido }}</strong><br>
                    {{ $factura->usuario->email }}<br>
                    @if($factura->pedido->direccion)
                        {{ $factura->pedido->direccion->direccion_exacta }}<br>
                        {{ $factura->pedido->direccion->corregimiento }}, {{ $factura->pedido->direccion->distrito }}<br>
                        {{ $factura->pedido->direccion->provincia }}
                    @else
                        <em>Retiro en tienda o sin dirección registrada</em>
                    @endif
                </div>
            </td>
            <td>
                <div class="section-title">Información de Pago</div>
                <div class="customer-info">
                    <strong>Método:</strong> {{ ucfirst(str_replace('_', ' ', $factura->metodo_pago)) }}<br>
                    @if($factura->referencia_pago_externo)
                        <strong>Referencia:</strong> Comprobante Adjunto<br>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">Cant</th>
                <th style="width: 50%;">Descripción</th>
                <th style="width: 20%; text-align: right;">Precio Unit.</th>
                <th style="width: 20%; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factura->pedido->items as $item)
            <tr>
                <td class="text-center">{{ $item->cantidad }}</td>
                <td>
                    <strong>{{ $item->producto->nombre }}</strong>
                    <div style="font-size: 11px; color: #777; margin-top: 3px;">
                        SKU: {{ $item->variante ? $item->variante->sku : $item->producto->sku }}
                    </div>
                </td>
                <td class="text-right">${{ number_format($item->precio_unitario, 2) }}</td>
                <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-container">
        <div class="notes">
            <strong>Notas Adicionales:</strong><br>
            Cualquier consulta relacionada con esta factura, por favor contáctenos a soporte@paymepanama.com o llámenos al +507 830-4500.
        </div>
        
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="amount">${{ number_format($factura->subtotal, 2) }}</td>
            </tr>
            @if($factura->descuento > 0)
            <tr>
                <td class="label">Descuento</td>
                <td class="amount" style="color: #006c47;">-${{ number_format($factura->descuento, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Envío</td>
                <td class="amount">${{ number_format($factura->costo_envio, 2) }}</td>
            </tr>
            <tr>
                <td class="label">ITBMS ({{ number_format($factura->itbms_tasa, 0) }}%)</td>
                <td class="amount">${{ number_format($factura->itbms_monto, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">TOTAL</td>
                <td class="amount">${{ number_format($factura->total, 2) }}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        Este documento es una representación impresa de un Comprobante Fiscal Digital.<br>
        Gracias por su preferencia. | <strong>PyME Panamá</strong>
    </div>

</body>
</html>