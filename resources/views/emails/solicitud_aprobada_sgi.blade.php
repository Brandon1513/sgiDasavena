<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Aprobada por Jefatura</title>
    <link href="https://fonts.googleapis.com/css2?family=Questrial&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0; padding: 0;
            background-color: #f3eef6;
            font-family: 'Century Gothic', 'Questrial', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        table { border-collapse: collapse; }
        a { text-decoration: none !important; }

        @media only screen and (max-width: 620px) {
            .container  { width: 100% !important; border-radius: 0 !important; }
            .pad        { padding: 30px 20px !important; }
            .pad-sm     { padding: 15px !important; }
            .hero-pad   { padding: 40px 24px 50px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f3eef6;font-family:'Century Gothic','Questrial',Helvetica,Arial,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0"
       style="background-color:#f3eef6;padding:40px 0;">
<tr><td align="center">

    <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="container"
           style="width:600px;max-width:600px;background:#faf7fb;border-radius:16px;
                  overflow:hidden;box-shadow:0 20px 60px rgba(106,44,117,0.12);
                  border:1px solid rgba(106,44,117,0.12);">

        <tr>
            <td style="height:3px;background:linear-gradient(90deg,#6A2C75,#D4A018,#6A2C75);
                       font-size:0;line-height:0;">&nbsp;</td>
        </tr>

        <tr>
            <td class="hero-pad"
                style="padding:56px 50px 60px;
                       background:linear-gradient(160deg,#2d1033 0%,#4a1d60 45%,#6A2C75 100%);
                       position:relative;overflow:hidden;text-align:center;">

                <div style="position:absolute;top:-80px;left:-80px;width:280px;height:280px;
                            border-radius:50%;
                            background:radial-gradient(circle,rgba(155,61,170,0.55) 0%,transparent 70%);
                            filter:blur(40px);pointer-events:none;"></div>

                <div style="position:absolute;bottom:-60px;right:-60px;width:220px;height:220px;
                            border-radius:50%;
                            background:radial-gradient(circle,rgba(212,160,24,0.45) 0%,transparent 70%);
                            filter:blur(35px);pointer-events:none;"></div>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                       style="position:relative;z-index:2;">
                    <tr>
                        <td align="center">

                            <div style="display:inline-block;padding:5px 16px;
                                        border-radius:100px;
                                        background:rgba(212,160,24,0.15);
                                        border:1px solid rgba(212,160,24,0.4);
                                        color:#D4A018;font-size:10px;
                                        letter-spacing:2px;text-transform:uppercase;
                                        font-weight:bold;margin-bottom:24px;">
                                ● &nbsp;Estatus Actualizado
                            </div>

                            <div style="display:inline-flex;align-items:center;justify-content:center;
                                        width:64px;height:64px;border-radius:50%;
                                        background:rgba(212,160,24,0.15);
                                        border:1px solid rgba(212,160,24,0.3);
                                        margin-bottom:22px;">
                                <img src="https://img.icons8.com/ios-filled/50/D4A018/ok.png"
                                     width="30" height="30" alt="aprobado"
                                     style="display:block;filter:brightness(0) invert(1) sepia(1) saturate(3) hue-rotate(3deg);">
                            </div>

                            <h1 style="margin:0;font-size:26px;line-height:1.25;
                                        color:#ffffff;font-weight:bold;
                                        letter-spacing:-0.5px;">
                                Solicitud Aprobada<br>por el Jefe
                            </h1>
                            <p style="margin:14px 0 0;font-size:14px;line-height:1.65;
                                      color:rgba(255,255,255,0.7);max-width:380px;
                                      display:inline-block;">
                                Excelentes noticias. El responsable ha revisado y otorgado el visto bueno a la solicitud presentada.
                            </p>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td class="pad" style="padding:44px 50px 36px;">

                <p style="margin:0 0 10px;font-size:15px;line-height:1.6;color:#2d1033;font-weight:bold;">
                    Hola, {{ $solicitud->usuario->name }},
                </p>
                <p style="margin:0 0 36px;font-size:14px;line-height:1.75;color:#5a4a65;">
                    Te informamos que tu solicitud ha avanzado exitosamente. El jefe directo ha validado el registro con los siguientes detalles:
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                       style="background:#ffffff;border:1px solid rgba(106,44,117,0.12);
                              border-radius:12px;overflow:hidden;">

                    <tr>
                        <td colspan="2"
                            style="padding:14px 24px;
                                   background:linear-gradient(135deg,rgba(106,44,117,0.06),rgba(212,160,24,0.04));
                                   border-bottom:1px solid rgba(106,44,117,0.08);">
                            <span style="font-size:10px;font-weight:bold;
                                         color:#6A2C75;letter-spacing:1.5px;
                                         text-transform:uppercase;">
                                Resumen de Aprobación
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;
                                   font-size:11px;color:#9b6baa;font-weight:bold;
                                   text-transform:uppercase;letter-spacing:1px;width:42%;">
                            Aprobado por
                        </td>
                        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;
                                   font-size:14px;color:#2d1033;text-align:right;font-weight:bold;">
                            {{ $solicitud->jefe->name ?? 'Responsable de Área' }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;
                                   font-size:11px;color:#9b6baa;font-weight:bold;
                                   text-transform:uppercase;letter-spacing:1px;">
                            Acción Realizada
                        </td>
                        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;
                                   font-size:14px;color:#4a2a55;text-align:right;">
                            {{ ucfirst($solicitud->accion) }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px;
                                   font-size:11px;color:#9b6baa;font-weight:bold;
                                   text-transform:uppercase;letter-spacing:1px;">
                            Estado Final
                        </td>
                        <td style="padding:16px 24px;text-align:right;">
                            <span style="display:inline-block;padding:5px 14px;
                                         border-radius:100px;
                                         background:rgba(212,160,24,0.1);
                                         border:1px solid rgba(212,160,24,0.3);
                                         color:#b38600;font-size:12px;font-weight:bold;">
                                {{ ucfirst($solicitud->estado) }}
                            </span>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                       style="margin-top:40px;">
                    <tr>
                        <td align="center">
                            <a href="{{ route('solicitudes.show', $solicitud->id) }}"
                               style="display:inline-block;padding:16px 40px;
                                      font-size:12px;font-weight:bold;
                                      color:#2d1033;text-decoration:none;
                                      border-radius:8px;
                                      background:linear-gradient(135deg,#D4A018,#f0c84a);
                                      box-shadow:0 4px 20px rgba(212,160,24,0.35);
                                      text-transform:uppercase;letter-spacing:2px;">
                                Ver Detalles Completos &rarr;
                            </a>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                       style="margin-top:32px;">
                    <tr>
                        <td style="padding:16px 20px;
                                   background:rgba(106,44,117,0.03);
                                   border-left:3px solid #6A2C75;
                                   border-radius:0 8px 8px 0;">
                            <p style="margin:0;font-size:12px;color:#5a4a65;line-height:1.6;">
                                <strong style="color:#6A2C75;">Próximos pasos:</strong> El proceso continuará según el flujo establecido. Puedes monitorear cualquier cambio adicional desde el portal de gestión.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        <tr>
            <td style="padding:28px 50px 32px;
                       background:linear-gradient(135deg,#2d1033,#4a1d60);
                       text-align:center;position:relative;overflow:hidden;">

                <div style="width:50px;height:2px;
                            background:linear-gradient(90deg,transparent,#D4A018,transparent);
                            margin:0 auto 20px;"></div>

                <p style="margin:0;font-size:11px;color:rgba(255,255,255,0.5);
                           font-weight:bold;text-transform:uppercase;letter-spacing:1.5px;">
                    {{ config('app.name') }} &nbsp;·&nbsp; Gestión Documental
                </p>
                <p style="margin:12px 0 0;font-size:11px;color:rgba(255,255,255,0.3);line-height:1.7;">
                    Esta es una notificación automática del sistema.<br>
                    &copy; {{ date('Y') }} Todos los derechos reservados.
                </p>
            </td>
        </tr>

        <tr>
            <td style="height:3px;background:linear-gradient(90deg,#D4A018,#6A2C75,#D4A018);
                       font-size:0;line-height:0;">&nbsp;</td>
        </tr>

    </table>

</td></tr>
</table>

</body>
</html>