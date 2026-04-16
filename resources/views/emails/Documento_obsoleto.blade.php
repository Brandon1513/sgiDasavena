<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documento Obsoleto</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial, Helvetica, sans-serif;color:#1f2937;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3f4f6" style="background-color:#f3f4f6;width:100%;margin:0;padding:40px 0;">
    <tr>
        <td align="center">

            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="width:600px;max-width:600px;background-color:#ffffff;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;">
                
                <tr>
                    <td bgcolor="#dc2626" style="height:4px;line-height:4px;font-size:0px;">&nbsp;</td>
                </tr>

                <tr>
                    <td bgcolor="#111827" align="center" style="padding:56px 40px;background: linear-gradient(160deg,#111827 0%,#374151 100%);color:#ffffff;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                        <tr>
                                            <td style="background-color:rgba(220,38,38,0.2);border:1px solid #dc2626;padding:6px 16px;border-radius:100px;">
                                                <span style="color:#fca5a5;font-size:10px;letter-spacing:1px;text-transform:uppercase;font-weight:bold;">● Aviso de Vigencia</span>
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <h1 style="margin:24px 0 0;font-size:26px;line-height:1.2;font-weight:bold;color:#ffffff;">Documento<br>Marcado como Obsoleto</h1>
                                    <p style="margin:14px 0 0;font-size:14px;color:#9ca3af;line-height:1.6;">
                                        Este archivo ha sido retirado de la circulación oficial y ya no debe ser utilizado.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:40px;background-color:#ffffff;">
                        <p style="margin:0 0 10px;font-size:15px;color:#111827;font-weight:bold;">Atención,</p>
                        <p style="margin:0 0 30px;font-size:14px;color:#4b5563;line-height:1.75;">
                            Se le informa que el siguiente documento ha cambiado su estado de vigencia:
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;">
                            <tr>
                                <td colspan="2" bgcolor="#f3f4f6" style="padding:14px 24px;border-bottom:1px solid #e5e7eb;border-radius:12px 12px 0 0;">
                                    <span style="font-size:10px;font-weight:bold;color:#374151;letter-spacing:1px;text-transform:uppercase;">Identificación del Documento</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #e5e7eb;font-size:11px;color:#6b7280;font-weight:bold;text-transform:uppercase;">Código</td>
                                <td align="right" style="padding:16px 24px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;font-weight:bold;">
                                    {{ $documento->codigo }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #e5e7eb;font-size:11px;color:#6b7280;font-weight:bold;text-transform:uppercase;">Nombre</td>
                                <td align="right" style="padding:16px 24px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">
                                    {{ $documento->nombre }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;font-size:11px;color:#6b7280;font-weight:bold;text-transform:uppercase;">Área</td>
                                <td align="right" style="padding:16px 24px;font-size:14px;color:#111827;">
                                    {{ $documento->area }}
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:24px;padding:20px;background-color:#fef2f2;border:1px solid #fecaca;border-radius:12px;">
                            <p style="margin:0 0 8px;font-size:10px;color:#991b1b;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">Acción realizada por:</p>
                            <p style="margin:0;font-size:14px;color:#b91c1c;line-height:1.4;">
                                <strong>{{ $usuario->name }}</strong><br>
                                <span style="font-size:13px;opacity:0.8;">{{ $usuario->email }}</span>
                            </p>
                        </div>

                        <p style="margin:30px 0 0;font-size:13px;color:#6b7280;text-align:center;font-style:italic;">
                            "Este documento ya no debe utilizarse para ningún proceso operativo o auditoría dentro del sistema SGI."
                        </p>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#111827" align="center" style="padding:32px 40px;color:#ffffff;">
                        <p style="margin:0;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                            {{ config('app.name') }}
                        </p>
                        <p style="margin:12px 0 0;font-size:11px;color:#9ca3af;line-height:1.6;">
                            &copy; {{ date('Y') }} Gestión de Calidad e Inocuidad<br>
                            Este es un aviso automático de control documental.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
                                
</body>
</html>