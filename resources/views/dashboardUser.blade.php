<x-app-layout>
    <div id="sgi-loading" class="fixed inset-0 z-50 flex items-center justify-center bg-gradient-to-br from-[#6A2C75] via-[#5a2565] to-[#4a1f55] backdrop-blur-sm">
        <div class="flex flex-col items-center gap-6">
            <div class="relative w-24 h-24">
                <img src="https://dasavenasite.domcloud.dev/images/dasavena-logo.png"
                     alt="Dasavena Gourmet logo"
                     class="w-full h-full object-contain animate-pulse">
                <div class="absolute inset-0 border-4 border-transparent border-t-[#D6A644] border-r-[#D6A644] rounded-full animate-spin"></div>
            </div>
            <div class="text-center">
                <p class="text-white text-lg font-semibold">Cargando Sistema SGI</p>
                <p class="text-[#D6A644] text-sm mt-2">Por favor espera...</p>
            </div>
        </div>
    </div>
    <script>
    // Ocultar pantalla de carga cuando la página cargue completamente
    window.addEventListener('load', () => {
        const loading = document.getElementById('sgi-loading');
        loading.style.opacity = '0';
        loading.style.pointerEvents = 'none';
        loading.style.transition = 'opacity 0.6s cubic-bezier(.22,1,.36,1)';
    });
</script>

@php
    $estadoActual = request('estado', 'all');
    $totalDocs    = ($stats['critico'] ?? 0) + ($stats['alerta'] ?? 0) + ($stats['en_regla'] ?? 0);
    $pctCritico   = $totalDocs > 0 ? round(($stats['critico'] ?? 0) / $totalDocs * 100) : 0;
    $pctAlerta    = $totalDocs > 0 ? round(($stats['alerta']  ?? 0) / $totalDocs * 100) : 0;
    $pctRegla     = $totalDocs > 0 ? round(($stats['en_regla']?? 0) / $totalDocs * 100) : 0;
    $saludLabel = match(true) {
        $pctRegla >= 80 => ['Óptima',    '#059669', 'ok'],
        $pctRegla >= 60 => ['Aceptable', '#d97706', 'alert'],
        default         => ['Crítica',   '#dc2626', 'crit'],
    };
@endphp

{{-- ══════════════════════════════════════════
     FONDO ORIGINAL (sin cambios)
══════════════════════════════════════════ --}}

