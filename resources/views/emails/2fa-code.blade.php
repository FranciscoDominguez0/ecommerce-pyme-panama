<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Código de Verificación - PayMe Panamá</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; color: #1e293b; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding-bottom: 60px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-spacing: 0; font-family: sans-serif; color: #1e293b; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-top: 40px; }
        .header { background-color: #002349; padding: 30px; text-align: center; }
        .header img { width: 48px; height: 48px; display: block; margin: 0 auto; }
        .content { padding: 40px 30px; text-align: center; }
        h1 { margin: 0 0 20px 0; font-size: 24px; color: #0f172a; font-weight: 700; }
        p { margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #475569; }
        .code-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #0f172a; display: inline-block; margin: 10px 0 20px 0; }
        .notice { font-size: 13px; color: #64748b; margin-top: 25px; }
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
                    <h1>Código de Verificación</h1>
                    <p>Hola <strong>{{ $nombreUsuario ?? 'usuario' }}</strong>, ingresa este código para acceder a tu cuenta:</p>
                    <div class="code-box">{{ $code }}</div>
                    <p class="notice">El código expira en 10 minutos. Si no fuiste tú, te recomendamos cambiar tu contraseña.</p>
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
