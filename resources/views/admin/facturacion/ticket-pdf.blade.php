<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $factura->numero }}</title>
    <style>
        @page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            line-height: 1.25;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            width: 100%;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Contenedor central con margen de seguridad garantizado para 80mm */
        .ticket-wrapper {
            width: 196pt;
            margin: 0 auto;
            padding: 8pt 0 14pt 0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .ticket-logo {
            max-width: 80px;
            max-height: 38px;
            margin: 0 auto 4px auto;
            display: block;
        }

        .company-name {
            font-size: 11.5px;
            font-weight: bold;
            color: #000000;
            letter-spacing: -0.2px;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .company-meta {
            font-size: 8px;
            color: #000000;
            line-height: 1.2;
        }

        .divider {
            border: none;
            border-top: 1px dashed #000000;
            margin: 5px 0;
            width: 100%;
        }

        .doc-title {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.3px;
            color: #000000;
        }

        .doc-number {
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            margin-top: 1px;
        }

        /* Tabla de metadatos */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin: 3px 0;
        }

        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
            color: #000000;
        }

        .meta-label {
            color: #000000;
            width: 38%;
            text-align: left;
        }

        .meta-value {
            color: #000000;
            width: 62%;
            text-align: right;
            font-weight: normal;
        }

        /* Tabla de artículos */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin: 3px 0;
        }

        .items-table th {
            text-align: left;
            padding: 3px 0;
            border-bottom: 1px dashed #000000;
            font-size: 7.5px;
            font-weight: bold;
            color: #000000;
        }

        .items-table td {
            padding: 2.5px 0;
            vertical-align: top;
            border-bottom: 1px dotted #000000;
            color: #000000;
        }

        .item-name {
            font-weight: bold;
            color: #000000;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .item-variant {
            font-size: 7px;
            color: #000000;
            word-break: break-word;
        }

        .item-unit {
            font-size: 7px;
            color: #000000;
        }

        /* Tabla de totales */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin: 3px 0;
        }

        .totals-table td {
            padding: 1.5px 0;
            color: #000000;
        }

        .total-highlight {
            font-size: 11px;
            font-weight: bold;
            color: #000000;
            border-top: 1px dashed #000000;
            border-bottom: 1px dashed #000000;
            padding-top: 3px !important;
            padding-bottom: 3px !important;
        }

        .badge-status {
            display: inline-block;
            border: 1px solid #000000;
            color: #000000;
            background-color: transparent;
            padding: 2px 6px;
            font-weight: bold;
            font-size: 8px;
            letter-spacing: 0.3px;
            margin: 3px 0;
        }

        .notes-box {
            width: 100%;
            box-sizing: border-box;
            border: 1px dashed #000000;
            padding: 3px 5px;
            margin: 4px 0;
            font-size: 7.5px;
            line-height: 1.25;
            text-align: left;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            color: #000000;
        }

        .notes-box strong {
            display: block;
            margin-bottom: 1px;
            font-size: 7.5px;
        }

        .footer-text {
            font-size: 7.5px;
            color: #000000;
            line-height: 1.25;
        }
    </style>
