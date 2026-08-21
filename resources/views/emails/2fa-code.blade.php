<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación - PayMe Panamá</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #0f172a;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #0f172a;
        }
        .message {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #475569;
        }
        .code-container {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            display: inline-block;
        }
        .code {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #0f172a;
            margin: 0;
            font-family: monospace;
        }
        .warning {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin-top: 20px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PayMe Panamá</h1>
        </div>
        
        <div class="content">
            <div class="greeting">Hola, {{ $nombreUsuario }}</div>
            
            <div class="message">
                Hemos recibido una solicitud de inicio de sesión en tu cuenta. Para completar el proceso de seguridad, por favor ingresa el siguiente código de verificación de 4 dígitos:
            </div>
            
            <div class="code-container">
                <div class="code">{{ $code }}</div>
            </div>
            
            <div class="message">
                Este código expirará en <strong>10 minutos</strong>.
            </div>
            
            <div class="warning">
                Si no solicitaste este código, es posible que alguien esté intentando acceder a tu cuenta. Te recomendamos cambiar tu contraseña inmediatamente.
            </div>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} PayMe Panamá. Todos los derechos reservados.<br>
            Este es un correo generado automáticamente, por favor no respondas.
        </div>
    </div>
</body>
</html>
