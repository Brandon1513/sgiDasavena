<x-app-layout>

@php
$user = auth()->user();
$roles = $user && method_exists($user, 'getRoleNames')
    ? $user->getRoleNames()->implode(', ')
    : '';
@endphp

{{-- ─── PANTALLA DE CARGA ─── --}}
<div id="sgi-loading" class="sgi-loading-screen">
    <div class="sgi-loading-inner">
        <div class="sgi-loading-ring">
            <img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="Dasavena" class="sgi-loading-logo">
        </div>
        <p class="sgi-loading-title">Sistema SGI</p>
        <p class="sgi-loading-sub">Cargando...</p>
    </div>
</div>

{{-- ─── TOAST BIENVENIDA ─── --}}
<div id="sgi-toast" role="status" aria-live="polite">
    <div class="sgi-toast-inner">
        <div class="sgi-toast-avatar">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</div>
        <div class="sgi-toast-body">
            <span class="sgi-toast-label">¡Hola de nuevo!</span>
            <span class="sgi-toast-name">{{ $user->name }}</span>
        </div>
        <span class="sgi-toast-dot" aria-hidden="true"></span>
    </div>
</div>

{{-- ─── BANNER ESPECIAL (id 33 o admin) ─── --}}
@if(auth()->user()->id == 33)
<div id="sgi-banner" class="sgi-banner">
    <div class="sgi-banner-inner">
        <span class="sgi-banner-icon">✦</span>
        <div>
            <p class="sgi-banner-title">Bienvenido a SGI</p>
            <p class="sgi-banner-sub">Plataforma de Gestión Integral</p>
        </div>
    </div>
</div>
@elseif(auth()->user()->hasRole('administrador_sgi'))
<div id="sgi-banner" class="sgi-banner">
    <div class="sgi-banner-inner">
        <span class="sgi-banner-icon">✦</span>
        <div>
            <p class="sgi-banner-title">Bienvenidas, Administradoras SGI</p>
            <p class="sgi-banner-sub">Plataforma de Gestión Integral</p>
        </div>
    </div>
</div>
<script>setTimeout(()=>{const b=document.getElementById('sgi-banner');if(!b)return;b.style.transition='opacity .4s';b.style.opacity='0';setTimeout(()=>b.remove(),400);},3000);</script>
@endif

{{-- ─── MARCA DE AGUA ─── --}}
<img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="" aria-hidden="true"
    class="sgi-watermark">

