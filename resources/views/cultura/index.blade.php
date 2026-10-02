<x-app-layout>

{{-- ─── BARRA DE PROGRESO DE SCROLL ─── --}}
<div class="cult-progress-track" aria-hidden="true">
    <div class="cult-progress-bar" id="cultProgress"></div>
</div>

<div class="cult-root" style="background-image:url('{{ asset('images/background-pattern.png') }}');">
    <div class="cult-overlay" aria-hidden="true"></div>
    <div class="cult-grain" aria-hidden="true"></div>
    <div class="cult-blob cult-blob-1" aria-hidden="true"></div>
    <div class="cult-blob cult-blob-2" aria-hidden="true"></div>
    <div class="cult-blob cult-blob-3" aria-hidden="true"></div>

    {{-- ── CURSOR PERSONALIZADO ── --}}
    <div class="cult-cursor" id="cultCursor" aria-hidden="true"></div>

    {{-- ── PARTÍCULAS FLOTANTES ── --}}
    <div class="cult-particles" aria-hidden="true">
        @for ($p = 0; $p < 14; $p++)
        <span class="cult-particle" style="
            left: {{ ($p * 7 + 4) % 100 }}%;
            animation-duration: {{ 14 + ($p % 6) * 3 }}s;
            animation-delay: -{{ ($p * 2.3) }}s;
            width: {{ 4 + ($p % 3) * 2 }}px; height: {{ 4 + ($p % 3) * 2 }}px;
            background: {{ $p % 2 === 0 ? 'rgba(106,44,117,.22)' : 'rgba(214,166,68,.28)' }};
        "></span>
        @endfor
    </div>

    {{-- ── NAV LATERAL DE VALORES ── --}}
    <nav class="cult-dotnav" aria-label="Navegación de valores">
        @foreach($valores as $i => $v)
        <a href="#valor-{{ $i }}" class="cult-dot" data-dot="{{ $i }}">
            <span class="cult-dot-mark"></span>
            <span class="cult-dot-label">{{ $v['titulo'] }}</span>
        </a>
        @endforeach
    </nav>

    <div class="cult-container">

        {{-- ══════════════════════════════
             HERO
        ══════════════════════════════ --}}
        <header class="cult-hero reveal-onscroll">
            <a href="{{ route('dashboard') }}" class="cult-back">
                <i class="ti ti-arrow-left" aria-hidden="true"></i> Volver al dashboard
            </a>
            <div class="cult-eyebrow">
                <span class="cult-eyebrow-dot"></span>
                Sistema de Gestión Integral
            </div>
            <h1 class="cult-hero-title">
                <span class="cult-word" style="--wi:0">Nuestra</span>
                <em id="cultShimmer" class="cult-word" style="--wi:1">Cultura</em>
            </h1>
            <p class="cult-hero-desc">
                Cinco valores que sostienen cada decisión que tomamos en Dasavena.
                Desplázate para conocerlos.
            </p>
            <div class="cult-scroll-hint" aria-hidden="true">
                <span></span>
                <i class="ti ti-chevron-down"></i>
            </div>
        </header>

        {{-- ══════════════════════════════
             VALORES
        ══════════════════════════════ --}}
        @foreach($valores as $i => $v)
        <section id="valor-{{ $i }}" class="cult-value reveal-onscroll {{ $i % 2 === 1 ? 'cult-value-rev' : '' }}" style="--vi: {{ $i % 3 }}">
            <div class="cult-value-media" data-parallax>
                <div class="cult-tilt" data-tilt>
                    <div class="cult-icon-card cult-ring-{{ $v['color'] }}" data-glow>
                        <img src="{{ asset('images/'.$v['imagen']) }}" alt="{{ $v['titulo'] }}" class="cult-value-img" loading="lazy">
                    </div>
                </div>
            </div>
            <div class="cult-value-copy">
                <span class="cult-value-num cult-num-{{ $v['color'] }}" data-count-target="{{ $i + 1 }}">00</span>
                <h2 class="cult-value-title">{{ $v['titulo'] }}</h2>
                <p class="cult-value-sub cult-sub-{{ $v['color'] }}">{{ $v['subtitulo'] }}</p>
                <p class="cult-value-text">{{ $v['texto'] }}</p>
            </div>
        </section>
        @endforeach

        {{-- ══════════════════════════════
             DIVISOR
        ══════════════════════════════ --}}
        <div class="cult-divider reveal-onscroll">
            <img src="{{ asset('images/Nueces.png') }}" alt="" class="cult-divider-img" loading="lazy">
            <p class="cult-divider-text">De pequeñas semillas nacen grandes equipos</p>
        </div>

        {{-- ══════════════════════════════
             CIERRE
        ══════════════════════════════ --}}
        <section class="cult-cta reveal-onscroll">
            <h2 class="cult-cta-title">¿Listo para vivirlos todos los días?</h2>
            <a href="{{ route('dashboard') }}" class="cult-cta-btn" data-magnetic>
                Ir al dashboard <i class="ti ti-arrow-right" aria-hidden="true"></i>
            </a>
        </section>

    </div>{{-- /cult-container --}}
