@include('emails.partials.ac_header', [
    'badge' => 'Acción Correctiva',
    'titulo' => 'Nueva Acción<br>Correctiva Asignada',
    'subtitulo' => 'Se te asignó como responsable de una Acción Correctiva. A continuación el detalle.',
])

<p style="margin:0 0 10px;font-size:15px;color:#2d1033;font-weight:bold;font-family:Arial, sans-serif;">Hola, {{ $accionCorrectiva->responsable->name }},</p>
<p style="margin:0 0 36px;font-size:14px;color:#5a4a65;line-height:1.75;font-family:Arial, sans-serif;">
    Se registró una Acción Correctiva y quedaste como responsable de darle seguimiento. Este es el resumen:
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;border:1px solid #e2d7e8;">
    <tr>
        <td colspan="2" bgcolor="#f8f4f9" style="padding:14px 24px;border-bottom:1px solid #e2d7e8;">
            <span style="font-size:10px;font-weight:bold;color:#6A2C75;letter-spacing:1px;text-transform:uppercase;font-family:Arial, sans-serif;">Detalle de la Acción Correctiva</span>
        </td>
    </tr>
    <tr>
        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Folio</td>
        <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-weight:bold;font-family:Arial, sans-serif;">
            {{ $accionCorrectiva->codigo }}
        </td>
    </tr>
    <tr>
        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Origen</td>
        <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-family:Arial, sans-serif;">
            {{ $accionCorrectiva->origen->nombre }}
        </td>
    </tr>
    <tr>
        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Fecha de apertura</td>
        <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-family:Arial, sans-serif;">
            {{ $accionCorrectiva->fecha_apertura->format('d/m/Y') }}
        </td>
    </tr>
    <tr>
        <td style="padding:16px 24px;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Estado</td>
        <td align="right" style="padding:16px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="right">
                <tr>
                    <td style="background-color:#f3eef6;border:1px solid #6A2C75;padding:4px 12px;border-radius:10px;">
                        <span style="color:#6A2C75;font-weight:bold;font-size:11px;text-transform:uppercase;font-family:Arial, sans-serif;">{{ $accionCorrectiva->estado->nombre }}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div style="margin-top:24px;border-left:4px solid #D4A018;background-color:#fffcf5;padding:16px;">
    <p style="margin:0;font-size:13px;color:#7a6040;line-height:1.6;font-family:Arial, sans-serif;">
        <strong style="color:#b38600;">Descripción:</strong> {{ $accionCorrectiva->descripcion }}
    </p>
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:40px;">
    <tr>
        <td align="center">
            <a href="{{ route('acciones-correctivas.show', $accionCorrectiva->id) }}"
               style="display:inline-block;padding:16px 34px;font-size:13px;font-weight:bold;color:#2d1033;background-color:#D4A018;border-radius:8px;text-transform:uppercase;letter-spacing:1px;text-decoration:none;font-family:Arial, sans-serif;">
                Ver expediente →
            </a>
        </td>
    </tr>
</table>

@include('emails.partials.ac_footer')
