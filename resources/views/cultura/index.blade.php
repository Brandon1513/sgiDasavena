<x-app-layout>

{{-- ─── BARRA DE PROGRESO DE SCROLL ─── --}}
<div class="cult-progress-track" aria-hidden="true">
    <div class="cult-progress-bar" id="cultProgress"></div>
</div>

<div class="cult-root" style="background-image:url('{{ asset('images/background-pattern.png') }}');">
    <div class="cult-overlay" aria-hidden="true"></div>

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
                Nuestra <em>Cultura</em>
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
        <section class="cult-value reveal-onscroll {{ $i % 2 === 1 ? 'cult-value-rev' : '' }}" style="--vi: {{ $i % 3 }}">
            <div class="cult-value-media">
                <div class="cult-tilt" data-tilt>
                    <div class="cult-value-ring cult-ring-{{ $v['color'] }}" aria-hidden="true"></div>
                    <img src="{{ asset('images/'.$v['imagen']) }}" alt="{{ $v['titulo'] }}" class="cult-value-img" loading="lazy">
                </div>
            </div>
            <div class="cult-value-copy">
                <span class="cult-value-num cult-num-{{ $v['color'] }}">0{{ $i + 1 }}</span>
                <h2 class="cult-value-title">{{ $v['titulo'] }}</h2>
                <p class="cult-value-sub cult-sub-{{ $v['color'] }}">{{ $v['subtitulo'] }}</p>
                <p class="cult-value-text">{{ $v['texto'] }}</p>
            </div>
        </section>
        @endforeach

        {{-- ══════════════════════════════
             DIVISOR — SEMILLAS
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
            <a href="{{ route('dashboard') }}" class="cult-cta-btn">
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
:root {
    --purple: #6A2C75;
    --purple-light: #8E3D9E;
    --purple-dark: #4a1f55;
    --gold: #D6A644;
    --gold-dark: #b38600;
    --glass-bg: rgba(255,255,255,.72);
    --glass-border: rgba(255,255,255,.88);
    --glass-blur: blur(24px) saturate(160%);
    --radius-lg: 24px;
    --shadow-card: 0 2px 20px rgba(106,44,117,.08), 0 1px 4px rgba(0,0,0,.06);
    --shadow-card-hover: 0 20px 60px rgba(106,44,117,.22), 0 4px 12px rgba(0,0,0,.1);
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
    background:
        radial-gradient(ellipse 90% 55% at 50% -5%, rgba(255,255,255,.6) 0%, transparent 65%),
        repeating-linear-gradient(0deg, rgba(106,44,117,.04) 0px, rgba(106,44,117,.04) 1px, transparent 1px, transparent 64px),
        repeating-linear-gradient(90deg, rgba(106,44,117,.04) 0px, rgba(106,44,117,.04) 1px, transparent 1px, transparent 64px);
}
.cult-container { position: relative; z-index: 10; max-width: 1180px; margin: 0 auto; padding: 56px 24px 100px; display: flex; flex-direction: column; gap: 140px; }

/* ── Reveal on scroll ── */
.reveal-onscroll { opacity: 0; transform: translateY(46px); transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); }
.reveal-onscroll.is-visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) { .reveal-onscroll { opacity: 1 !important; transform: none !important; transition: none !important; } }

/* ── Hero ── */
.cult-hero { text-align: center; padding: 20px 0 10px; display: flex; flex-direction: column; align-items: center; gap: 18px; }
.cult-back {
    align-self: flex-start; display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600; color: var(--purple); text-decoration: none;
    background: rgba(255,255,255,.6); border: 1px solid rgba(255,255,255,.9);
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
    font-style: normal; color: var(--purple);
    background: linear-gradient(90deg, var(--purple), var(--purple-light) 50%, var(--gold-dark));
    -webkit-background-clip: text; background-clip: text; color: transparent;
}
.cult-hero-desc { font-size: 1.05rem; color: #6b5a72; max-width: 520px; line-height: 1.7; }
.cult-scroll-hint { margin-top: 18px; display: flex; flex-direction: column; align-items: center; gap: 4px; color: var(--purple); opacity: .55; }
.cult-scroll-hint span { width: 1px; height: 30px; background: linear-gradient(var(--purple), transparent); }
.cult-scroll-hint i { animation: cult-bounce 1.6s ease-in-out infinite; font-size: 18px; }
@keyframes cult-bounce { 0%,100%{transform:translateY(0)} 50%{transform:translateY(6px)} }

/* ── Bloque de valor ── */
.cult-value {
    display: grid; grid-template-columns: 1fr 1fr; align-items: center; gap: 56px;
    transition-delay: calc(var(--vi, 0) * .08s);
}
.cult-value-rev .cult-value-media { order: 2; }
.cult-value-rev .cult-value-copy { order: 1; }

.cult-value-media { display: flex; justify-content: center; perspective: 1400px; }
.cult-tilt {
    position: relative; width: min(340px, 80vw); aspect-ratio: 1 / 1;
    display: flex; align-items: center; justify-content: center;
    transition: transform .25s ease-out;
    transform-style: preserve-3d;
    will-change: transform;
}
.cult-value-ring {
    position: absolute; inset: 6%; border-radius: 50%;
    background: var(--glass-bg); border: 1px solid var(--glass-border);
    box-shadow: var(--shadow-card); backdrop-filter: var(--glass-blur);
    transform: translateZ(-40px);
}
.cult-ring-purple { box-shadow: 0 20px 60px rgba(106,44,117,.2); }
.cult-ring-gold { box-shadow: 0 20px 60px rgba(214,166,68,.25); }
.cult-value-img {
    position: relative; width: 78%; height: 78%; object-fit: contain;
    filter: drop-shadow(0 24px 32px rgba(45,16,51,.18));
    transform: translateZ(40px);
    animation: cult-float 5s ease-in-out infinite;
}
@keyframes cult-float { 0%,100%{ transform: translateZ(40px) translateY(0);} 50%{ transform: translateZ(40px) translateY(-14px);} }

.cult-value-copy { display: flex; flex-direction: column; gap: 10px; }
.cult-value-num { font-family: var(--mono); font-size: 13px; font-weight: 700; letter-spacing: .16em; }
.cult-num-purple { color: var(--purple-light); }
.cult-num-gold { color: var(--gold-dark); }
.cult-value-title {
    font-family: var(--font); font-weight: 800; letter-spacing: -.01em; color: var(--purple-dark);
    font-size: clamp(2rem, 4.2vw, 3.4rem); line-height: 1.05; margin: 2px 0 4px;
}
.cult-value-sub { font-size: 1.05rem; font-weight: 700; }
.cult-sub-purple { color: var(--purple); }
.cult-sub-gold { color: var(--gold-dark); }
.cult-value-text { font-size: .96rem; color: #5a4a65; line-height: 1.8; max-width: 460px; }

/* ── Divisor de semillas ── */
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
    .cult-container { gap: 96px; padding: 40px 20px 80px; }
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

    /* ── Tilt 3D en ilustraciones ── */
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
});
</script>

</x-app-layout>
