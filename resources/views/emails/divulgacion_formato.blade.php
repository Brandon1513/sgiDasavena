@component('mail::message')
# Actualización de Formato Divulgada

Se ha finalizado una solicitud de actualización de formato.

**Solicitante:** {{ $solicitud->usuario->name }}  
**Acción:** {{ ucfirst($solicitud->accion) }}  
**Fecha de Alta en el SGI:** {{ \Carbon\Carbon::parse($solicitud->fecha_alta_sgi)->format('d/m/Y') }}  
**Revisión Actual:** {{ $solicitud->revision_actual }} 
**Observaciones del SGI:** {{ $solicitud->observaciones_sgi ?? 'Sin observaciones' }} 
**Medio de Archivo:**  
[Ver Documento]({{ $solicitud->liga_archivo }})

Gracias,  
{{ config('app.name') }}
@endcomponent
