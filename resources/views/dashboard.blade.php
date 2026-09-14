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
        <p class="sgi-loading-sub">// inicializando_módulos</p>
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
        <span class="sgi-banner-icon"><i class="ti ti-sparkles"></i></span>
        <div>
            <p class="sgi-banner-title">Bienvenido a SGI</p>
            <p class="sgi-banner-sub">Plataforma de Gestión Integral</p>
        </div>
    </div>
</div>
@elseif(auth()->user()->hasRole('administrador_sgi'))
<div id="sgi-banner" class="sgi-banner">
    <div class="sgi-banner-inner">
        <span class="sgi-banner-icon"><i class="ti ti-sparkles"></i></span>
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
        <header class="sgi-hero hud-corners sgi-reveal" style="--ri:1">
            <div class="sgi-hero-accent-line"></div>
            <div class="sgi-hero-readout">
                <span>SGI</span><span class="sgi-hero-readout-sep">//</span><span>{{ now()->format('d.m.Y') }}</span>
                <span class="sgi-hero-readout-dot"></span>
            </div>
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
                    <div class="sgi-hero-mascot-ring" aria-hidden="true"></div>
                    <img src="/images/Valentia .png" alt="Valentia" class="sgi-valentia">
                </div>
            </div>
        </header>

        {{-- ══════════════════════════════
             TARJETAS DE ESTADO
        ══════════════════════════════ --}}
        <section class="sgi-stats-grid sgi-reveal" style="--ri:2" aria-label="Resumen de solicitudes">
            @foreach([
                ['label'=>'Pendientes',    'val'=>$pendientes??'—',   'theme'=>'purple', 'icon'=>'ti-hourglass-high',  'desc'=>'Esperando revisión del jefe',        'cta'=>'Ver solicitudes'],
                ['label'=>'Aprobado jefe', 'val'=>$aprobadoJefe??'—','theme'=>'gold',   'icon'=>'ti-rosette-discount-check', 'desc'=>'En proceso de gestión SGI',           'cta'=>'Ver solicitudes'],
                ['label'=>'Atendidas',     'val'=>$atendidas??'—',    'theme'=>'green',  'icon'=>'ti-circle-check',    'desc'=>'Formato actualizado con éxito',       'cta'=>'Ver solicitudes'],
                ['label'=>'Rechazadas',    'val'=>$rechazadas??'—',   'theme'=>'rose',   'icon'=>'ti-circle-x',       'desc'=>'Requieren revisión y reenvío',        'cta'=>'Ver solicitudes'],
                ['label'=>'Por vencer',    'val'=>$totalPorVencer??'—','theme'=>'amber', 'icon'=>'ti-alert-triangle', 'desc'=>'Documentos con vigencia próxima a expirar', 'cta'=>'Ver calendario'],
            ] as $s)
            <article class="stat-card hud-corners stat-{{ $s['theme'] }}" tabindex="0" role="button" aria-label="{{ $s['label'] }}: {{ $s['val'] }}">
                <div class="stat-card-front">
                    <span class="hud-tag">0{{ $loop->iteration }}</span>
                    <span class="stat-icon-chip"><i class="ti {{ $s['icon'] }}"></i></span>
                    <span class="stat-num" data-count="{{ is_numeric($s['val']) ? $s['val'] : '' }}">{{ is_numeric($s['val']) ? 0 : $s['val'] }}</span>
                    <span class="stat-label">{{ $s['label'] }}</span>
                </div>
                <div class="stat-card-back" aria-hidden="true">
                    <span class="stat-icon-chip stat-icon-chip-ghost"><i class="ti {{ $s['icon'] }}"></i></span>
                    <p class="stat-back-text">{{ $s['desc'] }}</p>
                    <span class="stat-back-cta">{{ $s['cta'] }} <i class="ti ti-arrow-right"></i></span>
                </div>
            </article>
            @endforeach
        </section>

        {{-- ══════════════════════════════
             BENTO PRINCIPAL
        ══════════════════════════════ --}}
        <div class="sgi-bento sgi-reveal" style="--ri:3">

            {{-- GRÁFICA --}}
            <div class="sgi-card bento-tile bento-chart hud-corners">
                <span class="hud-tag">01</span>
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

            {{-- CULTURA / VIDEO --}}
            <div class="sgi-card bento-tile bento-video hud-corners">
                <span class="hud-tag">02</span>
                <div class="sgi-card-head">
                    <div>
                        <h2 class="sgi-card-title">Cultura Dasavena</h2>
                        <p class="sgi-card-sub">Nuestra identidad en movimiento</p>
                    </div>
                </div>
                <div class="sgi-video-wrap">
                    <video autoplay muted loop playsinline class="sgi-video">
                        <source src="{{ asset('Videos/Animacion.mp4') }}" type="video/mp4">
                    </video>
                </div>
            </div>

            {{-- ACCORDIONS --}}
            @foreach([
            ['icon'=>'ti-clipboard-list','color'=>'indigo','title'=>'Solicitudes','sub'=>'Altas, bajas y actualizaciones','items'=>['Flujo completo: usuario → jefe → SGI.','Identifica solicitudes detenidas por etapa.']],
            ['icon'=>'ti-file-description','color'=>'violet','title'=>'Documentos','sub'=>'Formatos SGI vigentes','items'=>['Control de versiones y fechas de alta.','Vincula documentos con solicitudes atendidas.']],
            ['icon'=>'ti-shield-check','color'=>'teal','title'=>'Auditorías','sub'=>'Evidencia y trazabilidad','items'=>['Evidencia para control documental ISO/auditorías.']],
            ] as $d)
            <details class="acc bento-tile bento-acc hud-corners acc-{{ $d['color'] }}">
                <span class="hud-tag">0{{ $loop->iteration + 2 }}</span>
                <summary class="acc-head">
                    <div class="acc-head-left">
                        <div class="acc-icon"><i class="ti {{ $d['icon'] }}"></i></div>
                        <div>
                            <p class="acc-title">{{ $d['title'] }}</p>
                            <p class="acc-sub">{{ $d['sub'] }}</p>
                        </div>
                    </div>
                    <span class="acc-chevron" aria-hidden="true"><i class="ti ti-chevron-right"></i></span>
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

            {{-- DOCUMENTOS POR VENCER --}}
            <div class="sgi-card bento-tile bento-doc hud-corners">
                <span class="hud-tag">06</span>
                <div class="sgi-card-head">
                    <div>
                        <h2 class="sgi-card-title">Documentos por vencer</h2>
                        <p class="sgi-card-sub">Vigencia de versión o revisión en zona de alerta</p>
                    </div>
                    @if($totalPorVencer > 0)
                    <div class="sgi-urgent-badge">
                        <span class="sgi-live-dot" style="background:#dc2626"></span>
                        {{ $totalPorVencer }}
                    </div>
                    @endif
                </div>
                @if(!empty($documentosPorVencer) && count($documentosPorVencer))
                <ul class="doc-list">
                    @foreach($documentosPorVencer as $doc)
                    <li class="doc-item">
                        <a href="{{ route('documentos.show', $doc->id) }}" class="doc-link">
                            <span class="doc-badge doc-badge-{{ $doc->semaforo_vencimiento }}"></span>
                            <div class="doc-body">
                                <p class="doc-code">{{ $doc->codigo }}</p>
                                <p class="doc-name">{{ \Illuminate\Support\Str::limit($doc->nombre, 34) }}</p>
                            </div>
                            <span class="doc-days doc-days-{{ $doc->semaforo_vencimiento }}">
                                {{ $doc->dias_para_vencimiento < 0 ? 'Vencido' : $doc->dias_para_vencimiento . ' d' }}
                            </span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @if($totalPorVencer > count($documentosPorVencer))
                <div class="doc-more">+ {{ $totalPorVencer - count($documentosPorVencer) }} documentos más por vencer</div>
                @endif
                @role('administrador_sgi')
                <a href="{{ route('solicitudes.calendar') }}" class="doc-cal-link">
                    Ver calendario completo <i class="ti ti-arrow-right" aria-hidden="true"></i>
                </a>
                @endrole
                @else
                <div class="sgi-empty">
                    <span class="sgi-empty-icon sgi-empty-icon-ok"><i class="ti ti-shield-check"></i></span>
                    <p>Nada por vencer en los próximos 60 días</p>
                </div>
                @endif
            </div>

            {{-- ACTIVIDAD RECIENTE --}}
            <div class="sgi-card bento-tile bento-act hud-corners">
                <span class="hud-tag">07</span>
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
                    <span class="sgi-empty-icon"><i class="ti ti-moon-stars"></i></span>
                    <p>Sin actividad reciente</p>
                </div>
                @endif
            </div>

            {{-- ACCESOS RÁPIDOS --}}
            <div class="sgi-card bento-tile bento-quick hud-corners">
                <span class="hud-tag">08</span>
                <div class="sgi-card-head">
                    <div>
                        <h2 class="sgi-card-title">Accesos rápidos</h2>
                        <p class="sgi-card-sub">Navegación directa</p>
                    </div>
                </div>
                <nav class="ql-nav" aria-label="Accesos rápidos">
                    <a href="{{ route('solicitudes.create') }}" class="ql-link ql-primary">
                        <span class="ql-icon-box"><i class="ti ti-plus"></i></span>
                        <span class="ql-text">Crear nueva solicitud</span>
                        <span class="ql-arr" aria-hidden="true"><i class="ti ti-arrow-right"></i></span>
                    </a>
                    <a href="{{ route('solicitudes.index') }}" class="ql-link">
                        <span class="ql-icon-box ql-icon-box-ghost"><i class="ti ti-clipboard-list"></i></span>
                        <span class="ql-text">Ver todas las solicitudes</span>
                        <span class="ql-arr" aria-hidden="true"><i class="ti ti-arrow-right"></i></span>
                    </a>
                    @if($user->hasRole('jefe'))
                    <a href="{{ route('solicitudes.index', ['estado' => 'pendiente']) }}" class="ql-link ql-amber">
                        <span class="ql-icon-box ql-icon-box-ghost"><i class="ti ti-clock"></i></span>
                        <span class="ql-text">Pendientes por aprobar</span>
                        <span class="ql-arr" aria-hidden="true"><i class="ti ti-arrow-right"></i></span>
                    </a>
                    @endif
                    @if($user->hasRole('administrador_sgi'))
                    <a href="{{ route('solicitudes.index', ['estado' => 'aprobado_jefe']) }}" class="ql-link ql-teal">
                        <span class="ql-icon-box ql-icon-box-ghost"><i class="ti ti-file-check"></i></span>
                        <span class="ql-text">Listas para alta SGI</span>
                        <span class="ql-arr" aria-hidden="true"><i class="ti ti-arrow-right"></i></span>
                    </a>
                    @endif
                </nav>
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
            <div class="info-card hud-corners info-{{ $f['color'] }}">
                <span class="hud-tag">0{{ $loop->iteration + 8 }}</span>
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
    --mono: 'SFMono-Regular', ui-monospace, 'Consolas', monospace;
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
.sgi-loading-sub   { color: var(--gold); font-family: var(--mono); font-size: 11px; letter-spacing: .06em; }

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
.sgi-banner-icon { color: var(--gold); font-size: 16px; display: inline-flex; }
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
    background:
        radial-gradient(ellipse 90% 55% at 50% -5%, rgba(255,255,255,.55) 0%, transparent 65%),
        repeating-linear-gradient(0deg, rgba(106,44,117,.04) 0px, rgba(106,44,117,.04) 1px, transparent 1px, transparent 64px),
        repeating-linear-gradient(90deg, rgba(106,44,117,.04) 0px, rgba(106,44,117,.04) 1px, transparent 1px, transparent 64px);
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
   HUD UTILITIES — esquinas + etiqueta técnica
