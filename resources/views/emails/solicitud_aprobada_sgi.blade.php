@component('mail::message')
# Solicitud Aprobada por el Jefe

El jefe **{{ $solicitud->jefe->name ?? 'Sin jefe' }}** ha aprobado una solicitud enviada por **{{ $solicitud->usuario->name }}**.

**Acción Solicitada:** {{ ucfirst($solicitud->accion) }}  
**Estado Actual:** {{ ucfirst($solicitud->estado) }}

@component('mail::button', ['url' => route('solicitudes.show', $solicitud->id)])
Ver Detalles de la Solicitud
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
