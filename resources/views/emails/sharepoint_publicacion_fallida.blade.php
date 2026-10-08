<!DOCTYPE html>
<html lang=es>
<h2>No se pudo publicar el archivo en SharePoint</h2>

<p>Después de varios intentos, no fue posible subir el archivo oficial de la
siguiente versión a SharePoint. La versión sigue vigente en el SGI (los
vencimientos siguen calculándose normalmente), pero el archivo debe
publicarse manualmente o reintentarse desde el expediente del documento.</p>

<ul>
    <li><strong>Código:</strong> {{ $documento->codigo }}</li>
    <li><strong>Nombre:</strong> {{ $documento->nombre }}</li>
    <li><strong>Área:</strong> {{ $documento->area ?? 'Sin área' }}</li>
    <li><strong>Versión:</strong> {{ $version->version }}</li>
</ul>

<p><strong>Error:</strong> {{ $error }}</p>

<p>Fecha: {{ now()->format('Y-m-d H:i') }}</p>

</html>
