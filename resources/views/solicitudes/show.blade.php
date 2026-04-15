
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Detalle de Solicitud #{{ $solicitud->id }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Solicitud + Publicación oficial (Documento/Versiones) + Vigencias.
            </p>
        </div>
    </x-slot>
    

    @php
        $today = now()->startOfDay();

        // ============================
        // 1) Cargar Documento + Vigente
        // ============================
        $doc = null;
        $vigente = null;

        if (!empty($solicitud->codigo_documento)) {
            $doc = \App\Models\Documento::with('versionVigente')
                ->where('codigo', $solicitud->codigo_documento)
                ->first();

            $vigente = $doc?->versionVigente;
        }

        // ============================
        // 2) Tomar valores (vigente primero, si no solicitud)
        // ============================
        $codigoDoc = $doc?->codigo ?? ($solicitud->codigo_documento ?? '—');
        $nombreDoc = $doc?->nombre ?? ($solicitud->nombre_documento ?? '—');
        $tipoDoc   = $doc?->tipo_documento ?? ($solicitud->tipo_documento ?? '—');
        $formato   = $doc?->formato_el_pa ?? ($solicitud->formato_el_pa ?? '—');
        $areaDoc   = $doc?->area ?? (optional($solicitud->usuario)->area ?? '—');

        $pubVersion = $vigente?->version ?? ($solicitud->folio_version ?? '—');
        $revActual  = $vigente?->revision_actual ?? ($solicitud->revision_actual ?? '—');
        $revAnt     = $vigente?->revision_anterior ?? ($solicitud->revision_anterior ?? '—');

        $lugar = $vigente?->lugar_almacenamiento ?? $solicitud->lugar_almacenamiento ?? '—';

        // Archivo oficial (prioridad: sp_web_url > liga_archivo)
        $archivoOficial = $vigente?->sp_web_url
            ?: ($vigente?->liga_archivo ?: ($solicitud->liga_archivo ?? null));

        // Fechas base
        $fechaVersion  = $vigente?->fecha_version  ?? $solicitud->fecha_version;
        $fechaRevision = $vigente?->fecha_revision ?? $solicitud->fecha_revision;

        // Fechas vencimiento
        $vencVersion   = $vigente?->fecha_vencimiento_version  ?? $solicitud->fecha_vencimiento_version;
        $vencRevision  = $vigente?->fecha_vencimiento_revision ?? $solicitud->fecha_vencimiento_revision;

        // Normaliza a Y-m-d para imprimir bonito
        $fechaVersion  = $fechaVersion  ? \Carbon\Carbon::parse($fechaVersion)->toDateString()  : null;
        $fechaRevision = $fechaRevision ? \Carbon\Carbon::parse($fechaRevision)->toDateString() : null;
        $vencVersion   = $vencVersion   ? \Carbon\Carbon::parse($vencVersion)->toDateString()   : null;
        $vencRevision  = $vencRevision  ? \Carbon\Carbon::parse($vencRevision)->toDateString()  : null;

        // Días restantes
        $daysVersion = $vencVersion
            ? $today->diffInDays(\Carbon\Carbon::parse($vencVersion)->startOfDay(), false)
            : null;

        $daysRevision = $vencRevision
            ? $today->diffInDays(\Carbon\Carbon::parse($vencRevision)->startOfDay(), false)
            : null;

        // Vigencias
        $vigV = $vigente?->vigencia_version_dias ?? ($solicitud->vigencia_version_dias ?? '—');
        $vigR = $vigente?->vigencia_revision_dias ?? ($solicitud->vigencia_revision_dias ?? '—');

        // Colores badges
        $badgeColor = function($days) {
            if ($days === null) return 'bg-gray-100 text-gray-600';
            if ($days < 0) return 'bg-gray-200 text-gray-700';
            if ($days <= 30) return 'bg-red-100 text-red-700';
            if ($days <= 60) return 'bg-amber-100 text-amber-700';
            return 'bg-cyan-100 text-cyan-700';
        };

        $estadoColor = match($solicitud->estado) {
            'pendiente'      => 'bg-yellow-500',
            'aprobado_jefe'  => 'bg-blue-600',
            'atendido'       => 'bg-green-600',
            default          => 'bg-red-600',
        };

        $obsSgi = $solicitud->observaciones_sgi ?? '—';
    @endphp

    <div class="py-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- A) Solicitud --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Información de la Solicitud</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>Solicitante:</strong> {{ optional($solicitud->usuario)->name ?? '—' }}</p>
                <p><strong>Área:</strong> {{ optional($solicitud->usuario)->area ?? '—' }}</p>
                <p><strong>Puesto:</strong> {{ optional($solicitud->usuario)->puesto ?? '—' }}</p>
                <p><strong>Acción:</strong> {{ ucfirst($solicitud->accion) }}</p>
            </div>

            <p>
                <strong>Estado:</strong>
                <span class="px-3 py-1 rounded text-white text-sm {{ $estadoColor }}">
                    {{ ucfirst($solicitud->estado) }}
                </span>
            </p>

            <p><strong>Comentarios del solicitante:</strong> {{ $solicitud->comentarios ?? '—' }}</p>

            @if($solicitud->archivo_adjunto)
                <p>
                    <strong>Archivo adjunto:</strong>
                    <a href="{{ Storage::url($solicitud->archivo_adjunto) }}" target="_blank" class="text-blue-600 underline">
                        Ver archivo
                    </a>
                </p>
            @endif
        </div>

        {{-- Documento --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Datos del Documento</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>Código:</strong> {{ $codigoDoc }}</p>
                <p><strong>Nombre:</strong> {{ $nombreDoc }}</p>
                <p><strong>Tipo:</strong> {{ $tipoDoc }}</p>
                <p><strong>EL / PA:</strong> {{ $formato }}</p>
                <p><strong>Área (depto):</strong> {{ $areaDoc }}</p>
                <p><strong>Versión / Folio:</strong> {{ $pubVersion }}</p>
            </div>

            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>Lugar de almacenamiento:</strong> {{ $lugar }}</p>
                <p>
                    <strong>Archivo oficial:</strong>
                    @if($archivoOficial)
                        <a href="{{ $archivoOficial }}" target="_blank" class="text-blue-600 underline">
                            Ver en SharePoint
                        </a>
                    @else
                        —
                    @endif
                </p>
            </div>

            <div class="mt-3 p-4 rounded-xl border bg-slate-50">
                <p class="text-sm text-slate-700">
                    <strong>Fuente oficial:</strong>
                    {{ $vigente ? 'documento_versiones (vigente)' : 'solicitud_formatos (fallback)' }}
                </p>
            </div>
        </div>

        {{-- C) Vigencias --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-4">
            <h3 class="text-lg font-bold border-b pb-2">Vigencias</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Versión --}}
                <div class="p-4 rounded-xl border bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">Versión</p>
                            <p class="text-sm text-gray-600">Fecha versión: {{ $fechaVersion ?? '—' }}</p>
                            <p class="text-sm text-gray-600">Vence: {{ $vencVersion ?? '—' }}</p>
                            <p class="text-xs text-gray-500 mt-1">Vigencia: {{ $vigV }} días</p>
                        </div>
                        <span class="px-3 py-1 rounded text-sm font-bold {{ $badgeColor($daysVersion) }}">
                            {{ $daysVersion !== null ? $daysVersion.' días' : 'N/A' }}
                        </span>
                    </div>
                </div>

                {{-- Revisión --}}
                <div class="p-4 rounded-xl border bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">Revisión</p>
                            <p class="text-sm text-gray-600">Fecha revisión: {{ $fechaRevision ?? '—' }}</p>
                            <p class="text-sm text-gray-600">Vence: {{ $vencRevision ?? '—' }}</p>
                            <p class="text-xs text-gray-500 mt-1">Vigencia: {{ $vigR }} días</p>
                        </div>
                        <span class="px-3 py-1 rounded text-sm font-bold {{ $badgeColor($daysRevision) }}">
                            {{ $daysRevision !== null ? $daysRevision.' días' : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>Revisión actual:</strong> {{ $revActual }}</p>
                <p><strong>Revisión anterior:</strong> {{ $revAnt }}</p>
            </div>
        </div>

        {{-- D) SGI --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Validación SGI</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>Administrador SGI:</strong> {{ optional($solicitud->administrador_sgi)->name ?? '—' }}</p>
                <p><strong>Alta SGI:</strong> {{ $solicitud->fecha_alta_sgi ?? '—' }}</p>
            </div>

            <p><strong>Observaciones SGI:</strong> {{ $obsSgi }}</p>
        </div>
        
        @php $user = auth()->user(); @endphp




    </div>
</x-app-layout>
