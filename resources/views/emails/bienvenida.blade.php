<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a PayMe</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #0f172a;
            color: #ffffff;
            text-align: center;
            padding: 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 32px;
            line-height: 1.6;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .button-container {
            text-align: center;
            margin-top: 32px;
            margin-bottom: 16px;
        }
        .button {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 9999px; /* rounded-full */
            font-weight: 600;
            font-size: 15px;
        }
        .footer {
            background-color: #f8fafc;
            text-align: center;
            padding: 24px;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido a PayMe</h1>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $usuario->nombre }}</strong>,</p>
            
            <p>Gracias por crear una cuenta en PayMe. Estamos emocionados de tenerte con nosotros.</p>
            
            <p>Con tu nueva cuenta, podrás disfrutar de una experiencia de compra más rápida, hacer seguimiento a tus pedidos y descubrir todas nuestras promociones exclusivas.</p>
            
            <div class="button-container">
                <a href="{{ url('/') }}" class="button">Ir a la tienda</a>
            </div>
            
            <p style="margin-bottom: 0;">Saludos cordiales,<br><strong>El equipo de PayMe</strong></p>
        </div>
        
        <div class="footer">
            <p style="margin: 0;">&copy; {{ date('Y') }} PayMe. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
