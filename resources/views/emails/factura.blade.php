<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $factura->numero }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding-bottom: 60px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif; color: #1e293b; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-top: 40px; }
        .header { background-color: #002349; padding: 30px; text-align: center; }
        .header img { width: 48px; height: 48px; display: block; margin: 0 auto; }
        .content { padding: 40px 30px; text-align: center; }
        h1 { margin: 0 0 20px 0; font-size: 24px; color: #0f172a; font-weight: 700; }
        p { margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #475569; }
        .details-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; text-align: center; margin-top: 20px; }
        .details-box p { margin: 0; font-size: 15px; color: #0f172a; line-height: 1.8; }
        .footer { text-align: center; padding: 20px; font-size: 13px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    @if(isset($message) && file_exists(public_path('images/logo.png')))
                        <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="PayMe Panamá Logo">
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="PayMe Panamá Logo">
                    @endif
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h1>Tu factura está lista</h1>
                    <p>Hola <strong>{{ $factura->usuario->nombre ?? 'Cliente' }}</strong>, adjunto encontrarás la factura <strong>{{ $factura->numero }}</strong>.</p>
                    
                    @if($mensajePersonalizado)
                        <div class="details-box" style="border-left: 4px solid #00875a;">
                            <p>{{ $mensajePersonalizado }}</p>
                        </div>
                    @endif

                    <div class="details-box">
                        <p><strong>Total:</strong> ${{ number_format($factura->total, 2) }}</p>
                        <p><strong>Pago:</strong> {{ ucfirst($factura->metodo_pago) }}</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} {{ \App\Models\Configuracion::obtener('empresa.nombre', 'PayMe Panamá') }}. Todos los derechos reservados.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
