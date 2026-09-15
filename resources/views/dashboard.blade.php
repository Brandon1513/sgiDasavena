<x-app-layout>

@php
    $user = auth()->user();
    $roles = $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames()->implode(', ') : '';

    $formatoHoras = function ($h) {
        if ($h === null) return null;
        return $h >= 24 ? number_format($h / 24, 1) . ' d' : number_format($h, 1) . ' h';
    };

    $kpis = [
        ['label' => 'Pendientes',    'val' => $pendientes,    'icon' => 'ti-hourglass-high',        'desc' => 'Esperando revisión del jefe',              'theme' => 'purple'],
        ['label' => 'Aprobado jefe', 'val' => $aprobadoJefe,  'icon' => 'ti-rosette-discount-check', 'desc' => 'En proceso de gestión SGI',                'theme' => 'gold'],
        ['label' => 'Atendidas',     'val' => $atendidas,     'icon' => 'ti-circle-check',           'desc' => 'Formato actualizado con éxito',            'theme' => 'green'],
        ['label' => 'Rechazadas',    'val' => $rechazadas,    'icon' => 'ti-circle-x',               'desc' => 'Requieren revisión y reenvío',             'theme' => 'rose'],
        ['label' => 'Por vencer',    'val' => $totalPorVencer,'icon' => 'ti-alert-triangle',         'desc' => 'Documentos con vigencia próxima a expirar','theme' => 'amber'],
    ];

    $slaKpis = [
        ['label' => 'Aprobación del jefe', 'val' => $formatoHoras($horasAprobacion), 'icon' => 'ti-clock-check', 'desc' => 'Tiempo promedio: solicitud creada → aprobada por el jefe', 'theme' => 'purple'],
        ['label' => 'Atención de SGI',     'val' => $formatoHoras($horasAtencion),   'icon' => 'ti-progress-check', 'desc' => 'Tiempo promedio: aprobada por el jefe → atendida por SGI', 'theme' => 'gold'],
        ['label' => 'Vencidas SLA',        'val' => $vencidasSla,                    'icon' => 'ti-alarm',       'desc' => 'Pendientes con más de 3 días sin movimiento',              'theme' => 'rose'],
    ];

    $themeClasses = [
        'purple' => ['text' => 'text-dasavena-purple',     'bg' => 'bg-dasavena-purple/10',   'ring' => 'ring-dasavena-purple/20',   'bar' => 'bg-dasavena-purple'],
        'gold'   => ['text' => 'text-dasavena-gold-dark',   'bg' => 'bg-dasavena-gold/15',     'ring' => 'ring-dasavena-gold/30',     'bar' => 'bg-dasavena-gold'],
        'green'  => ['text' => 'text-emerald-600',          'bg' => 'bg-emerald-50',           'ring' => 'ring-emerald-200',          'bar' => 'bg-emerald-500'],
        'rose'   => ['text' => 'text-rose-600',             'bg' => 'bg-rose-50',              'ring' => 'ring-rose-200',             'bar' => 'bg-rose-500'],
        'amber'  => ['text' => 'text-amber-600',            'bg' => 'bg-amber-50',             'ring' => 'ring-amber-200',            'bar' => 'bg-amber-500'],
    ];
@endphp

{{-- ─── PANTALLA DE CARGA ─── --}}
<div id="sgi-loading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-dasavena-purple-dark via-dasavena-purple to-dasavena-purple-light transition-opacity duration-500">
    <div class="flex flex-col items-center gap-4">
        <div class="relative w-20 h-20">
            <span class="absolute -inset-1.5 rounded-full border-2 border-transparent border-t-dasavena-gold border-r-dasavena-gold animate-spin"></span>
            <img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="Dasavena" class="w-20 h-20 object-contain animate-pulse">
        </div>
        <p class="text-white font-display font-bold tracking-wide">Sistema SGI</p>
        <p class="text-dasavena-gold font-mono text-[11px] tracking-wider">// inicializando_módulos</p>
    </div>
</div>

{{-- ─── TOAST BIENVENIDA ─── --}}
<div id="sgi-toast" role="status" aria-live="polite"
    class="fixed top-5 right-5 z-[8888] overflow-hidden rounded-2xl border border-white/90 bg-white/90 shadow-xl shadow-dasavena-purple/20 opacity-0 -translate-y-3 scale-95 transition-all duration-500">
    <div class="h-[3px] bg-gradient-to-r from-dasavena-purple to-dasavena-gold"></div>
    <div class="flex items-center gap-3 px-4 py-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-dasavena-purple-light to-dasavena-purple-dark text-sm font-bold text-white font-display">
            {{ strtoupper(mb_substr($user->name, 0, 1)) }}
        </div>
        <div class="flex flex-col gap-0.5">
            <span class="text-[11px] font-bold uppercase tracking-wide text-dasavena-gold-dark">¡Hola de nuevo!</span>
            <span class="text-sm text-gray-600 font-display">{{ $user->name }}</span>
        </div>
        <span class="ml-1 h-2 w-2 shrink-0 rounded-full bg-dasavena-gold shadow-[0_0_0_3px_rgba(214,166,68,.2)] animate-pulse"></span>
    </div>
</div>