══════════════════════════════ */
.hud-corners { position: relative; }
.hud-corners::before, .hud-corners::after {
    content: ''; position: absolute; width: 14px; height: 14px;
    border-color: var(--gold); border-style: solid; opacity: .5;
    pointer-events: none; transition: opacity var(--transition), width var(--transition), height var(--transition);
}
.hud-corners::before { top: 9px; left: 9px; border-width: 2px 0 0 2px; border-radius: 5px 0 0 0; }
.hud-corners::after  { bottom: 9px; right: 9px; border-width: 0 2px 2px 0; border-radius: 0 0 5px 0; }
.hud-corners:hover::before, .hud-corners:hover::after { opacity: .9; width: 18px; height: 18px; }
.hud-tag {
    position: absolute; top: 14px; right: 16px; z-index: 2;
    font-family: var(--mono); font-size: 9.5px; font-weight: 700; letter-spacing: .1em;
    color: var(--gold-dark); opacity: .55; pointer-events: none;
}

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
.sgi-hero.hud-corners::before, .sgi-hero.hud-corners::after { border-color: rgba(255,255,255,.55); opacity: .7; }

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

.sgi-hero-readout {
    position: absolute; top: 16px; right: 22px; z-index: 2;
    display: flex; align-items: center; gap: 6px;
    font-family: var(--mono); font-size: 10.5px; font-weight: 600; letter-spacing: .08em;
    color: rgba(255,255,255,.5);
}
.sgi-hero-readout-sep { color: var(--gold); opacity: .8; }
.sgi-hero-readout-dot {
    width: 6px; height: 6px; border-radius: 50%; background: #4ade80; margin-left: 2px;
    box-shadow: 0 0 0 3px rgba(74,222,128,.2);
    animation: live-pulse 1.8s ease-in-out infinite;
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

.sgi-hero-mascot { position: relative; display: flex; align-items: center; justify-content: center; }
.sgi-hero-mascot-ring {
    position: absolute; width: 210px; height: 210px; border-radius: 50%;
    border: 1px dashed rgba(214,166,68,.35);
    animation: spin-slow 22s linear infinite;
}
@keyframes spin-slow { to { transform: rotate(360deg); } }
.sgi-valentia {
    position: relative; width: 220px; height: auto; object-fit: contain;
    mix-blend-mode: multiply;
    filter: drop-shadow(0 8px 32px rgba(0,0,0,.15));
    animation: float 4s ease-in-out infinite;
}
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }

