<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización requerida</title>
    <link href="https://fonts.googleapis.com/css2?family=Questrial&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background-color: #f3eef6; font-family: 'Century Gothic', 'Questrial', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; }
        a { text-decoration: none !important; }
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; border-radius: 0 !important; }
            .pad { padding: 30px 20px !important; }
            .hero-pad { padding: 40px 24px 50px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f3eef6;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3eef6;padding:40px 0;">
<tr><td align="center">

    <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="container"
           style="width:600px;max-width:600px;background:#faf7fb;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(106,44,117,0.12);border:1px solid rgba(106,44,117,0.12);">

        <tr><td style="height:3px;background:linear-gradient(90deg,#6A2C75,#D4A018,#6A2C75);"></td></tr>

        <tr>
            <td class="hero-pad" style="padding:56px 50px 60px;background:linear-gradient(160deg,#2d1033 0%,#4a1d60 45%,#6A2C75 100%);position:relative;overflow:hidden;text-align:center;">
                <div style="position:absolute;top:-80px;left:-80px;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(155,61,170,0.55) 0%,transparent 70%);filter:blur(40px);"></div>
                <div style="position:absolute;bottom:-60px;right:-60px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(212,160,24,0.45) 0%,transparent 70%);filter:blur(35px);"></div>
                
                <table role="presentation" width="100%" style="position:relative;z-index:2;">
                    <tr><td align="center">
                        <div style="display:inline-block;padding:5px 16px;border-radius:100px;background:rgba(212,160,24,0.15);border:1px solid rgba(212,160,24,0.4);color:#D4A018;font-size:10px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;margin-bottom:24px;">
                            ● &nbsp;Acción Pendiente
                        </div>
                        <h1 style="margin:0;font-size:26px;color:#ffffff;font-weight:bold;">Actualización<br>Requerida</h1>
                        <p style="margin:14px 0 0;font-size:14px;color:rgba(255,255,255,0.6);">Se ha detectado un documento que requiere atención inmediata dentro del sistema.</p>
                    </td></tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="pad" style="padding:44px 50px 36px;">
                <p style="margin:0 0 10px;font-size:15px;color:#2d1033;font-weight:bold;">Hola,</p>
                <p style="margin:0 0 36px;font-size:14px;color:#5a4a65;line-height:1.75;">El siguiente documento ha sido marcado para su actualización. Por favor, revise la información detallada:</p>

                <table role="presentation" width="100%" style="background:#ffffff;border:1px solid rgba(106,44,117,0.12);border-radius:12px;overflow:hidden;">
                    <tr>
                        <td colspan="2" style="padding:14px 24px;background:linear-gradient(135deg,rgba(106,44,117,0.06),rgba(212,160,24,0.04));border-bottom:1px solid rgba(106,44,117,0.08);">
                            <span style="font-size:10px;font-weight:bold;color:#6A2C75;letter-spacing:1.5px;text-transform:uppercase;">Resumen del Documento</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;width:40%;">Código</td>
                        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;text-align:right;font-weight:bold;">{{ $documento->codigo }}</td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;">Nombre</td>
                        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;text-align:right;">{{ $documento->nombre }}</td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;">Estado</td>
                        <td style="padding:16px 24px;text-align:right;"><span style="color:#D4A018;font-weight:bold;font-size:12px;">Requiere actualización</span></td>
                    </tr>
                </table>

                <div style="margin-top:24px;background:#faf8fc;border:1px solid #ddcfe3;border-radius:12px;padding:20px;">
                    <p style="margin:0 0 8px;font-size:10px;color:#8b6b96;font-weight:bold;text-transform:uppercase;">Mensaje del SGI</p>
                    <p style="margin:0;font-size:14px;color:#4b5563;line-height:1.6;">{{ $mensaje }}</p>
                </div>

                <table role="presentation" width="100%" style="margin-top:40px;">
                    <tr><td align="center">
                        <a href="{{ route('documentos.show', $documento->id) }}" style="display:inline-block;padding:16px 40px;font-size:12px;font-weight:bold;color:#2d1033;border-radius:8px;background:linear-gradient(135deg,#D4A018,#f0c84a);box-shadow:0 4px 20px rgba(212,160,24,0.35);text-transform:uppercase;letter-spacing:2px;">Ver Documento &rarr;</a>
                    </td></tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding:28px 50px 32px;background:linear-gradient(135deg,#2d1033,#4a1d60);text-align:center;">
                <p style="margin:0;font-size:11px;color:rgba(255,255,255,0.5);font-weight:bold;text-transform:uppercase;letter-spacing:1.5px;">{{ config('app.name') }}</p>
                <p style="margin:12px 0 0;font-size:11px;color:rgba(255,255,255,0.3);">&copy; {{ date('Y') }} Gestión Documental</p>
            </td>
        </tr>
    </table>

</td></tr>
</table>

</body>
</html>