</div>{{-- /cult-root --}}

{{-- ─────────────────────────────────────────────
     ESTILOS
───────────────────────────────────────────── --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
<style>
*, *::before, *::after { box-sizing: border-box; }
html:has(.cult-root) { scroll-behavior: smooth; }
@media (prefers-reduced-motion: reduce) { html:has(.cult-root) { scroll-behavior: auto; } }
:root {
    --purple: #6A2C75;
    --purple-light: #8E3D9E;
    --purple-dark: #4a1f55;
    --gold: #D6A644;
    --gold-dark: #b38600;
    --glass-bg: rgba(255,255,255,.72);
    --glass-border: rgba(255,255,255,.88);
    --glass-blur: blur(24px) saturate(160%);
    --radius-lg: 28px;
    --shadow-card: 0 10px 32px rgba(106,44,117,.08), 0 1px 4px rgba(0,0,0,.05), inset 0 1px 1px rgba(255,255,255,.9);
    --font: 'Century Gothic', 'Trebuchet MS', sans-serif;
    --mono: 'SFMono-Regular', ui-monospace, 'Consolas', monospace;
    --transition: .25s cubic-bezier(.22,1,.36,1);
}

/* ── Barra de progreso ── */
.cult-progress-track { position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 9000; background: rgba(106,44,117,.08); }
.cult-progress-bar { height: 100%; width: 0%; background: linear-gradient(90deg, var(--purple), var(--gold)); transition: width .1s linear; }

/* ── Root ── */
.cult-root { min-height: 100vh; position: relative; background-attachment: fixed; background-size: cover; background-position: center; }
.cult-overlay {
    pointer-events: none; position: fixed; inset: 0; z-index: 1;
    background: radial-gradient(ellipse 90% 55% at 50% -5%, rgba(255,255,255,.6) 0%, transparent 65%);
}
.cult-container { position: relative; z-index: 10; max-width: 1180px; margin: 0 auto; padding: 56px 24px 100px; display: flex; flex-direction: column; gap: 110px; }

/* ── Grano de película (textura premium) ── */
.cult-grain {
    pointer-events: none; position: fixed; inset: -10%; z-index: 2; opacity: .035; mix-blend-mode: overlay;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* ── Blobs ambientales animados ── */
.cult-blob { pointer-events: none; position: fixed; z-index: 2; border-radius: 9999px; filter: blur(60px); will-change: transform; }
.cult-blob-1 { top: -12%; left: -8%; width: 460px; height: 460px; background: radial-gradient(circle, rgba(106,44,117,.14), transparent 70%); animation: cult-drift-1 22s ease-in-out infinite; }
.cult-blob-2 { top: 18%; right: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(214,166,68,.14), transparent 70%); animation: cult-drift-2 26s ease-in-out infinite; }
.cult-blob-3 { bottom: -10%; left: 30%; width: 420px; height: 420px; background: radial-gradient(circle, rgba(142,61,158,.12), transparent 70%); animation: cult-drift-3 30s ease-in-out infinite; }
@keyframes cult-drift-1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(60px,80px) scale(1.15); } }
@keyframes cult-drift-2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-70px,50px) scale(1.1); } }
@keyframes cult-drift-3 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(40px,-60px) scale(1.12); } }
@media (prefers-reduced-motion: reduce) { .cult-blob { animation: none !important; } }