<div class="dc-root min-h-screen bg-fixed bg-center bg-cover"
     style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">

    <img src="https://dasavenasite.domcloud.dev/images/dasavena-logo.png"
         alt="" aria-hidden="true"
         class="pointer-events-none fixed inset-0 m-auto w-80 h-80 md:w-96 md:h-96 object-contain z-0 select-none"
         style="opacity:.05;mix-blend-mode:multiply">

    {{-- Velo claro sobre el fondo --}}
    <div class="dc-veil" aria-hidden="true"></div>

    <div class="relative z-10 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-screen-xl mx-auto space-y-5">

            {{-- ══════════════════════════════════
                 HEADER
            ══════════════════════════════════ --}}
            <header class="dc-header dc-reveal" style="--i:0">
                <div class="dc-header-face">
                    {{-- Franja lateral brand --}}
                    <div class="dc-brand-strip"></div>
                    <div class="flex flex-col lg:flex-row lg:items-center gap-6 p-7 lg:p-8">

                        {{-- Identidad --}}
                        <div class="flex items-center gap-5 flex-1">
                            <div class="dc-header-icon">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"
                                          stroke="rgba(255,255,255,.95)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="14 2 14 8 20 8" stroke="rgba(255,255,255,.95)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="16" y1="13" x2="8" y2="13" stroke="rgba(255,255,255,.65)" stroke-width="1.7" stroke-linecap="round"/>
                                    <line x1="16" y1="17" x2="8" y2="17" stroke="rgba(255,255,255,.65)" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                <div class="dc-icon-halo"></div>
                            </div>
                            <div>
                                <p class="dc-breadcrumb">DasavenaSGI — Control Documental</p>
                                <h1 class="dc-page-title">Monitoreo de <em>Documentos</em></h1>
                                <p class="dc-page-sub">Vigencia y estado de los formatos del sistema de gestión integral</p>
                            </div>
                        </div>

                        <div class="dc-vdivider hidden lg:block"></div>

                        {{-- KPIs --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 lg:w-[520px]">
                            <div class="dc-kpi kpi-total">
                                <span class="dc-kpi-icon">Σ</span>
                                <span class="dc-kpi-num">{{ $totalDocs }}</span>
                                <span class="dc-kpi-lbl">Total</span>
                            </div>
                            <div class="dc-kpi kpi-crit">
                                <span class="dc-kpi-icon">!</span>
                                <span class="dc-kpi-num">{{ $stats['critico'] ?? 0 }}</span>
                                <span class="dc-kpi-lbl">Críticos</span>
                            </div>
                            <div class="dc-kpi kpi-alert">
                                <span class="dc-kpi-icon">⚠</span>
                                <span class="dc-kpi-num">{{ $stats['alerta'] ?? 0 }}</span>
                                <span class="dc-kpi-lbl">En alerta</span>
                            </div>
                            <div class="dc-kpi kpi-ok">
                                <span class="dc-kpi-icon">✓</span>
                                <span class="dc-kpi-num">{{ $stats['en_regla'] ?? 0 }}</span>
                                <span class="dc-kpi-lbl">En regla</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dc-header-shadow"></div>
            </header>

            {{-- ══════════════════════════════════
                 SALUD + FILTROS
            ══════════════════════════════════ --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 dc-reveal" style="--i:1">

                {{-- Salud documental --}}
                <div class="dc-panel dc-panel-3d lg:col-span-2">
                    <div class="dc-panel-face p-7">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                            <div>
                                <h2 class="dc-section-title">Salud documental</h2>
                                <p class="dc-section-sub">Distribución de los {{ $totalDocs }} documentos registrados</p>
                            </div>
                            <div class="dc-salud-badge salud-{{ $saludLabel[2] }}">
                                <span class="dc-salud-dot"></span>
                                Salud {{ $saludLabel[0] }}
                            </div>
                        </div>

                        <div class="space-y-5">
                            @foreach([
                                ['label'=>'En regla',  'pct'=>$pctRegla,  'count'=>$stats['en_regla']??0, 'cls'=>'bar-ok'],
                                ['label'=>'En alerta', 'pct'=>$pctAlerta, 'count'=>$stats['alerta']??0,  'cls'=>'bar-alert'],
                                ['label'=>'Crítico',   'pct'=>$pctCritico,'count'=>$stats['critico']??0, 'cls'=>'bar-crit'],
                            ] as $b)
                                <div class="dc-bar-row">
                                    <div class="dc-bar-meta">
                                        <span class="dc-bar-label">{{ $b['label'] }}</span>
                                        <div class="flex items-center gap-3">
                                            <span class="dc-bar-count">{{ $b['count'] }} docs</span>
                                            <span class="dc-bar-pct {{ $b['cls'] }}">{{ $b['pct'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="dc-bar-track">
                                        <div class="dc-bar-fill {{ $b['cls'] }}-fill"
                                             style="width:{{ $b['pct'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="dc-panel-shadow"></div>
                </div>

                {{-- Filtros --}}
                <div class="dc-panel dc-panel-3d">
                    <div class="dc-panel-face p-6">
                        <h2 class="dc-section-title mb-1">Filtrar vista</h2>
                        <p class="dc-section-sub mb-5">Selecciona qué documentos ver</p>
                        <div class="space-y-2.5">
                            @foreach([
                                ['href'=>'all',      'label'=>'Todos los documentos', 'count'=>$totalDocs,            'cls'=>'flt-all',   'icon'=>'#'],
                                ['href'=>'critico',  'label'=>'Críticos',             'count'=>$stats['critico']??0,  'cls'=>'flt-crit',  'icon'=>'!'],
                                ['href'=>'alerta',   'label'=>'En alerta',            'count'=>$stats['alerta']??0,   'cls'=>'flt-alert', 'icon'=>'⚠'],
                                ['href'=>'en_regla', 'label'=>'En regla',             'count'=>$stats['en_regla']??0, 'cls'=>'flt-ok',    'icon'=>'✓'],
                            ] as $f)
                                <a href="?estado={{ $f['href'] }}"
                                   class="dc-filter {{ $f['cls'] }} {{ $estadoActual === $f['href'] ? 'dc-filter-active' : '' }}">
                                    <div class="dc-filter-icon {{ $f['cls'] }}-icon">{{ $f['icon'] }}</div>
                                    <span class="flex-1">{{ $f['label'] }}</span>
                                    <span class="dc-filter-badge {{ $f['cls'] }}-badge">{{ $f['count'] }}</span>
                                    <span class="dc-filter-arrow">→</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="dc-panel-shadow"></div>
                </div>
            </div>

            {{-- ══════════════════════════════════
                 TABLA + DONUT
            ══════════════════════════════════ --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 dc-reveal" style="--i:2">

                {{-- Tabla --}}
                <div class="xl:col-span-2 dc-panel dc-panel-3d">
                    <div class="dc-panel-face">
                        <div class="dc-table-header">
                            <div>
                                <h2 class="dc-section-title">Lista de documentos</h2>
                                <p class="dc-section-sub">
                                    @if($estadoActual==='all') Todos los documentos
                                    @elseif($estadoActual==='critico') Documentos <strong>críticos</strong>
                                    @elseif($estadoActual==='alerta') Documentos en <strong>alerta</strong>
                                    @else Documentos <strong>en regla</strong>
                                    @endif
                                    — {{ count($list) }} resultados
                                </p>
                            </div>
                            @if($estadoActual !== 'all')
                                <a href="?estado=all" class="dc-clear-filter">✕ Limpiar filtro</a>
                            @endif
                        </div>

                        <div class="dc-table-wrap">
                            <table class="dc-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre del documento</th>
                                        <th>Área</th>
                                        <th class="text-center">Días restantes</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($list as $doc)
                                        @php
                                            $d = (int)($doc['days_left'] ?? -1);
                                            $estado = match(true) {
                                                $d >= 0 && $d <= 30 => 'Crítico',
                                                $d >= 31 && $d <= 60 => 'Alerta',
                                                $d > 60  => 'En Regla',
                                                default  => 'Sin Fecha'
                                            };
                                            $rowCls   = match($estado) { 'Crítico'=>'row-crit','Alerta'=>'row-alert','En Regla'=>'row-ok', default=>'' };
                                            $badgeCls = match($estado) { 'Crítico'=>'badge-crit','Alerta'=>'badge-alert','En Regla'=>'badge-ok', default=>'badge-none' };
                                            $daysCls  = match($estado) { 'Crítico'=>'days-crit','Alerta'=>'days-alert','En Regla'=>'days-ok', default=>'days-none' };
                                        @endphp
                                        <tr class="dc-tr {{ $rowCls }}">
                                            <td><span class="dc-codigo">{{ $doc['codigo'] }}</span></td>
                                            <td><span class="dc-nombre">{{ $doc['nombre'] }}</span></td>
                                            <td><span class="dc-area-tag">{{ $doc['area'] }}</span></td>
                                            <td class="text-center">
                                                <div class="dc-days {{ $daysCls }}">
                                                    @if($d >= 0)
                                                        <span class="dc-days-num">{{ $d }}</span>
                                                        <span class="dc-days-lbl">días</span>
                                                    @else
                                                        <span class="dc-days-none-txt">—</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="dc-badge {{ $badgeCls }}">
                                                    @if($estado==='Crítico') ● @elseif($estado==='Alerta') ◐ @elseif($estado==='En Regla') ○ @else ‒ @endif
                                                    {{ $estado }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="dc-empty-row">
                                            <div class="dc-empty-state">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#BBA4C0" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                                <p>No se encontraron documentos con este filtro</p>
                                            </div>
                                        </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="dc-panel-shadow"></div>
                </div>

                {{-- Donut + leyenda --}}
                <div class="dc-panel dc-panel-3d">
                    <div class="dc-panel-face p-6">
                        <div class="mb-6">
                            <h2 class="dc-section-title">Distribución</h2>
                            <p class="dc-section-sub">Por estado de vigencia</p>
                        </div>

                        <div class="dc-donut-wrap">
                            <canvas id="donutChart"></canvas>
                            <div class="dc-donut-center">
                                <span class="dc-donut-total">{{ $totalDocs }}</span>
                                <span class="dc-donut-lbl">documentos</span>
                            </div>
                        </div>

                        <div class="dc-legend">
                            @foreach([
                                ['label'=>'En regla',  'count'=>$stats['en_regla']??0,'pct'=>$pctRegla, 'color'=>'#059669'],
                                ['label'=>'En alerta', 'count'=>$stats['alerta']??0,  'pct'=>$pctAlerta,'color'=>'#d97706'],
                                ['label'=>'Crítico',   'count'=>$stats['critico']??0, 'pct'=>$pctCritico,'color'=>'#dc2626'],
                            ] as $l)
                                <div class="dc-legend-row">
                                    <span class="dc-legend-dot" style="background:{{ $l['color'] }}"></span>
                                    <span class="dc-legend-label">{{ $l['label'] }}</span>
                                    <span class="dc-legend-count">{{ $l['count'] }}</span>
                                    <span class="dc-legend-pct" style="color:{{ $l['color'] }}">{{ $l['pct'] }}%</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="dc-insight">
                            <span class="dc-insight-icon">ℹ</span>
                            <p class="dc-insight-text">
                                @if($pctCritico >= 30)
                                    <strong>Atención requerida:</strong> {{ $pctCritico }}% vence en menos de 30 días.
                                @elseif($pctAlerta >= 40)
                                    <strong>Seguimiento necesario:</strong> {{ $pctAlerta }}% entra pronto en crítico.
                                @else
                                    <strong>Estado saludable:</strong> la mayoría de documentos está al corriente.
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="dc-panel-shadow"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
/* ══════════════════════════════════════════
   CENTURY GOTHIC STACK
══════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,500;0,600;0,700;0,800;1,700&display=swap');

:root {
    /* Paleta de marca */
    --brand:      #6A2C75;
    --brand-dark: #501d59;
    --brand-mid:  #7e3a8a;
    --brand-lite: #BBA4C0;
    --brand-pale: #f0e8f3;
    --brand-faint:#faf6fb;

    /* Neutros */
    --black:   #0d0d0d;
    --white:   #ffffff;
    --surface: #ffffff;
    --surf-2:  #faf6fb;   /* faint brand tint */
    --border:  #e8dded;   /* lavender border */
    --text-1:  #1a0a1e;
    --text-2:  #4a3355;
    --text-3:  #9b84a5;

    /* Semáforo */
    --crit:  #dc2626;
    --alert: #d97706;
    --ok:    #059669;

    --rad: 16px;
    --font: 'Century Gothic','CenturyGothic','AppleGothic','Nunito','Trebuchet MS',sans-serif;
}

.dc-root * {
    font-family: var(--font);
    box-sizing: border-box;
}

/* Velo claro */
.dc-veil {
    pointer-events:none; ; inset:0; z-index:1;
    background: linear-gradient(180deg,
        rgba(250,246,251,.82) 0%,
        rgba(240,232,243,.72) 100%);
}

/* Reveal */
.dc-reveal {
    opacity:0; transform:translateY(18px);
    animation: dc-in .6s cubic-bezier(.22,1,.36,1) forwards;
    animation-delay: calc(var(--i,0) * .1s);
}
@keyframes dc-in { to { opacity:1; transform:translateY(0); } }

/* ── Panel 3D ── */
.dc-panel { position:relative; will-change:transform; }
.dc-panel-face {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--rad);
    position: relative; z-index:1;
    transition: transform .3s ease, box-shadow .3s ease;
    box-shadow:
        0 1px 0 rgba(255,255,255,.9) inset,
        0 1px 3px rgba(106,44,117,.05),
        0 4px 16px rgba(106,44,117,.07);
}
.dc-panel-shadow {
    position:absolute; inset:0; border-radius:var(--rad);
    background: rgba(106,44,117,.1);
    transform: translateY(6px) scale(.97);
    filter: blur(12px); z-index:0;
    transition: transform .3s ease, opacity .3s ease;
}
.dc-panel-3d:hover .dc-panel-face {
    transform: translateY(-3px);
    box-shadow:
        0 1px 0 rgba(255,255,255,.9) inset,
        0 12px 40px rgba(106,44,117,.1);
}
.dc-panel-3d:hover .dc-panel-shadow {
    transform: translateY(12px) scale(.95); opacity:.65;
}

/* ══════════════════════════════════════════
   HEADER
══════════════════════════════════════════ */
.dc-header { position:relative; }
.dc-header-face {
    background: linear-gradient(135deg, var(--brand-dark) 0%, var(--brand) 55%, var(--brand-mid) 100%);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 20px;
    position: relative; z-index:1; overflow:hidden;
    transition: transform .3s ease;
}
/* Reflejo sutil */
.dc-header-face::before {
    content:''; position:absolute; top:0; left:0; right:0; height:1px;
    background: linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);
}
/* Patrón de puntos dec */
.dc-header-face::after {
    content:''; pointer-events:none;
    position:absolute; inset:0;
    background-image: radial-gradient(rgba(255,255,255,.06) 1.8px, transparent 1px);
    background-size: 18px 18px;
    border-radius: 20px;
    animation: dc-pulse 2s ease-in-out infinite;
}

@keyframes dc-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.dc-brand-strip {
    position:absolute; left:0; top:0; bottom:0; width:4px;
    background: linear-gradient(180deg, #e8d5ed, var(--brand-lite), #e8d5ed);
    border-radius: 20px 0 0 20px;
}
.dc-header-shadow {
    position:absolute; inset:0; border-radius:20px;
    background: rgba(106,44,117,.35);
    transform: translateY(8px) scale(.97); filter:blur(16px); z-index:0;
}
.dc-header:hover .dc-header-face { transform:translateY(-2px); }

.dc-header-icon {
    position:relative; width:56px; height:56px; border-radius:14px; flex-shrink:0;
    background: rgba(255,255,255,.18);
    border: 1.5px solid rgba(255,255,255,.3);
    display:flex; align-items:center; justify-content:center;
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 16px rgba(0,0,0,.15), 0 0 0 1px rgba(255,255,255,.1);
}
.dc-icon-halo {
    position:absolute; inset:-5px; border-radius:18px;
    border: 1.5px solid rgba(255,255,255,.12);
}

.dc-breadcrumb {
    font-size:11px; text-transform:uppercase; letter-spacing:.16em;
    color: rgba(255,255,255,.5); font-weight:600; margin:0;
}
.dc-page-title {
    font-size: clamp(1.4rem,2.5vw,1.9rem); font-weight:800;
    color: rgba(255,255,255,.97); margin:4px 0 4px; line-height:1.15;
    letter-spacing: -.01em;
}
.dc-page-title em { font-style:italic; color: var(--brand-lite); }
.dc-page-sub { font-size:12px; color:rgba(255,255,255,.45); margin:0; font-weight:400; max-width:420px; }
.dc-vdivider { width:1px; height:60px; background:rgba(255,255,255,.15); flex-shrink:0; }

/* KPIs del header */
.dc-kpi {
    border-radius:12px; padding:14px 16px;
    display:flex; flex-direction:column; gap:3px;
    border: 1px solid rgba(255,255,255,.1);
    position:relative; overflow:hidden;
    transition: transform .2s ease;
    background: rgba(255,255,255,.1);
    backdrop-filter: blur(6px);
}
.dc-kpi:hover { transform:translateY(-2px); }
.dc-kpi::before { content:''; position:absolute; top:0; left:0; right:0; height:2px; }
.kpi-total::before  { background: linear-gradient(90deg, var(--brand-lite), #e8d5ed); }
.kpi-crit::before   { background: #f87171; }
.kpi-alert::before  { background: #fbbf24; }
.kpi-ok::before     { background: #34d399; }
.kpi-crit  { background: rgba(220,38,38,.18); }
.kpi-alert { background: rgba(217,119,6,.18); }
.kpi-ok    { background: rgba(5,150,105,.18); }

.dc-kpi-icon { font-size:14px; color:rgba(255,255,255,.5); font-weight:700; line-height:1; }
.dc-kpi-num  { font-size:1.9rem; font-weight:800; color:rgba(255,255,255,.97); line-height:1; }
.dc-kpi-lbl  { font-size:10.5px; color:rgba(255,255,255,.45); font-weight:600; text-transform:uppercase; letter-spacing:.1em; }

/* ══════════════════════════════════════════
   TIPOGRAFÍA DE SECCIÓN
══════════════════════════════════════════ */
.dc-section-title {
    font-size:17px; font-weight:800; color: var(--text-1); margin:0; letter-spacing:-.01em;
}
.dc-section-sub { font-size:12.5px; color: var(--text-3); margin:3px 0 0; font-weight:400; }

/* ══════════════════════════════════════════
   SALUD — BADGE Y BARRAS
══════════════════════════════════════════ */
.dc-salud-badge {
    display:inline-flex; align-items:center; gap:6px; padding:5px 14px;
    border-radius:20px; font-size:12px; font-weight:700; white-space:nowrap;
}
.dc-salud-dot { width:7px; height:7px; border-radius:50%; animation:pip 1.8s ease-in-out infinite; flex-shrink:0; }
@keyframes pip { 0%,100%{opacity:1} 50%{opacity:.3} }

.salud-ok    { background:rgba(5,150,105,.1);  border:1px solid rgba(5,150,105,.25); color:#065f46; }
.salud-ok .dc-salud-dot { background:#10b981; }
.salud-alert { background:rgba(217,119,6,.1);  border:1px solid rgba(217,119,6,.25); color:#92400e; }
.salud-alert .dc-salud-dot { background:#f59e0b; }
.salud-crit  { background:rgba(220,38,38,.1);  border:1px solid rgba(220,38,38,.25); color:#991b1b; }
.salud-crit .dc-salud-dot { background:#ef4444; }

.dc-bar-row { display:flex; flex-direction:column; gap:6px; }
.dc-bar-meta { display:flex; align-items:center; justify-content:space-between; }
.dc-bar-label { font-size:13px; font-weight:600; color:var(--text-1); }
.dc-bar-count { font-size:11px; color:var(--text-3); }
.dc-bar-pct   { font-size:12px; font-weight:700; min-width:38px; text-align:right; }
.bar-ok    { color:var(--ok); }
.bar-alert { color:var(--alert); }
.bar-crit  { color:var(--crit); }

.dc-bar-track { height:9px; background: var(--brand-pale); border-radius:9px; overflow:hidden; }
.dc-bar-fill  { height:100%; border-radius:9px; width:0; transition:width 1.2s cubic-bezier(.22,1,.36,1); }
.bar-ok-fill    { background:linear-gradient(90deg,#34d399,#059669); }
.bar-alert-fill { background:linear-gradient(90deg,#fbbf24,#d97706); }
.bar-crit-fill  { background:linear-gradient(90deg,#f87171,#dc2626); }

/* ══════════════════════════════════════════
   FILTROS
══════════════════════════════════════════ */
.dc-filter {
    display:flex; align-items:center; gap:11px; padding:12px 14px;
    border-radius:12px; border:1.5px solid var(--border);
    background: var(--surf-2); font-size:13px; font-weight:600;
    color:var(--text-1); text-decoration:none;
    transition:all .22s ease;
}
.dc-filter:hover {
    background: var(--surface); border-color: var(--brand-lite);
    transform:translateX(3px); box-shadow:0 4px 16px rgba(106,44,117,.1);
}
.dc-filter-active {
    background: var(--brand-pale) !important;
    transform:translateX(3px);
    box-shadow:0 4px 16px rgba(106,44,117,.1);
}
.dc-filter-icon {
    width:30px; height:30px; border-radius:8px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:700;
}
.dc-filter-badge { font-size:11px; font-weight:700; padding:2px 9px; border-radius:10px; }
.dc-filter-arrow { opacity:.3; transition:transform .2s, opacity .2s; font-size:15px; }
.dc-filter:hover .dc-filter-arrow,
.dc-filter-active .dc-filter-arrow { opacity:1; transform:translateX(3px); }

/* All */
.flt-all.dc-filter-active  { border-color:var(--brand-lite); }
.flt-all-icon   { background:var(--brand-pale); color:var(--brand); }
.flt-all-badge  { background:var(--brand-pale); color:var(--brand); }
/* Crítico */
.flt-crit.dc-filter-active { border-color:rgba(220,38,38,.4); }
.flt-crit-icon   { background:rgba(220,38,38,.1); color:var(--crit); }
.flt-crit-badge  { background:rgba(220,38,38,.1); color:var(--crit); }
/* Alerta */
.flt-alert.dc-filter-active { border-color:rgba(217,119,6,.4); }
.flt-alert-icon  { background:rgba(217,119,6,.1);  color:var(--alert); }
.flt-alert-badge { background:rgba(217,119,6,.1);  color:var(--alert); }
/* Ok */
.flt-ok.dc-filter-active   { border-color:rgba(5,150,105,.4); }
.flt-ok-icon     { background:rgba(5,150,105,.1); color:var(--ok); }
.flt-ok-badge    { background:rgba(5,150,105,.1); color:var(--ok); }

/* ══════════════════════════════════════════
   TABLA
══════════════════════════════════════════ */
.dc-table-header {
    display:flex; align-items:flex-start; justify-content:space-between;
    gap:12px; padding:24px 24px 16px; flex-wrap:wrap;
}
.dc-clear-filter {
    font-size:12px; font-weight:600; color:var(--text-3);
    padding:5px 12px; border:1px solid var(--border);
    border-radius:8px; text-decoration:none; white-space:nowrap; transition:all .2s;
}
.dc-clear-filter:hover { color:var(--crit); border-color:rgba(220,38,38,.3); background:rgba(220,38,38,.04); }

.dc-table-wrap { overflow-x:auto; }
.dc-table { width:100%; border-collapse:collapse; font-size:13px; }
.dc-table thead tr { border-bottom:2px solid var(--border); }
.dc-table th {
    padding:10px 20px; text-align:left; font-size:10.5px; font-weight:700;
    text-transform:uppercase; letter-spacing:.12em; color:var(--brand-lite);
    white-space:nowrap;
}
.dc-table tbody tr { border-bottom:1px solid var(--border); transition:background .15s; }
.dc-table tbody tr:last-child { border-bottom:none; }
.dc-table tbody tr:hover { background: var(--surf-2); }
.row-crit:hover  { background:rgba(220,38,38,.03)!important; }
.row-alert:hover { background:rgba(217,119,6,.03)!important; }
.row-ok:hover    { background:rgba(5,150,105,.03)!important; }
.dc-table td { padding:13px 20px; vertical-align:middle; }

/* Código — color de marca */
.dc-codigo {
    font-size:13px; font-weight:800; color:var(--brand);
    letter-spacing:.02em;
}
.dc-nombre { font-size:13px; font-weight:500; color:var(--text-1); }
.dc-area-tag {
    display:inline-block; font-size:11px; font-weight:600; padding:3px 10px;
    border-radius:6px; background:var(--brand-pale); color:var(--brand);
    border:1px solid var(--brand-lite);
}

/* Días */
.dc-days { display:inline-flex; flex-direction:column; align-items:center; gap:1px; min-width:48px; }
.dc-days-num { font-size:16px; font-weight:800; line-height:1; }
.dc-days-lbl { font-size:9px; text-transform:uppercase; letter-spacing:.1em; font-weight:700; opacity:.7; }
.days-crit  .dc-days-num, .days-crit  .dc-days-lbl { color:var(--crit); }
.days-alert .dc-days-num, .days-alert .dc-days-lbl { color:var(--alert); }
.days-ok    .dc-days-num, .days-ok    .dc-days-lbl { color:var(--ok); }
.dc-days-none-txt { font-size:16px; color:var(--text-3); }

/* Badges */
.dc-badge {
    display:inline-flex; align-items:center; gap:5px;
    font-size:11px; font-weight:700; padding:4px 11px; border-radius:8px; white-space:nowrap;
}
.badge-crit  { background:rgba(220,38,38,.1);  color:#991b1b; border:1px solid rgba(220,38,38,.2); }
.badge-alert { background:rgba(217,119,6,.1);  color:#92400e; border:1px solid rgba(217,119,6,.2); }
.badge-ok    { background:rgba(5,150,105,.1);  color:#065f46; border:1px solid rgba(5,150,105,.2); }
.badge-none  { background:rgba(186,164,192,.2);color:var(--text-2); border:1px solid var(--border); }

.dc-empty-row td { padding:40px 24px; }
.dc-empty-state { display:flex; flex-direction:column; align-items:center; gap:8px; }
.dc-empty-state p { font-size:13px; color:var(--text-3); margin:0; }

/* ══════════════════════════════════════════
   DONUT
══════════════════════════════════════════ */
.dc-donut-wrap {
    position:relative; width:160px; height:160px; margin:0 auto 20px;
}
.dc-donut-center {
    position:absolute; inset:0; display:flex; flex-direction:column;
    align-items:center; justify-content:center; z-index:2; pointer-events:none;
}
.dc-donut-total { font-size:2rem; font-weight:800; color:var(--brand); line-height:1; }
.dc-donut-lbl   { font-size:10px; text-transform:uppercase; letter-spacing:.12em; color:var(--brand-lite); font-weight:600; margin-top:3px; }

.dc-legend { border-top:1px solid var(--border); padding-top:16px; display:flex; flex-direction:column; gap:10px; }
.dc-legend-row   { display:flex; align-items:center; gap:8px; }
.dc-legend-dot   { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.dc-legend-label { font-size:13px; color:var(--text-1); font-weight:500; flex:1; }
.dc-legend-count { font-size:13px; font-weight:700; color:var(--text-1); min-width:28px; text-align:right; }
.dc-legend-pct   { font-size:12px; font-weight:700; min-width:38px; text-align:right; }

.dc-insight {
    margin-top:16px; display:flex; gap:10px; align-items:flex-start;
    padding:12px 14px; border-radius:10px;
    background: var(--brand-pale); border:1px solid var(--brand-lite);
}
.dc-insight-icon { font-size:14px; color:var(--brand); font-weight:700; flex-shrink:0; padding-top:1px; }
.dc-insight-text { font-size:12px; color:var(--text-2); line-height:1.55; margin:0; }
.dc-insight-text strong { color:var(--brand); font-weight:700; }

/* Scrollbar */
::-webkit-scrollbar       { width:4px; height:4px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--brand-lite); border-radius:4px; }
::-webkit-scrollbar-thumb:hover { background:var(--brand); }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* Tilt 3D */
    document.querySelectorAll('.dc-panel-3d, .dc-header').forEach(el => {
        const face = el.querySelector('.dc-panel-face, .dc-header-face');
        if (!face) return;
        el.addEventListener('mousemove', e => {
            const r = face.getBoundingClientRect();
            const x = ((e.clientX - r.left) / r.width  - .5) * 5;
            const y = ((e.clientY - r.top)  / r.height - .5) * 4;
            face.style.transform = `perspective(900px) rotateY(${x}deg) rotateX(${-y}deg) translateY(-3px)`;
        });
        el.addEventListener('mouseleave', () => { face.style.transform = ''; });
    });

    /* Barras animadas */
    document.querySelectorAll('.dc-bar-fill').forEach(bar => {
        const w = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = w; }, 250);
    });

    /* Donut */
    Chart.defaults.font.family = "'Century Gothic','CenturyGothic','Nunito',sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#9b84a5';

    const ok    = {{ $stats['en_regla'] ?? 0 }};
    const alert = {{ $stats['alerta']   ?? 0 }};
    const crit  = {{ $stats['critico']  ?? 0 }};
    const total = ok + alert + crit;

    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: ['En regla','En alerta','Crítico'],
            datasets: [{
                data: total > 0 ? [ok, alert, crit] : [1,0,0],
                backgroundColor: ['#059669','#d97706','#dc2626'],
                borderColor:     ['#ffffff','#ffffff','#ffffff'],
                borderWidth: 3, hoverBorderWidth: 4, hoverOffset: 7,
            }]
        },
        options: {
            responsive: true, cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#501d59',
                    titleColor: 'rgba(255,255,255,.9)',
                    bodyColor: 'rgba(255,255,255,.65)',
                    padding: 12, cornerRadius: 10,
                    callbacks: {
                        label: i => ` ${i.raw} docs (${total>0?Math.round(i.raw/total*100):0}%)`
                    }
                }
            },
            animation: { animateRotate:true, duration:1000, easing:'easeOutQuart' }
        }
    });
});
</script>

</x-app-layout>