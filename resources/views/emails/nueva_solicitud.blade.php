@component('mail::message')
# Nueva Solicitud de Actualización de Formato

El empleado **{{ $solicitud->usuario->name }}** ha enviado una nueva solicitud.

**Detalles de la Solicitud:**
- **Área:** {{ $solicitud->usuario->area ?? 'No especificado' }}
- **Puesto:** {{ $solicitud->usuario->puesto ?? 'No especificado' }}
- **Acción Solicitada:** {{ ucfirst($solicitud->accion) }}
- **Estado Actual:** {{ ucfirst($solicitud->estado) }}

@component('mail::button', ['url' => route('solicitudes.show', $solicitud->id)])
Ver Solicitud
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
