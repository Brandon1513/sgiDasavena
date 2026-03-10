<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualización requerida</title>
</head>
<body style="margin:0; padding:0; background-color:#eef2f7; font-family:Arial, Helvetica, sans-serif;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7; margin:0; padding:40px 0;">
        <tr>
            <td align="center">

                <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="width:680px; max-width:680px; background:#ffffff; border-radius:24px; overflow:hidden; border:1px solid #dde4ee; box-shadow:0 18px 45px rgba(31,41,55,0.10);">

                    <tr>
                        <td style="height:6px; background:linear-gradient(90deg, #6A2C75 0%, #9F6FB0 45%, #D9C2E0 100%); font-size:0; line-height:0;">
                            &nbsp;
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0; background:linear-gradient(135deg, #24112b 0%, #4b1f58 55%, #7b3f89 100%);">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:38px 42px 34px 42px;">

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="left" style="padding-bottom:18px;">
                                                    <span style="display:inline-block; padding:8px 14px; border-radius:999px; background:rgba(255,255,255,0.12); color:#f3e8f8; font-size:11px; letter-spacing:1.2px; text-transform:uppercase; font-weight:700;">
                                                        Sistema de Gestión
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        <h1 style="margin:0; font-size:31px; line-height:1.2; color:#ffffff; font-weight:700; letter-spacing:-0.5px;">
                                            Actualización requerida
                                        </h1>

                                        <p style="margin:14px 0 0; font-size:15px; line-height:1.7; color:#eadff0; max-width:540px;">
                                            Se ha detectado un documento que requiere atención y actualización dentro del sistema.
                                        </p>

                                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:24px;">
                                            <tr>
                                                <td style="padding:10px 14px; border-radius:14px; background:rgba(255,255,255,0.10); border:1px solid rgba(255,255,255,0.12);">
                                                    <span style="font-size:12px; color:#f5eefa; font-weight:700; letter-spacing:0.3px;">
                                                        Estado del flujo:
                                                    </span>
                                                    <span style="font-size:12px; color:#ffffff; font-weight:700;">
                                                        Requiere actualización
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px 42px 20px 42px;">

                            <p style="margin:0 0 16px; font-size:16px; line-height:1.7; color:#344054;">
                                Hola,
                            </p>

                            <p style="margin:0 0 30px; font-size:15px; line-height:1.8; color:#5b6472;">
                                El siguiente documento ha sido marcado para actualización. A continuación se presenta la información principal para su seguimiento.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(180deg, #fcfbfd 0%, #f7f4f9 100%); border:1px solid #e8deed; border-radius:20px; overflow:hidden;">
                                <tr>
                                    <td style="padding:22px 24px 12px 24px;">
                                        <p style="margin:0; font-size:12px; line-height:1.4; color:#8b6b96; text-transform:uppercase; letter-spacing:1.1px; font-weight:700;">
                                            Resumen del documento
                                        </p>
                                        <h2 style="margin:8px 0 0; font-size:20px; line-height:1.3; color:#3a1844; font-weight:700;">
                                            Información principal
                                        </h2>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:0 24px 24px 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ffffff; border:1px solid #ebe5f0; border-radius:16px; overflow:hidden;">

                                            <tr>
                                                <td style="padding:15px 18px; border-bottom:1px solid #f0eaf4; font-size:13px; color:#7a8699; width:38%; font-weight:700;">
                                                    Código
                                                </td>
                                                <td style="padding:15px 18px; border-bottom:1px solid #f0eaf4; font-size:14px; color:#1f2937; font-weight:700;">
                                                    {{ $documento->codigo }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="padding:15px 18px; border-bottom:1px solid #f0eaf4; font-size:13px; color:#7a8699; font-weight:700;">
                                                    Nombre
                                                </td>
                                                <td style="padding:15px 18px; border-bottom:1px solid #f0eaf4; font-size:14px; color:#1f2937; font-weight:600;">
                                                    {{ $documento->nombre }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td style="padding:15px 18px; font-size:13px; color:#7a8699; font-weight:700;">
                                                    Área
                                                </td>
                                                <td style="padding:15px 18px; font-size:14px; color:#1f2937; font-weight:600;">
                                                    {{ $documento->area ?? 'Sin asignar' }}
                                                </td>
                                            </tr>

                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px; background:#faf8fc; border:1px solid #ddcfe3; border-radius:16px; overflow:hidden;">
                                <tr>
                                    <td style="padding:18px 20px 10px 20px;">
                                        <p style="margin:0; font-size:12px; line-height:1.4; color:#8b6b96; text-transform:uppercase; letter-spacing:1px; font-weight:700;">
                                            Mensaje del SGI
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 20px 20px 20px;">
                                        <div style="background:#ffffff; border:1px solid #ebe5f0; border-radius:14px; padding:16px 18px; font-size:14px; line-height:1.8; color:#4b5563;">
                                            {{ $mensaje }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px; background:#faf8fc; border:1px dashed #ddcfe3; border-radius:16px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0; font-size:14px; line-height:1.8; color:#5b6472;">
                                            <strong style="color:#4b1f58;">Enviado por:</strong> {{ $remitenteNombre }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px; background:#faf8fc; border:1px dashed #ddcfe3; border-radius:16px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0; font-size:14px; line-height:1.8; color:#5b6472;">
                                            Puedes iniciar la actualización y revisar el detalle completo del documento desde el siguiente acceso.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:30px 0 8px 0;">
                                <tr>
                                    <td align="center" style="border-radius:14px; background:linear-gradient(135deg, #3b1645 0%, #6A2C75 60%, #8f56a0 100%); box-shadow:0 10px 24px rgba(106,44,117,0.28);">
                                        <a href="{{ route('documentos.show', $documento->id) }}"
                                           style="display:inline-block; padding:16px 28px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:14px; letter-spacing:0.2px;">
                                            Ver documento
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:22px 0 0; font-size:14px; line-height:1.8; color:#667085;">
                                Gracias por tu atención,<br>
                                <strong style="color:#4b1f58;">{{ config('app.name') }}</strong>
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 42px;">
                            <div style="height:1px; background:#ebe5f0; line-height:1px; font-size:1px;">&nbsp;</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 42px 28px 42px; background:#ffffff; text-align:center;">
                            <p style="margin:0 0 8px; font-size:12px; line-height:1.6; color:#98a2b3;">
                                Este correo fue generado automáticamente por el sistema.
                            </p>
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#98a2b3;">
                                © {{ date('Y') }} {{ config('app.name') }} · Gestión documental y seguimiento
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>