/* ══════════════════════════════
   STAT CARDS (flip)
══════════════════════════════ */
.sgi-stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px;
}
.stat-card {
    height: 168px; cursor: pointer; perspective: 1200px;
    border-radius: var(--radius-md); border: none; background: none;
    position: relative;
    --stat-c: var(--purple);
}
.stat-card-front, .stat-card-back {
    position: absolute; inset: 0; border-radius: var(--radius-md);
    display: flex; flex-direction: column; align-items: flex-start; justify-content: center; gap: 6px;
    padding: 22px 20px;
    backface-visibility: hidden; -webkit-backface-visibility: hidden;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    box-shadow: var(--shadow-card);
    transition: box-shadow var(--transition), border-color var(--transition);
}
.stat-card-front::before {
    content: ''; position: absolute; top: 0; left: 18px; right: 18px; height: 2px;
    border-radius: 0 0 2px 2px;
    background: linear-gradient(90deg, transparent, var(--stat-c), transparent);
    opacity: .55;
}
.stat-card-back { transform: rotateY(180deg); align-items: center; text-align: center; }
.stat-card:hover .stat-card-front { transform: rotateY(-180deg); box-shadow: var(--shadow-card-hover); border-color: color-mix(in srgb, var(--stat-c) 35%, transparent); }
.stat-card:hover .stat-card-back  { transform: rotateY(0deg);    box-shadow: var(--shadow-card-hover); border-color: color-mix(in srgb, var(--stat-c) 35%, transparent); }
.stat-card-front, .stat-card-back { transition: transform .65s cubic-bezier(.68,-.35,.265,1.4), box-shadow var(--transition), border-color var(--transition); }
.stat-card.hud-corners::before, .stat-card.hud-corners::after { border-color: var(--stat-c); }

