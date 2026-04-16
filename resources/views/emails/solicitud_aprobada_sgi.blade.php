<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Aprobada</title>
</head>
<body style="margin:0;padding:0;background-color:#f3eef6;font-family:Arial,sans-serif;">
<center style="width:100%;background-color:#f3eef6;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#f3eef6;">
    <tr>
        <td align="center" style="padding:40px 10px;">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;width:100%;max-width:600px;background-color:#faf7fb;border:1px solid #ddcfe3;border-radius:16px;overflow:hidden;">
                <tr>
                    <td bgcolor="#6A2C75" style="height:4px;line-height:4px;font-size:0px;">&nbsp;</td>
                </tr>
                <tr>
                    <td bgcolor="#4a1d60" align="center" style="padding:50px 40px;background-color:#4a1d60;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                            <tr>
                                <td align="center">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                        <tr>
                                            <td style="background-color:rgba(212,160,24,0.2);border:1px solid #D4A018;padding:5px 15px;border-radius:100px;">
                                                <span style="color:#D4A018;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">● Estatus Actualizado</span>
                                            </td>
                                        </tr>
                                    </table>

                                    <h1 style="margin:20px 0 0;font-size:28px;color:#ffffff;font-weight:bold;line-height:1.2;">Solicitud Aprobada<br>por el Jefe</h1>
                                    <p style="margin:15px 0 0;font-size:14px;color:#e1d5e5;line-height:1.6;">El responsable ha otorgado el visto bueno a tu gestión.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:40px;background-color:#faf7fb;">
                        <p style="font-size:15px;color:#2d1033;margin:0 0 10px;"><strong>Hola, {{ $solicitud->usuario->name }},</strong></p>
                        <p style="font-size:14px;color:#5a4a65;line-height:1.7;margin:0 0 30px;">Tu solicitud ha avanzado exitosamente con los siguientes detalles:</p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;border:1px solid #e2d7e8;border-radius:12px;background-color:#ffffff;">
                            <tr>
                                <td style="padding:15px 20px;background-color:#f8f4f9;border-bottom:1px solid #e2d7e8;">
                                    <span style="font-size:10px;font-weight:bold;color:#6A2C75;text-transform:uppercase;">Resumen de Aprobación</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:15px 20px;border-bottom:1px solid #f0eaf2;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                        <tr>
                                            <td style="font-size:11px;color:#9b6baa;text-transform:uppercase;font-weight:bold;">Aprobado por</td>
                                            <td align="right" style="font-size:14px;color:#2d1033;font-weight:bold;">{{ $solicitud->jefe->name ?? 'Responsable' }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:15px 20px;">
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                        <tr>
                                            <td style="font-size:11px;color:#9b6baa;text-transform:uppercase;font-weight:bold;">Estado</td>
                                            <td align="right">
                                                <span style="color:#b38600;font-size:12px;font-weight:bold;text-transform:uppercase;">{{ $solicitud->estado }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin-top:35px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ route('solicitudes.show', $solicitud->id) }}"
                                       style="background-color:#D4A018;color:#2d1033;padding:15px 35px;border-radius:8px;font-size:13px;font-weight:bold;text-decoration:none;display:inline-block;text-transform:uppercase;">
                                        Ver Detalles Completos
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#2d1033" align="center" style="padding:30px 40px;background-color:#2d1033;">
                        <p style="margin:0;font-size:11px;color:#ffffff;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">{{ config('app.name') }}</p>
                        <p style="margin:10px 0 0;font-size:10px;color:#a38ca8;">&copy; {{ date('Y') }} Gestión Documental SGI</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</center>
</body>
</html>