/* ── Nav lateral de puntos ── */
.cult-dotnav {
    position: fixed; right: 28px; top: 50%; transform: translateY(-50%); z-index: 500;
    display: flex; flex-direction: column; gap: 18px;
}
.cult-dot {
    display: flex; align-items: center; gap: 10px; text-decoration: none; justify-content: flex-end;
}
.cult-dot-mark {
    width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0;
    background: rgba(106,44,117,.25); border: 1px solid rgba(106,44,117,.3);
    transition: all .3s cubic-bezier(.22,1,.36,1);
}
.cult-dot-label {
    font-family: var(--mono); font-size: 10.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
    color: var(--purple-dark); background: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.9);
    padding: 4px 10px; border-radius: 100px; white-space: nowrap; backdrop-filter: blur(8px);
    opacity: 0; transform: translateX(8px); transition: all .25s cubic-bezier(.22,1,.36,1); pointer-events: none;
}
.cult-dot:hover .cult-dot-label { opacity: 1; transform: translateX(0); }
.cult-dot:hover .cult-dot-mark { background: var(--gold); border-color: var(--gold); transform: scale(1.3); }
.cult-dot.is-active .cult-dot-mark {
    background: linear-gradient(135deg, var(--purple), var(--gold)); border-color: transparent;
    transform: scale(1.5); box-shadow: 0 0 0 4px rgba(106,44,117,.15);
}
@media (max-width: 1060px) { .cult-dotnav { display: none; } }

/* ── Cursor personalizado ── */
.cult-cursor {
    position: fixed; top: 0; left: 0; z-index: 9500; width: 28px; height: 28px; border-radius: 50%;
    border: 1.5px solid var(--purple); pointer-events: none; mix-blend-mode: multiply;
    transform: translate(-50%,-50%); transition: width .25s ease, height .25s ease, border-color .25s ease, opacity .25s ease;
    opacity: 0;
}
.cult-cursor.is-active { opacity: 1; }
.cult-cursor.is-big { width: 54px; height: 54px; border-color: var(--gold); background: rgba(214,166,68,.08); }
@media (hover: none), (pointer: coarse) { .cult-cursor { display: none; } }

/* ── Partículas flotantes ── */
.cult-particles { position: fixed; inset: 0; z-index: 3; pointer-events: none; overflow: hidden; }
.cult-particle {
    position: absolute; bottom: -20px; border-radius: 50%;
    animation-name: cult-rise; animation-timing-function: linear; animation-iteration-count: infinite;
}
@keyframes cult-rise {
    0%   { transform: translateY(0) translateX(0); opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    100% { transform: translateY(-110vh) translateX(26px); opacity: 0; }
}
@media (prefers-reduced-motion: reduce) { .cult-particles { display: none; } }

/* ── Entrada escalonada del título ── */
.cult-word {
    display: inline-block; opacity: 0; transform: translateY(24px);
    animation: cult-word-in .7s cubic-bezier(.22,1,.36,1) forwards;
    animation-delay: calc(.15s + var(--wi, 0) * .12s);
}
@keyframes cult-word-in { to { opacity: 1; transform: translateY(0); } }
@media (prefers-reduced-motion: reduce) { .cult-word { animation: none; opacity: 1; transform: none; } }

/* ── Reveal on scroll ── */
.reveal-onscroll { opacity: 0; transform: translateY(46px); transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); }
.reveal-onscroll.is-visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) { .reveal-onscroll { opacity: 1 !important; transform: none !important; transition: none !important; } }

