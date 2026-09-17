<x-app-layout>

    <div class="cr-root relative min-h-screen">
        <div class="cr-bg pointer-events-none fixed inset-0 z-0" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');"></div>
        <div class="cr-veil pointer-events-none fixed inset-0 z-[1]"></div>

        <div class="relative z-10 mx-auto flex max-w-[860px] flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">

            {{-- =========================================================
                ENCABEZADO
            ========================================================== --}}
            <header class="cr-card cr-card-hero tilt-card reveal-3d relative overflow-hidden">
                <div class="gold-gleam absolute inset-x-0 top-0 z-[2] h-[3px]"></div>
                <div class="cr-hero-dotgrid pointer-events-none absolute inset-0" aria-hidden="true"></div>

                <div class="relative z-[1] flex flex-col gap-3 p-6 sm:p-8">
                    <a href="{{ route('acciones-correctivas.index') }}" class="cr-back-link">
                        <i class="ti ti-arrow-left"></i> Acciones Correctivas
                    </a>
                    <div>
                        <span class="cr-eyebrow">Nuevo expediente SGI</span>
                        <h1 class="mt-2 font-display text-2xl font-extrabold text-white sm:text-3xl">Nueva Acción Correctiva</h1>
                        <p class="mt-2 max-w-md text-sm text-white/70">Registra una nueva Acción Correctiva.</p>
                    </div>
                </div>
            </header>

            {{-- =========================================================
                ERRORES DE VALIDACIÓN
            ========================================================== --}}
            @if ($errors->any())
            <div class="cr-alert cr-alert-danger reveal-3d">
                <i class="ti ti-alert-triangle"></i>
                <div class="flex-1">
                    <div class="mb-1 font-bold">Revisa los siguientes campos:</div>
                    <ul class="mb-0 list-disc pl-4">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- =========================================================
                FORMULARIO
            ========================================================== --}}
            <section class="cr-card tilt-card reveal-3d">
                <div class="p-6 sm:p-8">
                    <form method="POST" action="{{ route('acciones-correctivas.store') }}">
                        @csrf

                        <div class="mb-5">
                            <label for="descripcion" class="cr-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="cr-field" rows="5" maxlength="2000" required
                                placeholder="Describe la situación que origina la Acción Correctiva...">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label for="origen_id" class="cr-label">Origen</label>
                                <select id="origen_id" name="origen_id" class="cr-field" required>
                                    <option value="">Selecciona un origen</option>
                                    @foreach($origenes as $origen)
                                    <option value="{{ $origen->id }}" @selected(old('origen_id') == $origen->id)>{{ $origen->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="responsable_id" class="cr-label">Responsable</label>
                                <select id="responsable_id" name="responsable_id" class="cr-field" required>
                                    <option value="">Selecciona un responsable</option>
                                    @foreach($responsables as $responsable)
                                    <option value="{{ $responsable->id }}" @selected(old('responsable_id') == $responsable->id)>{{ $responsable->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('acciones-correctivas.index') }}" class="btn-glass-light">Cancelar</a>
                            <button type="submit" class="btn-glass-primary">
                                <i class="ti ti-plus"></i> Crear Acción Correctiva
                            </button>
                        </div>
                    </form>
                </div>
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

    .cr-bg { background-size: cover; background-position: center; background-attachment: fixed; opacity: .55; }
    .cr-veil {
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

    .cr-card {
        background: rgba(255,255,255,.68);
        border: 1px solid rgba(255,255,255,.7);
        border-radius: 24px;
        backdrop-filter: blur(20px) saturate(160%);
        -webkit-backdrop-filter: blur(20px) saturate(160%);
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 8px 32px rgba(106,44,117,.08);
    }
    .cr-card-hero {
        background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 55%, var(--purple-light) 100%);
        border-color: rgba(255,255,255,.15);
        box-shadow: 0 12px 44px rgba(106,44,117,.3);
    }
    .cr-hero-dotgrid {
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

    .cr-back-link { display:inline-flex; align-items:center; gap:6px; font-size: 12.5px; font-weight:600; color: rgba(255,255,255,.6); text-decoration:none; transition: color .2s ease; width: fit-content; }
    .cr-back-link:hover { color: #fff; }
    .cr-eyebrow { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .14em; color: rgba(255,255,255,.55); }

    .cr-label { display:block; margin-bottom: 7px; font-size: 13px; font-weight: 700; color: #374151; }
    .cr-field {
        width: 100%; border-radius: 12px; border: 1px solid rgba(106,44,117,.18);
        background: rgba(255,255,255,.75); padding: 10px 13px; font-size: 14px; color: #1f2937;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .cr-field:focus { outline:none; border-color: var(--purple); box-shadow: 0 0 0 3px rgba(106,44,117,.14); }

    .btn-glass-primary, .btn-glass-light {
        display:inline-flex; align-items:center; justify-content:center; gap:7px;
        padding: 10px 18px; border-radius: 12px; font-size: 13px; font-weight: 700;
        border: 1px solid transparent; cursor: pointer; text-decoration:none; white-space:nowrap;
        transition: transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s ease, filter .2s ease, background .2s ease;
    }
    .btn-glass-primary { background: linear-gradient(135deg, var(--purple-light), var(--purple-dark)); color:#fff; box-shadow: 0 6px 18px rgba(106,44,117,.3); }
    .btn-glass-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
    .btn-glass-light { background: rgba(255,255,255,.6); color: #475569; border-color: rgba(0,0,0,.08); backdrop-filter: blur(8px); }
    .btn-glass-light:hover { transform: translateY(-2px); background: rgba(255,255,255,.85); }

    .cr-alert { display:flex; align-items:flex-start; gap:12px; padding: 14px 18px; border-radius: 16px; font-size: 13.5px; }
    .cr-alert-danger { background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.25); color: #991b1b; }

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
                            setTimeout(() => entry.target.classList.add('is-visible'), idx * 40);
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: .1, rootMargin: '0px 0px -40px 0px' });
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
                        card.style.transform = `perspective(1000px) rotateY(${px * 3}deg) rotateX(${-py * 2.2}deg) translateY(-2px)`;
                    });
                    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
                });
            }
        });
    </script>

</x-app-layout>
