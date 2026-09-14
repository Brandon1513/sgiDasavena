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
    <title>{{ $ticket->tipo === 'idea' ? 'Idea de mejora' : 'Ticket de soporte' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3eef6;font-family:Arial, Helvetica, sans-serif;color:#2d1033;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3eef6" style="background-color:#f3eef6;width:100%;margin:0;">
    <tr>
        <td align="center" style="padding:40px 10px;">
            <!--[if mso]>
            <table role="presentation" width="600" align="center" style="width:600px;">
            <tr><td>
            <![endif]-->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="width:100%;max-width:600px;background-color:#faf7fb;border-radius:16px;border:1px solid #e2d7e8;overflow:hidden;">

                <tr>
                    <td bgcolor="#6A2C75" style="height:4px;line-height:4px;font-size:0px;">&nbsp;</td>
                </tr>

                <tr>
                    <td bgcolor="#2d1033" align="center" style="padding:56px 40px;color:#ffffff;background-color:#2d1033;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="background-color:#3e1c4a;border:1px solid #D4A018;padding:6px 16px;border-radius:20px;">
                                                <span style="color:#D4A018;font-size:10px;letter-spacing:1px;text-transform:uppercase;font-weight:bold;font-family:Arial, sans-serif;">● Soporte SGI</span>
                                            </td>
                                        </tr>
                                    </table>

                                    <h1 style="margin:24px 0 0;font-size:26px;line-height:1.2;font-weight:bold;color:#ffffff;font-family:Arial, sans-serif;">
                                        {{ $ticket->tipo === 'idea' ? 'Idea de mejora' : 'Nuevo ticket de soporte' }}
                                    </h1>
                                    <p style="margin:14px 0 0;font-size:14px;color:#e1d5e5;line-height:1.6;font-family:Arial, sans-serif;">
                                        Un usuario del SGI envió {{ $ticket->tipo === 'idea' ? 'una idea de mejora para la plataforma' : 'una solicitud de soporte' }}.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:40px;background-color:#faf7fb;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;border:1px solid #e2d7e8;">
                            <tr>
                                <td colspan="2" bgcolor="#f8f4f9" style="padding:14px 24px;border-bottom:1px solid #e2d7e8;">
                                    <span style="font-size:10px;font-weight:bold;color:#6A2C75;letter-spacing:1px;text-transform:uppercase;font-family:Arial, sans-serif;">Detalle</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Solicitante</td>
                                <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-weight:bold;font-family:Arial, sans-serif;">
                                    {{ $ticket->usuario->name }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Correo</td>
                                <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-family:Arial, sans-serif;">
                                    {{ $ticket->usuario->email }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Área</td>
                                <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-family:Arial, sans-serif;">
                                    {{ $ticket->usuario->area ?? 'No especificado' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Asunto</td>
                                <td align="right" style="padding:16px 24px;font-size:14px;color:#2d1033;font-weight:bold;font-family:Arial, sans-serif;">
                                    {{ $ticket->asunto }}
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:24px;border-left:4px solid #6A2C75;background-color:#ffffff;border:1px solid #e2d7e8;padding:20px 24px;">
                            <p style="margin:0 0 8px;font-size:11px;font-weight:bold;color:#9b6baa;text-transform:uppercase;letter-spacing:1px;font-family:Arial, sans-serif;">Descripción</p>
                            <p style="margin:0;font-size:14px;color:#2d1033;line-height:1.7;white-space:pre-line;font-family:Arial, sans-serif;">{{ $ticket->descripcion }}</p>
                        </div>

                        <div style="margin-top:24px;border-left:4px solid #D4A018;background-color:#fffcf5;padding:16px;">
                            <p style="margin:0;font-size:13px;color:#7a6040;line-height:1.6;font-family:Arial, sans-serif;">
                                <strong style="color:#b38600;">Nota:</strong> Puede responder directamente a este correo, la respuesta llegará a {{ $ticket->usuario->email }}.
                            </p>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#2d1033" align="center" style="padding:32px 40px;color:#ffffff;background-color:#2d1033;">
                        <p style="margin:0;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;font-family:Arial, sans-serif;">
                            {{ config('app.name') }}
                        </p>
                        <p style="margin:12px 0 0;font-size:11px;color:#a38ca8;line-height:1.6;font-family:Arial, sans-serif;">
                            &copy; {{ date('Y') }} Gestión Documental · Enviado desde el asistente de soporte del dashboard el {{ $ticket->created_at->format('d/m/Y H:i') }}.
                        </p>
                    </td>
                </tr>
            </table>
            <!--[if mso]>
            </td></tr></table>
            <![endif]-->
        </td>
    </tr>
</table>

</body>
</html>
