<!DOCTYPE html>
<html lang=es>
<h2>Documento marcado como obsoleto</h2>

<p>Se ha marcado como obsoleto el siguiente documento:</p>

<ul>
    <li><strong>Código:</strong> {{ $documento->codigo }}</li>
    <li><strong>Nombre:</strong> {{ $documento->nombre }}</li>
    <li><strong>Área:</strong> {{ $documento->area }}</li>
</ul>

<p>Fecha: {{ now()->format('Y-m-d H:i') }}</p>

</html>