<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #002349;">Factura Generada - {{ \App\Models\Configuracion::obtener('empresa.nombre', 'PayMe Panamá') }}</h2>
        
        <p>Hola {{ $factura->usuario->nombre ?? 'Cliente' }},</p>
        
        <p>Adjunto encontrarás la factura <strong>{{ $factura->numero }}</strong> correspondiente a tu pedido.</p>
        
        @if($mensajePersonalizado)
            <div style="background-color: #f8f9ff; padding: 15px; border-left: 4px solid #002349; margin: 20px 0;">
                <p style="margin: 0;"><strong>Mensaje adicional:</strong><br>
                {{ $mensajePersonalizado }}</p>
            </div>
        @endif
        
        <p>
            <strong>Total:</strong> ${{ number_format($factura->total, 2) }}<br>
            <strong>Método de pago:</strong> {{ ucfirst($factura->metodo_pago) }}
        </p>

        <p>Gracias por confiar en {{ \App\Models\Configuracion::obtener('empresa.nombre', 'PayMe Panamá') }}.</p>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #777;">
            Este es un correo automático, por favor no respondas a esta dirección.
        </p>
    </div>
</body>
</html>
