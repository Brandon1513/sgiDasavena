<x-app-layout>

@php
    $user = auth()->user();

    $kpis = [
        ['label' => 'Pendientes',    'val' => $resumen['pendientes'],    'icon' => 'ti-hourglass-high',         'desc' => 'Esperando revisión del jefe',      'theme' => 'purple', 'href' => route('solicitudes.index', ['estado' => 'pendiente'])],
        ['label' => 'Aprobado jefe', 'val' => $resumen['aprobado_jefe'], 'icon' => 'ti-rosette-discount-check', 'desc' => 'En proceso de gestión SGI',        'theme' => 'gold',   'href' => route('solicitudes.index', ['estado' => 'aprobado_jefe'])],
        ['label' => 'Atendidas',     'val' => $resumen['atendidas'],     'icon' => 'ti-circle-check',           'desc' => 'Formato actualizado con éxito',    'theme' => 'green',  'href' => route('solicitudes.index', ['estado' => 'atendido'])],
        ['label' => 'Rechazadas',    'val' => $resumen['rechazadas'],    'icon' => 'ti-circle-x',               'desc' => 'Requieren revisión y reenvío',     'theme' => 'rose',   'href' => null],
        ['label' => 'Fuera de SLA',  'val' => $resumen['vencidas_sla'],  'icon' => 'ti-alarm',                  'desc' => 'Pendientes con más de 3 días',     'theme' => 'amber',  'href' => route('solicitudes.index', ['estado' => 'pendiente'])],
    ];

    $accionLabels = [
        'actualizacion'   => ['label' => 'Actualización', 'icon' => 'ti-refresh',   'class' => 'bg-dasavena-purple/10 text-dasavena-purple'],
        'baja'            => ['label' => 'Baja',          'icon' => 'ti-trash',     'class' => 'bg-rose-50 text-rose-600'],
        'nuevo_documento' => ['label' => 'Alta nueva',    'icon' => 'ti-file-plus', 'class' => 'bg-dasavena-gold/15 text-dasavena-gold-dark'],
    ];

    $estadoBadges = [
        'pendiente'      => ['label' => 'Pendiente',          'class' => 'bg-dasavena-purple/10 border-dasavena-purple/20 text-dasavena-purple'],
        'aprobado_jefe'  => ['label' => 'Aprobado jefe',      'class' => 'bg-dasavena-gold/20 border-dasavena-gold/30 text-dasavena-gold-dark'],
        'evaluando_sgi'  => ['label' => 'En revisión SGI',    'class' => 'bg-indigo-50 border-indigo-200 text-indigo-700'],
        'atendido'       => ['label' => 'Atendida',           'class' => 'bg-emerald-50 border-emerald-200 text-emerald-700'],
        'rechazado_jefe' => ['label' => 'Rechazada por jefe', 'class' => 'bg-rose-50 border-rose-200 text-rose-700'],
        'rechazado_sgi'  => ['label' => 'Rechazada por SGI',  'class' => 'bg-rose-50 border-rose-200 text-rose-700'],
    ];

    $nivelActual = [
        'pendiente'      => 'Jefatura de área',
        'aprobado_jefe'  => 'Validación SGI',
        'evaluando_sgi'  => 'Validación SGI',
        'atendido'       => 'Archivo central',
        'rechazado_jefe' => 'Revisión del solicitante',
        'rechazado_sgi'  => 'Revisión del solicitante',
    ];

    // Avance en el flujo: 1 = con el jefe, 2 = con SGI, 3 = atendida
    $pasoFlujo = [
        'pendiente'      => 1,
        'aprobado_jefe'  => 2,
        'evaluando_sgi'  => 2,
        'atendido'       => 3,
        'rechazado_jefe' => 1,
        'rechazado_sgi'  => 2,
    ];

    $filtrosActivos = collect(request()->only(['nombre', 'estado', 'accion', 'desde', 'hasta']))->filter()->count();

    $inputClass = 'w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 placeholder:text-gray-400 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20';
@endphp

