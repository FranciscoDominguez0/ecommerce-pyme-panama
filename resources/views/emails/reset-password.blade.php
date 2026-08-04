<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu contraseña - PayMe Panamá</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9ff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #0b1c30;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 580px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e5eeff;
            box-shadow: 0 4px 24px rgba(0, 35, 73, 0.06);
        }
        .header {
            background-color: #002349;
            padding: 32px 24px;
            text-align: center;
        }
        .logo-badge {
            display: inline-block;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 12px;
        }
        .title {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }
        .body {
            padding: 32px 28px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #002349;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .text {
            font-size: 15px;
            line-height: 1.6;
            color: #43474e;
            margin-bottom: 24px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background-color: #002349;
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(0, 35, 73, 0.15);
        }
        .notice-box {
            background-color: #e5eeff;
            border-left: 4px solid #006c47;
            padding: 14px 16px;
            border-radius: 6px;
            font-size: 13px;
            color: #002349;
            margin-bottom: 24px;
        }
        .url-fallback {
            font-size: 12px;
            color: #74777f;
            word-break: break-all;
            line-height: 1.4;
            border-top: 1px solid #e5eeff;
            padding-top: 20px;
            margin-top: 20px;
        }
        .footer {
            background-color: #f8f9ff;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #74777f;
            border-top: 1px solid #e5eeff;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-badge">
                <svg width="36" height="36" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="40" height="40" rx="10" fill="#002349"/>
                    <path d="M12 28V12H20C22.2091 12 24 13.7909 24 16C24 18.2091 22.2091 20 20 20H16.5V28H12Z" fill="white"/>
                    <path d="M22 20C24.2091 20 26 21.7909 26 24C26 26.2091 24.2091 28 22 28H18V24H22Z" fill="#00875A"/>
                    <circle cx="28" cy="14" r="3" fill="#D4AF37"/>
                </svg>
            </div>
            <h1 class="title">PayMe Panamá</h1>
        </div>

        <!-- Content -->
        <div class="body">
            <h2 class="greeting">¡Hola, {{ $usuario->nombre ?? 'Estimado usuario' }}!</h2>
            <p class="text">
                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>PayMe Panamá</strong>. Si fuiste tú quien solicitó este cambio, haz clic en el siguiente botón para continuar:
            </p>

            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn" target="_blank">Restablecer Contraseña</a>
            </div>

            <div class="notice-box">
                ⏱️ <strong>Aviso de seguridad:</strong> Este enlace de recuperación expirará en <strong>60 minutos</strong>. Si no solicitaste este cambio, puedes ignorar este correo de manera segura; tu cuenta permanece protegida.
            </div>

            <div class="url-fallback">
                Si el botón no funciona, copia y pega el siguiente enlace en tu navegador web:<br>
                <a href="{{ $resetUrl }}" style="color: #006c47;">{{ $resetUrl }}</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            © {{ date('Y') }} PayMe Panamá. Todos los derechos reservados.<br>
            Plataforma de Comercio Electrónico y Pagos Seguros.
        </div>
    </div>
</body>
</html>
