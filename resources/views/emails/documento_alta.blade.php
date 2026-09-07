
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Documento</title>
    <style>
        body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #eef4fb; color: #2c3e50; margin: 0; padding: 20px; }
        .container { max-width: 640px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 16px; padding: 30px; box-shadow: 0 18px 32px rgba(11, 45, 80, 0.08); border: 1px solid #d9e2ec; }
        .header { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; }
        .header-icon { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #0f6fc2 0%, #00bcd4 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .header h2 { margin: 0; color: #0f2a55; font-size: 24px; }
        .header p { margin: 0; color: #64748b; font-size: 14px; }
        .intro { color: #475569; line-height: 1.75; margin-bottom: 20px; }
        .details { background: #f5fbff; border: 1px solid #d7e9ff; border-radius: 14px; padding: 4px 18px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; gap: 16px; padding: 12px 0; border-bottom: 1px solid #e1edfa; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #5b7a9d; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; }
        .detail-value { color: #1f3a8a; font-weight: 600; text-align: right; }
        .note { background: #fffcf5; border-left: 4px solid #D4A018; padding: 14px 16px; border-radius: 6px; color: #7a6040; font-size: 13px; line-height: 1.6; margin: 20px 0; }
        .footer { margin-top: 24px; font-size: 13px; color: #6b7280; border-top: 1px solid #e2e8f0; padding-top: 16px; }
    </style>
</head>
<body>

    <div class="container">
        <div class="card">
            <div class="header">
                <div class="header-icon">D</div>
                <div>
                    <h2>Notificación de Alta</h2>
                    <p>Se ha registrado un nuevo documento en el sistema.</p>
                </div>
            </div>

            <p class="intro">Estimado usuario,</p>
            <p class="intro">Te informamos que se ha procesado con éxito el alta del documento en el sistema.</p>

            <div class="details">
                <div class="detail-row">
                    <span class="detail-label">Documento</span>
                    <span class="detail-value">{{ $solicitud->nombre_documento ?? 'N/A' }}</span>
                </div>
                @if(!empty($solicitud->codigo_documento))
                    <div class="detail-row">
                        <span class="detail-label">Código</span>
                        <span class="detail-value">{{ $solicitud->codigo_documento }}</span>
                    </div>
                @endif
                @if(!empty($solicitud->tipo_documento))
                    <div class="detail-row">
                        <span class="detail-label">Tipo de documento</span>
                        <span class="detail-value">{{ $solicitud->tipo_documento }}</span>
                    </div>
                @endif
                @if(!empty($solicitud->formato_el_pa))
                    <div class="detail-row">
                        <span class="detail-label">Formato</span>
                        <span class="detail-value">{{ $solicitud->formato_el_pa }}</span>
                    </div>
                @endif
                @if(!empty($solicitud->folio_version))
                    <div class="detail-row">
                        <span class="detail-label">Folio / Versión</span>
                        <span class="detail-value">{{ $solicitud->folio_version }}</span>
                    </div>
                @endif
                @if($solicitud->fecha_alta_sgi)
                    <div class="detail-row">
                        <span class="detail-label">Fecha de alta</span>
                        <span class="detail-value">{{ $solicitud->fecha_alta_sgi->format('d/m/Y') }}</span>
                    </div>
                @endif
                @if(!empty($solicitud->lugar_almacenamiento))
                    <div class="detail-row">
                        <span class="detail-label">Lugar de almacenamiento</span>
                        <span class="detail-value">{{ $solicitud->lugar_almacenamiento }}</span>
                    </div>
                @endif
                @if(!empty($solicitud->liga_archivo))
                    <div class="detail-row">
                        <span class="detail-label">Archivo</span>
                        <span class="detail-value"><a href="{{ $solicitud->liga_archivo }}" style="color:#0f6fc2;">Ver documento</a></span>
                    </div>
                @endif
            </div>

            @if(!empty($solicitud->comentarios))
                <div class="note">
                    <strong>Comentarios:</strong> {{ $solicitud->comentarios }}
                </div>
            @endif

            <p class="intro">Si tienes alguna duda o aclaración, por favor ponte en contacto con el administrador del sistema.</p>

            <div class="footer">
                <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            </div>
        </div>
    </div>

</body>
</html>