</head>
<body>

    @php
        // Procesar notas del pedido evitando desbordes o JSON técnico
        $notaCliente = !empty($factura->pedido?->notas_cliente) ? trim($factura->pedido->notas_cliente) : null;
        $detallesEnvio = null;
        $notaInternaTexto = null;

        if (!empty($factura->pedido?->notas_internas)) {
            $raw = trim($factura->pedido->notas_internas);
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                if (!empty($decoded['metodo_entrega'])) {
                    if ($decoded['metodo_entrega'] === 'retiro_local' || $decoded['metodo_entrega'] === 'tienda_fisica') {
                        $partes[] = 'Venta en Tienda Física';
                    } elseif ($decoded['metodo_entrega'] === 'retiro_courier') {
                        $partes[] = 'Retiro en Courier';
                    } elseif ($decoded['metodo_entrega'] === 'delivery') {
                        $partes[] = 'Envío a Domicilio';
                    }
                }
                if (!empty($decoded['courier_sucursal_id'])) {
                    $partes[] = 'Sucursal #' . $decoded['courier_sucursal_id'];
                }
                if (!empty($decoded['contacto']['nombre'])) {
                    $partes[] = 'Contacto: ' . $decoded['contacto']['nombre'] . ' ' . ($decoded['contacto']['apellido'] ?? '');
                }
                if (!empty($decoded['contacto']['telefono1'])) {
                    $partes[] = 'Tel: ' . $decoded['contacto']['telefono1'];
                }
                if (!empty($partes)) {
                    $detallesEnvio = implode(' | ', $partes);
                }
            } elseif ($raw !== 'Pedido registrado manualmente desde panel de administración.') {
                $notaInternaTexto = $raw;
            }
        }
    @endphp

    <div class="ticket-wrapper">

        <!-- Logotipo de la Empresa (Monocromático / Blanco y Negro) -->
        @if(!empty($datosEmpresa['logo_src']))
            <div class="text-center">
                <img src="{{ $datosEmpresa['logo_src'] }}" alt="{{ $datosEmpresa['nombre'] ?? 'Logo' }}" class="ticket-logo">
            </div>
        @endif

        <!-- Encabezado de la Empresa -->
        <div class="text-center">
            <div class="company-name">{{ $datosEmpresa['nombre'] ?? 'PayMe Panamá' }}</div>
            <div class="company-meta">RUC: {{ $datosEmpresa['ruc'] ?? 'No Registrado' }}</div>
            <div class="company-meta">{{ $datosEmpresa['direccion'] ?? 'Ciudad de Panamá, Panamá' }}</div>
            @if(!empty($datosEmpresa['telefono']))
                <div class="company-meta">Tel: {{ $datosEmpresa['telefono'] }}</div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Título y Número de Documento -->
        <div class="text-center">
            <div class="doc-title uppercase">TICKET / RECIBO POS</div>
            <div class="doc-number">No. {{ $factura->numero }}</div>
            @if($factura->pedido)
                <div style="font-size: 8px; color: #000000; margin-top: 1px;">
                    Pedido Ref: <span class="bold">{{ $factura->pedido->numero_pedido }}</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Metadatos de la Venta -->
        <table class="meta-table">
            <tr>
                <td class="meta-label">Fecha y Hora:</td>
                <td class="meta-value">{{ $factura->emitida_en ? $factura->emitida_en->format('d/m/Y h:i A') : date('d/m/Y h:i A') }}</td>
            </tr>
            @if($factura->pedido && $factura->pedido->vendedor)
                <tr>
                    <td class="meta-label">Cajero / Vendedor:</td>
                    <td class="meta-value bold">{{ $factura->pedido->vendedor->nombre }}</td>
                </tr>
            @endif
            <tr>
                <td class="meta-label">Cliente:</td>
                <td class="meta-value bold">
                    {{ $factura->usuario ? $factura->usuario->nombre . ' ' . ($factura->usuario->apellido ?? '') : 'Consumidor Final' }}
                </td>
            </tr>
            @if($factura->usuario && $factura->usuario->telefono)
                <tr>
                    <td class="meta-label">Teléfono:</td>
                    <td class="meta-value">{{ $factura->usuario->telefono }}</td>
                </tr>
            @endif
            <tr>
                <td class="meta-label">Método de Pago:</td>
                <td class="meta-value bold uppercase">{{ str_replace('_', ' ', $factura->metodo_pago ?? 'Efectivo') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <!-- Listado de Artículos -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 14%;">CANT</th>
                    <th style="width: 58%;">DESCRIPCIÓN</th>
                    <th style="width: 28%;" class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @if($factura->pedido && $factura->pedido->items)
                    @foreach($factura->pedido->items as $item)
                        <tr>
                            <td class="bold">{{ $item->cantidad }}</td>
                            <td>
                                <div class="item-name">{{ $item->producto->nombre ?? 'Producto' }}</div>
                                @if($item->variante)
                                    <div class="item-variant">{{ $item->variante->nombre }}</div>
                                @endif
                                <div class="item-unit">x ${{ number_format($item->precio_unitario, 2) }}</div>
                            </td>
                            <td class="text-right bold">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="3" class="text-center" style="color: #000000; padding: 6px 0;">
                            No hay artículos registrados
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Desglose de Totales -->
        <table class="totals-table">
            <tr>
                <td>SUBTOTAL:</td>
                <td class="text-right bold">${{ number_format($factura->subtotal, 2) }}</td>
            </tr>
            @if((float) $factura->descuento > 0)
                <tr>
                    <td>DESCUENTO:</td>
                    <td class="text-right bold">-${{ number_format($factura->descuento, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>ITBMS ({{ number_format($factura->itbms_tasa, 0) }}%):</td>
                <td class="text-right bold">${{ number_format($factura->itbms_monto, 2) }}</td>
            </tr>
            @if((float) $factura->costo_envio > 0)
                <tr>
                    <td>ENVÍO:</td>
                    <td class="text-right bold">${{ number_format($factura->costo_envio, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td style="font-size: 8px;">ENTREGA:</td>
                    <td class="text-right" style="font-size: 8px;">RETIRO EN LOCAL</td>
                </tr>
            @endif
            <tr>
                <td class="total-highlight">TOTAL:</td>
                <td class="total-highlight text-right">${{ number_format($factura->total, 2) }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <!-- Estado de la Venta -->
        <div class="text-center">
            <div class="badge-status uppercase">
                {{ $factura->estado === 'emitida' ? 'VENTA PAGADA / CONFIRMADA' : strtoupper($factura->estado) }}
            </div>
        </div>

        <!-- Sección de Notas y Observaciones (Sin desbordes) -->
        @if($notaCliente)
            <div class="notes-box">
                <strong>NOTAS DEL CLIENTE:</strong>
                {!! nl2br(e($notaCliente)) !!}
            </div>
        @endif

        @if($detallesEnvio)
            <div class="notes-box">
                <strong>DATOS DE ENTREGA:</strong>
                {{ $detallesEnvio }}
            </div>
        @endif

        @if($notaInternaTexto)
            <div class="notes-box">
                <strong>OBSERVACIONES:</strong>
                {!! nl2br(e($notaInternaTexto)) !!}
            </div>
        @endif

        <!-- Pie de Página -->
        <div class="text-center" style="margin-top: 4px;">
            <div class="bold" style="font-size: 8.5px; color: #000000;">
                ¡GRACIAS POR SU COMPRA!
            </div>
            <div class="footer-text" style="margin-top: 1px;">
                Conserve este comprobante para cualquier reclamo o garantía.
            </div>
            <div class="footer-text" style="margin-top: 2px;">
                {{ config('app.url', 'www.paymepanama.com') }}
            </div>
        </div>

    </div>

</body>
</html>