{{-- ─── BANNER ESPECIAL ─── --}}
@if(auth()->user()->id == 33 || auth()->user()->hasRole('administrador_sgi'))
<div id="sgi-banner" class="fixed inset-x-0 top-4 z-[8000] flex justify-center pointer-events-none">
    <div class="pointer-events-auto flex items-center gap-3 rounded-full border border-white/15 bg-gray-950/85 px-6 py-2.5 shadow-2xl backdrop-blur">
        <span class="text-dasavena-gold text-base"><i class="ti ti-sparkles"></i></span>
        <div>
            <p class="text-sm font-bold text-white">{{ auth()->user()->id == 33 ? 'Bienvenido a SGI' : 'Bienvenidas, Administradoras SGI' }}</p>
            <p class="text-xs text-white/50">Plataforma de Gestión Integral</p>
        </div>
    </div>
</div>
<script>setTimeout(()=>{const b=document.getElementById('sgi-banner');if(!b)return;b.style.transition='opacity .4s';b.style.opacity='0';setTimeout(()=>b.remove(),400);},3000);</script>
@endif

{{-- ─── FONDO ─── --}}
<div class="relative min-h-screen bg-cover bg-center bg-fixed" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">
    <div class="pointer-events-none fixed inset-0 z-[1] bg-[radial-gradient(ellipse_90%_55%_at_50%_-5%,rgba(255,255,255,.55)_0%,transparent_65%)]"></div>
    <img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="" aria-hidden="true"
        class="pointer-events-none fixed bottom-5 right-5 z-[5] h-20 w-20 object-contain opacity-15 mix-blend-multiply select-none">

    <div class="relative z-10 mx-auto flex max-w-7xl flex-col gap-8 px-4 py-10 sm:px-6 lg:px-8">

        {{-- ══════════════════════════════ HERO ══════════════════════════════ --}}
        <header class="reveal relative overflow-hidden rounded-3xl border border-white/15 bg-dasavena-purple shadow-[0_4px_40px_rgba(106,44,117,.3)]">
            <div class="gold-gleam absolute inset-x-0 top-0 h-[3px]"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[.07]" style="background-image:radial-gradient(rgba(255,255,255,.9) 1.5px, transparent 1.5px); background-size:22px 22px;"></div>

            <div class="absolute top-4 right-6 z-10 hidden items-center gap-1.5 font-mono text-[10.5px] font-semibold tracking-wide text-white/50 sm:flex">
                <span>SGI</span><span class="text-dasavena-gold/80">//</span><span>{{ now()->format('d.m.Y') }}</span>
                <span class="ml-1 h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_0_3px_rgba(74,222,128,.2)] animate-pulse"></span>
            </div>

            <div class="relative z-[1] grid grid-cols-1 items-center gap-8 px-6 py-10 sm:px-10 sm:py-14 md:grid-cols-[1fr_auto]">
                <div class="flex flex-col gap-4">
                    <div class="inline-flex w-fit items-center gap-2 text-[11px] font-bold uppercase tracking-[.2em] text-dasavena-gold">
                        <span class="h-[7px] w-[7px] rounded-full bg-dasavena-gold shadow-[0_0_0_3px_rgba(214,166,68,.3)] animate-pulse"></span>
                        Sistema de Gestión Integral
                    </div>
                    <h1 class="font-display text-[clamp(2rem,4vw,3.2rem)] font-extrabold leading-[1.08] tracking-tight text-white/55">
                        Hola, <br>
                        <em class="not-italic border-b-2 border-dasavena-gold pb-0.5 text-white">{{ $user->name }}</em>
                    </h1>
                    <p class="max-w-md text-sm leading-relaxed text-white/70">
                        Bienvenido a <strong class="font-bold text-white">DasavenaSGI</strong>.
                        Consulta el estado de tus solicitudes de formatos, aprobaciones y actividad reciente.
                    </p>
                    @if($roles)
                    <div class="inline-flex w-fit items-center gap-2 rounded-full border border-dasavena-gold/35 bg-dasavena-gold/15 px-3.5 py-1.5 text-xs font-semibold text-dasavena-gold">
                        <span class="h-[5px] w-[5px] rounded-full bg-dasavena-gold"></span>
                        {{ $roles }}
                    </div>
                    @endif
                </div>
                <div class="relative hidden items-center justify-center md:flex">
                    <div class="absolute h-[210px] w-[210px] animate-[spin_22s_linear_infinite] rounded-full border border-dashed border-dasavena-gold/35"></div>
                    <img src="/images/Valentia .png" alt="Valentia" class="relative w-[220px] object-contain mix-blend-multiply drop-shadow-[0_8px_32px_rgba(0,0,0,.15)] float-slow">
                </div>
            </div>
        </header>

        {{-- ══════════════════════════════ KPIS PRINCIPALES ══════════════════════════════ --}}
        <section class="reveal grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5" aria-label="Resumen de solicitudes">
            @foreach($kpis as $k)
            @php $t = $themeClasses[$k['theme']]; @endphp
            <article class="group rounded-2xl border border-white/90 bg-white/70 p-5 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-dasavena-purple/10">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl {{ $t['bg'] }} {{ $t['text'] }} text-lg ring-1 {{ $t['ring'] }}">
                    <i class="ti {{ $k['icon'] }}"></i>
                </div>
                <div class="font-mono text-3xl font-extrabold tracking-tight {{ $t['text'] }}" data-count="{{ $k['val'] }}">0</div>
                <div class="mt-1 text-[10px] font-bold uppercase tracking-widest text-gray-500">{{ $k['label'] }}</div>
                <p class="mt-2 text-[11.5px] leading-snug text-gray-400 opacity-0 max-h-0 overflow-hidden transition-all duration-300 group-hover:opacity-100 group-hover:max-h-16">{{ $k['desc'] }}</p>
            </article>
            @endforeach
        </section>

        {{-- ══════════════════════════════ KPIS SLA ══════════════════════════════ --}}
        <section class="reveal">
            <div class="mb-3 flex items-center gap-2">
                <i class="ti ti-gauge text-dasavena-purple"></i>
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-gray-500">Tiempos de respuesta (SLA)</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach($slaKpis as $k)
                @php $t = $themeClasses[$k['theme']]; @endphp
                <div class="flex items-start gap-4 rounded-2xl border border-white/90 bg-white/70 p-5 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $t['bg'] }} {{ $t['text'] }} text-lg ring-1 {{ $t['ring'] }}">
                        <i class="ti {{ $k['icon'] }}"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-mono text-xl font-extrabold {{ $t['text'] }}">{{ $k['val'] ?? '—' }}</div>
                        <div class="text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ $k['label'] }}</div>
                        <p class="mt-1 text-[11px] leading-snug text-gray-400">{{ $k['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        {{-- ══════════════════════════════ BENTO PRINCIPAL ══════════════════════════════ --}}
        <div class="reveal grid grid-cols-1 gap-5 lg:grid-cols-12">

            {{-- GRÁFICA --}}
            <div class="flex min-h-[360px] flex-col rounded-3xl border border-white/90 bg-white/70 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl lg:col-span-8">
                <div class="flex items-start justify-between gap-3 px-6 pt-6">
                    <div>
                        <h2 class="font-display text-base font-bold text-indigo-950">Actividad del sistema</h2>
                        <p class="mt-0.5 text-xs text-gray-400">Solicitudes registradas — últimos 30 días</p>
                    </div>
                    <div class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-800">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        En tiempo real
                    </div>
                </div>
                <div class="flex-1 px-6 pb-6 pt-5">
                    <canvas id="mainChart" role="img" aria-label="Gráfica de solicitudes de los últimos 30 días"></canvas>
                </div>
            </div>

            {{-- CULTURA / VIDEO --}}
            <div class="flex flex-col rounded-3xl border border-white/90 bg-white/70 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl lg:col-span-4">
                <div class="px-6 pt-6">
                    <h2 class="font-display text-base font-bold text-indigo-950">Cultura Dasavena</h2>
                    <p class="mt-0.5 text-xs text-gray-400">Nuestra identidad en movimiento</p>
                </div>
                <div class="flex flex-1 items-center justify-center px-5 pb-3 pt-3">
                    <video autoplay muted loop playsinline class="max-h-[260px] w-full rounded-2xl object-contain mix-blend-multiply">
                        <source src="{{ asset('Videos/Animacion.mp4') }}" type="video/mp4">
                    </video>
                </div>
                <a href="{{ route('cultura.index') }}" class="mx-5 mb-5 flex items-center justify-center gap-1.5 rounded-xl border border-dasavena-purple/15 bg-dasavena-purple/5 py-2.5 text-xs font-bold text-dasavena-purple transition-all hover:-translate-y-0.5 hover:bg-dasavena-purple/10">
                    Conoce nuestros valores <i class="ti ti-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            {{-- DESGLOSE POR ÁREA --}}
            <div class="rounded-3xl border border-white/90 bg-white/70 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl lg:col-span-5">
                <div class="px-6 pt-6">
                    <h2 class="font-display text-base font-bold text-indigo-950">Solicitudes por área</h2>
                    <p class="mt-0.5 text-xs text-gray-400">Volumen y avance por departamento</p>
                </div>
                @if($porArea->count())
                <ul class="flex flex-col gap-4 px-6 py-5">
                    @foreach($porArea as $a)
                    @php $pct = $a->total > 0 ? round(($a->atendidas / $a->total) * 100) : 0; @endphp
                    <li>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-bold text-indigo-950">{{ $a->area }}</span>
                            <span class="text-gray-400">
                                {{ $a->total }} solicitud{{ $a->total === 1 ? '' : 'es' }}
                                @if($a->pendientes > 0)
                                <span class="ml-1 rounded-full bg-amber-50 px-2 py-0.5 font-semibold text-amber-700">{{ $a->pendientes }} pend.</span>
                                @endif
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-dasavena-purple to-dasavena-gold transition-all duration-700" style="width: {{ $pct }}%"></div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="px-6 py-10 text-center">
                    <span class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-xl bg-gray-50 text-xl text-gray-400"><i class="ti ti-chart-bar"></i></span>
                    <p class="text-sm text-gray-400">Aún no hay solicitudes registradas</p>
                </div>
                @endif
            </div>

            {{-- DOCUMENTOS POR VENCER --}}
            <div class="rounded-3xl border border-white/90 bg-white/70 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl lg:col-span-4">
                <div class="flex items-start justify-between gap-3 px-6 pt-6">
                    <div>
                        <h2 class="font-display text-base font-bold text-indigo-950">Documentos por vencer</h2>
                        <p class="mt-0.5 text-xs text-gray-400">Vigencia en zona de alerta</p>
                    </div>
                    @if($totalPorVencer > 0)
                    <div class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-600"></span>
                        {{ $totalPorVencer }}
                    </div>
                    @endif
                </div>
                @if(!empty($documentosPorVencer) && count($documentosPorVencer))
                <ul class="flex flex-col gap-1 px-3 pb-2 pt-3">
                    @foreach($documentosPorVencer as $doc)
                    @php
                        $badge = match ($doc->semaforo_vencimiento) {
                            'vencido' => 'bg-red-600 text-white',
                            'critico' => 'bg-rose-100 text-rose-700',
                            'alerta' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <li>
                        <a href="{{ route('documentos.show', $doc->id) }}" class="flex items-center gap-2.5 rounded-xl px-2.5 py-2 transition-colors hover:bg-amber-50/60">
                            <span class="h-2 w-2 shrink-0 rounded-full {{ $doc->semaforo_vencimiento === 'vencido' ? 'bg-red-600 shadow-[0_0_0_3px_rgba(220,38,38,.15)]' : ($doc->semaforo_vencimiento === 'critico' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="font-mono text-xs font-bold text-indigo-950">{{ $doc->codigo }}</p>
                                <p class="truncate text-[11px] text-gray-400">{{ \Illuminate\Support\Str::limit($doc->nombre, 34) }}</p>
                            </div>
                            <span class="shrink-0 whitespace-nowrap rounded-full px-2.5 py-0.5 font-mono text-[10.5px] font-bold {{ $badge }}">
                                {{ $doc->dias_para_vencimiento < 0 ? 'Vencido' : $doc->dias_para_vencimiento . ' d' }}
                            </span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @if($totalPorVencer > count($documentosPorVencer))
                <div class="px-6 pb-3 pt-1 text-[11px] text-gray-400">+ {{ $totalPorVencer - count($documentosPorVencer) }} documentos más por vencer</div>
                @endif
                @role('administrador_sgi')
                <a href="{{ route('solicitudes.calendar') }}" class="mx-4 mb-4 mt-1 flex items-center justify-center gap-1.5 rounded-xl border border-dasavena-purple/15 bg-dasavena-purple/5 py-2 text-xs font-bold text-dasavena-purple transition-all hover:bg-dasavena-purple/10">
                    Ver calendario completo <i class="ti ti-arrow-right" aria-hidden="true"></i>
                </a>
                @endrole
                @else
                <div class="px-6 py-10 text-center">
                    <span class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-xl text-emerald-600"><i class="ti ti-shield-check"></i></span>
                    <p class="text-sm text-gray-400">Nada por vencer en los próximos 60 días</p>
                </div>
                @endif
            </div>

            {{-- ACTIVIDAD RECIENTE --}}
            <div class="rounded-3xl border border-white/90 bg-white/70 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl lg:col-span-4">
                <div class="px-6 pt-6">
                    <h2 class="font-display text-base font-bold text-indigo-950">Actividad reciente</h2>
                    <p class="mt-0.5 text-xs text-gray-400">Últimas acciones en el sistema</p>
                </div>
                @if(!empty($ultimasSolicitudes) && count($ultimasSolicitudes))
                <ul class="flex max-h-[340px] flex-col gap-1 overflow-y-auto px-3 py-4">
                    @foreach($ultimasSolicitudes as $item)
                    <li class="flex items-start gap-2.5 rounded-xl px-2.5 py-2 transition-colors hover:bg-indigo-50/60">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 font-display text-xs font-bold text-white">
                            {{ strtoupper(mb_substr($item->usuario->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[13px] font-bold text-indigo-950">{{ $item->usuario->name ?? 'Usuario' }}</p>
                            <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700">{{ ucfirst(str_replace('_', ' ', $item->estado)) }}</span>
                                <span class="text-[10px] text-gray-400">{{ $item->accion }}</span>
                            </div>
                            @if($item->comentarios)
                            <p class="mt-1 truncate text-[11px] italic text-gray-400">"{{ \Illuminate\Support\Str::limit($item->comentarios, 55) }}"</p>
                            @endif
                            <time class="mt-0.5 block font-mono text-[10px] text-gray-300">{{ $item->created_at?->format('d/m/Y H:i') }}</time>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="px-6 py-10 text-center">
                    <span class="mx-auto mb-2 flex h-11 w-11 items-center justify-center rounded-xl bg-gray-50 text-xl text-gray-400"><i class="ti ti-moon-stars"></i></span>
                    <p class="text-sm text-gray-400">Sin actividad reciente</p>
                </div>
                @endif
            </div>

            {{-- ACCESOS RÁPIDOS --}}
            <div class="rounded-3xl border border-white/90 bg-white/70 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl lg:col-span-4">
                <div class="px-6 pt-6">
                    <h2 class="font-display text-base font-bold text-indigo-950">Accesos rápidos</h2>
                    <p class="mt-0.5 text-xs text-gray-400">Navegación directa</p>
                </div>
                <nav class="flex flex-col gap-2 px-5 py-5" aria-label="Accesos rápidos">
                    <a href="{{ route('solicitudes.create') }}" class="group flex items-center gap-2.5 rounded-xl border border-dasavena-purple/25 bg-dasavena-purple/10 px-3.5 py-2.5 text-[13px] font-medium text-dasavena-purple transition-all hover:-translate-y-0.5 hover:bg-dasavena-purple/15">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-dasavena-purple-light to-dasavena-purple-dark text-sm text-white shadow"><i class="ti ti-plus"></i></span>
                        Crear nueva solicitud
                        <i class="ti ti-arrow-right ml-auto text-sm opacity-30 transition-all group-hover:translate-x-1 group-hover:opacity-100"></i>
                    </a>
                    <a href="{{ route('solicitudes.index') }}" class="group flex items-center gap-2.5 rounded-xl border border-white/90 bg-white/55 px-3.5 py-2.5 text-[13px] font-medium text-gray-700 shadow-sm transition-all hover:-translate-y-0.5 hover:border-indigo-200 hover:text-indigo-700">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-black/5 bg-black/5 text-sm text-gray-500"><i class="ti ti-clipboard-list"></i></span>
                        Ver todas las solicitudes
                        <i class="ti ti-arrow-right ml-auto text-sm opacity-30 transition-all group-hover:translate-x-1 group-hover:opacity-100"></i>
                    </a>
                    @if($user->hasRole('jefe'))
                    <a href="{{ route('solicitudes.index', ['estado' => 'pendiente']) }}" class="group flex items-center gap-2.5 rounded-xl border border-amber-200/60 bg-amber-50/80 px-3.5 py-2.5 text-[13px] font-medium text-amber-900 transition-all hover:-translate-y-0.5 hover:bg-amber-100/80">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-amber-600/20 bg-amber-600/10 text-sm text-amber-800"><i class="ti ti-clock"></i></span>
                        Pendientes por aprobar
                        <i class="ti ti-arrow-right ml-auto text-sm opacity-30 transition-all group-hover:translate-x-1 group-hover:opacity-100"></i>
                    </a>
                    @endif
                    @if($user->hasRole('administrador_sgi'))
                    <a href="{{ route('solicitudes.index', ['estado' => 'aprobado_jefe']) }}" class="group flex items-center gap-2.5 rounded-xl border border-teal-200/60 bg-teal-50/80 px-3.5 py-2.5 text-[13px] font-medium text-teal-900 transition-all hover:-translate-y-0.5 hover:bg-teal-100/80">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-teal-700/20 bg-teal-700/10 text-sm text-teal-800"><i class="ti ti-file-check"></i></span>
                        Listas para alta SGI
                        <i class="ti ti-arrow-right ml-auto text-sm opacity-30 transition-all group-hover:translate-x-1 group-hover:opacity-100"></i>
                    </a>
                    @endif
                </nav>
            </div>

        </div>

        {{-- ══════════════════════════════ PIE — TARJETAS INFORMATIVAS ══════════════════════════════ --}}
        <section class="reveal grid grid-cols-1 gap-4 sm:grid-cols-3" aria-label="Características del sistema">
            @foreach([
                ['icon'=>'ti-zoom-in','bg'=>'bg-sky-50','ic'=>'text-sky-700','bar'=>'from-sky-500 to-sky-300','title'=>'Trazabilidad completa','text'=>'Cada solicitud genera evidencia de auditoría. Registra quién, cuándo y qué cambió en cada formato del sistema.'],
                ['icon'=>'ti-settings','bg'=>'bg-violet-50','ic'=>'text-violet-700','bar'=>'from-violet-500 to-violet-300','title'=>'Flujo estandarizado','text'=>'Un solo proceso aprobado: el usuario solicita, el jefe aprueba, SGI ejecuta y cierra el ciclo documental.'],
                ['icon'=>'ti-trending-up','bg'=>'bg-emerald-50','ic'=>'text-emerald-700','bar'=>'from-emerald-500 to-emerald-300','title'=>'Mejora continua','text'=>'Identifica patrones en solicitudes, detecta áreas con más cambios y mide el tiempo de respuesta del equipo.'],
            ] as $f)
            <div class="overflow-hidden rounded-2xl border border-white/90 {{ $f['bg'] }}/80 shadow-md shadow-dasavena-purple/5 backdrop-blur-xl transition-transform hover:-translate-y-1">
                <div class="p-6">
                    <div class="mb-3.5 flex h-10 w-10 items-center justify-center rounded-xl border border-black/[.07] bg-white/75 text-lg {{ $f['ic'] }} shadow-sm">
                        <i class="ti {{ $f['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <h3 class="font-display text-sm font-bold text-indigo-950">{{ $f['title'] }}</h3>
                    <p class="mt-1.5 text-[12.5px] leading-relaxed text-gray-600">{{ $f['text'] }}</p>
                </div>
                <div class="h-[3px] bg-gradient-to-r {{ $f['bar'] }}"></div>
            </div>
            @endforeach
        </section>

    </div>
</div>

{{-- ══════════════════════════════
     CHATBOT DE SOPORTE IT
══════════════════════════════ --}}
<div class="itchat" x-data="itSupportChat()" x-cloak>

    <button type="button" class="itchat-fab" @click="toggle()" :class="{ 'itchat-fab-open': open }" aria-label="Abrir asistente de soporte IT">
        <i class="ti" :class="open ? 'ti-x' : 'ti-headset'"></i>
        <span class="itchat-fab-dot" x-show="!open"></span>
    </button>

    <div class="itchat-panel hud-corners" x-show="open" x-transition
         @keydown.escape.window="open=false" style="display:none;">
        <span class="hud-tag">IT</span>

        <div class="itchat-head">
            <div class="itchat-head-avatar"><i class="ti ti-headset"></i></div>
            <div class="itchat-head-body">
                <p class="itchat-head-title">Soporte IT</p>
                <p class="itchat-head-sub">Tickets e ideas para la plataforma</p>
            </div>
            <button type="button" class="itchat-close" @click="open=false" aria-label="Cerrar">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <div class="itchat-body" x-ref="body">
            <template x-for="(m, i) in messages" :key="i">
                <div class="itchat-msg" :class="m.from === 'user' ? 'itchat-msg-user' : 'itchat-msg-bot'">
                    <div class="itchat-bubble" x-text="m.text"></div>
                </div>
            </template>

            <template x-if="step === 'tipo'">
                <div class="itchat-options">
                    <button type="button" class="itchat-opt" @click="selectTipo('ticket')">
                        <i class="ti ti-alert-circle"></i> Reportar un problema
                    </button>
                    <button type="button" class="itchat-opt" @click="selectTipo('idea')">
                        <i class="ti ti-bulb"></i> Proponer una idea
                    </button>
                </div>
            </template>

            <template x-if="step === 'confirm'">
                <div class="itchat-options">
                    <button type="button" class="itchat-opt itchat-opt-primary" @click="enviar()" :disabled="sending">
                        <i class="ti ti-send"></i> <span x-text="sending ? 'Enviando…' : 'Enviar a IT'"></span>
                    </button>
                    <button type="button" class="itchat-opt" @click="reset()" :disabled="sending">
                        <i class="ti ti-arrow-back-up"></i> Empezar de nuevo
                    </button>
                </div>
            </template>

            <template x-if="step === 'done'">
                <div class="itchat-options">
                    <button type="button" class="itchat-opt itchat-opt-primary" @click="reset()">
                        <i class="ti ti-plus"></i> Enviar otro
                    </button>
                </div>
            </template>
        </div>

        <form class="itchat-input-row" x-show="step === 'asunto' || step === 'descripcion'" @submit.prevent="submitPaso()">
            <template x-if="step === 'asunto'">
                <input type="text" x-model="form.asunto" maxlength="150" placeholder="Escribe el asunto…" class="itchat-input" autofocus>
            </template>
            <template x-if="step === 'descripcion'">
                <textarea x-model="form.descripcion" maxlength="5000" rows="1" placeholder="Cuéntanos con más detalle…" class="itchat-input itchat-textarea"></textarea>
            </template>
            <button type="submit" class="itchat-send" aria-label="Enviar mensaje" :disabled="!puedeAvanzar()">
                <i class="ti ti-send"></i>
            </button>
        </form>
    </div>
</div>

{{-- ─────────────────────────────────────────────
     ESTILOS
───────────────────────────────────────────── --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
<style>
:root {
    --purple: #6A2C75;
    --purple-light: #8E3D9E;
    --purple-dark: #4a1f55;
    --gold: #D6A644;
    --gold-dark: #b38600;
}

/* ── Revelado al hacer scroll ── */
.reveal { opacity: 0; transform: translateY(28px); transition: opacity .7s cubic-bezier(.22,1,.36,1), transform .7s cubic-bezier(.22,1,.36,1); }
.reveal.is-visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) { .reveal { opacity: 1 !important; transform: none !important; transition: none !important; } }

/* ── Firma dorada del hero ── */
.gold-gleam {
    background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, rgba(255,255,255,.8) 50%, var(--gold) 70%, transparent 100%);
    background-size: 200% 100%;
    animation: gold-shimmer 3.5s ease-in-out infinite;
}
@keyframes gold-shimmer { 0%{background-position:100% 0} 100%{background-position:-100% 0} }
.float-slow { animation: float 4s ease-in-out infinite; }
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

/* ── HUD corners (chat de soporte) ── */
.hud-corners { position: relative; }
.hud-corners::before, .hud-corners::after {
    content: ''; position: absolute; width: 14px; height: 14px;
    border-color: var(--gold); border-style: solid; opacity: .5;
    pointer-events: none;
}
.hud-corners::before { top: 9px; left: 9px; border-width: 2px 0 0 2px; border-radius: 5px 0 0 0; }
.hud-corners::after  { bottom: 9px; right: 9px; border-width: 0 2px 2px 0; border-radius: 0 0 5px 0; }
.hud-tag {
    position: absolute; top: 14px; right: 16px; z-index: 2;
    font-family: 'SFMono-Regular', ui-monospace, 'Consolas', monospace; font-size: 9.5px; font-weight: 700; letter-spacing: .1em;
    color: var(--gold-dark); opacity: .55; pointer-events: none;
}

/* ══════════════════════════════
   CHATBOT DE SOPORTE IT
══════════════════════════════ */
[x-cloak] { display: none !important; }
.itchat { position: fixed; right: 24px; bottom: 24px; z-index: 7000; }

.itchat-fab {
    width: 58px; height: 58px; border-radius: 50%; border: none; cursor: pointer;
    background: linear-gradient(135deg, var(--purple-light), var(--purple-dark));
    color: #fff; font-size: 22px; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 28px rgba(106,44,117,.4);
    transition: transform .25s cubic-bezier(.22,1,.36,1), box-shadow .25s cubic-bezier(.22,1,.36,1);
    position: relative;
}
.itchat-fab:hover { transform: translateY(-3px) scale(1.04); box-shadow: 0 12px 34px rgba(106,44,117,.5); }
.itchat-fab-open { background: linear-gradient(135deg, #4b5563, #1f2937); }
.itchat-fab-dot {
    position: absolute; top: 4px; right: 4px; width: 12px; height: 12px; border-radius: 50%;
    background: var(--gold); border: 2px solid #fff;
    animation: gold-shimmer 1.8s ease-in-out infinite;
}

.itchat-panel {
    position: absolute; right: 0; bottom: 74px;
    width: min(370px, calc(100vw - 32px)); max-height: min(560px, calc(100vh - 140px));
    display: flex; flex-direction: column;
    background: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.88);
    border-radius: 24px; backdrop-filter: blur(24px) saturate(160%); -webkit-backdrop-filter: blur(24px) saturate(160%);
    box-shadow: 0 16px 48px rgba(106,44,117,.28);
    overflow: hidden;
}
.itchat-head {
    display: flex; align-items: center; gap: 12px; padding: 18px 18px 16px;
    background: linear-gradient(135deg, var(--purple-dark), var(--purple));
    color: #fff; flex-shrink: 0;
}
.itchat-head-avatar {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.25);
    display: flex; align-items: center; justify-content: center; font-size: 17px;
}
.itchat-head-body { flex: 1; min-width: 0; }
.itchat-head-title { font-family: inherit; font-size: 14px; font-weight: 700; }
.itchat-head-sub { font-size: 11px; color: rgba(255,255,255,.7); margin-top: 1px; }
.itchat-close {
    width: 28px; height: 28px; border-radius: 50%; border: none; cursor: pointer; flex-shrink: 0;
    background: rgba(255,255,255,.12); color: #fff; display: flex; align-items: center; justify-content: center;
    transition: background .2s ease;
}
.itchat-close:hover { background: rgba(255,255,255,.24); }

.itchat-body {
    flex: 1; overflow-y: auto; padding: 18px; display: flex; flex-direction: column; gap: 10px;
    min-height: 200px;
}
.itchat-msg { display: flex; }
.itchat-msg-bot { justify-content: flex-start; }
.itchat-msg-user { justify-content: flex-end; }
.itchat-bubble {
    max-width: 85%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.55;
    white-space: pre-line;
}
.itchat-msg-bot .itchat-bubble { background: rgba(255,255,255,.9); color: #2d1033; border: 1px solid rgba(255,255,255,.9); border-bottom-left-radius: 4px; }
.itchat-msg-user .itchat-bubble { background: var(--purple); color: #fff; border-bottom-right-radius: 4px; }

.itchat-options { display: flex; flex-direction: column; gap: 8px; margin-top: 2px; }
.itchat-opt {
    display: flex; align-items: center; gap: 8px; padding: 11px 14px; border-radius: 12px;
    background: rgba(255,255,255,.7); border: 1px solid rgba(106,44,117,.2); cursor: pointer;
    font-size: 13px; font-weight: 600; color: var(--purple-dark); text-align: left;
    transition: all .2s ease;
}
.itchat-opt:hover:not(:disabled) { background: #fff; border-color: rgba(106,44,117,.4); transform: translateY(-1px); }
.itchat-opt:disabled { opacity: .55; cursor: not-allowed; }
.itchat-opt-primary { background: linear-gradient(135deg, var(--purple-light), var(--purple-dark)); color: #fff; border-color: transparent; }
.itchat-opt-primary:hover:not(:disabled) { filter: brightness(1.08); }

.itchat-input-row { display: flex; align-items: flex-end; gap: 8px; padding: 12px 14px; border-top: 1px solid rgba(0,0,0,.06); flex-shrink: 0; }
.itchat-input {
    flex: 1; border: 1px solid rgba(106,44,117,.2); border-radius: 12px; padding: 10px 12px;
    font-size: 13px; font-family: inherit; background: #fff; color: #2d1033; resize: none;
}
.itchat-input:focus { outline: none; border-color: var(--purple); }
.itchat-textarea { min-height: 40px; max-height: 120px; }
.itchat-send {
    width: 38px; height: 38px; border-radius: 50%; border: none; cursor: pointer; flex-shrink: 0;
    background: var(--purple); color: #fff; display: flex; align-items: center; justify-content: center;
    transition: all .2s ease;
}
.itchat-send:hover:not(:disabled) { background: var(--purple-dark); }
.itchat-send:disabled { opacity: .4; cursor: not-allowed; }

@media (max-width: 480px) {
    .itchat { right: 14px; bottom: 14px; }
    .itchat-panel { right: -6px; }
}
</style>

{{-- ─────────────────────────────────────────────
     SCRIPTS
───────────────────────────────────────────── --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
/* ── Loading fade ── */
window.addEventListener('load', () => {
    const el = document.getElementById('sgi-loading');
    if (!el) return;
    el.style.opacity = '0';
    el.style.pointerEvents = 'none';
    setTimeout(() => el.remove(), 650);
});

document.addEventListener('DOMContentLoaded', () => {
    /* ── Toast ── */
    const t = document.getElementById('sgi-toast');
    if (t) {
        setTimeout(() => t.classList.remove('opacity-0', '-translate-y-3', 'scale-95'), 500);
        setTimeout(() => t.classList.add('opacity-0', '-translate-y-3', 'scale-95'), 4200);
    }

    /* ── Revelado al hacer scroll ── */
    const targets = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry, idx) => {
                if (entry.isIntersecting) {
                    setTimeout(() => entry.target.classList.add('is-visible'), idx * 40);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: .12, rootMargin: '0px 0px -60px 0px' });
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

    /* ── Gráfica ── */
    const diasLabels = @json($porDia->pluck('fecha'));
    const diasData   = @json($porDia->pluck('total'));

    Chart.defaults.font.family = "'Century Gothic', sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#9ca3af';

    const chartEl = document.getElementById('mainChart');
    if (chartEl) {
        new Chart(chartEl, {
            type: 'line',
            data: {
                labels: diasLabels,
                datasets: [{
                    label: 'Solicitudes',
                    data: diasData,
                    tension: 0.42,
                    borderColor: '#6A2C75',
                    backgroundColor: ctx => {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                        g.addColorStop(0,   'rgba(106,44,117,.22)');
                        g.addColorStop(.6,  'rgba(142,61,158,.06)');
                        g.addColorStop(1,   'rgba(106,44,117,0)');
                        return g;
                    },
                    borderWidth: 2.5, fill: true,
                    pointRadius: 5, pointBackgroundColor: '#fff',
                    pointBorderColor: '#6A2C75', pointBorderWidth: 2.5,
                    pointHoverRadius: 7, pointHoverBackgroundColor: '#6A2C75',
                    pointHoverBorderColor: '#fff', pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(255,255,255,.97)',
                        titleColor: '#1e1b4b', bodyColor: '#4b5563',
                        borderColor: 'rgba(106,44,117,.2)', borderWidth: 1,
                        padding: 12, cornerRadius: 10,
                        callbacks: { label: item => ` ${item.raw} solicitudes` }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8, color: '#d1d5db' } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { color: '#d1d5db', padding: 6 } }
                }
            }
        });
    }
});

/* ── Chatbot de soporte IT ── */
document.addEventListener('alpine:init', () => {
    Alpine.data('itSupportChat', () => ({
        open: false,
        step: 'tipo',
        sending: false,
        messages: [],
        form: { tipo: '', asunto: '', descripcion: '' },

        init() {
            this.saludar();
        },

        toggle() {
            this.open = !this.open;
        },

        saludar() {
            this.messages = [
                { from: 'bot', text: 'Hola 👋 Soy el asistente de Soporte IT. ¿Qué necesitas hoy?' },
            ];
            this.step = 'tipo';
        },

        selectTipo(tipo) {
            this.form.tipo = tipo;
            this.messages.push({ from: 'user', text: tipo === 'idea' ? 'Quiero proponer una idea' : 'Quiero reportar un problema' });
            this.messages.push({ from: 'bot', text: tipo === 'idea' ? 'Genial, cuéntame en pocas palabras cuál es tu idea (asunto).' : 'Entendido, dame un resumen breve del problema (asunto).' });
            this.step = 'asunto';
            this.scrollAbajo();
        },

        puedeAvanzar() {
            if (this.step === 'asunto') return this.form.asunto.trim().length > 0;
            if (this.step === 'descripcion') return this.form.descripcion.trim().length > 0;
            return false;
        },

        submitPaso() {
            if (!this.puedeAvanzar()) return;

            if (this.step === 'asunto') {
                this.messages.push({ from: 'user', text: this.form.asunto });
                this.messages.push({ from: 'bot', text: 'Perfecto. Ahora dame el detalle: qué pasó, dónde, o en qué consiste tu idea.' });
                this.step = 'descripcion';
            } else if (this.step === 'descripcion') {
                this.messages.push({ from: 'user', text: this.form.descripcion });
                this.messages.push({ from: 'bot', text: '¿Confirmas que quieres enviar esto al equipo de IT?' });
                this.step = 'confirm';
            }
            this.scrollAbajo();
        },

        async enviar() {
            if (this.sending) return;
            this.sending = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                const res = await fetch('{{ route('soporte.ticket.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok || res.status === 202) {
                    this.messages.push({ from: 'bot', text: data.message ?? 'Listo, tu solicitud fue enviada.' });
                } else {
                    this.messages.push({ from: 'bot', text: data.message ?? 'No pude enviar tu solicitud, intenta de nuevo en un momento.' });
                }
            } catch (e) {
                this.messages.push({ from: 'bot', text: 'Ocurrió un error de conexión al enviar tu solicitud. Intenta de nuevo.' });
            } finally {
                this.sending = false;
                this.step = 'done';
                this.scrollAbajo();
            }
        },

        reset() {
            this.form = { tipo: '', asunto: '', descripcion: '' };
            this.saludar();
        },

        scrollAbajo() {
            this.$nextTick(() => {
                const el = this.$refs.body;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },
    }));
});
</script>

</x-app-layout>
