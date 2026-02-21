{{-- resources/views/emails/email-verification.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Correo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #0C3C61 0%, #2B7BB9 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .content p {
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #0C3C61 0%, #2B7BB9 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background: linear-gradient(135deg, #1A5A8A 0%, #3B8BC9 100%);
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Costeo360</h1>
        </div>
        
        <div class="content">
            <h2 style="color: #0C3C61;">¡Bienvenido a Costeo360!</h2>
            
            <p>Hola,</p>
            
            <p>Gracias por registrarte en Costeo360. Para completar tu registro, por favor verifica tu dirección de correo electrónico.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $verificationUrl }}" class="button">
                    Verificar Correo Electrónico
                </a>
            </div>
            
            <p>O copia y pega este enlace en tu navegador:</p>
            <p style="word-break: break-all; color: #0C3C61; font-size: 14px;">
                <a href="{{ $verificationUrl }}" style="color: #0C3C61; text-decoration: underline;">
                    {{ $verificationUrl }}
                </a>
            </p>
            
            <p>Si no te registraste en Costeo360, puedes ignorar este email.</p>
            
            <p>Gracias por unirte a nosotros.</p>
            
            <p>Equipo de Costeo360</p>
        </div>
        
        <div class="footer">
            <p>© 2026 Costeo360. Todos los derechos reservados.</p>
            <p>Software profesional para presupuestos y costeo de obras</p>
        </div>
    </div>
</body>
</html>