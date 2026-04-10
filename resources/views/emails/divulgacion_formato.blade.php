<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formato Divulgado</title>
    <link href="https://fonts.googleapis.com/css2?family=Questrial&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background-color: #f3eef6; font-family: 'Century Gothic', 'Questrial', Helvetica, Arial, sans-serif; }
        table { border-collapse: collapse; }
        a { text-decoration: none !important; }
        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; }
            .pad { padding: 30px 20px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f3eef6;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3eef6;padding:40px 0;">
<tr><td align="center">

    <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="container"
           style="width:600px;background:#faf7fb;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(106,44,117,0.12);border:1px solid rgba(106,44,117,0.12);">

        <tr><td style="height:3px;background:linear-gradient(90deg,#6A2C75,#9F6FB0,#6A2C75);"></td></tr>

        <tr>
            <td class="hero-pad" style="padding:56px 50px 60px;background:linear-gradient(160deg,#24112b 0%,#4b1f58 55%,#6A2C75 100%);position:relative;overflow:hidden;text-align:center;">
                <div style="position:absolute;top:-50px;right:-50px;width:200px;height:200px;border-radius:50%;background:rgba(212,160,24,0.15);filter:blur(40px);"></div>
                
                <table role="presentation" width="100%" style="position:relative;z-index:2;">
                    <tr><td align="center">
                        <div style="display:inline-block;padding:5px 16px;border-radius:100px;background:rgba(159,111,176,0.2);border:1px solid rgba(159,111,176,0.4);color:#e0c3fc;font-size:10px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;margin-bottom:24px;">
                            ✓ &nbsp;Proceso Finalizado
                        </div>
                        <h1 style="margin:0;font-size:26px;color:#ffffff;font-weight:bold;">Formato<br>Divulgado</h1>
                        <p style="margin:14px 0 0;font-size:14px;color:rgba(255,255,255,0.7);">La actualización se ha completado satisfactoriamente y ya está disponible.</p>
                    </td></tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="pad" style="padding:44px 50px 36px;">
                <p style="margin:0 0 10px;font-size:15px;color:#2d1033;font-weight:bold;">Hola equipo,</p>
                <p style="margin:0 0 36px;font-size:14px;color:#5a4a65;line-height:1.75;">Se ha concluido el flujo de actualización. El nuevo formato ya se encuentra vigente en la plataforma:</p>

                <table role="presentation" width="100%" style="background:linear-gradient(180deg, #fcfbfd 0%, #f7f4f9 100%);border:1px solid #e8deed;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:20px;">
                            <table role="presentation" width="100%">
                                <tr>
                                    <td style="font-size:11px;color:#8b6b96;font-weight:bold;text-transform:uppercase;">Documento</td>
                                    <td style="font-size:14px;color:#3a1844;text-align:right;font-weight:bold;">{{ $documento->nombre }}</td>
                                </tr>
                                <tr><td colspan="2" style="height:15px;"></td></tr>
                                <tr>
                                    <td style="font-size:11px;color:#8b6b96;font-weight:bold;text-transform:uppercase;">Código</td>
                                    <td style="font-size:14px;color:#3a1844;text-align:right;">{{ $documento->codigo }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" style="margin-top:30px;">
                    <tr><td align="center">
                        <a href="{{ route('documentos.show', $documento->id) }}" style="display:inline-block;padding:16px 40px;font-size:12px;font-weight:bold;color:#ffffff;border-radius:8px;background:linear-gradient(135deg,#6A2C75,#8f56a0);box-shadow:0 4px 20px rgba(106,44,117,0.3);text-transform:uppercase;letter-spacing:2px;">Acceder al Formato</a>
                    </td></tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding:28px 50px 32px;background:#2d1033;text-align:center;">
                <p style="margin:0;font-size:10px;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:1px;">Sistema de Gestión Integral</p>
            </td>
        </tr>
    </table>

</td></tr>
</table>

</body>
</html>