{{-- ─── PANTALLA DE CARGA ─── --}}
<div id="sgi-loading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-dasavena-purple-dark via-dasavena-purple to-dasavena-purple-light transition-opacity duration-500">
    <div class="flex flex-col items-center gap-4">
        <div class="relative w-20 h-20">
            <span class="absolute -inset-1.5 rounded-full border-2 border-transparent border-t-dasavena-gold border-r-dasavena-gold animate-spin"></span>
            <img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="Dasavena" class="w-20 h-20 object-contain animate-pulse">
        </div>
        <p class="text-white font-display font-bold tracking-wide">Sistema SGI</p>
        <p class="text-dasavena-gold font-mono text-[11px] tracking-wider">// cargando_solicitudes</p>
    </div>
</div>

{{-- ─── FONDO + LUCES AMBIENTALES ─── --}}
<div class="relative min-h-screen bg-cover bg-center bg-fixed" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">
    <div class="pointer-events-none fixed inset-0 z-[1] bg-[radial-gradient(ellipse_90%_55%_at_50%_-5%,rgba(255,255,255,.55)_0%,transparent_65%)]"></div>
    <div class="pointer-events-none fixed -left-20 -top-24 z-[1] h-[420px] w-[420px] rounded-full bg-dasavena-purple/10 blur-3xl"></div>
    <div class="pointer-events-none fixed -right-24 top-[22%] z-[1] h-[380px] w-[380px] rounded-full bg-dasavena-gold/10 blur-3xl"></div>
    <div class="pointer-events-none fixed bottom-[-10%] left-[18%] z-[1] h-[320px] w-[500px] rounded-full bg-dasavena-purple-light/10 blur-3xl"></div>

    <div class="relative z-10 mx-auto flex max-w-7xl flex-col gap-7 px-4 py-10 sm:px-6 lg:px-8">

        {{-- ══════════════════════════════ HERO ══════════════════════════════ --}}
        <header id="hero" class="reveal relative overflow-hidden rounded-[28px] border border-white/15 bg-gradient-to-br from-dasavena-purple-dark via-dasavena-purple to-[#250a2c] p-7 shadow-[0_12px_40px_rgba(74,30,82,0.3),inset_0_1px_2px_rgba(255,255,255,0.25)] sm:p-9">
            <div class="absolute inset-x-0 top-0 h-[2px] gold-gleam"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[.06]" style="background-image:radial-gradient(rgba(255,255,255,.9) 1.5px, transparent 1.5px); background-size:22px 22px;"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-20 h-80 w-80 rounded-full bg-dasavena-gold/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -top-20 right-1/3 h-72 w-72 rounded-full bg-dasavena-purple-light/20 blur-3xl"></div>
            <div class="hero-shine pointer-events-none absolute inset-0" aria-hidden="true"></div>

            <div class="relative z-[1] flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
                <div class="flex max-w-2xl flex-col gap-4">
                    <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[.18em] text-dasavena-gold shadow-[inset_0_1px_1px_rgba(255,255,255,0.3)] backdrop-blur-2xl">
                        <span class="h-[7px] w-[7px] rounded-full bg-dasavena-gold shadow-[0_0_8px_#D6A644] animate-pulse"></span>
                        Bandeja de solicitudes
                    </div>

                    <h1 class="font-display text-[clamp(1.8rem,3.6vw,2.4rem)] font-extrabold leading-[1.1] tracking-tight text-white/90">
                        Solicitudes de formatos
                    </h1>
                    <p class="max-w-xl text-sm leading-relaxed text-white/70">
                        Da seguimiento a tus altas, actualizaciones y bajas de documentos: quién la revisa, en qué etapa está y cuánto tiempo lleva.
                    </p>
                </div>

                <div class="flex flex-col items-start gap-5 self-stretch lg:items-end lg:self-auto">
                    <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-black/25 px-4 py-2.5 text-white shadow-[inset_0_1px_1px_rgba(255,255,255,0.15)] backdrop-blur-2xl">
                        <span class="font-mono text-2xl font-extrabold text-dasavena-gold" data-count="{{ $resumen['total'] }}">0</span>
                        <div class="flex flex-col leading-tight">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-white/50">Total</span>
                            <span class="text-[12px] font-medium text-white/80">solicitudes visibles</span>
                        </div>
                    </div>

                    <div class="flex w-full items-center gap-3 sm:w-auto">
                        <a href="{{ route('solicitudes.create') }}"
                           class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-dasavena-gold px-5 py-2.5 text-[13px] font-bold text-dasavena-purple-dark shadow-[0_6px_20px_rgba(214,166,68,0.35),inset_0_1px_1px_rgba(255,255,255,0.6)] transition-all hover:bg-dasavena-gold/90 active:scale-95 sm:flex-initial">
                            <i class="ti ti-plus text-[16px]"></i> Nueva solicitud
                        </a>
                        @if ($user->hasRole('administrador_sgi'))
                        <a href="{{ route('solicitudes.calendar') }}"
                           class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-white/30 bg-white/15 px-5 py-2.5 text-[13px] font-medium text-white shadow-[0_4px_16px_rgba(0,0,0,0.1),inset_0_1px_1px_rgba(255,255,255,0.25)] backdrop-blur-2xl transition-all hover:bg-white/25 active:scale-95 sm:flex-initial">
                            <i class="ti ti-calendar-event text-[16px]"></i> Calendario
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        @if (session('success'))
        <div class="reveal flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-800 shadow-sm backdrop-blur-xl">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-200 text-emerald-700"><i class="ti ti-check text-[15px]"></i></span>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
        @endif

        {{-- ══════════════════════════════ KPI ══════════════════════════════ --}}
        <section class="reveal grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5" aria-label="Resumen de solicitudes">
            @foreach($kpis as $k)
            <x-dashboard.kpi-widget :icon="$k['icon']" :value="$k['val']" :label="$k['label']" :desc="$k['desc']" :theme="$k['theme']" :href="$k['href']" />
            @endforeach
        </section>

        {{-- ══════════════════════════════ FILTROS ══════════════════════════════ --}}
        <x-dashboard.panel class="reveal p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <i class="ti ti-adjustments-horizontal text-[20px] text-dasavena-purple"></i>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Filtros de búsqueda</span>
                    @if($filtrosActivos > 0)
                    <span class="rounded-full border border-dasavena-purple/20 bg-dasavena-purple/10 px-2.5 py-0.5 text-[11px] font-semibold text-dasavena-purple">{{ $filtrosActivos }} activo{{ $filtrosActivos === 1 ? '' : 's' }}</span>
                    @endif
                </div>
                @if($filtrosActivos > 0)
                <a href="{{ route('solicitudes.index') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-gray-400 transition-colors hover:text-dasavena-purple">
                    <i class="ti ti-x text-[14px]"></i> Limpiar
                </a>
                @endif
            </div>

            <form method="GET">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
                    <div class="space-y-1.5 lg:col-span-2">
                        <label for="f-nombre" class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Buscar</label>
                        <div class="relative">
                            <i class="ti ti-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[16px] text-gray-400"></i>
                            <input id="f-nombre" type="text" name="nombre" value="{{ request('nombre') }}" placeholder="Solicitante, documento o folio…"
                                class="{{ $inputClass }} pl-9">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="f-estado" class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Estado</label>
                        <select id="f-estado" name="estado" class="{{ $inputClass }} cursor-pointer">
                            <option value="">Todos</option>
                            @foreach (['pendiente','aprobado_jefe','rechazado_jefe','atendido','rechazado_sgi'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado')===$estado ? 'selected' : '' }}>
                                {{ $estadoBadges[$estado]['label'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="f-accion" class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Tipo</label>
                        <select id="f-accion" name="accion" class="{{ $inputClass }} cursor-pointer">
                            <option value="">Todos</option>
                            @foreach ($accionLabels as $valor => $info)
                            <option value="{{ $valor }}" {{ request('accion')===$valor ? 'selected' : '' }}>{{ $info['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="f-desde" class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Desde</label>
                        <input id="f-desde" type="date" name="desde" value="{{ request('desde') }}" class="{{ $inputClass }}">
                    </div>

                    <div class="space-y-1.5">
                        <label for="f-hasta" class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Hasta</label>
                        <input id="f-hasta" type="date" name="hasta" value="{{ request('hasta') }}" class="{{ $inputClass }}">
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-dasavena-gold px-6 py-2 text-sm font-bold text-dasavena-purple-dark shadow-[0_3px_10px_rgba(214,166,68,0.3)] transition-all hover:-translate-y-0.5 hover:shadow-[0_5px_16px_rgba(214,166,68,0.4)] active:scale-95">
                        <i class="ti ti-filter text-[16px]"></i> Filtrar
                    </button>
                </div>
            </form>
        </x-dashboard.panel>

        {{-- ══════════════════════════════ TABLA ══════════════════════════════ --}}
        <x-dashboard.panel class="reveal flex flex-col gap-5 p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="font-display text-lg font-bold text-indigo-950">Listado de solicitudes</h2>
                    <p class="text-xs text-gray-400">Ordenadas de la más reciente a la más antigua</p>
                </div>
                @if ($solicitudes->isNotEmpty())
                <button type="button" id="export-csv" class="inline-flex w-fit items-center gap-1.5 rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-[12px] font-semibold text-gray-600 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-colors hover:text-gray-900">
                    <i class="ti ti-file-export text-[16px]"></i> Exportar página (CSV)
                </button>
                @endif
            </div>

            @if ($solicitudes->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-gray-50 text-gray-300">
                    <i class="ti ti-inbox text-[28px]"></i>
                </span>
                <p class="text-sm font-semibold text-indigo-950">No hay solicitudes para mostrar</p>
                <p class="mt-1 text-xs text-gray-400">Intenta con otros filtros o crea una nueva solicitud</p>
            </div>
            @else
            <div class="w-full overflow-x-auto rounded-2xl border border-white/80">
                <table class="w-full border-collapse bg-white/60 text-left">
                    <thead class="border-b border-black/[0.04] bg-white/40 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                        <tr>
                            <th class="px-4 py-3.5">Folio</th>
                            <th class="px-4 py-3.5">Documento</th>
                            <th class="px-4 py-3.5">Solicitante</th>
                            <th class="px-4 py-3.5">Jefe revisor</th>
                            <th class="px-4 py-3.5">Estado</th>
                            <th class="px-4 py-3.5">Registro</th>
                            <th class="px-4 py-3.5">Comentarios</th>
                            <th class="px-4 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] text-[13px] text-gray-700">
                        @foreach ($solicitudes as $solicitud)
                        @php
                            $accionInfo = $accionLabels[$solicitud->accion] ?? ['label' => ucfirst(str_replace('_', ' ', $solicitud->accion)), 'icon' => 'ti-file', 'class' => 'bg-gray-50 text-gray-500'];
                            $badge = $estadoBadges[$solicitud->estado] ?? ['label' => ucfirst(str_replace('_', ' ', $solicitud->estado)), 'class' => 'bg-gray-50 border-gray-200 text-gray-600'];
                            $esRechazada = in_array($solicitud->estado, ['rechazado_jefe', 'rechazado_sgi']);
                            $paso = $pasoFlujo[$solicitud->estado] ?? 0;

                            $folio = 'SOL-' . str_pad($solicitud->id, 5, '0', STR_PAD_LEFT);
                            $nombreDoc = $solicitud->nombre_documento ?: optional($solicitud->documento)->nombre;
                            $codigoDoc = $solicitud->codigo_documento ?: optional($solicitud->documento)->codigo;
                            $tipoDoc = $solicitud->tipo_documento ?: optional($solicitud->documento)->tipo_documento;

                            $diasAbierta = (int) $solicitud->created_at->copy()->startOfDay()->diffInDays(now()->startOfDay());
                            $esVencida = $solicitud->estado === 'pendiente' && $solicitud->created_at <= now()->subDays(3);

                            $antiguedad = match (true) {
                                $diasAbierta === 0 => 'Hoy',
                                $diasAbierta === 1 => 'Hace 1 día',
                                default            => "Hace {$diasAbierta} días",
                            };

                            // Para las atendidas, mostrar cuánto tardó el flujo completo.
                            $duracion = null;
                            if ($solicitud->estado === 'atendido' && $solicitud->atendido_at) {
                                $horas = $solicitud->created_at->diffInHours($solicitud->atendido_at);
                                $duracion = $horas >= 24 ? number_format($horas / 24, 1) . ' d' : number_format($horas, 0) . ' h';
                            }
                        @endphp
                        <tr class="row-solicitud transition-colors hover:bg-white/70">
                            <td class="whitespace-nowrap px-4 py-4">
                                <span class="font-mono text-[12px] font-bold {{ $esVencida ? 'text-rose-600' : 'text-dasavena-purple' }}">
                                    @if($esVencida)<i class="ti ti-alarm mr-0.5 text-[14px]" title="Fuera de SLA"></i>@endif{{ $folio }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $accionInfo['class'] }}">
                                        <i class="ti {{ $accionInfo['icon'] }} text-[17px]"></i>
                                    </div>
                                    <div class="flex min-w-0 max-w-[260px] flex-col">
                                        <span class="truncate font-medium text-indigo-950" title="{{ $nombreDoc }}">{{ $nombreDoc ?: 'Documento nuevo' }}</span>
                                        <span class="truncate text-[11px] text-gray-400">
                                            <span class="font-semibold">{{ $accionInfo['label'] }}</span>
                                            @if($codigoDoc) · <span class="font-mono">{{ $codigoDoc }}</span>@endif
                                            @if($tipoDoc) · {{ $tipoDoc }}@endif
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-dasavena-purple to-dasavena-gold text-[10px] font-bold text-white">
                                        {{ strtoupper(mb_substr(optional($solicitud->usuario)->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="whitespace-nowrap text-[13px] font-semibold text-indigo-950">{{ optional($solicitud->usuario)->name ?? 'Sin usuario' }}</span>
                                        @if(optional($solicitud->usuario)->area)<span class="text-[11px] text-gray-400">{{ $solicitud->usuario->area }}</span>@endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                @if($solicitud->jefe)
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-[10px] font-bold text-indigo-700">
                                        {{ strtoupper(mb_substr($solicitud->jefe->name, 0, 1)) }}
                                    </div>
                                    <span class="whitespace-nowrap text-[12.5px] text-gray-600">{{ $solicitud->jefe->name }}</span>
                                </div>
                                @else
                                <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <span class="inline-flex w-fit items-center gap-1.5 whitespace-nowrap rounded-full border px-3 py-1 text-[11px] font-semibold {{ $badge['class'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $badge['label'] }}
                                    </span>
                                    {{-- Avance: jefe → SGI → atendida --}}
                                    <div class="flex w-24 gap-1" title="{{ $nivelActual[$solicitud->estado] ?? '' }}">
                                        @for ($i = 1; $i <= 3; $i++)
                                        <span class="h-1 flex-1 rounded-full {{ $i < $paso || $solicitud->estado === 'atendido' ? 'bg-gradient-to-r from-dasavena-purple to-dasavena-gold' : ($i === $paso ? ($esRechazada ? 'bg-rose-400' : 'bg-dasavena-purple/40') : 'bg-gray-200') }}"></span>
                                        @endfor
                                    </div>
                                    <span class="text-[11px] text-gray-400">{{ $nivelActual[$solicitud->estado] ?? '—' }}</span>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="text-[12.5px] text-gray-600">{{ $solicitud->created_at->format('d/m/Y H:i') }}</span>
                                    @if($duracion)
                                    <span class="text-[11px] font-semibold text-emerald-600"><i class="ti ti-clock-check text-[12px]"></i> Resuelta en {{ $duracion }}</span>
                                    @elseif(in_array($solicitud->estado, ['pendiente', 'aprobado_jefe']))
                                    <span class="text-[11px] {{ $esVencida ? 'font-semibold text-rose-600' : 'text-gray-400' }}">{{ $antiguedad }}</span>
                                    @else
                                    <span class="text-[11px] text-gray-400">{{ $antiguedad }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="max-w-[200px] px-4 py-4">
                                @if($solicitud->comentarios)
                                <span class="block truncate text-[12.5px] text-gray-500" title="{{ $solicitud->comentarios }}">
                                    {{ Str::limit($solicitud->comentarios, 40) }}
                                </span>
                                @else
                                <span class="text-xs text-gray-300">—</span>
                                @endif
                                @if($esRechazada && ($solicitud->observaciones_jefe || $solicitud->observaciones_sgi))
                                <span class="mt-1 flex items-center gap-1 truncate text-[11px] text-rose-600" title="{{ $solicitud->observaciones_sgi ?: $solicitud->observaciones_jefe }}">
                                    <i class="ti ti-message-report text-[12px]"></i> {{ Str::limit($solicitud->observaciones_sgi ?: $solicitud->observaciones_jefe, 30) }}
                                </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-right">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">

                                    @if (auth()->user()->hasRole('jefe') && $solicitud->estado === 'pendiente' && Route::has('solicitudes.approval_form'))
                                    <a href="{{ route('solicitudes.approval_form', $solicitud->id) }}"
                                        class="inline-flex items-center gap-1 whitespace-nowrap rounded-xl bg-emerald-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-[0_2px_8px_rgba(5,150,105,0.3)] transition-all hover:opacity-95 active:scale-95">
                                        <i class="ti ti-circle-check text-[14px]"></i> Revisar
                                    </a>
                                    @endif

                                    @if (auth()->user()->hasRole('administrador_sgi') && $solicitud->estado === 'aprobado_jefe' && Route::has('solicitudes.finalize_form'))
                                    <a href="{{ route('solicitudes.finalize_form', $solicitud->id) }}"
                                        class="inline-flex items-center gap-1 whitespace-nowrap rounded-xl bg-dasavena-purple px-3 py-1.5 text-[11px] font-semibold text-white shadow-[0_2px_8px_rgba(106,44,117,0.3)] transition-all hover:opacity-95 active:scale-95">
                                        <i class="ti ti-check text-[14px]"></i> Finalizar
                                    </a>
                                    @endif

                                    @if (Route::has('solicitudes.show'))
                                    <a href="{{ route('solicitudes.show', $solicitud->id) }}"
                                        class="inline-flex items-center gap-1 whitespace-nowrap rounded-xl border border-white/80 bg-white/80 px-3 py-1.5 text-[11px] font-semibold text-gray-700 shadow-sm transition-all hover:bg-white active:scale-95">
                                        <i class="ti ti-eye text-[14px]"></i> Ver
                                    </a>
                                    @endif

                                    @if($solicitud->accion === 'actualizacion' && $solicitud->documento_id)
                                    <a href="{{ route('solicitudes.solicitar_actualizacion.form', ['documento' => $solicitud->documento_id]) }}"
                                        title="Solicitar una nueva actualización de este documento"
                                        class="inline-flex items-center gap-1 whitespace-nowrap rounded-xl border border-dasavena-gold/25 bg-dasavena-gold/10 px-3 py-1.5 text-[11px] font-semibold text-dasavena-gold-dark transition-colors hover:bg-dasavena-gold/20">
                                        <i class="ti ti-refresh text-[14px]"></i> Actualizar
                                    </a>
                                    @endif

                                    {{-- LÓGICA DE ELIMINACIÓN: Solo si no ha sido aprobada/rechazada por SGI --}}
                                    @if (Route::has('solicitudes.destroy') && !in_array($solicitud->estado, ['atendido', 'rechazado_sgi']))
                                    <form action="{{ route('solicitudes.destroy', $solicitud->id) }}" method="POST" class="inline-flex"
                                        onsubmit="return confirm('¿Está seguro de que desea eliminar esta solicitud?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar solicitud" aria-label="Eliminar solicitud {{ $folio }}"
                                          class="inline-flex h-[30px] w-[30px] items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition-colors hover:bg-rose-100">
                                          <i class="ti ti-trash text-[15px]"></i>
                                        </button>
                                    </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col items-center justify-between gap-3 pt-1 sm:flex-row">
                <span class="text-[12.5px] text-gray-400">
                    Mostrando <strong class="font-semibold text-gray-700">{{ $solicitudes->firstItem() }}–{{ $solicitudes->lastItem() }}</strong> de <strong class="font-semibold text-gray-700">{{ $solicitudes->total() }}</strong> resultados
                </span>
                <div class="pagination-glass">
                    {{ $solicitudes->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </x-dashboard.panel>

    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">

<style>
:root {
    --purple: #6A2C75;
    --gold: #D6A644;
}
.reveal { opacity: 0; transform: translateY(24px); transition: opacity .6s cubic-bezier(.22,1,.36,1), transform .6s cubic-bezier(.22,1,.36,1); }
.reveal.is-visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) { .reveal { opacity: 1 !important; transform: none !important; transition: none !important; } }

/* ── Firma dorada del hero (igual que el dashboard) ── */
.gold-gleam {
    background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, rgba(255,255,255,.8) 50%, var(--gold) 70%, transparent 100%);
    background-size: 200% 100%;
    animation: gold-shimmer 3.5s ease-in-out infinite;
}
@keyframes gold-shimmer { 0%{background-position:100% 0} 100%{background-position:-100% 0} }

.hero-shine {
    opacity: 0;
    transition: opacity .35s ease;
    background: radial-gradient(260px 220px at var(--hx, 50%) var(--hy, 0%), rgba(255,255,255,.22), rgba(255,255,255,.05) 45%, transparent 70%);
}
#hero:hover .hero-shine { opacity: 1; }
@media (prefers-reduced-motion: reduce) { .hero-shine { display: none; } }

.pagination-glass nav span[aria-current="page"] > span,
.pagination-glass nav a:hover {
    background-color: var(--purple) !important;
    border-color: var(--purple) !important;
    color: #fff !important;
}
.pagination-glass nav a, .pagination-glass nav span {
    color: var(--purple) !important;
    border-radius: 10px !important;
}
</style>

<script>
window.addEventListener('load', () => {
    const el = document.getElementById('sgi-loading');
    if (!el) return;
    el.style.opacity = '0';
    el.style.pointerEvents = 'none';
    setTimeout(() => el.remove(), 650);
});

document.addEventListener('DOMContentLoaded', () => {
    /* ── Revelado al hacer scroll ── */
    const targets = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry, idx) => {
                if (entry.isIntersecting) {
                    setTimeout(() => entry.target.classList.add('is-visible'), idx * 50);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: .1, rootMargin: '0px 0px -40px 0px' });
        targets.forEach(el => io.observe(el));
    } else {
        targets.forEach(el => el.classList.add('is-visible'));
    }

    /* ── Conteo ascendente en los KPI ── */
    document.querySelectorAll('[data-count]').forEach(el => {
        const target = parseInt(el.dataset.count, 10);
        if (isNaN(target)) return;
        const duration = 900;
        const start = performance.now();
        function tick(now) {
            const p = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(eased * target);
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });

    /* ── Hero: brillo que sigue al cursor ── */
    const hero = document.getElementById('hero');
    if (hero) {
        hero.addEventListener('mousemove', (e) => {
            const r = hero.getBoundingClientRect();
            hero.style.setProperty('--hx', ((e.clientX - r.left) / r.width * 100) + '%');
            hero.style.setProperty('--hy', ((e.clientY - r.top) / r.height * 100) + '%');
        });
    }

    /* ── Exportar la página actual a CSV ── */
    const exportBtn = document.getElementById('export-csv');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const encabezados = ['Folio', 'Documento', 'Solicitante', 'Jefe revisor', 'Estado', 'Registro', 'Comentarios'];
            const filas = Array.from(document.querySelectorAll('.row-solicitud'));
            const lineas = filas.map(fila => {
                const celdas = fila.querySelectorAll('td');
                const texto = i => (celdas[i]?.innerText ?? '').replace(/\s+/g, ' ').trim().replace(/"/g, '""');
                return encabezados.map((_, i) => `"${texto(i)}"`).join(',');
            });
            const csv = [encabezados.join(','), ...lineas].join('\n');
            const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'solicitudes.csv';
            a.click();
            URL.revokeObjectURL(url);
        });
    }
});
</script>

</x-app-layout>
