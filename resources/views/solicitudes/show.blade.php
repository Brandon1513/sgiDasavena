<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Detalle de Solicitud #{{ $solicitud->id }}
        </h2>
    </x-slot>

    <div class="py-12" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="p-6 space-y-6 bg-white border border-gray-200 rounded-lg shadow">

                <div class="space-y-2">
                    <h3 class="pb-2 text-lg font-semibold border-b">Información General</h3>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Solicitante</span>: {{ $solicitud->usuario->name }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Área</span>: {{ $solicitud->usuario->area }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Puesto</span>: {{ $solicitud->usuario->puesto }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Acción</span>: {{ ucfirst($solicitud->accion) }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Estado</span>: 
                        <span class="inline-block px-2 py-1 rounded text-white {{ $solicitud->estado === 'pendiente' ? 'bg-yellow-500' : ($solicitud->estado === 'atendido' ? 'bg-green-600' : 'bg-red-600') }}">
                            {{ ucfirst($solicitud->estado) }}
                        </span>
                    </p>
                    @if ($solicitud->archivo_adjunto)
                        <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Archivo Adjunto</span>: 
                            <a href="{{ Storage::url($solicitud->archivo_adjunto) }}" target="_blank" class="text-blue-600 underline">Ver Documento</a>
                        </p>
                    @endif
                </div>
                    <div class="space-y-2">
                    <h3 class="pb-2 text-lg font-semibold border-b">Modificaciones</h3>


                    <p>
                        <span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">
                            Comentarios del solicitante
                        </span>:
                        {{ $solicitud->comentarios ?: 'Sin comentarios' }}
                    </p>
                </div>

                <div class="space-y-2">
                    <h3 class="pb-2 text-lg font-semibold border-b">Evaluación del Jefe</h3>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Jefe que Aprobó</span>: {{ $solicitud->jefe?->name ?? 'Aún no procesado' }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Observaciones del Jefe</span>: {{ $solicitud->observaciones_jefe ?? 'Aún no procesado' }}</p>
                </div>
                @role('jefe')
                    @if ($solicitud->estado === 'pendiente' && auth()->id() === $solicitud->jefe_id)
                        <div class="mt-6 text-right">
                            <a href="{{ route('solicitudes.approval_form', $solicitud->id) }}"
                            class="inline-block px-4 py-2 font-bold text-white bg-green-600 rounded hover:bg-green-700">
                                Aprobar/Rechazar Solicitud
                            </a>
                        </div>
                    @endif
                @endrole

                <div class="space-y-2">
                    <h3 class="pb-2 text-lg font-semibold border-b">Revisión SGI</h3>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Administrador SGI</span>: {{ $solicitud->administrador_sgi?->name ?? 'Aún no procesado' }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Alta en el Sistema de Gestión</span>: {{ $solicitud->fecha_alta_sgi ?? 'Aún no procesado' }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Número de Revisión Actual</span>: {{ $solicitud->revision_actual ?? 'Aún no procesado' }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Número de Revisión Anterior</span>: {{ $solicitud->revision_anterior ?? 'Aún no procesado' }}</p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Medio de Archivo (Liga)</span>: 
                        @if ($solicitud->liga_archivo)
                            <a href="{{ $solicitud->liga_archivo }}" target="_blank" class="text-blue-600 underline">Ver Medio</a>
                        @else
                            Aún no procesado
                        @endif
                    </p>
                    <p><span class="inline-block px-2 py-1 text-sm bg-gray-100 rounded">Observaciones SGI</span>: {{ $solicitud->observaciones_sgi ?? 'Aún no procesado' }}</p>
                </div>

                @role('administrador_sgi')
                    @if ($solicitud->estado === 'aprobado_jefe')
                        <div class="mt-4">
                            <a href="{{ route('solicitudes.finalize_form', $solicitud->id) }}"
                            class="inline-block px-4 py-2 font-semibold text-white bg-orange-600 rounded hover:bg-orange-700">
                                Finalizar Solicitud
                            </a>
                        </div>
                    @endif
                @endrole

            </div>
        </div>
    </div>
</x-app-layout>