{{-- ─── FONDO + CONTENEDOR RAÍZ ─── --}}
<div class="sgi-root" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">
    <div class="sgi-overlay" aria-hidden="true"></div>

    <img src="https://dasavenasite.domcloud.dev/images/dasavena-logo.png" alt="" aria-hidden="true"
        class="sgi-bg-logo">

    <div class="sgi-container">

        {{-- ══════════════════════════════
             HERO
        ══════════════════════════════ --}}
        <header class="sgi-hero sgi-reveal" style="--ri:1">
            <div class="sgi-hero-accent-line"></div>
            <div class="sgi-hero-grid">
                <div class="sgi-hero-content">
                    <div class="sgi-eyebrow">
                        <span class="sgi-eyebrow-dot"></span>
                        Sistema de Gestión Integral
                    </div>
                    <h1 class="sgi-hero-title">
                        Hola, <br>
                        <em class="sgi-hero-name">{{ $user->name }}</em>
                    </h1>
                    <p class="sgi-hero-desc">
                        Bienvenido a <strong>DasavenaSGI</strong>.
                        Consulta el estado de tus solicitudes de formatos, aprobaciones y actividad reciente.
                    </p>
                    @if($roles)
                    <div class="sgi-role-badge">
                        <span class="sgi-role-pip"></span>
                        {{ $roles }}
                    </div>
                    @endif
                </div>
                <div class="sgi-hero-mascot">
                    <img src="/images/Valentia .png" alt="Valentia" class="sgi-valentia">
                </div>
            </div>
        </header>

        {{-- ══════════════════════════════
             TARJETAS DE ESTADO
        ══════════════════════════════ --}}
        <section class="sgi-stats-grid sgi-reveal" style="--ri:2" aria-label="Resumen de solicitudes">
            @foreach([
                ['label'=>'Pendientes',    'val'=>$pendientes??'—',   'theme'=>'purple', 'icon'=>'⏳', 'desc'=>'Esperando revisión del jefe'],
                ['label'=>'Aprobado jefe', 'val'=>$aprobadoJefe??'—','theme'=>'gold',   'icon'=>'✓',  'desc'=>'En proceso de gestión SGI'],
                ['label'=>'Atendidas',     'val'=>$atendidas??'—',    'theme'=>'green',  'icon'=>'◉',  'desc'=>'Formato actualizado con éxito'],
                ['label'=>'Rechazadas',    'val'=>$rechazadas??'—',   'theme'=>'rose',   'icon'=>'✕',  'desc'=>'Requieren revisión y reenvío'],
            ] as $s)
            <article class="stat-card stat-{{ $s['theme'] }}" tabindex="0" role="button" aria-label="{{ $s['label'] }}: {{ $s['val'] }}">
                <div class="stat-card-front">
                    <span class="stat-icon">{{ $s['icon'] }}</span>
                    <span class="stat-num">{{ $s['val'] }}</span>
                    <span class="stat-label">{{ $s['label'] }}</span>
                </div>
                <div class="stat-card-back" aria-hidden="true">
                    <p class="stat-back-text">{{ $s['desc'] }}</p>
                    <span class="stat-back-cta">Ver solicitudes →</span>
                </div>
            </article>
            @endforeach
        </section>

        {{-- ══════════════════════════════
             GRID PRINCIPAL
        ══════════════════════════════ --}}
        <div class="sgi-main-grid">

            {{-- ─── COLUMNA IZQUIERDA ─── --}}
            <div class="sgi-col-main">

            
                {{-- VIDEO --}}
                <div class="sgi-video-wrap sgi-reveal" style="--ri:5">
                    <video autoplay muted loop playsinline class="sgi-video">
                        <source src="/videos/animacion.mp4" type="video/mp4">
                    </video>
                </div>
                

                {{-- GRÁFICA --}}
                <div class="sgi-card sgi-reveal" style="--ri:3">
                    <div class="sgi-card-head">
                        <div>
                            <h2 class="sgi-card-title">Actividad del sistema</h2>
                            <p class="sgi-card-sub">Solicitudes registradas — últimos 30 días</p>
                        </div>
                        <div class="sgi-live-badge">
                            <span class="sgi-live-dot"></span>
                            En tiempo real
                        </div>
                    </div>
                    <div class="sgi-chart-wrap">
                        <canvas id="mainChart" role="img" aria-label="Gráfica de solicitudes de los últimos 30 días"></canvas>
                    </div>
                </div>

                {{-- ACCORDIONS --}}
                <div class="sgi-acc-grid sgi-reveal" style="--ri:4">
                    @foreach([
                    ['icon'=>'ti-clipboard-list','color'=>'indigo','title'=>'Solicitudes','sub'=>'Altas, bajas y actualizaciones','items'=>['Flujo completo: usuario → jefe → SGI.','Identifica solicitudes detenidas por etapa.']],
                    ['icon'=>'ti-file-description','color'=>'violet','title'=>'Documentos','sub'=>'Formatos SGI vigentes','items'=>['Control de versiones y fechas de alta.','Vincula documentos con solicitudes atendidas.']],
                    ['icon'=>'ti-shield-check','color'=>'teal','title'=>'Auditorías','sub'=>'Evidencia y trazabilidad','items'=>['Evidencia para control documental ISO/auditorías.']],
                    ] as $d)
                    <details class="acc acc-{{ $d['color'] }}">
                        <summary class="acc-head">
                            <div class="acc-head-left">
                                <div class="acc-icon"><i class="ti {{ $d['icon'] }}"></i></div>
                                <div>
                                    <p class="acc-title">{{ $d['title'] }}</p>
                                    <p class="acc-sub">{{ $d['sub'] }}</p>
                                </div>
                            </div>
                            <span class="acc-chevron">›</span>
                        </summary>
                        <div class="acc-body">
                            @foreach($d['items'] as $item)
                            <div class="acc-item">
                                <span class="acc-bullet"></span>
                                <p>{{ $item }}</p>
                            </div>
                            @endforeach
                        </div>
                    </details>
                    @endforeach
                </div>


            </div>

            {{-- ─── COLUMNA DERECHA ─── --}}
            <div class="sgi-col-side">

                {{-- ACTIVIDAD RECIENTE --}}
                <div class="sgi-card sgi-reveal" style="--ri:3">
                    <div class="sgi-card-head">
                        <div>
                            <h2 class="sgi-card-title">Actividad reciente</h2>
                            <p class="sgi-card-sub">Últimas acciones en el sistema</p>
                        </div>
                    </div>
                    @if(!empty($ultimasSolicitudes) && count($ultimasSolicitudes))
                    <ul class="act-list">
                        @foreach($ultimasSolicitudes as $item)
                        <li class="act-item">
                            <div class="act-avatar">
                                {{ strtoupper(mb_substr($item->usuario->name ?? 'S', 0, 1)) }}
                            </div>
                            <div class="act-body">
                                <p class="act-name">{{ $item->usuario->name ?? 'Usuario' }}</p>
                                <div class="act-meta">
                                    <span class="act-status">{{ ucfirst(str_replace('_', ' ', $item->estado)) }}</span>
                                    <span class="act-action">{{ $item->accion }}</span>
                                </div>
                                @if($item->comentarios)
                                <p class="act-comment">"{{ \Illuminate\Support\Str::limit($item->comentarios, 55) }}"</p>
                                @endif
                                <time class="act-time">{{ $item->created_at?->format('d/m/Y H:i') }}</time>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="sgi-empty">
                        <span class="sgi-empty-icon">🌙</span>
                        <p>Sin actividad reciente</p>
                    </div>
                    @endif
                </div>

                {{-- ACCESOS RÁPIDOS --}}
                <div class="sgi-card sgi-reveal" style="--ri:4">
                    <div class="sgi-card-head">
                        <div>
                            <h2 class="sgi-card-title">Accesos rápidos</h2>
                            <p class="sgi-card-sub">Navegación directa al sistema</p>
                        </div>
                    </div>
                    <nav class="ql-nav" aria-label="Accesos rápidos">
                        <a href="{{ route('solicitudes.create') }}" class="ql-link ql-primary">
                            <span class="ql-icon-box">+</span>
                            <span class="ql-text">Crear nueva solicitud</span>
                            <span class="ql-arr" aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('solicitudes.index') }}" class="ql-link">
                            <i class="ti ti-clipboard-list ql-emoji" aria-hidden="true"></i>
                            <span class="ql-text">Ver todas las solicitudes</span>
                            <span class="ql-arr" aria-hidden="true">→</span>
                        </a>
                        @if($user->hasRole('jefe'))
                        <a href="{{ route('solicitudes.index', ['estado' => 'pendiente']) }}" class="ql-link ql-amber">
                            <i class="ti ti-clock ql-emoji" aria-hidden="true"></i>
                            <span class="ql-text">Pendientes por aprobar</span>
                            <span class="ql-arr" aria-hidden="true">→</span>
                        </a>
                        @endif
                        @if($user->hasRole('administrador_sgi'))
                        <a href="{{ route('solicitudes.index', ['estado' => 'aprobado_jefe']) }}" class="ql-link ql-teal">
                            <i class="ti ti-file-check ql-emoji" aria-hidden="true"></i>
                            <span class="ql-text">Listas para alta SGI</span>
                            <span class="ql-arr" aria-hidden="true">→</span>
                        </a>
                        @endif
                    </nav>
                </div>

            </div>
        </div>

        {{-- ══════════════════════════════
             PIE — TARJETAS INFORMATIVAS
        ══════════════════════════════ --}}
        <section class="sgi-info-grid sgi-reveal" style="--ri:6" aria-label="Características del sistema">
            @foreach([
            ['icon'=>'ti-zoom-in','color'=>'sky','title'=>'Trazabilidad completa','text'=>'Cada solicitud genera evidencia de auditoría. Registra quién, cuándo y qué cambió en cada formato del sistema.'],
            ['icon'=>'ti-settings','color'=>'violet','title'=>'Flujo estandarizado','text'=>'Un solo proceso aprobado: el usuario solicita, el jefe aprueba, SGI ejecuta y cierra el ciclo documental.'],
            ['icon'=>'ti-trending-up','color'=>'emerald','title'=>'Mejora continua','text'=>'Identifica patrones en solicitudes, detecta áreas con más cambios y mide el tiempo de respuesta del equipo.'],
            ] as $f)
            <div class="info-card info-{{ $f['color'] }}">
                <div class="info-body">
                    <div class="info-icon-wrap"><i class="ti {{ $f['icon'] }}" aria-hidden="true"></i></div>
                    <h3 class="info-title">{{ $f['title'] }}</h3>
                    <p class="info-text">{{ $f['text'] }}</p>
                </div>
                <div class="info-bar"></div>
            </div>
            @endforeach
        </section>

    </div>{{-- /sgi-container --}}
