<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-800">
            Detalle de Solicitud #{{ $solicitud->id }}
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Vista completa: solicitante, aprobación del jefe y validación SGI.
        </p>
    </x-slot>

    @php
        $today = \Carbon\Carbon::now()->startOfDay();

        // VERSION
        $daysVersion = $solicitud->fecha_vencimiento_version
            ? $today->diffInDays(
                \Carbon\Carbon::parse($solicitud->fecha_vencimiento_version)->startOfDay(),
                false
            )
            : null;

        // REVISION
        $daysRevision = $solicitud->fecha_vencimiento_revision
            ? $today->diffInDays(
                \Carbon\Carbon::parse($solicitud->fecha_vencimiento_revision)->startOfDay(),
                false
            )
            : null;

        function badgeColor($days) {
            if ($days === null) return 'bg-gray-100 text-gray-600';
            if ($days < 0) return 'bg-gray-200 text-gray-700';
            if ($days <= 30) return 'bg-red-100 text-red-700';
            if ($days <= 60) return 'bg-amber-100 text-amber-700';
            return 'bg-cyan-100 text-cyan-700';
        }
    @endphp

    <div class="py-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- INFO GENERAL --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Información General</h3>

            <p><strong>Solicitante:</strong> {{ $solicitud->usuario->name }}</p>
            <p><strong>Área:</strong> {{ $solicitud->usuario->area }}</p>
            <p><strong>Puesto:</strong> {{ $solicitud->usuario->puesto }}</p>
            <p><strong>Acción:</strong> {{ ucfirst($solicitud->accion) }}</p>

            <p>
                <strong>Estado:</strong>
                <span class="px-3 py-1 rounded text-white text-sm
                    {{ $solicitud->estado === 'pendiente' ? 'bg-yellow-500' :
                       ($solicitud->estado === 'atendido' ? 'bg-green-600' :
                       ($solicitud->estado === 'aprobado_jefe' ? 'bg-blue-600' : 'bg-red-600')) }}">
                    {{ ucfirst($solicitud->estado) }}
                </span>
            </p>

            @if ($solicitud->archivo_adjunto)
                <p>
                    <strong>Archivo:</strong>
                    <a href="{{ Storage::url($solicitud->archivo_adjunto) }}" target="_blank"
                       class="text-blue-600 underline">
                        Ver documento
                    </a>
                </p>
            @endif
        </div>

        {{-- DATOS DEL DOCUMENTO --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Datos del Documento</h3>

            <p><strong>Código:</strong> {{ $solicitud->codigo_documento ?? '—' }}</p>
            <p><strong>Nombre:</strong> {{ $solicitud->nombre_documento ?? '—' }}</p>
            <p><strong>Tipo:</strong> {{ $solicitud->tipo_documento ?? '—' }}</p>
            <p><strong>EL / PA:</strong> {{ $solicitud->formato_el_pa ?? '—' }}</p>
            <p><strong>Folio:</strong> {{ $solicitud->folio_version ?? '—' }}</p>
            <p><strong>Ubicación:</strong> {{ $solicitud->lugar_almacenamiento ?? '—' }}</p>
        </div>

        {{-- VIGENCIAS --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-4">
            <h3 class="text-lg font-bold border-b pb-2">Vigencias</h3>

            {{-- VERSION --}}
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold">Versión</p>
                    <p class="text-sm text-gray-600">
                        Vence: {{ $solicitud->fecha_vencimiento_version ?? '—' }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded text-sm font-bold {{ badgeColor($daysVersion) }}">
                    {{ $daysVersion !== null ? $daysVersion.' días' : 'N/A' }}
                </span>
            </div>

            {{-- REVISION --}}
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold">Revisión</p>
                    <p class="text-sm text-gray-600">
                        Vence: {{ $solicitud->fecha_vencimiento_revision ?? '—' }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded text-sm font-bold {{ badgeColor($daysRevision) }}">
                    {{ $daysRevision !== null ? $daysRevision.' días' : 'N/A' }}
                </span>
            </div>
        </div>

        {{-- JEFE --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Revisión del Jefe</h3>

            <p><strong>Jefe:</strong> {{ $solicitud->jefe?->name ?? '—' }}</p>
            <p><strong>Observaciones:</strong> {{ $solicitud->observaciones_jefe ?? '—' }}</p>

            @role('jefe')
                @if ($solicitud->estado === 'pendiente' && auth()->id() === $solicitud->jefe_id)
                    <a href="{{ route('solicitudes.approval_form', $solicitud->id) }}"
                       class="inline-block mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Aprobar / Rechazar
                    </a>
                @endif
            @endrole
        </div>

        {{-- SGI --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Validación SGI</h3>

            <p><strong>Administrador SGI:</strong> {{ $solicitud->administrador_sgi?->name ?? '—' }}</p>
            <p><strong>Alta SGI:</strong> {{ $solicitud->fecha_alta_sgi ?? '—' }}</p>
            <p><strong>Revisión actual:</strong> {{ $solicitud->revision_actual ?? '—' }}</p>
            <p><strong>Revisión anterior:</strong> {{ $solicitud->revision_anterior ?? '—' }}</p>

            @if ($solicitud->liga_archivo)
                <p>
                    <strong>Archivo oficial:</strong>
                    <a href="{{ $solicitud->liga_archivo }}" target="_blank"
                       class="text-blue-600 underline">
                        Ver archivo
                    </a>
                </p>
            @endif

            <p><strong>Observaciones SGI:</strong> {{ $solicitud->observaciones_sgi ?? '—' }}</p>

            @role('administrador_sgi')
                @if ($solicitud->estado === 'aprobado_jefe')
                    <a href="{{ route('solicitudes.finalize_form', $solicitud->id) }}"
                       class="inline-block mt-3 px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700">
                        Finalizar Solicitud
                    </a>
                @endif
            @endrole
        </div>

    </div>
</x-app-layout>
