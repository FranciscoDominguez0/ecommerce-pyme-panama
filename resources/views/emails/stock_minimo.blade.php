<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Alerta de Stock Mínimo - {{ config('app.name') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding-bottom: 60px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif; color: #1e293b; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-top: 40px; }
        .header { background-color: #002349; padding: 30px; text-align: center; }
        .header img { width: 48px; height: 48px; display: block; margin: 0 auto; }
        .content { padding: 40px 30px; text-align: center; }
        h1 { margin: 0 0 20px 0; font-size: 24px; color: #0f172a; font-weight: 700; }
        p { margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #475569; }
        .alert-box { background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold; }
        .button { background-color: #00875a; color: #ffffff !important; text-decoration: none; padding: 14px 30px; border-radius: 6px; display: inline-block; font-weight: bold; font-size: 16px; margin-top: 10px; box-shadow: 0 2px 4px rgba(0, 135, 90, 0.3); }
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
                    <h1>Alerta de Stock Mínimo</h1>
                    <p>Hola, el siguiente producto ha alcanzado o está por debajo de su umbral de stock mínimo permitido.</p>
                    
                    <div class="alert-box">
                        Producto: {{ $nombreProducto }}<br>
                        Stock Actual: {{ $stockActual }} unidades
                    </div>
                    
                    <p>Por favor, revisa el inventario para reabastecer el producto lo antes posible y evitar quiebres de stock en la tienda.</p>
                    
                    <a href="{{ $url }}" class="button">Gestionar Inventario</a>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} {{ config('app.name', 'PayMe Panamá') }}. Todos los derechos reservados.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