</div>{{-- /sgi-root --}}

{{-- ─────────────────────────────────────────────
     ESTILOS
───────────────────────────────────────────── --}}
<style>
/* ── Reset & Base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --purple: #6A2C75;
    --purple-light: #8E3D9E;
    --purple-dark: #4a1f55;
    --gold: #D6A644;
    --gold-dark: #b38600;
    --white: #ffffff;
    --glass-bg: rgba(255,255,255,.72);
    --glass-border: rgba(255,255,255,.88);
    --glass-blur: blur(24px) saturate(160%);
    --radius-sm: 12px;
    --radius-md: 18px;
    --radius-lg: 24px;
    --shadow-card: 0 2px 20px rgba(106,44,117,.08), 0 1px 4px rgba(0,0,0,.06);
    --shadow-card-hover: 0 8px 40px rgba(106,44,117,.15), 0 2px 8px rgba(0,0,0,.08);
    --font: 'Century Gothic', 'Trebuchet MS', sans-serif;
    --transition: .22s cubic-bezier(.22,1,.36,1);
}

/* ── Loading ── */
.sgi-loading-screen {
    position: fixed; inset: 0; z-index: 9999;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 50%, var(--purple-light) 100%);
    transition: opacity .6s var(--transition);
}
.sgi-loading-inner { display: flex; flex-direction: column; align-items: center; gap: 16px; }
.sgi-loading-ring {
    position: relative; width: 80px; height: 80px;
}
.sgi-loading-ring::after {
    content: ''; position: absolute; inset: -6px;
    border-radius: 50%;
    border: 3px solid transparent;
    border-top-color: var(--gold);
    border-right-color: var(--gold);
    animation: spin .9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.sgi-loading-logo { width: 80px; height: 80px; object-fit: contain; animation: pulse-soft 1.8s ease-in-out infinite; }
@keyframes pulse-soft { 0%,100%{opacity:1} 50%{opacity:.65} }
.sgi-loading-title { color: #fff; font-family: var(--font); font-size: 18px; font-weight: 700; letter-spacing: .04em; }
.sgi-loading-sub   { color: var(--gold); font-family: var(--font); font-size: 12px; }

/* ── Toast ── */
#sgi-toast {
    position: fixed; top: 20px; right: 20px; z-index: 8888;
    background: rgba(255,255,255,.88);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-md);
    backdrop-filter: var(--glass-blur);
    box-shadow: 0 8px 40px rgba(106,44,117,.18);
    opacity: 0; transform: translateY(-14px) scale(.95);
    transition: opacity .4s var(--transition), transform .4s var(--transition);
    overflow: hidden;
}
#sgi-toast::after { content: ''; display: block; height: 3px; background: linear-gradient(90deg, var(--purple), var(--gold)); }
#sgi-toast.show { opacity: 1; transform: translateY(0) scale(1); }
#sgi-toast.hide { opacity: 0; transform: translateY(-10px) scale(.95); pointer-events: none; }
.sgi-toast-inner { display: flex; align-items: center; gap: 12px; padding: 12px 16px; }
.sgi-toast-avatar {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--purple-light), var(--purple-dark));
    color: #fff; font-family: var(--font); font-size: 14px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
}
.sgi-toast-body { display: flex; flex-direction: column; gap: 2px; }
.sgi-toast-label { font-size: 11px; font-weight: 700; color: var(--gold-dark); letter-spacing: .06em; text-transform: uppercase; }
.sgi-toast-name  { font-size: 13px; color: #555; font-family: var(--font); }
.sgi-toast-dot {
    width: 8px; height: 8px; border-radius: 50%; background: var(--gold);
    flex-shrink: 0; box-shadow: 0 0 0 3px rgba(214,166,68,.2);
    animation: live-pulse 2s ease-in-out infinite;
}
@keyframes live-pulse { 0%,100%{box-shadow:0 0 0 3px rgba(214,166,68,.2)} 50%{box-shadow:0 0 0 6px rgba(214,166,68,.04)} }

/* ── Banner admin ── */
.sgi-banner {
    position: fixed; inset-x: 0; top: 16px; z-index: 8000;
    display: flex; justify-content: center; pointer-events: none;
}
.sgi-banner-inner {
    display: flex; align-items: center; gap: 12px;
    background: rgba(10,10,20,.82); border: 1px solid rgba(255,255,255,.15);
    border-radius: 100px; padding: 10px 22px;
    backdrop-filter: var(--glass-blur);
    box-shadow: 0 8px 40px rgba(0,0,0,.3);
    pointer-events: auto;
}
.sgi-banner-icon { color: var(--gold); font-size: 18px; }
.sgi-banner-title { color: #fff; font-size: 13px; font-weight: 700; }
.sgi-banner-sub   { color: rgba(255,255,255,.5); font-size: 11px; }

/* ── Watermark & BG Logo ── */
.sgi-watermark {
    pointer-events: none; position: fixed; bottom: 20px; right: 20px;
    width: 80px; height: 80px; object-fit: contain; z-index: 5;
    opacity: .15; mix-blend-mode: multiply; user-select: none;
}
.sgi-bg-logo {
    pointer-events: none; position: fixed; inset: 0; margin: auto;
    width: 320px; height: 320px; object-fit: contain; z-index: 0;
    opacity: .05; mix-blend-mode: multiply; user-select: none;
}

/* ── Root & Overlay ── */
.sgi-root {
    min-height: 100vh; position: relative;
    background-attachment: fixed; background-size: cover; background-position: center;
}
.sgi-overlay {
    pointer-events: none; position: fixed; inset: 0; z-index: 1;
    background: radial-gradient(ellipse 90% 55% at 50% -5%, rgba(255,255,255,.55) 0%, transparent 65%);
}

/* ── Container ── */
.sgi-container {
    position: relative; z-index: 10;
    max-width: 1320px; margin: 0 auto;
    padding: 40px 24px 60px;
    display: flex; flex-direction: column; gap: 24px;
}

/* ── Reveal animation ── */
.sgi-reveal {
    opacity: 0; transform: translateY(20px);
    animation: sgi-in .55s cubic-bezier(.22,1,.36,1) forwards;
    animation-delay: calc(var(--ri, 1) * .07s);
}
@keyframes sgi-in { to { opacity: 1; transform: translateY(0); } }

/* ══════════════════════════════
   HERO
══════════════════════════════ */
.sgi-hero {
    position: relative; overflow: hidden;
    background-color: var(--purple);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255,255,255,.15);
    box-shadow: 0 4px 40px rgba(106,44,117,.3), 0 1px 0 rgba(255,255,255,.1) inset;
}

/* Linha dourada — element signature */
.sgi-hero-accent-line {
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, rgba(255,255,255,.8) 50%, var(--gold) 70%, transparent 100%);
    background-size: 200% 100%;
    animation: gold-shimmer 3.5s ease-in-out infinite;
}
@keyframes gold-shimmer { 0%{background-position:100% 0} 100%{background-position:-100% 0} }

