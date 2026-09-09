<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Restablecer tu contraseña</title>
    <style>
        body {
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc; /* slate-50 */
            color: #334155; /* slate-700 */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #059669; /* emerald-600 */
            padding: 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 32px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .button-container {
            text-align: center;
            margin-bottom: 24px;
        }
        .button {
            display: inline-block;
            background-color: #059669;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #047857; /* emerald-700 */
        }
        .footer {
            background-color: #f1f5f9; /* slate-100 */
            padding: 16px;
            text-align: center;
            font-size: 14px;
            color: #64748b; /* slate-500 */
            border-top: 1px solid #e2e8f0;
        }
        .small {
            font-size: 13px;
            color: #64748b;
            word-break: break-all;
        }
        a {
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Restablecer Contraseña</h1>
        </div>
        <div class="content">
            <p>¡Hola!</p>
            <p>Estás recibiendo este correo electrónico porque solicitaste restablecer la contraseña de tu cuenta en <strong>{{ config('app.name') }}</strong>.</p>
            
            <div class="button-container">
                <a href="{{ $url }}" class="button">Restablecer Mi Contraseña</a>
            </div>
            
            <p>Este enlace de restablecimiento de contraseña expirará en 60 minutos.</p>
            <p>Si no solicitaste restablecer tu contraseña, no es necesario realizar ninguna otra acción. Tu cuenta está segura.</p>
            
            <p>Saludos cordiales,<br>El equipo de {{ config('app.name') }}</p>
            
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 32px 0;">
            
            <p class="small">
                Si tienes problemas haciendo clic en el botón "Restablecer Mi Contraseña", copia y pega el siguiente enlace en tu navegador web:<br>
                <a href="{{ $url }}">{{ $url }}</a>
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
