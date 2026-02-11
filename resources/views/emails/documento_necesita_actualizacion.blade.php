<div style="font-family: Arial, sans-serif; line-height: 1.5;">
    <h2>Actualización requerida</h2>

    <p><strong>Código:</strong> {{ $documento->codigo }}</p>
    <p><strong>Nombre:</strong> {{ $documento->nombre }}</p>
    <p><strong>Área:</strong> {{ $documento->area ?? '—' }}</p>

    <p><strong>Mensaje de SGI:</strong></p>
    <p style="background:#f5f5f5;padding:12px;border-radius:8px;">
        {{ $mensaje }}
    </p>

    <p>Enviado por: <strong>{{ $remitenteNombre }}</strong></p>

    <p>
        Puedes iniciar la actualización entrando al documento:
        <a href="{{ route('documentos.show', $documento->id) }}">
            Ver documento
        </a>
    </p>
</div>