/* Dot pattern overlay */
.sgi-hero::after {
    content: ''; pointer-events: none; position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.07) 1.5px, transparent 1.5px);
    background-size: 22px 22px;
    border-radius: var(--radius-lg);
}

.sgi-hero-grid {
    display: grid; grid-template-columns: 1fr auto;
    align-items: center; gap: 32px;
    padding: 40px 48px;
    position: relative; z-index: 1;
}
.sgi-hero-content { display: flex; flex-direction: column; gap: 16px; }

.sgi-eyebrow {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 11px; text-transform: uppercase; letter-spacing: .2em;
    color: var(--gold); font-weight: 700;
}
.sgi-eyebrow-dot {
    width: 7px; height: 7px; border-radius: 50%; background: var(--gold);
    box-shadow: 0 0 0 3px rgba(214,166,68,.3);
    animation: live-pulse 1.8s ease-in-out infinite;
}

.sgi-hero-title {
    font-family: var(--font); font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 800; line-height: 1.08; color: rgba(255,255,255,.55);
    letter-spacing: -.02em;
}
.sgi-hero-name {
    font-style: normal; color: #fff;
    display: inline-block;
    border-bottom: 2px solid var(--gold);
    padding-bottom: 2px;
}
.sgi-hero-desc {
    font-size: .9rem; color: rgba(255,255,255,.72); line-height: 1.7;
    max-width: 480px;
}
.sgi-hero-desc strong { color: #fff; font-weight: 700; }

.sgi-role-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(214,166,68,.15); border: 1px solid rgba(214,166,68,.35);
    border-radius: 100px; padding: 5px 14px;
    font-size: 12px; color: var(--gold); font-weight: 600;
}
.sgi-role-pip { width: 5px; height: 5px; border-radius: 50%; background: var(--gold); }

