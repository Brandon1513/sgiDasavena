<!DOCTYPE html>
<html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <title>Solicitud Aprobada</title>
    <style>
        /* Estilos generales para otros clientes */
        .button-td, .button-a { transition: all 100ms ease-in; }
        .button-a:hover { background: #b88a15 !important; }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f3eef6;font-family:Arial, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
<center style="width:100%;background-color:#f3eef6;">

    <!--[if mso]>
    <table role="presentation" width="600" align="center" style="width:600px;">
    <tr><td>
    <![endif]-->

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#f3eef6; max-width:600px; margin: 0 auto;">
        <tr>
            <td align="center" style="padding:40px 10px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;background-color:#faf7fb;border:1px solid #ddcfe3;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td bgcolor="#6A2C75" style="height:4px;line-height:4px;font-size:0px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td bgcolor="#4a1d60" align="center" style="padding:50px 40px;">
                            <!-- Etiqueta Estatus -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
                                <tr>
                                    <td align="center" style="background-color:#5d3570; border:1px solid #D4A018; padding:5px 15px; border-radius:20px;">
                                        <span style="color:#D4A018;font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-family:Arial, sans-serif;">● Estatus Actualizado</span>
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin:20px 0 0;font-size:28px;color:#ffffff;font-weight:bold;line-height:1.2;font-family:Arial, sans-serif;">Solicitud Aprobada<br>por el Jefe</h1>
                            <p style="margin:15px 0 0;font-size:14px;color:#e1d5e5;line-height:1.6;font-family:Arial, sans-serif;">El responsable ha otorgado el visto bueno a tu gestión.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px;background-color:#faf7fb;">
                            <p style="font-size:15px;color:#2d1033;margin:0 0 10px;font-family:Arial, sans-serif;"><strong>Hola, {{ $solicitud->usuario->name }},</strong></p>
                            <p style="font-size:14px;color:#5a4a65;line-height:1.7;margin:0 0 30px;font-family:Arial, sans-serif;">Tu solicitud ha avanzado exitosamente con los siguientes detalles:</p>

                            <!-- Tabla de Resumen -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;border:1px solid #e2d7e8;background-color:#ffffff;">
                                <tr>
                                    <td style="padding:15px 20px;background-color:#f8f4f9;border-bottom:1px solid #e2d7e8;">
                                        <span style="font-size:10px;font-weight:bold;color:#6A2C75;text-transform:uppercase;font-family:Arial, sans-serif;">Resumen de Aprobación</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:15px 20px;border-bottom:1px solid #f0eaf2;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size:11px;color:#9b6baa;text-transform:uppercase;font-weight:bold;font-family:Arial, sans-serif;">Aprobado por</td>
                                                <td align="right" style="font-size:14px;color:#2d1033;font-weight:bold;font-family:Arial, sans-serif;">{{ $solicitud->jefe->name ?? 'Responsable' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:15px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size:11px;color:#9b6baa;text-transform:uppercase;font-weight:bold;font-family:Arial, sans-serif;">Estado</td>
                                                <td align="right">
                                                    <span style="color:#b38600;font-size:12px;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">{{ $solicitud->estado }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Botón optimizado para Outlook -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:35px;">
                                <tr>
                                    <td align="center">
                                        <div>
                                            <!--[if mso]>
                                            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ route('solicitudes.show', $solicitud->id) }}" style="height:45px;v-text-anchor:middle;width:220px;" arcsize="18%" stroke="f" fillcolor="#D4A018">
                                                <w:anchorlock/>
                                                <center style="color:#2d1033;font-family:Arial,sans-serif;font-size:13px;font-weight:bold;text-transform:uppercase;">Ver Detalles Completos</center>
                                            </v:roundrect>
                                            <![endif]-->
                                            <a href="{{ route('solicitudes.show', $solicitud->id) }}"
                                               style="background-color:#D4A018;color:#2d1033;padding:15px 35px;border-radius:8px;font-size:13px;font-weight:bold;text-decoration:none;display:inline-block;text-transform:uppercase;font-family:Arial, sans-serif;mso-hide:all;">
                                                Ver Detalles Completos
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td bgcolor="#2d1033" align="center" style="padding:30px 40px;">
                            <p style="margin:0;font-size:11px;color:#ffffff;font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-family:Arial, sans-serif;">{{ config('app.name') }}</p>
                            <p style="margin:10px 0 0;font-size:10px;color:#a38ca8;font-family:Arial, sans-serif;">&copy; {{ date('Y') }} Gestión Documental SGI</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!--[if mso]>
    </td></tr></table>
    <![endif]-->

</center>
</body>
</html>