/* ── Hero ── */
.cult-hero { text-align: center; padding: 20px 0 10px; display: flex; flex-direction: column; align-items: center; gap: 18px; }
.cult-back {
    align-self: flex-start; display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; color: var(--purple); text-decoration: none;
    background: rgba(255,255,255,.7); border: 1px solid rgba(255,255,255,.9);
    padding: 8px 16px; border-radius: 100px; backdrop-filter: blur(8px);
    transition: all var(--transition);
}
.cult-back:hover { background: #fff; transform: translateX(-3px); }
.cult-eyebrow {
    display: inline-flex; align-items: center; gap: 8px; font-size: 12px;
    text-transform: uppercase; letter-spacing: .22em; color: var(--gold-dark); font-weight: 700;
}
.cult-eyebrow-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--gold); box-shadow: 0 0 0 4px rgba(214,166,68,.25); }
.cult-hero-title {
    font-family: var(--font); font-weight: 800; letter-spacing: -.02em;
    font-size: clamp(2.6rem, 7vw, 5.4rem); line-height: 1.02; color: var(--purple-dark);
}
.cult-hero-title em {
    font-style: normal;
    background: linear-gradient(90deg, var(--purple), var(--gold) 50%, var(--purple));
    background-size: 250% 100%; background-position: 50% 50%;
    -webkit-background-clip: text; background-clip: text; color: transparent;
    transition: background-position .2s ease-out;
}
.cult-hero-desc { font-size: 1.05rem; color: #6b5a72; max-width: 520px; line-height: 1.7; }
.cult-scroll-hint { margin-top: 18px; display: flex; flex-direction: column; align-items: center; gap: 4px; color: var(--purple); opacity: .55; }
.cult-scroll-hint span { width: 1px; height: 30px; background: linear-gradient(var(--purple), transparent); }
.cult-scroll-hint i { animation: cult-bounce 1.6s ease-in-out infinite; font-size: 18px; }
@keyframes cult-bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(6px)} }

/* ── Bloque de valor ── */
.cult-value {
    display: grid; grid-template-columns: .85fr 1fr; align-items: center; gap: 56px;
    transition-delay: calc(var(--vi, 0) * .08s);
}
.cult-value-rev .cult-value-media { order: 2; }
.cult-value-rev .cult-value-copy { order: 1; }

.cult-value-media { display: flex; justify-content: center; perspective: 1400px; }
.cult-tilt {
    position: relative; width: min(280px, 72vw); aspect-ratio: 1 / 1;
    display: flex; align-items: center; justify-content: center;
    transition: transform .25s ease-out;
    transform-style: preserve-3d;
    will-change: transform;
}
.cult-icon-card {
    position: relative; inset: 0; width: 100%; height: 100%; border-radius: 32px;
    background: var(--glass-bg); border: 1px solid var(--glass-border);
    box-shadow: var(--shadow-card); backdrop-filter: var(--glass-blur);
    display: flex; align-items: center; justify-content: center;
    transform: translateZ(0);
    overflow: hidden;
}
.cult-icon-card::before {
    content: ''; position: absolute; inset: 0; opacity: .5;
    background-image: radial-gradient(rgba(255,255,255,.9) 1.5px, transparent 1.5px);
    background-size: 20px 20px;
}
.cult-icon-card::after {
    content: ''; position: absolute; inset: 0; z-index: 2; opacity: 0;
    background: radial-gradient(220px 220px at var(--mx,50%) var(--my,50%), rgba(255,255,255,.65), transparent 60%);
    transition: opacity .35s ease; pointer-events: none;
}
.cult-icon-card:hover::after { opacity: 1; }
.cult-ring-purple { box-shadow: var(--shadow-card), 0 24px 60px rgba(106,44,117,.18); }
.cult-ring-gold { box-shadow: var(--shadow-card), 0 24px 60px rgba(214,166,68,.22); }
.cult-value-img {
    position: relative; z-index: 1; width: 78%; height: 78%; object-fit: contain;
    filter: drop-shadow(0 24px 32px rgba(45,16,51,.18));
    transform: translateZ(40px);
    animation: cult-float 5s ease-in-out infinite;
}
@keyframes cult-float { 0%,100%{ transform: translateZ(40px) translateY(0);} 50%{ transform: translateZ(40px) translateY(-12px);} }

