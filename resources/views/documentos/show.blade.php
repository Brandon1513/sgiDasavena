<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Documento: {{ $documento->codigo }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Histórico de Versiones + Revisiones + Vigencias.
                </p>
            </div>

            <div class="shrink-0">
                @php
                    $badgeDoc = match($documento->semaforo_vencimiento ?? 'sin_fecha') {
                        'vencido' => 'bg-gray-200 text-gray-800',
                        'critico' => 'bg-red-100 text-red-700',
                        'alerta'  => 'bg-amber-100 text-amber-700',
                        'en_regla'=> 'bg-cyan-100 text-cyan-700',
                        default   => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <span class="px-4 py-2 rounded-xl text-sm font-bold {{ $badgeDoc }}">
                    {{ $documento->etiqueta_vencimiento ?? 'Sin fecha' }}
                </span>
            </div>
        </div>
    </x-slot>

    @php
        $today = now()->startOfDay();
        $vigente = $documento->versionVigente;

        $archivoOficial = $vigente?->sp_web_url ?: ($vigente?->liga_archivo ?: null);
        $lugar = $vigente?->lugar_almacenamiento ?? '—';

        // Normaliza helpers
        $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->toDateString() : null;
        $daysLeft = fn($d) => $d ? $today->diffInDays(\Carbon\Carbon::parse($d)->startOfDay(), false) : null;

        $badgeColor = function($days) {
            if ($days === null) return 'bg-gray-100 text-gray-600';
            if ($days < 0) return 'bg-gray-200 text-gray-700';
            if ($days <= 30) return 'bg-red-100 text-red-700';
            if ($days <= 60) return 'bg-amber-100 text-amber-700';
            return 'bg-cyan-100 text-cyan-700';
        };

        // Vigencias actuales
        $fechaVersion  = $fmtDate($vigente?->fecha_version);
        $fechaRevision = $fmtDate($vigente?->fecha_revision);

        $vencVersion   = $fmtDate($vigente?->fecha_vencimiento_version);
        $vencRevision  = $fmtDate($vigente?->fecha_vencimiento_revision);

        $daysVersion   = $daysLeft($vencVersion);
        $daysRevision  = $daysLeft($vencRevision);

        $vigV = $vigente?->vigencia_version_dias ?? '—';
        $vigR = $vigente?->vigencia_revision_dias ?? '—';
    @endphp

    <div class="py-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- A) Datos del documento --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Datos del Documento</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <p><strong>Código:</strong> {{ $documento->codigo ?? '—' }}</p>
                <p><strong>Nombre:</strong> {{ $documento->nombre ?? '—' }}</p>
                <p><strong>Tipo:</strong> {{ $documento->tipo_documento ?? '—' }}</p>
                <p><strong>EL / PA:</strong> {{ $documento->formato_el_pa ?? '—' }}</p>
                <p><strong>Área (depto):</strong> {{ $documento->area ?? '—' }}</p>
                <p><strong>Estatus:</strong> {{ $documento->estatus ?? '—' }}</p>
            </div>
        </div>

        {{-- B) Versión vigente (actual) --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-4">
            <h3 class="text-lg font-bold border-b pb-2">Vigente Actual</h3>

            @if($vigente)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <p><strong>Versión / Folio:</strong> {{ $vigente->version ?? '—' }}</p>
                    <p><strong>Estatus versión:</strong> {{ $vigente->estatus ?? '—' }}</p>
                    <p><strong>Revisión actual:</strong> {{ $vigente->revision_actual ?? '—' }}</p>
                    <p><strong>Revisión anterior:</strong> {{ $vigente->revision_anterior ?? '—' }}</p>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
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

                {{-- C) Vigencias actuales --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Versión --}}
                    <div class="p-4 rounded-xl border bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold">Vigencia de Versión</p>
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
                                <p class="font-semibold">Vigencia de Revisión</p>
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
            @else
                <p class="text-gray-600">Este documento no tiene versión vigente asignada.</p>
            @endif
        </div>

        {{-- D) HISTÓRICO DE VERSIONES --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Histórico de Versiones</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left">Versión</th>
                            <th class="p-3 text-left">Fecha versión</th>
                            <th class="p-3 text-left">Vence versión</th>
                            <th class="p-3 text-left">Días</th>
                            <th class="p-3 text-left">Estatus</th>
                            <th class="p-3 text-left">Archivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($versiones as $ver)
                            @php
                                $vv = $fmtDate($ver->fecha_vencimiento_version);
                                $dl = $daysLeft($vv);
                                $file = $ver->sp_web_url ?: ($ver->liga_archivo ?: null);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-semibold">{{ $ver->version ?? '—' }}</td>
                                <td class="p-3">{{ $ver->fecha_version?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-3">{{ $ver->fecha_vencimiento_version?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-3">
                                    <span class="px-3 py-1 rounded text-xs font-bold {{ $badgeColor($dl) }}">
                                        {{ $dl !== null ? $dl.' días' : 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-3">{{ $ver->estatus ?? '—' }}</td>
                                <td class="p-3">
                                    @if($file)
                                        <a href="{{ $file }}" target="_blank" class="text-blue-600 underline">Ver</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3 text-gray-500" colspan="6">Sin versiones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- E) HISTÓRICO DE REVISIONES --}}
        <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
            <h3 class="text-lg font-bold border-b pb-2">Histórico de Revisiones</h3>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left">Versión</th>
                            <th class="p-3 text-left">Rev. actual</th>
                            <th class="p-3 text-left">Fecha revisión</th>
                            <th class="p-3 text-left">Vence revisión</th>
                            <th class="p-3 text-left">Días</th>
                            <th class="p-3 text-left">Estatus</th>
                            <th class="p-3 text-left">Archivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($versiones as $ver)
                            @php
                                $rv = $fmtDate($ver->fecha_vencimiento_revision);
                                $dl = $daysLeft($rv);
                                $file = $ver->sp_web_url ?: ($ver->liga_archivo ?: null);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-semibold">{{ $ver->version ?? '—' }}</td>
                                <td class="p-3">{{ $ver->revision_actual ?? '—' }}</td>
                                <td class="p-3">{{ $ver->fecha_revision?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-3">{{ $ver->fecha_vencimiento_revision?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-3">
                                    <span class="px-3 py-1 rounded text-xs font-bold {{ $badgeColor($dl) }}">
                                        {{ $dl !== null ? $dl.' días' : 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-3">{{ $ver->estatus ?? '—' }}</td>
                                <td class="p-3">
                                    @if($file)
                                        <a href="{{ $file }}" target="_blank" class="text-blue-600 underline">Ver</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="p-3 text-gray-500" colspan="7">Sin revisiones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-500 mt-2">
                Nota: este “histórico de revisiones” se basa en los campos de revisión guardados dentro de cada versión
                (fecha_revision / fecha_vencimiento_revision). No requiere otra tabla.
            </p>
        </div>
        @php $user = auth()->user(); @endphp

@if($user->hasRole('administrador_sgi'))
    <form method="POST" action="{{ route('documentos.notificar_actualizacion', $documento->id) }}" class="mb-6">
        @csrf
        <div class="bg-white border rounded-2xl p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="font-black text-gray-800">Notificar que requiere actualización</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-gray-600 uppercase">Mensaje</label>
                    <input name="mensaje" required
                        value="{{ old('mensaje', 'Este documento requiere actualización. Favor de generar solicitud de actualización.') }}"
                        class="w-full mt-2 rounded-xl border border-gray-300/50 px-4 py-3">
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-600 uppercase">Enviar a</label>
                    <select name="destino" class="w-full mt-2 rounded-xl border border-gray-300/50 px-4 py-3">
                        <option value="area">Todo el departamento/área del documento</option>
                        <option value="jefe">Solo jefes del área</option>
                        <option value="usuario">Un usuario específico</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="text-xs font-bold text-gray-600 uppercase">Usuario (solo si eliges “usuario”)</label>
                    <input type="number" name="usuario_id" placeholder="ID usuario (opcional)"
                        class="w-full mt-2 rounded-xl border border-gray-300/50 px-4 py-3">
                    <p class="text-xs text-gray-500 mt-1">Si quieres, luego lo cambiamos por un select bonito.</p>
                </div>
            </div>

            <button class="px-6 py-3 rounded-xl bg-amber-600 text-white font-bold hover:bg-amber-700">
                Enviar correo
            </button>
        </div>
    </form>
@endif


    </div>
</x-app-layout>
