@php
    $vencida = $dias < 0;
    $colorAlerta = $vencida ? '#ba1a1a' : '#b38600';
    $bgAlerta = $vencida ? '#fff0f0' : '#fffcf5';
@endphp
@include('emails.partials.ac_header', [
    'badge' => $vencida ? 'Actividad Vencida' : 'Próxima a Vencer',
    'titulo' => $vencida ? 'Actividad Vencida<br>Sin Cumplir' : 'Actividad Próxima<br>a Vencer',
    'subtitulo' => $vencida
        ? 'Una actividad bajo tu responsabilidad ya pasó su fecha de compromiso sin marcarse como cumplida.'
        : 'Una actividad bajo tu responsabilidad está por llegar a su fecha de compromiso.',
])

<p style="margin:0 0 10px;font-size:15px;color:#2d1033;font-weight:bold;font-family:Arial, sans-serif;">Hola, {{ $actividad->responsable->name }},</p>
<p style="margin:0 0 36px;font-size:14px;color:#5a4a65;line-height:1.75;font-family:Arial, sans-serif;">
    Este es el recordatorio de tu actividad en el plan de acción de <strong>{{ $accionCorrectiva->codigo }}</strong>:
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff;border:1px solid #e2d7e8;">
    <tr>
        <td colspan="2" bgcolor="#f8f4f9" style="padding:14px 24px;border-bottom:1px solid #e2d7e8;">
            <span style="font-size:10px;font-weight:bold;color:#6A2C75;letter-spacing:1px;text-transform:uppercase;font-family:Arial, sans-serif;">Detalle de la actividad</span>
        </td>
    </tr>
    <tr>
        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Acción Correctiva</td>
        <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-weight:bold;font-family:Arial, sans-serif;">
            {{ $accionCorrectiva->codigo }}
        </td>
    </tr>
    <tr>
        <td style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Fecha de compromiso</td>
        <td align="right" style="padding:16px 24px;border-bottom:1px solid #f5f0f8;font-size:14px;color:#2d1033;font-family:Arial, sans-serif;">
            {{ $actividad->fecha_compromiso->format('d/m/Y') }}
        </td>
    </tr>
    <tr>
        <td style="padding:16px 24px;font-size:11px;color:#9b6baa;font-weight:bold;text-transform:uppercase;font-family:Arial, sans-serif;">Días</td>
        <td align="right" style="padding:16px 24px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="right">
                <tr>
                    <td style="background-color:{{ $vencida ? '#ffdad6' : '#fdeec0' }};border:1px solid {{ $colorAlerta }};padding:4px 12px;border-radius:10px;">
                        <span style="color:{{ $colorAlerta }};font-weight:bold;font-size:11px;text-transform:uppercase;font-family:Arial, sans-serif;">
                            {{ $vencida ? 'Vencida hace ' . abs($dias) . ' día' . (abs($dias) === 1 ? '' : 's') : 'Vence en ' . $dias . ' día' . ($dias === 1 ? '' : 's') }}
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div style="margin-top:24px;border-left:4px solid {{ $colorAlerta }};background-color:{{ $bgAlerta }};padding:16px;">
    <p style="margin:0;font-size:13px;color:#7a6040;line-height:1.6;font-family:Arial, sans-serif;">
        <strong style="color:{{ $colorAlerta }};">Actividad:</strong> {{ $actividad->descripcion }}
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
