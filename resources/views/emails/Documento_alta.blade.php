
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
        .details { background: #f5fbff; border: 1px solid #d7e9ff; border-radius: 14px; padding: 18px; margin: 20px 0; }
        .details p { margin: 0; color: #1f3a8a; font-weight: 600; }
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
                <p><strong>Folio / Detalle:</strong> <span>{{ $data->nombre ?? 'N/A' }}</span></p>
                @if(!empty($data->descripcion))
                    <p><span>{{ $data->descripcion }}</span></p>
                @endif
            </div>

            <p class="intro">Si tienes alguna duda o aclaración, por favor ponte en contacto con el administrador del sistema.</p>

            <div class="footer">
                <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            </div>
        </div>
    </div>

</body>
</html>