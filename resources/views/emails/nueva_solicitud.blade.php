<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva solicitud de formato</title>
</head>
<body style="margin:0;padding:0;background-color:#f3eef6;font-family:Arial, Helvetica, sans-serif;color:#2d1033;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3eef6" style="background-color:#f3eef6;width:100%;margin:0;padding:40px 0;">
    <tr>
        <td align="center">

            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="width:600px;max-width:600px;background-color:#faf7fb;border-radius:16px;border:1px solid #e2d7e8;">
                
                <tr>
                    <td bgcolor="#6A2C75" style="height:4px;line-height:4px;font-size:0px;border-radius:16px 16px 0 0;">&nbsp;</td>
                </tr>

                <tr>
                    <td bgcolor="#4a1d60" align="center" style="padding:56px 40px;background: linear-gradient(160deg,#2d1033 0%,#4a1d60 100%);color:#ffffff;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="background-color:rgba(212,160,24,0.15);border:1px solid #D4A018;padding:6px 16px;border-radius:100px;">
                                                <span style="color:#D4A018;font-size:10px;letter-spacing:1px;text-transform:uppercase;font-weight:bold;">● Notificación de Sistema</span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <h1 style="margin:24px 0 0;font-size:26px;line-height:1.2;font-weight:bold;color:#ffffff;">Nueva Solicitud<br>de Formato</h1>
                                    <p style="margin:14px 0 0;font-size:14px;color:#e1d5e5;line-height:1.6;">
                                        Se ha registrado una solicitud que requiere su revisión y seguimiento en la plataforma.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:40px;background-color:#faf7fb;">
                        <p style="margin:0 0 10px;font-size:15px;color:#2d1033;font-weight:bold;">Estimado Revisor,</p>
                        <p style="margin:0 0 36px;font-size:14px;color:#5a4a65;line-height:1.75;">
                            Una nueva solicitud ha sido asignada para su gestión. A continuación encontrará un resumen con la información relevante:
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;border:1px solid #e2d7e8;border-radius:12px;">
                            <tr>
                                <td colspan="2" bgcolor="#f8f4f9" style="padding:14px 24px;border-bottom:1px solid #e2d7e8;border-radius:12px 12px 0 0;">
                                    <span style="font-size:10px;font-weight:bold;color:#6A2C75;letter-spacing:1px;text-transform:uppercase;">Detalle de la solicitud</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;">Solicitante</td>
                                <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-weight:bold;">
                                    {{ $solicitud->usuario->name }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;">Área</td>
                                <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;">
                                    {{ $solicitud->usuario->area ?? 'No especificado' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;">Acción</td>
                                <td align="right" style="padding:16px 24px;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="display:inline-block;">
                                        <tr>
                                            <td style="background-color:#f3eef6;border:1px solid #6A2C75;padding:4px 12px;border-radius:100px;">
                                                <span style="color:#6A2C75;font-weight:bold;font-size:11px;text-transform:uppercase;">{{ $solicitud->accion }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:24px;border-left:4px solid #D4A018;background-color:#fffcf5;padding:16px;border-radius:4px;">
                            <p style="margin:0;font-size:13px;color:#7a6040;line-height:1.6;">
                                <strong style="color:#b38600;">Nota:</strong> Esta notificación fue generada automáticamente. Por favor, acceda a la plataforma para gestionar los cambios.
                            </p>
                        </div>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:40px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ route('solicitudes.show', $solicitud->id) }}"
                                       style="display:inline-block;padding:16px 34px;font-size:13px;font-weight:bold;color:#2d1033;background-color:#D4A018;border-radius:8px;text-transform:uppercase;letter-spacing:1px;text-decoration:none;">
                                        Gestionar Solicitud →
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#2d1033" align="center" style="padding:32px 40px;border-radius:0 0 16px 16px;color:#ffffff;">
                        <p style="margin:0;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                            {{ config('app.name') }}
                        </p>
                        <p style="margin:12px 0 0;font-size:11px;color:#a38ca8;line-height:1.6;">
                            &copy; {{ date('Y') }} Gestión Documental · Unidad de TI<br>
                            Notificación automática, por favor no responder.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
                                
</body>
</html>