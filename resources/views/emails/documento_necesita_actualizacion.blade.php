<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización requerida</title>
</head>
<body style="margin:0;padding:0;background-color:#f3eef6;font-family:Arial,Helvetica,sans-serif;color:#2d1033;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3eef6" style="background-color:#f3eef6;width:100%;min-width:100%;margin:0;padding:40px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                   style="width:600px;max-width:600px;background:#faf7fb;border:1px solid rgba(106,44,117,0.12);border-radius:16px;box-shadow:0 20px 60px rgba(106,44,117,0.12);overflow:hidden;">
                <tr>
                    <td style="height:4px;background:linear-gradient(90deg,#6A2C75,#D4A018,#6A2C75);"></td>
                </tr>
                <tr>
                    <td style="padding:56px 40px 60px;background:#4a1d60;background-image:linear-gradient(160deg,#2d1033 0%,#4a1d60 45%,#6A2C75 100%);text-align:center;color:#ffffff;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center">
                                    <div style="display:inline-block;padding:6px 16px;border-radius:100px;background:rgba(212,160,24,0.15);border:1px solid rgba(212,160,24,0.4);color:#D4A018;font-size:10px;letter-spacing:1px;text-transform:uppercase;font-weight:bold;margin-bottom:24px;">
                                        ● &nbsp;Acción Pendiente
                                    </div>
                                    <h1 style="margin:0;font-size:26px;line-height:1.2;font-weight:bold;">Actualización<br>Requerida</h1>
                                    <p style="margin:14px 0 0;font-size:14px;color:rgba(255,255,255,0.8);line-height:1.6;">
                                        Se ha detectado un documento que requiere atención inmediata dentro del sistema.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:40px 40px 36px;background:#faf7fb;">
                        <p style="margin:0 0 10px;font-size:15px;color:#2d1033;font-weight:bold;">Hola,</p>
                        <p style="margin:0 0 36px;font-size:14px;color:#5a4a65;line-height:1.75;">
                            El siguiente documento ha sido marcado para su actualización. Por favor, revise la información detallada:
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background:#ffffff;border:1px solid rgba(106,44,117,0.12);border-radius:12px;">
                            <tr>
                                <td colspan="2" style="padding:14px 24px;background:linear-gradient(135deg,rgba(106,44,117,0.06),rgba(212,160,24,0.04));border-bottom:1px solid rgba(106,44,117,0.08);">
                                    <span style="font-size:10px;font-weight:bold;color:#6A2C75;letter-spacing:1px;text-transform:uppercase;">Resumen del Documento</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;width:40%;">
                                    Código
                                </td>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;text-align:right;font-weight:bold;">
                                    {{ $documento->codigo }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;">
                                    Nombre
                                </td>
                                <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;text-align:right;">
                                    {{ $documento->nombre }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:16px 24px;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;">
                                    Estado
                                </td>
                                <td style="padding:16px 24px;text-align:right;">
                                    <span style="color:#D4A018;font-weight:bold;font-size:12px;">Requiere actualización</span>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:24px;background:#faf8fc;border:1px solid #ddcfe3;border-radius:12px;padding:20px;">
                            <p style="margin:0 0 8px;font-size:10px;color:#8b6b96;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                                Mensaje del SGI
                            </p>
                            <p style="margin:0;font-size:14px;color:#4b5563;line-height:1.6;">
                                {{ $mensaje }}
                            </p>
                        </div>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:40px;">
                            <tr>
                                <td align="center">
                                    <a href="{{ route('documentos.show', $documento->id) }}"
                                       style="display:inline-block;padding:14px 34px;font-size:13px;font-weight:bold;color:#2d1033;background:linear-gradient(135deg,#D4A018,#f0c84a);border-radius:8px;text-transform:uppercase;letter-spacing:1px;text-decoration:none;box-shadow:0 4px 20px rgba(212,160,24,0.25);">
                                        Ver Documento →
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 40px 32px;background:#2d1033;background-image:linear-gradient(135deg,#2d1033,#4a1d60);text-align:center;color:#ffffff;">
                        <p style="margin:0;font-size:11px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
                            {{ config('app.name') }}
                        </p>
                        <p style="margin:12px 0 0;font-size:11px;color:rgba(255,255,255,0.65);">
                            &copy; {{ date('Y') }} Gestión Documental
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>