.stat-icon-chip {
    width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; color: var(--stat-c);
    background: color-mix(in srgb, var(--stat-c) 12%, transparent);
    border: 1px solid color-mix(in srgb, var(--stat-c) 22%, transparent);
    margin-bottom: 4px;
}
.stat-icon-chip-ghost { background: transparent; }
.stat-num    { font-family: var(--mono); font-size: 2.6rem; font-weight: 800; line-height: 1; letter-spacing: -.03em; color: var(--stat-c); font-variant-numeric: tabular-nums; }
.stat-label  { font-size: 10px; text-transform: uppercase; letter-spacing: .16em; font-weight: 700; color: #6b7280; }
.stat-back-text { font-size: 12.5px; text-align: center; line-height: 1.55; font-weight: 500; color: #4b5563; }
.stat-back-cta  { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; text-transform: uppercase; letter-spacing: .1em; font-weight: 700; color: var(--stat-c); margin-top: 4px; }
.stat-back-cta i { font-size: 12px; }

.stat-purple { --stat-c: var(--purple); }
.stat-gold   { --stat-c: var(--gold-dark); }
.stat-green  { --stat-c: #059669; }
.stat-rose   { --stat-c: #e11d48; }
.stat-amber  { --stat-c: #d97706; }

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

/* ── Bento grid layout ── */
.sgi-bento {
    display: grid; grid-template-columns: repeat(12, 1fr); gap: 20px;
    align-items: start;
}
.bento-tile, .bento-acc { min-width: 0; }
.bento-chart { grid-column: span 8; min-height: 360px; align-self: stretch; display: flex; flex-direction: column; }
.bento-video { grid-column: span 4; align-self: stretch; display: flex; flex-direction: column; }
.bento-acc   { grid-column: span 4; }
.bento-doc   { grid-column: span 5; }
.bento-act   { grid-column: span 4; }
.bento-quick { grid-column: span 3; }

/* ── Chart ── */
.sgi-chart-wrap { padding: 20px 24px 24px; flex: 1; }
.sgi-chart-wrap canvas { width: 100% !important; height: 100% !important; }

/* ── Video ── */
.sgi-video-wrap { flex: 1; display: flex; align-items: center; justify-content: center; padding: 12px 20px 20px; min-height: 0; }
.sgi-video { width: 100%; height: 100%; max-height: 260px; object-fit: contain; mix-blend-mode: multiply; border-radius: var(--radius-md); }

/* ── Accordions ── */
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
    width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; color: var(--purple);
    background: rgba(106,44,117,.1); border: 1px solid rgba(106,44,117,.18);
}
.acc-indigo .acc-icon { color: #4338ca; background: rgba(67,56,202,.1); border-color: rgba(67,56,202,.2); }
.acc-violet .acc-icon { color: #7c3aed; background: rgba(124,58,237,.1); border-color: rgba(124,58,237,.2); }
.acc-teal   .acc-icon { color: #0f766e; background: rgba(15,118,110,.1); border-color: rgba(15,118,110,.2); }

.acc-title  { font-size: 13px; font-weight: 700; color: #1e1b4b; }
.acc-sub    { font-size: 11px; color: #9ca3af; margin-top: 1px; }
.acc-chevron { font-size: 15px; color: #9ca3af; display: inline-flex; transition: transform .3s var(--transition); line-height: 1; }
details[open] .acc-chevron { transform: rotate(90deg); }
.acc-body { padding: 0 14px 14px; border-top: 1px solid rgba(0,0,0,.06); padding-top: 10px; }
.acc-item { display: flex; align-items: flex-start; gap: 8px; padding: 3px 0; }
.acc-bullet { width: 4px; height: 4px; border-radius: 50%; background: var(--purple-light); flex-shrink: 0; margin-top: 6px; opacity: .6; }
.acc-item p { font-size: 12px; color: #6b7280; line-height: 1.55; }

/* ── Documentos por vencer ── */
.sgi-urgent-badge {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    padding: 4px 12px; border-radius: 100px;
    background: rgba(220,38,38,.1); border: 1px solid rgba(220,38,38,.22);
    font-size: 11px; font-weight: 700; color: #b91c1c;
}
.doc-list { list-style: none; display: flex; flex-direction: column; gap: 2px; padding: 14px 12px 8px; }
.doc-item { border-radius: var(--radius-sm); }
.doc-link {
    display: flex; align-items: center; gap: 10px; padding: 9px 10px;
    border-radius: var(--radius-sm); text-decoration: none; transition: background var(--transition);
}
.doc-link:hover { background: rgba(217,119,6,.06); }
.doc-badge { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.doc-badge-vencido { background: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.15); }
.doc-badge-critico { background: #f43f5e; }
.doc-badge-alerta  { background: #d97706; }
.doc-body { flex: 1; min-width: 0; }
.doc-code { font-family: var(--mono); font-size: 12px; font-weight: 700; color: #1e1b4b; }
.doc-name { font-size: 11px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.doc-days {
    flex-shrink: 0; font-family: var(--mono); font-size: 10.5px; font-weight: 700; padding: 3px 9px;
    border-radius: 100px; white-space: nowrap;
}
.doc-days-vencido { background: #dc2626; color: #fff; }
.doc-days-critico { background: rgba(244,63,94,.12); color: #be123c; }
.doc-days-alerta  { background: rgba(217,119,6,.12); color: #b45309; }
.doc-more { padding: 4px 24px 14px; font-size: 11px; color: #9ca3af; }
.doc-cal-link {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    margin: 4px 12px 16px; padding: 9px; border-radius: var(--radius-sm);
    background: rgba(106,44,117,.06); border: 1px solid rgba(106,44,117,.15);
    font-size: 12px; font-weight: 700; color: var(--purple); text-decoration: none;
    transition: all var(--transition);
}
.doc-cal-link:hover { background: rgba(106,44,117,.12); transform: translateY(-1px); }

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
.act-time   { font-family: var(--mono); font-size: 10px; color: #d1d5db; margin-top: 3px; display: block; }

.sgi-empty { text-align: center; padding: 40px 0; }
.sgi-empty-icon {
    width: 44px; height: 44px; border-radius: 12px; margin: 0 auto 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: #9ca3af;
    background: rgba(107,114,128,.08); border: 1px solid rgba(107,114,128,.15);
}
.sgi-empty-icon-ok { color: #059669; background: rgba(5,150,105,.1); border-color: rgba(5,150,105,.2); }
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
    width: 30px; height: 30px; border-radius: 9px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--purple-light), var(--purple-dark));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 15px; line-height: 1;
    box-shadow: 0 3px 10px rgba(106,44,117,.3);
}
.ql-icon-box-ghost {
    background: rgba(0,0,0,.04); color: #6b7280; box-shadow: none;
    border: 1px solid rgba(0,0,0,.06);
}
.ql-amber .ql-icon-box-ghost { background: rgba(217,119,6,.12); color: #92400e; border-color: rgba(217,119,6,.2); }
.ql-teal  .ql-icon-box-ghost { background: rgba(15,118,110,.12); color: #065f46; border-color: rgba(15,118,110,.2); }
.ql-arr  { margin-left: auto; display: inline-flex; font-size: 14px; opacity: .3; transition: transform var(--transition), opacity var(--transition); }

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
    .sgi-bento { grid-template-columns: repeat(6, 1fr); }
    .bento-chart { grid-column: span 6; min-height: 300px; }
    .bento-video { grid-column: span 6; }
    .bento-acc   { grid-column: span 2; }
    .bento-doc   { grid-column: span 3; }
    .bento-act   { grid-column: span 3; }
    .bento-quick { grid-column: span 6; }
    .sgi-stats-grid { grid-template-columns: repeat(2, 1fr); }
    .sgi-info-grid  { grid-template-columns: repeat(1, 1fr); }
}
@media (max-width: 640px) {
    .sgi-container { padding: 20px 16px 40px; gap: 16px; }
    .sgi-hero-grid { grid-template-columns: 1fr; padding: 28px 24px; }
    .sgi-hero-mascot { display: none; }
    .sgi-hero-readout { display: none; }
    .sgi-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .sgi-bento { grid-template-columns: 1fr; }
    .bento-chart, .bento-video, .bento-acc, .bento-doc, .bento-act, .bento-quick { grid-column: span 1; }
    .sgi-info-grid  { grid-template-columns: 1fr; }
}

@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
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
    transition: transform var(--transition), box-shadow var(--transition);
    position: relative;
}
.itchat-fab:hover { transform: translateY(-3px) scale(1.04); box-shadow: 0 12px 34px rgba(106,44,117,.5); }
.itchat-fab-open { background: linear-gradient(135deg, #4b5563, #1f2937); }
.itchat-fab-dot {
    position: absolute; top: 4px; right: 4px; width: 12px; height: 12px; border-radius: 50%;
    background: var(--gold); border: 2px solid #fff;
    animation: live-pulse 1.8s ease-in-out infinite;
}

.itchat-panel {
    position: absolute; right: 0; bottom: 74px;
    width: min(370px, calc(100vw - 32px)); max-height: min(560px, calc(100vh - 140px));
    display: flex; flex-direction: column;
    background: var(--glass-bg); border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur);
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
.itchat-head-title { font-family: var(--font); font-size: 14px; font-weight: 700; }
.itchat-head-sub { font-size: 11px; color: rgba(255,255,255,.7); margin-top: 1px; }
.itchat-close {
    width: 28px; height: 28px; border-radius: 50%; border: none; cursor: pointer; flex-shrink: 0;
    background: rgba(255,255,255,.12); color: #fff; display: flex; align-items: center; justify-content: center;
    transition: background var(--transition);
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
    transition: all var(--transition);
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
    transition: all var(--transition);
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

/* ── Conteo ascendente en las tarjetas de estadísticas ── */
document.querySelectorAll('.stat-num[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10);
    if (el.dataset.count === '' || isNaN(target)) return;
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
        maintainAspectRatio: false,
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
