<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>¡Stock Disponible! - {{ config('app.name', 'PayMe Panamá') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding-bottom: 60px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif; color: #1e293b; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); margin-top: 40px; }
        .header { background-color: #002349; padding: 30px; text-align: center; }
        .header img { width: 48px; height: 48px; display: block; margin: 0 auto; }
        .content { padding: 40px 30px; text-align: center; }
        h1 { margin: 0 0 12px 0; font-size: 24px; color: #002349; font-weight: 800; letter-spacing: -0.5px; }
        p { margin: 0 0 20px 0; font-size: 15px; line-height: 1.6; color: #475569; }
        .product-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 24px; margin: 24px 0; text-align: center; }
        .product-img { max-width: 160px; max-height: 160px; object-fit: contain; margin: 0 auto 16px auto; display: block; border-radius: 8px; }
        .product-title { font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 8px; line-height: 1.4; }
        .product-price { font-size: 22px; font-weight: 800; color: #006148; font-family: monospace; margin-bottom: 6px; }
        .button { background-color: #006148; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 50px; display: inline-block; font-weight: 700; font-size: 15px; margin-top: 10px; box-shadow: 0 4px 10px rgba(0, 97, 72, 0.3); }
        .button:hover { background-color: #004f3b; }
        .note { font-size: 12px; color: #94a3b8; margin-top: 24px; line-height: 1.5; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    @if(isset($message) && file_exists(public_path('images/logo.png')))
                        <img src="{{ $message->embed(public_path('images/logo.png')) }}" alt="{{ config('app.name', 'PayMe Panamá') }}">
                    @else
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'PayMe Panamá') }}">
                    @endif
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h1>¡Buenas noticias!</h1>
                    <p>Nos solicitaste que te avisáramos cuando tuviéramos existencias del siguiente producto:</p>

                    <div class="product-card">
                        @php
                            $img = $producto->imagenPrincipal();
                            $imgSrc = $producto->imagen_url;
                        @endphp
                        @if($imgSrc)
                            <img src="{{ $imgSrc }}" alt="{{ $producto->nombre }}" class="product-img">
                        @endif

                        <div class="product-title">
                            {{ $producto->nombre }}
                        </div>

                        <div class="product-price">
                            @if($producto->tienePromocionOPrecioOferta())
                                ${{ number_format($producto->precioFinalPromocional(), 2) }}
                            @else
                                ${{ number_format($producto->precio, 2) }}
                            @endif
                        </div>

                        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                            ¡Unidades disponibles por tiempo limitado!
                        </p>

                        <a href="{{ route('cliente.producto.detalle', $producto->slug) }}" class="button">
                            Ver Producto y Comprar
                        </a>
                    </div>

                    <p class="note">
                        Recibiste este correo porque ingresaste tu dirección de correo en nuestra tienda solicitando una notificación de stock para este producto.
                    </p>
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
