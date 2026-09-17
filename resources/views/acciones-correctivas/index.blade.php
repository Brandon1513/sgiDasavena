<x-app-layout>

    <div class="ix-root relative min-h-screen">
        <div class="ix-bg pointer-events-none fixed inset-0 z-0" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');"></div>
        <div class="ix-veil pointer-events-none fixed inset-0 z-[1]"></div>

        <div class="relative z-10 mx-auto flex max-w-[1400px] flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">

            {{-- =========================================================
                ENCABEZADO
            ========================================================== --}}
            <header class="ix-card ix-card-hero tilt-card reveal-3d relative overflow-hidden">
                <div class="gold-gleam absolute inset-x-0 top-0 z-[2] h-[3px]"></div>
                <div class="ix-hero-dotgrid pointer-events-none absolute inset-0" aria-hidden="true"></div>

                <div class="relative z-[1] flex flex-wrap items-center justify-between gap-4 p-6 sm:p-8">
                    <div>
                        <span class="ix-eyebrow">SGI Calidad</span>
                        <h1 class="mt-2 font-display text-2xl font-extrabold text-white sm:text-3xl">Acciones Correctivas</h1>
                        <p class="mt-2 max-w-lg text-sm text-white/70">Gestión y seguimiento de acciones correctivas.</p>
                    </div>

                    <a href="{{ route('acciones-correctivas.create') }}" class="ix-hero-cta">
                        <i class="ti ti-plus"></i> Nueva Acción Correctiva
                    </a>
                </div>
            </header>

            {{-- =========================================================
                FILTROS
            ========================================================== --}}
            <section class="ix-card tilt-card reveal-3d">
                <div class="p-6">
                    <form method="GET" action="{{ route('acciones-correctivas.index') }}">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                            <div class="md:col-span-4">
                                <label class="ix-label">Buscar</label>
                                <input type="text" name="buscar" value="{{ request('buscar') }}" class="ix-field" placeholder="Código o descripción...">
                            </div>

                            <div class="md:col-span-3">
                                <label class="ix-label">Estado</label>
                                <select name="estado" class="ix-field">
                                    <option value="">Todos</option>
                                    @foreach($estados as $estado)
                                    <option value="{{ $estado->codigo }}" @selected(request('estado')===$estado->codigo)>{{ $estado->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-3">
                                <label class="ix-label">Responsable</label>
                                <select name="responsable" class="ix-field">
                                    <option value="">Todos</option>
                                    @foreach($responsables as $responsable)
                                    <option value="{{ $responsable->id }}" @selected((string) request('responsable')===(string) $responsable->id)>{{ $responsable->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end md:col-span-2">
                                <button type="submit" class="btn-glass-primary w-full justify-center">
                                    <i class="ti ti-filter"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            {{-- =========================================================
                TABLA
            ========================================================== --}}
            <section class="ix-card tilt-card reveal-3d overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="ix-table">
                        <thead>
                            <tr>
                                <th>AC</th>
                                <th>Descripción</th>
                                <th>Origen</th>
                                <th>Responsable</th>
                                <th>Estado</th>
                                <th>Avance</th>
                                <th>Ciclo</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accionesCorrectivas as $ac)
                            @php
                            $estadoClase = match ($ac->estado?->codigo) {
                            'borrador' => 'badge-slate',
                            'abierta' => 'badge-purple',
                            'contencion' => 'badge-amber',
                            'analisis', 'validacion_causa' => 'badge-sky',
                            'plan_accion', 'ejecucion' => 'badge-purple',
                            'verificacion_cierre', 'espera_eficacia', 'verificacion_eficacia' => 'badge-amber',
                            'cerrada' => 'badge-emerald',
                            default => 'badge-slate',
                            };
                            @endphp
                            <tr>
                                <td><span class="ix-codigo">{{ $ac->codigo }}</span></td>
                                <td class="ix-desc">{{ $ac->descripcion }}</td>
                                <td class="text-sm text-gray-500">{{ $ac->origen->nombre ?? 'Sin origen' }}</td>
                                <td class="text-sm text-gray-500">{{ $ac->responsable->name ?? 'Sin responsable' }}</td>
                                <td><span class="badge-pill {{ $estadoClase }}">{{ $ac->estado->nombre ?? 'Sin estado' }}</span></td>
                                <td style="min-width: 150px;">
                                    <div class="flex items-center gap-2">
                                        <div class="ix-progress-track flex-1">
                                            <div class="ix-progress-fill" style="width: {{ $ac->porcentaje_avance }}%;"></div>
                                        </div>
                                        <span class="w-10 shrink-0 text-right text-xs font-semibold text-gray-500">{{ number_format($ac->porcentaje_avance, 0) }}%</span>
                                    </div>
                                </td>
                                <td class="text-sm text-gray-500">{{ $ac->ciclo_actual }}</td>
                                <td class="text-right">
                                    <a href="{{ route('acciones-correctivas.show', $ac) }}" class="btn-glass-outline btn-glass-sm">
                                        Ver <i class="ti ti-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="ix-empty">No hay acciones correctivas registradas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($accionesCorrectivas->hasPages())
                <div class="ix-pagination">
                    {{ $accionesCorrectivas->links() }}
                </div>
                @endif
            </section>

        </div>
    </div>

    {{-- ─────────────────────────────────────────────
         ESTILOS — Liquid glass + 3D (paleta Dasavena)
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

    .ix-bg { background-size: cover; background-position: center; background-attachment: fixed; opacity: .55; }
    .ix-veil {
        background: radial-gradient(ellipse 90% 55% at 50% -5%, rgba(255,255,255,.65) 0%, transparent 65%),
                    linear-gradient(180deg, rgba(250,246,251,.55) 0%, rgba(240,232,243,.7) 100%);
    }

    .reveal-3d {
        opacity: 0;
        transform: perspective(1200px) translateY(36px) rotateX(5deg);
        transform-origin: top center;
        transition: opacity .65s cubic-bezier(.22,1,.36,1), transform .65s cubic-bezier(.22,1,.36,1);
    }
    .reveal-3d.is-visible { opacity: 1; transform: perspective(1200px) translateY(0) rotateX(0deg); }
    .tilt-card { transition: transform .25s cubic-bezier(.22,1,.36,1), box-shadow .25s ease; will-change: transform; }

    .ix-card {
        background: rgba(255,255,255,.68);
        border: 1px solid rgba(255,255,255,.7);
        border-radius: 24px;
        backdrop-filter: blur(20px) saturate(160%);
        -webkit-backdrop-filter: blur(20px) saturate(160%);
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 8px 32px rgba(106,44,117,.08);
    }
    .ix-card-hero {
        background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 55%, var(--purple-light) 100%);
        border-color: rgba(255,255,255,.15);
        box-shadow: 0 12px 44px rgba(106,44,117,.3);
    }
    .ix-hero-dotgrid {
        background-image: radial-gradient(rgba(255,255,255,.9) 1.5px, transparent 1.5px);
        background-size: 22px 22px;
        opacity: .06;
    }
    .gold-gleam {
        background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, rgba(255,255,255,.8) 50%, var(--gold) 70%, transparent 100%);
        background-size: 200% 100%;
        animation: gold-shimmer 3.5s ease-in-out infinite;
    }
    @keyframes gold-shimmer { 0%{background-position:100% 0} 100%{background-position:-100% 0} }

    .ix-eyebrow { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .14em; color: rgba(255,255,255,.55); }
    .ix-hero-cta {
        display:inline-flex; align-items:center; gap:8px; padding: 11px 20px; border-radius: 14px;
        font-size: 13px; font-weight: 700; color: var(--purple-dark);
        background: linear-gradient(135deg, #fff, #f3e7d8);
        box-shadow: 0 10px 26px rgba(0,0,0,.22);
        transition: transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s ease;
        text-decoration: none; white-space: nowrap;
    }
    .ix-hero-cta:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(0,0,0,.28); }

    .ix-label { display:block; margin-bottom: 6px; font-size: 12.5px; font-weight: 700; color: #374151; }
    .ix-field {
        width: 100%; border-radius: 12px; border: 1px solid rgba(106,44,117,.18);
        background: rgba(255,255,255,.75); padding: 9px 12px; font-size: 13.5px; color: #1f2937;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .ix-field:focus { outline:none; border-color: var(--purple); box-shadow: 0 0 0 3px rgba(106,44,117,.14); }

    .btn-glass-primary, .btn-glass-outline {
        display:inline-flex; align-items:center; justify-content:center; gap:7px;
        padding: 9px 16px; border-radius: 12px; font-size: 12.5px; font-weight: 700;
        border: 1px solid transparent; cursor: pointer; text-decoration:none; white-space:nowrap;
        transition: transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s ease, filter .2s ease, background .2s ease;
    }
    .btn-glass-sm { padding: 6px 12px; font-size: 11.5px; }
    .btn-glass-primary { background: linear-gradient(135deg, var(--purple-light), var(--purple-dark)); color:#fff; box-shadow: 0 6px 18px rgba(106,44,117,.3); }
    .btn-glass-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
    .btn-glass-outline { background: rgba(106,44,117,.06); color: var(--purple); border-color: rgba(106,44,117,.25); }
    .btn-glass-outline:hover { transform: translateY(-2px); background: rgba(106,44,117,.12); }

    /* ── Tabla ── */
    .ix-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .ix-table thead tr { border-bottom: 1px solid rgba(0,0,0,.06); }
    .ix-table th {
        padding: 14px 20px; text-align: left; font-size: 10.5px; font-weight: 800;
        text-transform: uppercase; letter-spacing: .1em; color: #9ca3af; white-space: nowrap;
    }
    .ix-table tbody tr { border-bottom: 1px solid rgba(0,0,0,.05); transition: background .15s ease; }
    .ix-table tbody tr:last-child { border-bottom: none; }
    .ix-table tbody tr:hover { background: rgba(106,44,117,.04); }
    .ix-table td { padding: 14px 20px; vertical-align: middle; }
    .ix-codigo { font-family: ui-monospace, 'SFMono-Regular', Consolas, monospace; font-weight: 800; color: var(--purple); }
    .ix-desc { max-width: 320px; color: #374151; }
    .ix-empty { text-align: center; padding: 48px 12px; color: #9ca3af; font-size: 13.5px; }

    .ix-progress-track { height: 8px; border-radius: 9999px; background: rgba(106,44,117,.08); overflow: hidden; }
    .ix-progress-fill { height: 100%; border-radius: 9999px; background: linear-gradient(90deg, var(--purple-light), var(--purple)); }

    /* ── Badges ── */
    .badge-pill { display:inline-flex; align-items:center; gap:5px; padding: 4px 12px; border-radius: 9999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; border: 1px solid transparent; }
    .badge-purple  { background: rgba(106,44,117,.1); color: var(--purple); border-color: rgba(106,44,117,.2); }
    .badge-slate   { background: rgba(148,163,184,.15); color: #475569; border-color: rgba(148,163,184,.3); }
    .badge-sky     { background: rgba(14,165,233,.1); color: #0369a1; border-color: rgba(14,165,233,.25); }
    .badge-amber   { background: rgba(217,119,6,.1); color: #92400e; border-color: rgba(217,119,6,.25); }
    .badge-emerald { background: rgba(5,150,105,.1); color: #065f46; border-color: rgba(5,150,105,.25); }

    .ix-pagination { padding: 16px 24px; border-top: 1px solid rgba(0,0,0,.05); }
    .ix-pagination nav { font-size: 13px; }

    @media (prefers-reduced-motion: reduce) {
        .reveal-3d { opacity: 1 !important; transform: none !important; transition: none !important; }
        .tilt-card { transform: none !important; }
        .gold-gleam { animation: none !important; }
    }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const targets = document.querySelectorAll('.reveal-3d');
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry, idx) => {
                        if (entry.isIntersecting) {
                            setTimeout(() => entry.target.classList.add('is-visible'), idx * 30);
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: .08, rootMargin: '0px 0px -40px 0px' });
                targets.forEach(el => io.observe(el));
            } else {
                targets.forEach(el => el.classList.add('is-visible'));
            }

            if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
                document.querySelectorAll('.tilt-card').forEach(card => {
                    card.addEventListener('mousemove', (e) => {
                        const r = card.getBoundingClientRect();
                        const px = (e.clientX - r.left) / r.width - .5;
                        const py = (e.clientY - r.top) / r.height - .5;
                        card.style.transform = `perspective(1000px) rotateY(${px * 2.5}deg) rotateX(${-py * 2}deg) translateY(-2px)`;
                    });
                    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
                });
            }
        });
    </script>

</x-app-layout>
