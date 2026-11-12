<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <h2 class="text-3xl font-extrabold text-[#6A2C75] tracking-tight">
                    Documento: {{ $documento->codigo }}
                </h2>
                <p class="mt-1 text-sm font-medium text-gray-500">
                    Histórico de Versiones • Revisiones • Vigencias
                </p>
            </div>

            <div class="flex flex-col items-start gap-3 sm:flex-row md:items-center shrink-0">
                @php
                $badgeDoc = match($documento->semaforo_vencimiento ?? 'sin_fecha') {
                'baja' => 'bg-gray-200 text-gray-700 border-gray-300',
                'vencido' => 'bg-red-600 text-white border-red-700',
                'critico' => 'bg-red-50 text-red-700 border-red-200',
                'alerta' => 'bg-amber-50 text-amber-700 border-amber-200',
                'en_regla' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                default => 'bg-slate-50 text-slate-600 border-slate-200',
                };
                @endphp
                <span class="px-4 py-2 rounded-xl text-sm font-bold border {{ $badgeDoc }} shadow-sm">
                    {{ $documento->etiqueta_vencimiento ?? 'Sin fecha' }}
                </span>

                @if(auth()->user()->hasRole('administrador_sgi'))
                <a href="{{ route('documentos.historial', $documento->id) }}"
                    class="bg-white border border-[#6A2C75] text-[#6A2C75] hover:bg-[#6A2C75]/5 font-semibold py-2 px-5 rounded-xl shadow-sm transition-all duration-300 flex items-center gap-2">
                    <i class="ti ti-history"></i> Historial
                </a>
                @endif

                @if($documento->estatus !== 'baja' && (auth()->user()->hasRole('administrador_sgi') ||
                auth()->user()->hasRole('administrador')))
                <a href="{{ route('documentos.edit', $documento->id) }}"
                    class="bg-[#6A2C75] hover:bg-[#53225c] text-white font-semibold py-2 px-5 rounded-xl shadow-md transition-all duration-300 flex items-center gap-2">
                    <i class="ti ti-edit"></i> Editar Datos Oficiales
                </a>
                @endif
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
    if ($days < 0) return 'bg-red-600 text-white' ; if ($days <=30) return 'bg-red-100 text-red-700' ; if ($days <=60)
        return 'bg-amber-100 text-amber-700' ; return 'bg-cyan-100 text-cyan-700' ; }; // Vigencias actuales
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
        $revisionesPorVersion = $revisionesPorVersion ?? $revisiones->groupBy(fn($r) => $r->version?->version ??
        ('Versión ID '.$r->documento_version_id));
        @endphp

        <div class="px-4 py-10 mx-auto space-y-8 max-w-7xl sm:px-6 lg:px-8">

            {{-- A) Datos del documento --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#BBA4C0]/40 p-6 space-y-4">
                <h3 class="text-xl font-bold text-[#6A2C75] border-b-2 border-[#BBA4C0]/30 pb-2 mb-4">
                    Datos del Documento
                </h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Código</span>
                        <span class="text-lg font-medium text-gray-900">{{ $documento->codigo ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Nombre</span>
                        <span class="font-medium text-gray-900">{{ $documento->nombre ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Tipo</span>
                        <span class="text-gray-900">{{ $documento->tipo_documento ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold tracking-wider text-gray-500 uppercase">EL / PA</span>
                        <span class="text-gray-900">{{ $documento->formato_el_pa ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Área (Depto)</span>
                        <span class="text-gray-900">{{ $documento->area ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold tracking-wider text-gray-500 uppercase">Estatus</span>
                        <span class="inline-flex items-center gap-1.5 mt-1">
                            <span
                                class="w-2 h-2 rounded-full {{ ($documento->estatus == 'vigente') ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                            <span class="text-gray-900 capitalize">{{ $documento->estatus ?? '—' }}</span>
                        </span>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-[#f8f6f9] rounded-xl border border-[#BBA4C0]/20">
                    <span class="block text-xs text-[#6A2C75] uppercase tracking-wider font-bold mb-1">Observaciones
                        Jefe</span>
                    <p class="italic text-gray-700">{{ $solicitud->observaciones_jefe ?? 'Sin observaciones' }}</p>
                </div>
            </div>

            {{-- B) Vigente Actual --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#BBA4C0]/40 p-6 space-y-5">
                <h3 class="text-xl font-bold text-[#6A2C75] border-b-2 border-[#BBA4C0]/30 pb-2">
                    Vigente Actual
                </h3>

                @if($vigente)
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-4">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Versión / Folio</span>
                        <span class="text-lg font-bold text-gray-900">{{ $vigente->version ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Estatus versión</span>
                        <span class="text-gray-900">{{ $vigente->estatus ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Revisión actual</span>
                        <span class="text-gray-900">{{ $vigente->revision_actual ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Revisión anterior</span>
                        <span class="text-gray-900">{{ $vigente->revision_anterior ?? '—' }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 pt-2 md:grid-cols-2">
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Lugar de almacenamiento</span>
                        <span class="text-gray-900">{{ $lugar }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 uppercase">Archivo oficial</span>
                        @if($archivoOficial)
                        <a href="{{ $archivoOficial }}" target="_blank"
                            class="text-[#6A2C75] hover:text-[#BBA4C0] font-medium flex items-center gap-1 mt-1 transition-colors">
                            <i class="text-sm ti ti-external-link"></i> Ver en SharePoint
                        </a>
                        @else
                        <span class="mt-1 text-gray-400">—</span>
                        @endif

                        @if($vigente?->sp_estado === 'pendiente')
                        <span
                            class="mt-1 inline-flex w-fit items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-700">
                            Publicando en SharePoint…
                        </span>
                        @elseif($vigente?->sp_estado === 'error')
                        <span
                            class="mt-1 inline-flex w-fit items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-bold text-red-700"
                            title="{{ $vigente->sp_error }}">
                            Error al publicar en SharePoint
                        </span>
                        @if($user && $user->hasRole('administrador_sgi'))
                        <form action="{{ route('documento_versiones.sharepoint.reintentar', $vigente->id) }}"
                            method="POST" class="mt-1">
                            @csrf
                            <button type="submit" class="text-[11px] font-bold text-[#6A2C75] hover:underline">
                                Reintentar publicación
                            </button>
                        </form>
                        @endif
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-2">
                    <div
                        class="p-5 rounded-xl border border-[#BBA4C0]/30 bg-gradient-to-br from-[#fcfbfe] to-white shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-bold text-[#6A2C75] mb-2">Vigencia de Versión</p>
                                <p class="text-sm text-gray-700"><span class="font-medium">Fecha:</span> {{
                                    $fechaVersion ?? '—' }}</p>
                                <p class="text-sm text-gray-700"><span class="font-medium">Vence:</span> {{ $vencVersion
                                    ?? '—' }}</p>
                                <p class="text-xs text-[#BBA4C0] font-bold mt-2">Vigencia: {{ $vigV }} días</p>
                            </div>
                            <span
                                class="px-3 py-1.5 rounded-lg text-sm font-bold {{ $badgeColor($daysVersion) }} shadow-sm">
                                {{ $daysVersion !== null ? $daysVersion.' días' : '—' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="p-5 rounded-xl border border-[#BBA4C0]/30 bg-gradient-to-br from-[#fcfbfe] to-white shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-bold text-[#6A2C75] mb-2">Vigencia de Revisión</p>
                                <p class="text-sm text-gray-700"><span class="font-medium">Fecha:</span> {{
                                    $fechaRevision ?? '—' }}</p>
                                <p class="text-sm text-gray-700"><span class="font-medium">Vence:</span> {{
                                    $vencRevision ?? '—' }}</p>
                                <p class="text-xs text-[#BBA4C0] font-bold mt-2">Vigencia: {{ $vigR }} días</p>
                            </div>
                            <span
                                class="px-3 py-1.5 rounded-lg text-sm font-bold {{ $badgeColor($daysRevision) }} shadow-sm">
                                {{ $daysRevision !== null ? $daysRevision.' días' : '—' }}
                            </span>
                        </div>
                    </div>
                </div>
                @else
                <div class="p-6 text-center border-2 border-dashed border-[#BBA4C0]/50 rounded-xl bg-gray-50">
                    <p class="font-medium text-gray-500">Este documento no tiene versión vigente asignada.</p>
                </div>
                @endif
            </div>

            {{-- C) HISTÓRICO DE VERSIONES --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#BBA4C0]/40 overflow-hidden space-y-3">
                <div
                    class="p-6 border-b border-[#BBA4C0]/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <h3 class="text-xl font-bold text-[#6A2C75]">Histórico de Versiones</h3>
                    <p class="px-3 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full">Solo “vigente”
                        cuenta días. Obsoletos muestran “—”.</p>
                </div>

                <div class="pb-4 overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-[#BBA4C0]/10 text-[#6A2C75]">
                            <tr>
                                <th class="p-4 font-bold">Versión</th>
                                <th class="p-4 font-bold">Fecha versión</th>
                                <th class="p-4 font-bold">Vence versión</th>
                                <th class="p-4 font-bold">Días</th>
                                <th class="p-4 font-bold">Estatus</th>
                                <th class="p-4 font-bold">Comentarios</th>
                                <th class="p-4 font-bold text-center">Archivo</th>
                                <th class="p-4 font-bold text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($versiones as $ver)
                            @php
                            $vv = $fmtDate($ver->fecha_vencimiento_version);
                            $dl = $daysLeftIfVigente($ver->estatus, $vv);
                            $file = $ver->sp_web_url ?: ($ver->liga_archivo ?: null);
                            $coment = $ver->comentarios ?? $ver->observaciones ?? null;
                            @endphp
                            <tr class="transition-colors hover:bg-gray-50/50">
                                <td class="p-4 font-bold text-gray-900">{{ $ver->version ?? '—' }}</td>
                                <td class="p-4 text-gray-600">{{ $ver->fecha_version?->format('Y-m-d') ?? '—' }}</td>
                                <td class="p-4 text-gray-600">{{ $ver->fecha_vencimiento_version?->format('Y-m-d') ??
                                    '—' }}</td>
                                <td class="p-4">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $badgeColor($dl) }}">
                                        {{ $dl !== null ? $dl.' días' : '—' }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 text-gray-700 capitalize">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ ($ver->estatus == 'vigente') ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ $ver->estatus ?? '—' }}
                                    </span>
                                </td>
                                <td class="p-4 max-w-[280px] text-gray-600 truncate" title="{{ $coment ?? '' }}">
                                    {{ \Illuminate\Support\Str::limit($coment ?? '—', 45) }}
                                </td>
                                <td class="p-4 text-center">
                                    @if($file)
                                    <a href="{{ $file }}" target="_blank"
                                        class="text-[#6A2C75] hover:text-[#BBA4C0] bg-[#6A2C75]/10 hover:bg-[#BBA4C0]/20 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        Ver
                                    </a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif

                                    @if($ver->sp_estado === 'pendiente')
                                    <span class="mt-1 block text-[10px] font-bold text-amber-600">Publicando…</span>
                                    @elseif($ver->sp_estado === 'error')
                                    <span class="mt-1 block text-[10px] font-bold text-red-600"
                                        title="{{ $ver->sp_error }}">Error SharePoint</span>
                                    @if($user && $user->hasRole('administrador_sgi'))
                                    <form action="{{ route('documento_versiones.sharepoint.reintentar', $ver->id) }}"
                                        method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="text-[10px] font-bold text-[#6A2C75] hover:underline">Reintentar</button>
                                    </form>
                                    @endif
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    @if($isSgi && ($ver->estatus ?? null) === 'vigente')
                                    <form method="POST"
                                        action="{{ route('documento_versiones.marcar_obsoleto', $ver->id) }}"
                                        onsubmit="return confirm('¿Marcar esta versión como obsoleta? Esto detendrá el conteo de días.');">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-100 hover:text-red-600 transition-colors shadow-sm">
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
                                <td class="p-6 text-center text-gray-500" colspan="8">Sin versiones registradas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- D) TIMELINE DE REVISIONES (documento_revisiones) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-[#BBA4C0]/40 p-6 space-y-5">
                <div class="flex items-center justify-between border-b-2 border-[#BBA4C0]/30 pb-2">
                    <h3 class="text-xl font-bold text-[#6A2C75]">Línea de Tiempo de Revisiones</h3>
                    <p class="text-xs font-medium text-gray-500">Agrupado por versión</p>
                </div>

                @if($revisiones->isEmpty())
                <div
                    class="p-8 rounded-xl border-2 border-dashed border-[#BBA4C0]/50 bg-gray-50 text-center text-gray-500 font-medium">
                    No hay revisiones registradas aún.
                </div>
                @else
                <div class="mt-6 space-y-8">
                    @foreach($revisionesPorVersion as $labelVersion => $items)
                    <div class="rounded-2xl border border-[#BBA4C0]/30 bg-white overflow-hidden shadow-sm">
                        <div
                            class="px-5 py-4 border-b border-[#BBA4C0]/30 bg-[#f8f6f9] flex items-center justify-between">
                            <div class="font-extrabold text-[#6A2C75] text-lg">
                                <i class="ti ti-stack-2 mr-2 text-[#BBA4C0]"></i> {{ $labelVersion }}
                            </div>
                            <div
                                class="px-3 py-1 text-xs font-bold text-gray-500 bg-white border border-gray-200 rounded-full">
                                {{ $items->count() }} revisión(es)
                            </div>
                        </div>

                        <div class="p-6">
                            <ol class="relative border-s-2 border-[#BBA4C0]/40 ms-3 space-y-8">
                                @foreach($items as $rev)
                                @php
                                $vr = $fmtDate($rev->fecha_vencimiento_revision);
                                $dlr = $daysLeftIfVigente($rev->estatus, $vr);
                                $fileR = $rev->liga_archivo ?: null;
                                $comentR = $rev->comentarios ?? $rev->observaciones ?? null;

                                // Colores según estatus
                                $isVigente = ($rev->estatus === 'vigente');
                                $dotColor = $isVigente ? 'bg-[#6A2C75] border-white' : 'bg-[#BBA4C0] border-white';
                                $badgeE = $isVigente ? 'bg-emerald-100 text-emerald-800 border-emerald-200' :
                                'bg-gray-100 text-gray-600 border-gray-200';
                                @endphp

                                <li class="ms-8">
                                    <span
                                        class="absolute -start-[9px] mt-1.5 flex h-4 w-4 items-center justify-center rounded-full border-2 {{ $dotColor }} shadow-sm"></span>

                                    <div
                                        class="flex flex-col justify-between gap-4 p-4 border border-gray-100 lg:flex-row lg:items-start bg-gray-50/50 rounded-xl">
                                        <div class="flex-1 space-y-3">
                                            <div class="flex flex-wrap items-center gap-3">
                                                <span
                                                    class="px-3 py-1 rounded-lg text-xs font-bold border {{ $badgeE }}">
                                                    {{ $rev->estatus ?? '—' }}
                                                </span>
                                                <span class="text-lg font-black text-gray-900">
                                                    Rev {{ $rev->revision_actual ?? '—' }}
                                                </span>
                                                @if($rev->revision_anterior)
                                                <span class="text-xs font-medium text-gray-500">
                                                    (Anterior: {{ $rev->revision_anterior }})
                                                </span>
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-1 gap-2 text-sm text-gray-700 sm:grid-cols-3">
                                                <div><span class="font-bold text-[#6A2C75]">Fecha:</span> {{
                                                    $rev->fecha_revision?->format('Y-m-d') ?? '—' }}</div>
                                                <div><span class="font-bold text-[#6A2C75]">Vence:</span> {{
                                                    $rev->fecha_vencimiento_revision?->format('Y-m-d') ?? '—' }}</div>
                                                <div>
                                                    <span class="font-bold text-[#6A2C75]">Días:</span>
                                                    <span
                                                        class="px-2 py-0.5 rounded text-xs font-bold {{ $badgeColor($dlr) }}">
                                                        {{ $dlr !== null ? $dlr.' días' : '—' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div
                                                class="p-3 text-sm bg-white border border-gray-100 rounded-lg shadow-sm">
                                                <span class="font-bold text-[#6A2C75] block mb-1">Comentarios:</span>
                                                <span class="italic text-gray-600" title="{{ $comentR ?? '' }}">
                                                    {{ $comentR ? \Illuminate\Support\Str::limit($comentR, 150) : 'Sin
                                                    comentarios.' }}
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2 text-sm">
                                                <span class="font-bold text-[#6A2C75]">Archivo:</span>
                                                @if($fileR)
                                                <a href="{{ $fileR }}" target="_blank"
                                                    class="text-sm text-[#6A2C75] hover:text-[#BBA4C0] font-semibold underline decoration-2 underline-offset-2 transition-colors">
                                                    Visualizar Documento
                                                </a>
                                                @else
                                                <span class="text-gray-400">—</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-2 shrink-0 lg:mt-0">
                                            @if($isSgi && $isVigente)
                                            <form method="POST"
                                                action="{{ route('documento_revisiones.marcar_obsoleto', $rev->id) }}"
                                                onsubmit="return confirm('¿Marcar esta revisión como obsoleta? Esto detendrá el conteo de días.');">
                                                @csrf
                                                <button type="submit"
                                                    class="w-full px-4 py-2 text-xs font-bold text-white transition-colors bg-gray-800 shadow-md lg:w-auto rounded-xl hover:bg-gray-900">
                                                    <i class="mr-1 ti ti-ban"></i> Marcar obsoleto
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
                <div class="bg-gradient-to-r from-[#6A2C75] to-[#8c4098] rounded-2xl p-1 shadow-lg">
                    <div class="p-6 space-y-5 bg-white rounded-xl">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="bg-[#BBA4C0]/20 p-2 rounded-lg text-[#6A2C75]">
                                <i class="text-xl ti ti-bell"></i>
                            </div>
                            <h4 class="text-lg font-black text-gray-900">
                                Notificar que requiere actualización
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                            <div class="md:col-span-2">
                                <label class="text-xs font-bold text-[#6A2C75] uppercase tracking-wider">Mensaje</label>
                                <input type="text" name="mensaje" required
                                    value="{{ old('mensaje', 'Este documento requiere actualización. Favor de generar solicitud de actualización.') }}"
                                    class="w-full mt-2 rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-[#BBA4C0] focus:border-[#6A2C75] transition-all shadow-sm">
                            </div>

                            <div>
                                <label class="text-xs font-bold text-[#6A2C75] uppercase tracking-wider">Enviar
                                    a</label>
                                <select name="destino" id="destino_select"
                                    class="w-full mt-2 rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-[#BBA4C0] focus:border-[#6A2C75] transition-all shadow-sm"
                                    onchange="toggleUsuarioSelect()">
                                    <option value="area">Todo el departamento/área</option>
                                    <option value="jefe">Solo jefes del área</option>
                                    <option value="usuario">Un usuario específico</option>
                                </select>
                            </div>

                            <div id="usuario_container"
                                class="md:col-span-3 hidden bg-[#f8f6f9] p-4 rounded-xl border border-[#BBA4C0]/30 mt-2">
                                <label class="text-xs font-bold text-[#6A2C75] uppercase tracking-wider">Seleccionar
                                    Usuario</label>
                                <select name="usuario_id"
                                    class="w-full mt-2 rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-[#BBA4C0] focus:border-[#6A2C75] shadow-sm bg-white">
                                    <option value="">-- Selecciona un usuario --</option>
                                    @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}">
                                        {{ $usuario->name }} - {{ $usuario->email }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                class="px-8 py-3 rounded-xl bg-[#6A2C75] text-white font-bold hover:bg-[#53225c] transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                                <i class="ti ti-send"></i> Enviar correo
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <script>
                function toggleUsuarioSelect() {
                const destino = document.getElementById('destino_select').value;
                const container = document.getElementById('usuario_container');
                if (destino === 'usuario') {
                    container.classList.remove('hidden');
                    container.classList.add('block');
                } else {
                    container.classList.add('hidden');
                    container.classList.remove('block');
                }
            }
            </script>
            @endif

        </div>
</x-app-layout>