.sgi-hero-mascot { display: flex; align-items: center; justify-content: center; }
.sgi-valentia {
    width: 220px; height: auto; object-fit: contain;
    mix-blend-mode: multiply;
    filter: drop-shadow(0 8px 32px rgba(0,0,0,.15));
    animation: float 4s ease-in-out infinite;
}
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

/* ══════════════════════════════
   STAT CARDS (flip)
══════════════════════════════ */
.sgi-stats-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
}
.stat-card {
    height: 152px; cursor: pointer; perspective: 1200px;
    border-radius: var(--radius-md); border: none; background: none;
    position: relative;
}
.stat-card-front, .stat-card-back {
    position: absolute; inset: 0; border-radius: var(--radius-md);
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
    padding: 20px 16px;
    backface-visibility: hidden; -webkit-backface-visibility: hidden;
    border: 1px solid rgba(255,255,255,.6);
    box-shadow: var(--shadow-card);
    transition: box-shadow var(--transition);
}
.stat-card-back { transform: rotateY(180deg); }
.stat-card:hover .stat-card-front { transform: rotateY(-180deg); box-shadow: var(--shadow-card-hover); }
.stat-card:hover .stat-card-back  { transform: rotateY(0deg);    box-shadow: var(--shadow-card-hover); }
.stat-card-front, .stat-card-back { transition: transform .65s cubic-bezier(.68,-.35,.265,1.4), box-shadow var(--transition); }