.cult-value-copy { display: flex; flex-direction: column; gap: 10px; }
.cult-value-num { font-family: var(--mono); font-size: 13px; font-weight: 700; letter-spacing: .16em; }
.cult-num-purple { color: var(--purple-light); }
.cult-num-gold { color: var(--gold-dark); }
.cult-value-title {
    font-family: var(--font); font-weight: 800; letter-spacing: -.01em; color: var(--purple-dark);
    font-size: clamp(2rem, 4.2vw, 3.4rem); line-height: 1.05; margin: 2px 0 4px;
}
.cult-value-sub { position: relative; display: inline-block; font-size: 1.05rem; font-weight: 700; padding-bottom: 8px; }
.cult-value-sub::after {
    content: ''; position: absolute; left: 0; bottom: 0; height: 2px; width: 100%;
    transform: scaleX(0); transform-origin: left; transition: transform .7s cubic-bezier(.22,1,.36,1) .25s;
}
.cult-value.is-visible .cult-value-sub::after { transform: scaleX(1); }
.cult-sub-purple { color: var(--purple); }
.cult-sub-purple::after { background: linear-gradient(90deg, var(--purple), transparent); }
.cult-sub-gold { color: var(--gold-dark); }
.cult-sub-gold::after { background: linear-gradient(90deg, var(--gold), transparent); }
.cult-value-text { font-size: .96rem; color: #5a4a65; line-height: 1.8; max-width: 460px; }

/* ── Divisor ── */
.cult-divider { display: flex; flex-direction: column; align-items: center; gap: 8px; opacity: .9; }
.cult-divider-img { width: min(560px, 90vw); height: auto; opacity: .85; mix-blend-mode: multiply; }
.cult-divider-text { font-family: var(--mono); font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: var(--gold-dark); opacity: .8; }

/* ── CTA final ── */
.cult-cta { text-align: center; display: flex; flex-direction: column; align-items: center; gap: 24px; }
.cult-cta-title { font-family: var(--font); font-weight: 800; color: var(--purple-dark); font-size: clamp(1.6rem, 3.4vw, 2.4rem); }
.cult-cta-btn {
    display: inline-flex; align-items: center; gap: 10px; text-decoration: none;
    padding: 16px 34px; border-radius: 100px; font-weight: 700; font-size: .95rem;
    color: #fff; background: linear-gradient(135deg, var(--purple-light), var(--purple-dark));
    box-shadow: 0 10px 34px rgba(106,44,117,.35); transition: all var(--transition);
}
.cult-cta-btn:hover { transform: translateY(-3px); box-shadow: 0 16px 44px rgba(106,44,117,.45); }
.cult-cta-btn i { transition: transform var(--transition); }
.cult-cta-btn:hover i { transform: translateX(4px); }

/* ── Responsive ── */
@media (max-width: 860px) {
    .cult-container { gap: 80px; padding: 40px 20px 80px; }
    .cult-value { grid-template-columns: 1fr; gap: 32px; text-align: center; }
    .cult-value-rev .cult-value-media, .cult-value-rev .cult-value-copy { order: initial; }
    .cult-value-copy { align-items: center; }
    .cult-value-text { max-width: 100%; }
}
</style>

{{-- ─────────────────────────────────────────────
     SCRIPTS
───────────────────────────────────────────── --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    /* ── Revelado al hacer scroll ── */
    const targets = document.querySelectorAll('.reveal-onscroll');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: .18, rootMargin: '0px 0px -60px 0px' });
        targets.forEach(t => io.observe(t));
    } else {
        targets.forEach(t => t.classList.add('is-visible'));
    }

    /* ── Barra de progreso ── */
    const bar = document.getElementById('cultProgress');
    const onScroll = () => {
        const h = document.documentElement;
        const scrolled = h.scrollTop;
        const height = h.scrollHeight - h.clientHeight;
        bar.style.width = height > 0 ? (scrolled / height * 100) + '%' : '0%';
    };
    document.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ── Tilt 3D en las tarjetas de ícono ── */
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!prefersReduced) {
        document.querySelectorAll('[data-tilt]').forEach(el => {
            el.addEventListener('mousemove', e => {
                const r = el.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - .5;
                const y = (e.clientY - r.top) / r.height - .5;
                el.style.transform = `rotateY(${x * 16}deg) rotateX(${-y * 14}deg)`;
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = 'rotateY(0) rotateX(0)';
            });
        });
    }

    /* ── Glow que sigue al cursor en las tarjetas de ícono ── */
    document.querySelectorAll('[data-glow]').forEach(el => {
        el.addEventListener('mousemove', e => {
            const r = el.getBoundingClientRect();
            el.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
            el.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
        });
    });

    /* ── Shimmer del título que sigue al cursor ── */
    const shimmer = document.getElementById('cultShimmer');
    if (shimmer) {
        document.querySelector('.cult-hero').addEventListener('mousemove', e => {
            const r = shimmer.getBoundingClientRect();
            const pct = Math.min(100, Math.max(0, ((e.clientX - r.left) / r.width) * 100));
            shimmer.style.backgroundPosition = `${pct}% 50%`;
        });
    }

    /* ── Paralaje sutil en las tarjetas al hacer scroll ── */
    if (!prefersReduced) {
        const parallaxEls = document.querySelectorAll('[data-parallax]');
        const onParallax = () => {
            const vh = window.innerHeight;
            parallaxEls.forEach(el => {
                const r = el.getBoundingClientRect();
                const center = r.top + r.height / 2;
                const offset = (center - vh / 2) * 0.06;
                el.style.transform = `translateY(${offset}px)`;
            });
        };
        document.addEventListener('scroll', onParallax, { passive: true });
        onParallax();
    }

    /* ── Nav lateral: resaltar el valor activo ── */
    const dots = document.querySelectorAll('.cult-dot');
    if (dots.length && 'IntersectionObserver' in window) {
        const dotIo = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const idx = entry.target.id.replace('valor-', '');
                const dot = document.querySelector(`.cult-dot[data-dot="${idx}"]`);
                if (dot) dot.classList.toggle('is-active', entry.isIntersecting);
            });
        }, { threshold: .5 });
        document.querySelectorAll('.cult-value').forEach(s => dotIo.observe(s));
    }

    /* ── Cursor personalizado con retardo (lerp) ── */
    const cursor = document.getElementById('cultCursor');
    if (cursor && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        let tx = 0, ty = 0, cx = 0, cy = 0, started = false;
        document.addEventListener('mousemove', e => {
            tx = e.clientX; ty = e.clientY;
            if (!started) { cx = tx; cy = ty; started = true; }
            cursor.classList.add('is-active');
        });
        const tick = () => {
            cx += (tx - cx) * 0.18;
            cy += (ty - cy) * 0.18;
            cursor.style.transform = `translate(${cx}px, ${cy}px) translate(-50%,-50%)`;
            requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);

        document.querySelectorAll('a, button, [data-tilt], [data-glow]').forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('is-big'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('is-big'));
        });
    }

    /* ── Conteo ascendente en los números de valor ── */
    const numEls = document.querySelectorAll('[data-count-target]');
    if (numEls.length && 'IntersectionObserver' in window) {
        const numIo = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const target = parseInt(el.dataset.countTarget, 10);
                const duration = 900;
                const start = performance.now();
                const tick = (now) => {
                    const p = Math.min((now - start) / duration, 1);
                    const val = Math.max(1, Math.round(p * target));
                    el.textContent = String(val).padStart(2, '0');
                    if (p < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
                numIo.unobserve(el);
            });
        }, { threshold: .4 });
        numEls.forEach(el => numIo.observe(el));
    }

    /* ── Botón magnético en el CTA ── */
    document.querySelectorAll('[data-magnetic]').forEach(btn => {
        btn.addEventListener('mousemove', e => {
            const r = btn.getBoundingClientRect();
            const x = (e.clientX - r.left - r.width / 2) * 0.35;
            const y = (e.clientY - r.top - r.height / 2) * 0.35 - 3;
            btn.style.transform = `translate(${x}px, ${y}px)`;
        });
        btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
    });
});
</script>

</x-app-layout>
