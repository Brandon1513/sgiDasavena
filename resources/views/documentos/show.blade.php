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
            @if(auth()->user()->hasRole('administrador_sgi') || auth()->user()->hasRole('administrador'))
            <div class="mb-4">
                <a href="{{ route('documentos.edit', $documento->id) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-md transition duration-300">
                    <i class="fas fa-edit mr-2"></i> Editar Datos Oficiales
                </a>
            </div>
            @endif

            <div class="shrink-0">
                @php
                $badgeDoc = match($documento->semaforo_vencimiento ?? 'sin_fecha') {
                'vencido' => 'bg-gray-200 text-gray-800',
                'critico' => 'bg-red-100 text-red-700',
                'alerta' => 'bg-amber-100 text-amber-700',
                'en_regla'=> 'bg-cyan-100 text-cyan-700',
                default => 'bg-slate-100 text-slate-600',
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

    $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->toDateString() : null;

    // ✅ Solo contar días si estatus = vigente
    $daysLeftIfVigente = function($estatus, $d) use ($today) {
    if (($estatus ?? null) !== 'vigente') return null;
    if (!$d) return null;
    return $today->diffInDays(\Carbon\Carbon::parse($d)->startOfDay(), false);
    };

    $badgeColor = function($days) {
    if ($days === null) return 'bg-gray-100 text-gray-600';
    if ($days < 0) return 'bg-gray-200 text-gray-700' ;
        if ($days <=30) return 'bg-red-100 text-red-700' ;
        if ($days <=60) return 'bg-amber-100 text-amber-700' ;
        return 'bg-cyan-100 text-cyan-700' ;
        };

        // Vigencias actuales
        $fechaVersion=$fmtDate($vigente?->fecha_version);
        $fechaRevision = $fmtDate($vigente?->fecha_revision);

        $vencVersion = $fmtDate($vigente?->fecha_vencimiento_version);
        $vencRevision = $fmtDate($vigente?->fecha_vencimiento_revision);

        $daysVersion = $daysLeftIfVigente($vigente?->estatus, $vencVersion);
        $daysRevision = $daysLeftIfVigente($vigente?->estatus, $vencRevision);

        $vigV = $vigente?->vigencia_version_dias ?? '—';
        $vigR = $vigente?->vigencia_revision_dias ?? '—';

        $user = auth()->user();
        $isSgi = $user && ($user->hasRole('administrador_sgi') || $user->hasRole('administrador'));

        // Fallbacks para evitar errores
        $revisiones = $revisiones ?? collect();
        $revisionesPorVersion = $revisionesPorVersion ?? $revisiones->groupBy(fn($r) => $r->version?->version ?? ('Versión ID '.$r->documento_version_id));
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

            {{-- B) Vigente Actual --}}
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

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold">Vigencia de Versión</p>
                                <p class="text-sm text-gray-600">Fecha versión: {{ $fechaVersion ?? '—' }}</p>
                                <p class="text-sm text-gray-600">Vence: {{ $vencVersion ?? '—' }}</p>
                                <p class="text-xs text-gray-500 mt-1">Vigencia: {{ $vigV }} días</p>
                            </div>
                            <span class="px-3 py-1 rounded text-sm font-bold {{ $badgeColor($daysVersion) }}">
                                {{ $daysVersion !== null ? $daysVersion.' días' : '—' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border bg-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold">Vigencia de Revisión</p>
                                <p class="text-sm text-gray-600">Fecha revisión: {{ $fechaRevision ?? '—' }}</p>
                                <p class="text-sm text-gray-600">Vence: {{ $vencRevision ?? '—' }}</p>
                                <p class="text-xs text-gray-500 mt-1">Vigencia: {{ $vigR }} días</p>
                            </div>
                            <span class="px-3 py-1 rounded text-sm font-bold {{ $badgeColor($daysRevision) }}">
                                {{ $daysRevision !== null ? $daysRevision.' días' : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
                @else
                <p class="text-gray-600">Este documento no tiene versión vigente asignada.</p>
                @endif
            </div>

            {{-- C) HISTÓRICO DE VERSIONES --}}
            <div class="bg-white rounded-2xl shadow border p-6 space-y-3">
                <div class="flex items-center justify-between border-b pb-2">
                    <h3 class="text-lg font-bold">Histórico de Versiones</h3>
                    <p class="text-xs text-gray-500">Solo “vigente” cuenta días. Obsoletos muestran “—”.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left">Versión</th>
                                <th class="p-3 text-left">Fecha versión</th>
                                <th class="p-3 text-left">Vence versión</th>
                                <th class="p-3 text-left">Días</th>
                                <th class="p-3 text-left">Estatus</th>
                                <th class="p-3 text-left">Comentarios</th>
                                <th class="p-3 text-left">Archivo</th>
                                <th class="p-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($versiones as $ver)
                            @php
                            $vv = $fmtDate($ver->fecha_vencimiento_version);
                            $dl = $daysLeftIfVigente($ver->estatus, $vv);

                            $file = $ver->sp_web_url ?: ($ver->liga_archivo ?: null);

                            // Ajusta el nombre de campo si es diferente
                            $coment = $ver->comentarios ?? $ver->observaciones ?? null;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 font-semibold">{{ $ver->version ?? '—' }}</td>
                                <td class="p-3">{{ $ver->fecha_version?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-3">{{ $ver->fecha_vencimiento_version?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-3">
                                    <span class="px-3 py-1 rounded text-xs font-bold {{ $badgeColor($dl) }}">
                                        {{ $dl !== null ? $dl.' días' : '—' }}
                                    </span>
                                </td>
                                <td class="p-3">{{ $ver->estatus ?? '—' }}</td>
                                <td class="p-3 max-w-[360px]">
                                    <span title="{{ $coment ?? '' }}">
                                        {{ \Illuminate\Support\Str::limit($coment ?? '—', 55) }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    @if($file)
                                    <a href="{{ $file }}" target="_blank" class="text-blue-600 underline">Ver</a>
                                    @else
                                    —
                                    @endif
                                </td>
                                <td class="p-3 text-right">
                                    @if($isSgi && ($ver->estatus ?? null) === 'vigente')
                                    <form method="POST"
                                        action="{{ route('documento_versiones.marcar_obsoleto', $ver->id) }}"
                                        onsubmit="return confirm('¿Marcar esta versión como obsoleta? Esto detendrá el conteo de días.');">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold hover:bg-gray-800">
                                            Marcar obsoleto
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="p-3 text-gray-500" colspan="8">Sin versiones registradas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- D) TIMELINE DE REVISIONES (documento_revisiones) --}}
            <div class="bg-white rounded-2xl shadow border p-6 space-y-5">
                <div class="flex items-center justify-between border-b pb-2">
                    <h3 class="text-lg font-bold">Timeline de Revisiones</h3>
                    <p class="text-xs text-gray-500">Agrupado por versión (fuente: documento_revisiones).</p>
                </div>

                @if($revisiones->isEmpty())
                <div class="p-6 rounded-xl border bg-slate-50 text-slate-600">
                    No hay revisiones registradas aún.
                </div>
                @else
                <div class="space-y-6">
                    @foreach($revisionesPorVersion as $labelVersion => $items)
                    <div class="rounded-2xl border bg-white">
                        <div class="px-5 py-4 border-b bg-slate-50 rounded-t-2xl flex items-center justify-between">
                            <div class="font-black text-slate-800">
                                {{ $labelVersion }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $items->count() }} revisión(es)
                            </div>
                        </div>

                        <div class="p-5">
                            <ol class="relative border-s border-slate-200 ms-3 space-y-5">
                                @foreach($items as $rev)
                                @php
                                $vr = $fmtDate($rev->fecha_vencimiento_revision);
                                $dlr = $daysLeftIfVigente($rev->estatus, $vr);

                                $fileR = $rev->liga_archivo ?: null;

                                $comentR = $rev->comentarios ?? $rev->observaciones ?? null;

                                $dot = ($rev->estatus === 'vigente') ? 'bg-emerald-500' : 'bg-slate-300';
                                $badgeE = ($rev->estatus === 'vigente')
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-slate-100 text-slate-600';
                                @endphp

                                <li class="ms-6">
                                    <span class="absolute -start-1.5 mt-2 flex h-3 w-3 rounded-full {{ $dot }}"></span>

                                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $badgeE }}">
                                                    {{ $rev->estatus ?? '—' }}
                                                </span>

                                                <span class="font-bold text-slate-900">
                                                    Rev {{ $rev->revision_actual ?? '—' }}
                                                </span>

                                                @if($rev->revision_anterior)
                                                <span class="text-xs text-slate-500">
                                                    (antes: {{ $rev->revision_anterior }})
                                                </span>
                                                @endif
                                            </div>

                                            <div class="text-sm text-slate-700">
                                                <span class="font-semibold">Fecha:</span>
                                                {{ $rev->fecha_revision?->format('Y-m-d') ?? '—' }}
                                                <span class="mx-2 text-slate-300">|</span>
                                                <span class="font-semibold">Vence:</span>
                                                {{ $rev->fecha_vencimiento_revision?->format('Y-m-d') ?? '—' }}
                                            </div>

                                            <div class="text-sm text-slate-700">
                                                <span class="font-semibold">Días:</span>
                                                <span class="px-3 py-1 rounded text-xs font-bold {{ $badgeColor($dlr) }}">
                                                    {{ $dlr !== null ? $dlr.' días' : '—' }}
                                                </span>
                                            </div>

                                            <div class="text-sm text-slate-700">
                                                <span class="font-semibold">Comentarios:</span>
                                                <span title="{{ $comentR ?? '' }}">
                                                    {{ \Illuminate\Support\Str::limit($comentR ?? '—', 120) }}
                                                </span>
                                            </div>

                                            <div class="text-sm">
                                                <span class="font-semibold text-slate-700">Archivo:</span>
                                                @if($fileR)
                                                <a href="{{ $fileR }}" target="_blank" class="text-blue-600 underline">Ver</a>
                                                @else
                                                <span class="text-slate-400">—</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="shrink-0">
                                            @if($isSgi && ($rev->estatus ?? null) === 'vigente')
                                            <form method="POST"
                                                action="{{ route('documento_revisiones.marcar_obsoleto', $rev->id) }}"
                                                onsubmit="return confirm('¿Marcar esta revisión como obsoleta? Esto detendrá el conteo de días.');">
                                                @csrf
                                                <button type="submit"
                                                    class="px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold hover:bg-gray-800">
                                                    Marcar obsoleto
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- E) Notificar actualización (SGI) --}}
            @if($user && $user->hasRole('administrador_sgi'))
            <form method="POST" action="{{ route('documentos.notificar_actualizacion', $documento->id) }}" class="mb-6">
                @csrf

                <div class="bg-white border rounded-2xl p-5 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h4 class="font-black text-gray-800">
                            Notificar que requiere actualización
                        </h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-xs font-bold text-gray-600 uppercase">Mensaje</label>
                            <input
                                type="text"
                                name="mensaje"
                                required
                                value="{{ old('mensaje', 'Este documento requiere actualización. Favor de generar solicitud de actualización.') }}"
                                class="w-full mt-2 rounded-xl border border-gray-300/50 px-4 py-3 focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-gray-600 uppercase">Enviar a</label>
                            <select
                                name="destino"
                                id="destino_select"
                                class="w-full mt-2 rounded-xl border border-gray-300/50 px-4 py-3 focus:ring-2 focus:ring-amber-500"
                                onchange="toggleUsuarioSelect()">
                                <option value="area">Todo el departamento/área del documento</option>
                                <option value="jefe">Solo jefes del área</option>
                                <option value="usuario">Un usuario específico</option>
                            </select>
                        </div>

                        <div id="usuario_container" class="md:col-span-3 hidden">
                            <label class="text-xs font-bold text-gray-600 uppercase">Seleccionar Usuario</label>
                            <select
                                name="usuario_id"
                                class="w-full mt-2 rounded-xl border border-gray-300/50 px-4 py-3 focus:ring-2 focus:ring-amber-500">
                                <option value="">-- Selecciona un usuario --</option>
                                @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}">
                                    {{ $usuario->name }} - {{ $usuario->email }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="px-6 py-3 rounded-xl bg-amber-600 text-white font-bold hover:bg-amber-700 transition">
                        Enviar correo
                    </button>
                </div>
            </form>

            <script>
                function toggleUsuarioSelect() {
                    const destino = document.getElementById('destino_select').value;
                    const container = document.getElementById('usuario_container');
                    if (destino === 'usuario') container.classList.remove('hidden');
                    else container.classList.add('hidden');
                }
            </script>
            @endif

        </div>
</x-app-layout>