.stat-icon   { font-size: 18px; opacity: .6; }
.stat-num    { font-family: var(--font); font-size: 3rem; font-weight: 900; line-height: 1; letter-spacing: -.04em; }
.stat-label  { font-size: 10px; text-transform: uppercase; letter-spacing: .18em; font-weight: 700; opacity: .7; }
.stat-back-text { font-size: 12.5px; text-align: center; line-height: 1.55; font-weight: 500; }
.stat-back-cta  { font-size: 10px; text-transform: uppercase; letter-spacing: .1em; font-weight: 700; opacity: .6; margin-top: 4px; }

/* Purple */
.stat-purple .stat-card-front { background: linear-gradient(145deg, rgba(106,44,117,.1), rgba(142,61,158,.06)); }
.stat-purple .stat-num   { color: var(--purple); }
.stat-purple .stat-icon  { color: var(--purple); }
.stat-purple .stat-label { color: var(--purple); }
.stat-purple .stat-card-back  { background: linear-gradient(145deg, rgba(106,44,117,.16), rgba(142,61,158,.1)); }
.stat-purple .stat-back-text  { color: #4a2255; }
.stat-purple .stat-back-cta   { color: var(--purple); }

/* Gold */
.stat-gold .stat-card-front { background: linear-gradient(145deg, rgba(214,166,68,.12), rgba(240,200,80,.06)); }
.stat-gold .stat-num   { color: var(--gold-dark); }
.stat-gold .stat-icon  { color: var(--gold-dark); }
.stat-gold .stat-label { color: var(--gold-dark); }
.stat-gold .stat-card-back   { background: linear-gradient(145deg, rgba(214,166,68,.18), rgba(240,200,80,.1)); }
.stat-gold .stat-back-text   { color: #7a5a00; }
.stat-gold .stat-back-cta    { color: var(--gold-dark); }

/* Green */
.stat-green .stat-card-front { background: linear-gradient(145deg, rgba(5,150,105,.1), rgba(16,185,129,.06)); }
.stat-green .stat-num   { color: #065f46; }
.stat-green .stat-icon  { color: #059669; }
.stat-green .stat-label { color: #065f46; }
.stat-green .stat-card-back  { background: linear-gradient(145deg, rgba(5,150,105,.15), rgba(16,185,129,.1)); }
.stat-green .stat-back-text  { color: #064e3b; }
.stat-green .stat-back-cta   { color: #065f46; }

/* Rose */
.stat-rose .stat-card-front { background: linear-gradient(145deg, rgba(190,18,60,.09), rgba(244,63,94,.05)); }
.stat-rose .stat-num   { color: #be123c; }
.stat-rose .stat-icon  { color: #f43f5e; }
.stat-rose .stat-label { color: #be123c; }
.stat-rose .stat-card-back   { background: linear-gradient(145deg, rgba(190,18,60,.14), rgba(244,63,94,.09)); }
.stat-rose .stat-back-text   { color: #7f1d3a; }
.stat-rose .stat-back-cta    { color: #be123c; }

/* ══════════════════════════════
   CARDS GLASS
══════════════════════════════ */
.sgi-card {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    transition: box-shadow var(--transition), transform var(--transition);
}
.sgi-card:hover { box-shadow: var(--shadow-card-hover); }

.sgi-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 24px 24px 0; }
.sgi-card-title { font-family: var(--font); font-size: 16px; font-weight: 700; color: #1e1b4b; }
.sgi-card-sub   { font-size: 12px; color: #9ca3af; margin-top: 2px; }
.sgi-live-badge {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    padding: 4px 12px; border-radius: 100px;
    background: rgba(5,150,105,.1); border: 1px solid rgba(5,150,105,.2);
    font-size: 11px; font-weight: 600; color: #065f46; white-space: nowrap;
}
.sgi-live-dot { width: 6px; height: 6px; border-radius: 50%; background: #10b981; animation: live-pulse 1.6s ease-in-out infinite; }

/* ── Main grid layout ── */
.sgi-main-grid {
    display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;
}
.sgi-col-main { display: flex; flex-direction: column; gap: 20px; }
.sgi-col-side  { display: flex; flex-direction: column; gap: 20px; }

/* ── Chart ── */
.sgi-chart-wrap { padding: 20px 24px 24px; }

/* ── Accordions ── */
.sgi-acc-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.acc {
    background: rgba(255,255,255,.62); border: 1px solid rgba(255,255,255,.88);
    border-radius: var(--radius-sm); backdrop-filter: blur(16px); overflow: hidden;
    transition: border-color var(--transition), box-shadow var(--transition);
}
.acc[open], .acc:hover { box-shadow: 0 6px 24px rgba(0,0,0,.08); }
.acc-indigo[open], .acc-indigo:hover { border-color: rgba(99,102,241,.4); }
.acc-violet[open], .acc-violet:hover { border-color: rgba(124,58,237,.4); }
.acc-teal[open], .acc-teal:hover     { border-color: rgba(20,184,166,.4); }

.acc-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; list-style: none; cursor: pointer; user-select: none; gap: 10px; }
.acc-head::-webkit-details-marker { display: none; }
.acc-head-left { display: flex; align-items: center; gap: 10px; }
.acc-icon {
    width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
    background: rgba(255,255,255,.8); border: 1px solid rgba(0,0,0,.07);
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; color: var(--purple);
}
.acc-indigo .acc-icon { color: #4338ca; }
.acc-violet .acc-icon { color: #7c3aed; }
.acc-teal   .acc-icon { color: #0f766e; }

.acc-title  { font-size: 13px; font-weight: 700; color: #1e1b4b; }
.acc-sub    { font-size: 11px; color: #9ca3af; margin-top: 1px; }
.acc-chevron { font-size: 16px; color: #9ca3af; transition: transform .3s var(--transition); line-height: 1; }
details[open] .acc-chevron { transform: rotate(90deg); }
.acc-body { padding: 0 14px 14px; border-top: 1px solid rgba(0,0,0,.06); padding-top: 10px; }
.acc-item { display: flex; align-items: flex-start; gap: 8px; padding: 3px 0; }
.acc-bullet { width: 4px; height: 4px; border-radius: 50%; background: var(--purple-light); flex-shrink: 0; margin-top: 6px; opacity: .6; }
.acc-item p { font-size: 12px; color: #6b7280; line-height: 1.55; }

/* ── Video ── */
.sgi-video-wrap { display: flex; justify-content: center; padding: 8px 0; }
.sgi-video { width: 100%; max-width: 600px; height: auto; mix-blend-mode: multiply; border-radius: var(--radius-md); }

/* ── Activity list ── */
.act-list { list-style: none; display: flex; flex-direction: column; gap: 2px; padding: 16px 24px 20px; max-height: 340px; overflow-y: auto; }
.act-item { display: flex; gap: 10px; align-items: flex-start; padding: 9px 10px; border-radius: var(--radius-sm); transition: background var(--transition); }
.act-item:hover { background: rgba(99,102,241,.05); }
.act-avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #818cf8, #4f46e5);
    color: #fff; font-size: 12px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--font);
}
.act-body { flex: 1; min-width: 0; }
.act-name   { font-size: 13px; font-weight: 700; color: #1e1b4b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.act-meta   { display: flex; align-items: center; gap: 6px; margin-top: 3px; flex-wrap: wrap; }
.act-status { font-size: 10px; background: rgba(99,102,241,.1); color: #4338ca; padding: 1px 8px; border-radius: 100px; font-weight: 700; }
.act-action { font-size: 10px; color: #9ca3af; }
.act-comment{ font-size: 11px; color: #9ca3af; font-style: italic; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.act-time   { font-size: 10px; color: #d1d5db; margin-top: 3px; display: block; }

.sgi-empty { text-align: center; padding: 40px 0; }
.sgi-empty-icon { font-size: 30px; display: block; margin-bottom: 8px; opacity: .4; }
.sgi-empty p    { font-size: 13px; color: #9ca3af; }

/* ── Quick links ── */
.ql-nav { display: flex; flex-direction: column; gap: 8px; padding: 16px 20px 20px; }
.ql-link {
    display: flex; align-items: center; gap: 10px; padding: 11px 14px;
    border-radius: var(--radius-sm);
    background: rgba(255,255,255,.55); border: 1px solid rgba(255,255,255,.9);
    font-size: 13px; font-weight: 500; color: #374151; text-decoration: none;
    backdrop-filter: blur(8px); box-shadow: 0 1px 4px rgba(0,0,0,.04);
    transition: all var(--transition);
}
.ql-link:hover { background: rgba(255,255,255,.9); border-color: rgba(99,102,241,.3); color: #4338ca; box-shadow: 0 4px 16px rgba(99,102,241,.12); transform: translateY(-2px); }
.ql-link:hover .ql-arr { transform: translateX(4px); opacity: 1; }
.ql-primary { background: rgba(106,44,117,.1); border-color: rgba(106,44,117,.28); color: var(--purple); }
.ql-primary:hover { background: rgba(106,44,117,.18); border-color: rgba(106,44,117,.4); color: var(--purple-dark); }
.ql-amber { background: rgba(254,243,199,.8); border-color: rgba(253,211,77,.4); color: #92400e; }
.ql-teal  { background: rgba(204,251,241,.8); border-color: rgba(94,234,212,.4); color: #065f46; }
.ql-icon-box {
    width: 26px; height: 26px; border-radius: 7px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--purple-light), var(--purple-dark));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 900; line-height: 1;
    box-shadow: 0 3px 10px rgba(106,44,117,.3);
}
.ql-emoji { font-size: 15px; flex-shrink: 0; color: inherit; }
.ql-arr  { margin-left: auto; opacity: .3; transition: transform var(--transition), opacity var(--transition); }

/* ══════════════════════════════
   INFO CARDS
══════════════════════════════ */
.sgi-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.info-card {
    border-radius: var(--radius-md); overflow: hidden;
    border: 1px solid rgba(255,255,255,.88);
    backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur);
    box-shadow: var(--shadow-card);
    transition: transform var(--transition), box-shadow var(--transition);
}
.info-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-card-hover); }
.info-sky     { background: rgba(224,242,254,.82); }
.info-violet  { background: rgba(237,233,254,.82); }
.info-emerald { background: rgba(209,250,229,.82); }
.info-body { padding: 22px 22px 18px; }
.info-icon-wrap {
    width: 42px; height: 42px; border-radius: 12px; margin-bottom: 14px;
    background: rgba(255,255,255,.75); border: 1px solid rgba(0,0,0,.07);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; color: var(--purple);
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}
.info-sky .info-icon-wrap     { color: #0369a1; }
.info-violet .info-icon-wrap  { color: #7c3aed; }
.info-emerald .info-icon-wrap { color: #065f46; }
.info-title { font-family: var(--font); font-size: 14px; font-weight: 700; color: #1e1b4b; margin-bottom: 8px; }
.info-text  { font-size: 12.5px; color: #4b5563; line-height: 1.65; }
.info-bar { height: 3px; }
.info-sky .info-bar     { background: linear-gradient(90deg, rgba(14,165,233,.6), rgba(56,189,248,.3)); }
.info-violet .info-bar  { background: linear-gradient(90deg, rgba(124,58,237,.6), rgba(167,139,250,.3)); }
.info-emerald .info-bar { background: linear-gradient(90deg, rgba(5,150,105,.6), rgba(16,185,129,.3)); }

/* ── Scrollbar ── */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(106,44,117,.2); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: rgba(106,44,117,.35); }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .sgi-main-grid { grid-template-columns: 1fr; }
    .sgi-col-side  { order: -1; }
    .sgi-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .sgi-info-grid  { grid-template-columns: repeat(1, 1fr); }
}
@media (max-width: 640px) {
    .sgi-container { padding: 20px 16px 40px; gap: 16px; }
    .sgi-hero-grid { grid-template-columns: 1fr; padding: 28px 24px; }
    .sgi-hero-mascot { display: none; }
    .sgi-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .sgi-acc-grid   { grid-template-columns: 1fr; }
    .sgi-info-grid  { grid-template-columns: 1fr; }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
}
</style>

{{-- ─────────────────────────────────────────────
     SCRIPTS
───────────────────────────────────────────── --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
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

/* ── Toast ── */
document.addEventListener('DOMContentLoaded', () => {
    const t = document.getElementById('sgi-toast');
    if (!t) return;
    setTimeout(() => t.classList.add('show'), 500);
    setTimeout(() => t.classList.add('hide'), 4200);
});

/* ── Tilt 3D en cards stat ── */
document.querySelectorAll('.stat-card, .info-card').forEach(card => {
    card.addEventListener('mousemove', e => {
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - .5;
        const y = (e.clientY - r.top)  / r.height - .5;
        card.style.transform = `perspective(700px) rotateY(${x * 8}deg) rotateX(${-y * 6}deg)`;
    });
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(700px) rotateY(0) rotateX(0)';
    });
});

/* ── Gráfica ── */
const diasLabels = @json($porDia->pluck('fecha'));
const diasData   = @json($porDia->pluck('total'));

Chart.defaults.font.family = "'Century Gothic', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#9ca3af';

new Chart(document.getElementById('mainChart'), {
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
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(255,255,255,.97)',
                titleColor: '#1e1b4b', bodyColor: '#4b5563',
                borderColor: 'rgba(106,44,117,.2)', borderWidth: 1,
                padding: 12, cornerRadius: 10,
                callbacks: {
                    label: item => ` ${item.raw} solicitudes`
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { maxTicksLimit: 8, color: '#d1d5db' } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' }, ticks: { color: '#d1d5db', padding: 6 } }
        }
    }
});
</script>

</x-app-layout>