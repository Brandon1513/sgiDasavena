<x-app-layout>

    @php
    $user = auth()->user();
    $roles = $user && method_exists($user, 'getRoleNames')
    ? $user->getRoleNames()->implode(', ')
    : '';
    @endphp
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

    <img src="https://dasavenasite.domcloud.dev/images/dasa vena-logo.png"
        alt="dsda"
        class="pointer-events-none fixed bottom-6 right-6 object-contain z-10 select-none"
        style="mix-blend-mode: multiply; width: 130px; height: auto;">

    <img src="https://dasavenasite.domcloud.dev/images/dasavena-logo.png"
        alt="Dasavena Gourmet watermark"
        class="pointer-events-none fixed bottom-6 right-6 w-20 h-20 object-contain z-5 select-none opacity-20"
        style="mix-blend-mode:multiply Z-20">

    <script>
        window.addEventListener('load', () => {
            const loading = document.getElementById('sgi-loading');
            loading.style.opacity = '0';
            loading.style.pointerEvents = 'none';
            loading.style.transition = 'opacity 0.6s cubic-bezier(.22,1,.36,1)';
        });
    </script>

    {{-- TOAST --}}
    <div id="sgi-toast" aria-live="polite">
        <div class="sgi-toast-glass dc-header-face::after">
            <div class="sgi-toast-orb">
                <span class="sgi-toast-initial">{{ strtoupper(mb_substr($user->name,0,1)) }}</span>
                <div class="sgi-toast-halo"></div>
            </div>
            <div>
                <p class="sgi-toast-hi">¡Hola de nuevo!</p>
                <p class="sgi-toast-user">{{ $user->name }}</p>
            </div>
            <span class="sgi-toast-live"></span>
        </div>
        <div class="sgi-toast-bar"></div>
    </div>

    {{-- FONDO --}}
    <div class="sgi-root min-h-screen relative bg-fixed bg-center bg-cover"
        style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">

        <img src="https://dasavenasite.domcloud.dev/images/dasavena-logo.png" alt="" aria-hidden="true"
            class="pointer-events-none fixed inset-0 m-auto opacity-[.07] w-80 h-80 md:w-96 md:h-96 object-contain z-0 select-none"
            style="mix-blend-mode:multiply">

        <div class="sgi-overlay" aria-hidden="true"></div>

        <div class="relative z-10 min-h-screen flex items-start justify-center py-10 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-7xl space-y-6">

                {{-- HERO --}}
                <div class="crystal-hero dc-header-face tilt-card sgi-reveal" style="--ri:1">
                    <div class="crystal-shine"></div>
                    <div class="flex flex-col lg:flex-row items-start lg:items-center gap-8 p-8 md:p-10">

                        <img src="/images/Valentia .png" alt="Valentia" class="fixed left-6 object-contain z-10 select-none" style="mix-blend-mode: multiply; width: 300px; height: auto; top:5%; margin-left:850px;">

                        <div class="flex-1 space-y-4">
                            <div class="sgi-eyebrow">
                                <span class="sgi-pulse-ring"></span>
                                Sistema de Gestión Integral
                            </div>
                            <h1 class="sgi-hero-title">
                                Hola,<br>
                                <span class="sgi-gradient-name">{{ $user->name }}</span>
                            </h1>
                            <p class="sgi-hero-sub">
                                Bienvenido a <strong class="sgi-gradient-name">DasavenaSGI</strong>.
                                Consulta el estado de tus solicitudes de formatos, aprobaciones y actividad reciente.
                            </p>
                            @if($roles)
                            <div class="sgi-role-badge">
                                <span class="sgi-role-dot"></span>
                                {{ $roles }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════
                     TARJETAS DE ESTADO — FLIP REDISEÑADAS
                ═══════════════════════════════ --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sgi-reveal" style="--ri:2">
                    @foreach([
                        ['label'=>'Pendientes',    'val'=>$pendientes??'—',   'cls'=>'fc-purple', 'desc'=>'Esperando revisión del jefe'],
                        ['label'=>'Aprobado jefe', 'val'=>$aprobadoJefe??'—', 'cls'=>'fc-gold',   'desc'=>'En proceso de gestión SGI'],
                        ['label'=>'Atendidas',     'val'=>$atendidas??'—',    'cls'=>'fc-sage',   'desc'=>'Formato actualizado con éxito'],
                        ['label'=>'Rechazadas',    'val'=>$rechazadas??'—',   'cls'=>'fc-rose2',  'desc'=>'Requieren revisión y reenvío'],
                    ] as $s)
                    <article class="flip-card">
                        <div class="flip-card-inner">

                            {{-- FRENTE --}}
                            <div class="flip-front flip-front-new {{ $s['cls'] }}">
                                <div class="fc-shine"></div>
                                <div class="fc-orb-bg"></div>
                                <span class="fc-num-new">{{ $s['val'] }}</span>
                                <span class="fc-label-new">{{ $s['label'] }}</span>
                            </div>

                            {{-- REVERSO --}}
                            <div class="flip-back flip-back-new {{ $s['cls'] }}-back">
                                <div class="fc-shine"></div>
                                <p class="fc-back-desc-new">{{ $s['desc'] }}</p>
                                <span class="fc-ver-btn">Ver más →</span>
                            </div>

                        </div>
                    </article>
                    @endforeach
                </div>

                {{-- GRID PRINCIPAL --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- COLUMNA IZQUIERDA (2/3) --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- GRÁFICA --}}
                        <div class="crystal-card tilt-card sgi-reveal" style="--ri:3">
                            <div class="crystal-shine"></div>
                            <div class="p-6 md:p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h2 class="sgi-card-title">Actividad del sistema</h2>
                                        <p class="sgi-card-sub">Solicitudes registradas — últimos 30 días</p>
                                    </div>
                                    <div class="sgi-chart-badge">
                                        <span class="sgi-chart-dot"></span>
                                        En tiempo real
                                    </div>
                                </div>
                                <div class="sgi-chart-wrap">
                                    <canvas id="mainChart"></canvas>
                                </div>
                            </div>
                        </div>

                        {{-- ACCORDIONS --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sgi-reveal" style="--ri:4">
                            @foreach([
                            ['icon'=>'📋','color'=>'indigo','title'=>'Solicitudes','sub'=>'Altas, bajas y actualizaciones','items'=>['Flujo completo: usuario → jefe → SGI.','Identifica solicitudes detenidas por etapa.']],
                            ['icon'=>'📄','color'=>'violet','title'=>'Documentos','sub'=>'Formatos SGI vigentes','items'=>['Control de versiones y fechas de alta.','Liga documentos con solicitudes atendidas.']],
                            ['icon'=>'🔐','color'=>'teal','title'=>'Auditorías','sub'=>'Evidencia y trazabilidad','items'=>['Evidencia para control documental ISO/auditorías.']],
                            ] as $d)
                            <details class="acc3d acc3d-{{ $d['color'] }}">
                                <summary class="acc3d-head">
                                    <div class="flex items-center gap-3">
                                        <div class="acc3d-icon-wrap">{{ $d['icon'] }}</div>
                                        <div>
                                            <p class="acc3d-title">{{ $d['title'] }}</p>
                                            <p class="acc3d-sub">{{ $d['sub'] }}</p>
                                        </div>
                                    </div>
                                    <span class="acc3d-chevron">›</span>
                                </summary>
                                <div class="acc3d-body">
                                    @foreach($d['items'] as $item)
                                    <div class="acc3d-item">
                                        <span class="acc3d-bullet"></span>
                                        <p>{{ $item }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </details>
                            @endforeach
                            <div>
                                <img src="/images/Creatividad.png" alt="creatividad" class="z-10 left-6 object-contain select-none" style="mix-blend-mode: multiply; width: 330px; height:auto; margin-top: 40px; position:relative">
                            </div>
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA (1/3) --}}
                    <div class="space-y-6">

                        {{-- ACTIVIDAD --}}
                        <div class="crystal-card tilt-card sgi-reveal" style="--ri:3">
                            <div class="crystal-shine"></div>
                            <div class="p-6">
                                <h2 class="sgi-card-title mb-1">Actividad reciente</h2>
                                <p class="sgi-card-sub mb-5">Últimas acciones en el sistema</p>

                                @if(!empty($ultimasSolicitudes) && count($ultimasSolicitudes))
                                <ul class="space-y-3 max-h-[340px] overflow-y-auto pr-1">
                                    @foreach($ultimasSolicitudes as $item)
                                    <li class="act3d-item">
                                        <div class="act3d-avatar">
                                            {{ strtoupper(mb_substr($item->usuario->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="act3d-name truncate">{{ $item->usuario->name ?? 'Usuario' }}</p>
                                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                                <span class="act3d-estado">{{ ucfirst(str_replace('_',' ',$item->estado)) }}</span>
                                                <span class="act3d-accion">{{ $item->accion }}</span>
                                            </div>
                                            @if($item->comentarios)
                                            <p class="act3d-comment line-clamp-1">
                                                "{{ \Illuminate\Support\Str::limit($item->comentarios, 55) }}"
                                            </p>
                                            @endif
                                            <p class="act3d-time">{{ $item->created_at?->format('d/m/Y H:i') }}</p>
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
                        </div>

                        {{-- ACCESOS RÁPIDOS --}}
                        <div class="crystal-card tilt-card sgi-reveal" style="--ri:4">
                            <div class="crystal-shine"></div>
                            <div class="p-6">
                                <h2 class="sgi-card-title mb-1">Accesos rápidos</h2>
                                <p class="sgi-card-sub mb-5">Navegación directa al sistema</p>
                                <div class="space-y-2.5">
                                    <a href="{{ route('solicitudes.create') }}" class="ql3d ql3d-primary">
                                        <span class="ql3d-iconbox">＋</span>
                                        <span class="flex-1">Crear nueva solicitud</span>
                                        <span class="ql3d-arr">→</span>
                                    </a>
                                    <a href="{{ route('solicitudes.index') }}" class="ql3d">
                                        <span class="ql3d-emoji">📋</span>
                                        <span class="flex-1">Ver todas las solicitudes</span>
                                        <span class="ql3d-arr">→</span>
                                    </a>
                                    @if($user->hasRole('jefe'))
                                    <a href="{{ route('solicitudes.index', ['estado' => 'pendiente']) }}" class="ql3d ql3d-amber">
                                        <span class="ql3d-emoji">⏳</span>
                                        <span class="flex-1">Pendientes por aprobar</span>
                                        <span class="ql3d-arr">→</span>
                                    </a>
                                    @endif
                                    @if($user->hasRole('administrador_sgi'))
                                    <a href="{{ route('solicitudes.index', ['estado' => 'aprobado_jefe']) }}" class="ql3d ql3d-teal">
                                        <span class="ql3d-emoji">📄</span>
                                        <span class="flex-1">Listas para alta SGI</span>
                                        <span class="ql3d-arr">→</span>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PIE — TARJETAS INFORMATIVAS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sgi-reveal" style="--ri:5">
                    @foreach([
                    ['icon'=>'🔍','color'=>'sky','title'=>'Trazabilidad completa','text'=>'Cada solicitud genera evidencia auditoria. Registra quién, cuándo y qué cambió en cada formato del sistema.'],
                    ['icon'=>'⚙️','color'=>'violet','title'=>'Flujo estandarizado','text'=>'Un solo proceso aprobado: el usuario solicita, el jefe aprueba, SGI ejecuta y cierra el ciclo documental.'],
                    ['icon'=>'📈','color'=>'emerald','title'=>'Mejora continua','text'=>'Identifica patrones en las solicitudes, detecta áreas con más cambios y mide el tiempo de respuesta del equipo.'],
                    ] as $f)
                    <div class="info3d-card info3d-{{ $f['color'] }} tilt-card">
                        <div class="crystal-shine"></div>
                        <div class="info3d-body">
                            <div class="info3d-icon-wrap">{{ $f['icon'] }}</div>
                            <h4 class="info3d-title">{{ $f['title'] }}</h4>
                            <p class="info3d-text">{{ $f['text'] }}</p>
                        </div>
                        <div class="info3d-bar"></div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Outfit:wght@300;400;500;600&display=swap');

        .sgi-root * { font-family: 'Century Gothic', sans-serif; box-sizing: border-box; }

        .sgi-overlay {
            pointer-events: none; position: fixed; inset: 0; z-index: 1;
            background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(255,255,255,.45) 0%, transparent 70%);
        }

        .sgi-reveal {
            opacity: 0; transform: translateY(22px);
            animation: sgi-in .65s cubic-bezier(.22,1,.36,1) forwards;
            animation-delay: calc(var(--ri,1) * .08s);
        }
        @keyframes sgi-in { to { opacity:1; transform:translateY(0); } }

        /* ── Crystal Card ── */
        .crystal-card {
            position: relative; overflow: hidden;
            background: rgba(255,255,255,.62);
            border: 1px solid rgba(255,255,255,.95);
            border-radius: 24px;
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            box-shadow: 0 1px 0 0 rgba(255,255,255,.9) inset, 0 -1px 0 0 rgba(0,0,0,.04) inset, 0 8px 40px rgba(99,102,241,.08), 0 2px 8px rgba(0,0,0,.06);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .crystal-hero {
            position: relative; overflow: hidden;
            background-color: #6A2C75;
            border: 1px solid rgba(255,255,255,.95);
            border-radius: 24px;
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            box-shadow: 0 1px 0 0 rgba(255,255,255,.9) inset, 0 8px 40px rgba(99,102,241,.08), 0 2px 8px rgba(0,0,0,.06);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .crystal-shine {
            pointer-events: none; position: absolute; inset: 0; z-index: 0; border-radius: 24px;
            background: linear-gradient(130deg, rgba(255,255,255,.55) 0%, rgba(255,255,255,.0) 40%, rgba(255,255,255,.15) 100%);
        }
        .tilt-card { transform-style: preserve-3d; will-change: transform; }
        .tilt-card > * { position: relative; z-index: 1; }

        /* ── Toast ── */
        #sgi-toast {
            position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
            min-width: 270px; border-radius: 18px; overflow: hidden;
            background: rgba(255,255,255,.75); border: 1px solid rgba(255,255,255,.95);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 8px 40px rgba(99,102,241,.18), 0 2px 8px rgba(0,0,0,.08);
            opacity: 0; transform: translateY(-16px) scale(.94);
            transition: opacity .45s cubic-bezier(.22,1,.36,1), transform .45s cubic-bezier(.22,1,.36,1);
        }
        #sgi-toast.show { opacity:1; transform:translateY(0) scale(1); }
        #sgi-toast.hide { opacity:0; transform:translateY(-12px) scale(.95); }
        .sgi-toast-glass { display:flex; align-items:center; gap:12px; padding:14px 16px; }
        .sgi-toast-orb {
            position:relative; width:42px; height:42px; border-radius:50%; flex-shrink:0;
            background:linear-gradient(135deg,#818cf8,#4f46e5);
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 4px 16px rgba(79,70,229,.35);
        }
        .sgi-toast-initial { color:#fff; font-weight:700; font-size:15px; }
        .sgi-toast-halo {
            position:absolute; inset:-4px; border-radius:50%;
            border:2px solid rgba(99,102,241,.4);
            animation: halo-pulse 2s ease-in-out infinite;
        }
        @keyframes halo-pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.3;transform:scale(1.15)} }
        .sgi-toast-hi  { font-size:12px; font-weight:600; color:#D6A644; margin:0; }
        .sgi-toast-user{ font-size:12px; color:#6b7280; margin:2px 0 0; font-weight:400; }
        .sgi-toast-live {
            width:8px; height:8px; border-radius:50%; background:#d6a644;
            box-shadow:0 0 0 3px rgba(16,185,129,.2);
            animation: toast-live 1.6s ease-in-out infinite; flex-shrink:0;
        }
        @keyframes toast-live { 0%,100%{box-shadow:0 0 0 3px rgba(16,185,129,.2)} 50%{box-shadow:0 0 0 6px rgba(16,185,129,.05)} }
        .sgi-toast-bar { height:3px; background:linear-gradient(90deg,#818cf8,#4f46e5,#7c3aed); border-radius:0 0 18px 18px; }

        /* ── Hero ── */
        .sgi-eyebrow { display:inline-flex; align-items:center; gap:8px; font-size:11px; text-transform:uppercase; letter-spacing:.18em; color:#D6A644; font-weight:600; }
        .sgi-pulse-ring { width:8px; height:8px; border-radius:50%; background:#D6A644; box-shadow:0 0 0 3px rgba(100,500,68,.3); animation:toast-live 1.6s ease-in-out infinite; flex-shrink:0; }
        .sgi-hero-title { font-family:'Century Gothic',sans-serif; font-size:clamp(1.9rem,4.5vw,3rem); font-weight:800; line-height:1.05; letter-spacing:-.02em; color:#ffffff; margin:0; }
        .sgi-gradient-name {
            background:linear-gradient(135deg,#6366f1 0%,#a78bfa 50%,#ffffff 100%);
            background-size:200% 200%;
            -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent;
            animation:gradient-shift 4s ease infinite;
        }
        @keyframes gradient-shift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
        .sgi-hero-sub { font-size:.9rem; color:#ffff; line-height:1.65; margin:0; max-width:460px; font-weight:400; }
        .sgi-role-badge { display:inline-flex; align-items:center; gap:6px; padding:5px 14px; background:rgba(99,102,241,.1); border:1px solid rgba(99,102,241,.25); border-radius:30px; font-size:12px; color:#fff; font-weight:500; }
        .sgi-role-dot { width:6px; height:6px; border-radius:50%; background:#6366f1; }

        .dc-header-face::before { content:''; position:absolute; top:0; left:0; right:0; height:1px; background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent); }
        .dc-header-face::after { z-index:20; content:''; pointer-events:none; position:absolute; inset:0; background-image:radial-gradient(rgba(255,255,255,.06) 1.8px,transparent 1px); background-size:18px 18px; border-radius:20px; animation:dc-pulse 2s ease-in-out infinite; }
        @keyframes dc-pulse { 0%,100%{opacity:1} 50%{opacity:0.5} }

        /* ══════════════════════════════════════
           FLIP CARDS — TEMA PÚRPURA / DORADO
        ══════════════════════════════════════ */
        .flip-card { perspective:1200px; height:148px; cursor:pointer; }
        .flip-card:hover { filter:drop-shadow(0 12px 32px rgba(106,44,117,.25)); }
        .flip-card-inner {
            position:relative; width:100%; height:100%;
            transform-style:preserve-3d;
            transition:transform .7s cubic-bezier(.68,-.55,.265,1.55);
        }
        .flip-card:hover .flip-card-inner { transform:rotateY(180deg); }

        .flip-front-new,
        .flip-back-new {
            position:absolute; inset:0; border-radius:20px;
            backface-visibility:hidden; -webkit-backface-visibility:hidden;
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            gap:8px; padding:20px 16px; overflow:hidden;
            border:1px solid rgba(255,255,255,.5);
            box-shadow: 0 1px 0 rgba(255,255,255,.9) inset, 0 8px 32px rgba(106,44,117,.12), 0 2px 8px rgba(0,0,0,.06);
            transition:box-shadow .3s ease;
        }
        .flip-back-new { transform:rotateY(180deg); }
        .flip-card:hover .flip-front-new,
        .flip-card:hover .flip-back-new { box-shadow:0 14px 40px rgba(106,44,117,.22), 0 2px 8px rgba(0,0,0,.08); }

        .fc-shine {
            pointer-events:none; position:absolute; inset:0; z-index:0; border-radius:20px;
            background:linear-gradient(130deg, rgba(255,255,255,.55) 0%, rgba(255,255,255,0) 45%, rgba(255,255,255,.12) 100%);
        }
        .fc-orb-bg {
            pointer-events:none; position:absolute;
            width:120px; height:120px; border-radius:50%;
            bottom:-40px; right:-30px;
            filter:blur(28px); opacity:.35; z-index:0;
        }
        .fc-num-new {
            position:relative; z-index:1;
            font-family:'Century Gothic',sans-serif;
            font-size:2.8rem; font-weight:900; line-height:1; letter-spacing:-.03em;
        }
        .fc-label-new {
            position:relative; z-index:1;
            font-size:10px; text-transform:uppercase; letter-spacing:.16em; font-weight:700;
        }
        .fc-back-desc-new {
            position:relative; z-index:1;
            font-size:12.5px; font-weight:500; text-align:center;
            line-height:1.55; margin:0; padding:0 4px;
        }
        .fc-ver-btn {
            position:relative; z-index:1;
            display:inline-block; padding:5px 16px; border-radius:100px;
            font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
            background:rgba(255,255,255,.35); border:1px solid rgba(255,255,255,.5);
            transition:background .2s;
        }
        .fc-ver-btn:hover { background:rgba(255,255,255,.55); }

        /* Variante PÚRPURA */
        .fc-purple { background:linear-gradient(135deg,rgba(106,44,117,.12),rgba(142,61,158,.08)); border-color:rgba(106,44,117,.25); }
        .fc-purple .fc-num-new { background:linear-gradient(135deg,#6A2C75,#a044b8); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
        .fc-purple .fc-label-new { color:#6A2C75; }
        .fc-purple .fc-orb-bg   { background:#6A2C75; }
        .fc-purple-back { background:linear-gradient(135deg,rgba(106,44,117,.18),rgba(142,61,158,.12)); border-color:rgba(106,44,117,.3); }
        .fc-purple-back .fc-back-desc-new { color:#4a2a55; }
        .fc-purple-back .fc-ver-btn { color:#6A2C75; border-color:rgba(106,44,117,.35); background:rgba(106,44,117,.1); }

        /* Variante DORADA */
        .fc-gold { background:linear-gradient(135deg,rgba(212,160,24,.12),rgba(240,200,74,.08)); border-color:rgba(212,160,24,.3); }
        .fc-gold .fc-num-new { background:linear-gradient(135deg,#b38600,#D4A018); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
        .fc-gold .fc-label-new { color:#b38600; }
        .fc-gold .fc-orb-bg   { background:#D4A018; }
        .fc-gold-back { background:linear-gradient(135deg,rgba(212,160,24,.15),rgba(240,200,74,.1)); border-color:rgba(212,160,24,.35); }
        .fc-gold-back .fc-back-desc-new { color:#7a5a00; }
        .fc-gold-back .fc-ver-btn { color:#b38600; border-color:rgba(212,160,24,.4); background:rgba(212,160,24,.1); }

        /* Variante SALVIA */
        .fc-sage { background:linear-gradient(135deg,rgba(5,150,105,.1),rgba(16,185,129,.07)); border-color:rgba(5,150,105,.22); }
        .fc-sage .fc-num-new { background:linear-gradient(135deg,#065f46,#059669); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
        .fc-sage .fc-label-new { color:#065f46; }
        .fc-sage .fc-orb-bg   { background:#10b981; }
        .fc-sage-back { background:linear-gradient(135deg,rgba(5,150,105,.15),rgba(16,185,129,.1)); border-color:rgba(5,150,105,.28); }
        .fc-sage-back .fc-back-desc-new { color:#064e3b; }
        .fc-sage-back .fc-ver-btn { color:#065f46; border-color:rgba(5,150,105,.35); background:rgba(5,150,105,.1); }

        /* Variante ROSA */
        .fc-rose2 { background:linear-gradient(135deg,rgba(190,18,60,.09),rgba(244,63,94,.06)); border-color:rgba(190,18,60,.2); }
        .fc-rose2 .fc-num-new { background:linear-gradient(135deg,#be123c,#f43f5e); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
        .fc-rose2 .fc-label-new { color:#be123c; }
        .fc-rose2 .fc-orb-bg   { background:#f43f5e; }
        .fc-rose2-back { background:linear-gradient(135deg,rgba(190,18,60,.13),rgba(244,63,94,.09)); border-color:rgba(190,18,60,.25); }
        .fc-rose2-back .fc-back-desc-new { color:#881337; }
        .fc-rose2-back .fc-ver-btn { color:#be123c; border-color:rgba(190,18,60,.35); background:rgba(190,18,60,.08); }

        /* ── Chart ── */
        .sgi-card-title { font-family:'Century Gothic',sans-serif; font-size:17px; font-weight:700; color:#1e1b4b; margin:0; }
        .sgi-card-sub   { font-size:12px; color:#9ca3af; margin:2px 0 0; font-weight:400; }
        .sgi-chart-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.25); border-radius:20px; font-size:11px; color:#065f46; font-weight:600; white-space:nowrap; }
        .sgi-chart-dot  { width:6px; height:6px; border-radius:50%; background:#10b981; animation:toast-live 1.6s ease-in-out infinite; }
        .sgi-chart-wrap { position:relative; }

        /* ── Accordions ── */
        .acc3d { background:rgba(255,255,255,.58); border:1px solid rgba(255,255,255,.9); border-radius:18px; backdrop-filter:blur(20px); overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.05); transition:box-shadow .25s,border-color .25s; }
        .acc3d[open] { box-shadow:0 8px 32px rgba(0,0,0,.1); }
        .acc3d-indigo[open],.acc3d-indigo:hover { border-color:rgba(129,140,248,.5); box-shadow:0 8px 32px rgba(99,102,241,.12); }
        .acc3d-violet[open],.acc3d-violet:hover { border-color:rgba(167,139,250,.5); box-shadow:0 8px 32px rgba(124,58,237,.12); }
        .acc3d-teal[open],.acc3d-teal:hover     { border-color:rgba(94,234,212,.5);  box-shadow:0 8px 32px rgba(20,184,166,.12); }
        .acc3d-head { display:flex; align-items:center; justify-content:space-between; padding:16px; list-style:none; cursor:pointer; user-select:none; }
        .acc3d-head::-webkit-details-marker { display:none; }
        .acc3d-icon-wrap { width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,.7); border:1px solid rgba(0,0,0,.06); display:flex; align-items:center; justify-content:center; font-size:16px; box-shadow:0 2px 6px rgba(0,0,0,.06); flex-shrink:0; }
        .acc3d-title { font-size:13px; font-weight:600; color:#1e1b4b; margin:0; }
        .acc3d-sub   { font-size:11px; color:#9ca3af; margin:1px 0 0; }
        .acc3d-chevron { font-size:18px; color:#9ca3af; font-weight:600; line-height:1; transition:transform .3s cubic-bezier(.22,1,.36,1); }
        details[open] .acc3d-chevron { transform:rotate(90deg); }
        .acc3d-body  { padding:0 16px 16px; border-top:1px solid rgba(0,0,0,.05); padding-top:12px; }
        .acc3d-item  { display:flex; align-items:flex-start; gap:8px; padding:4px 0; }
        .acc3d-bullet{ width:5px; height:5px; border-radius:50%; background:#a5b4fc; flex-shrink:0; margin-top:6px; }
        .acc3d-item p{ font-size:12px; color:#6b7280; margin:0; line-height:1.5; }

        /* ── Actividad ── */
        .act3d-item { display:flex; gap:10px; align-items:flex-start; padding:10px; border-radius:14px; transition:background .2s; }
        .act3d-item:hover { background:rgba(99,102,241,.05); }
        .act3d-avatar { width:36px; height:36px; border-radius:50%; flex-shrink:0; background:linear-gradient(135deg,#818cf8,#4f46e5); display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; font-weight:700; box-shadow:0 4px 12px rgba(79,70,229,.3); }
        .act3d-name   { font-size:13px; font-weight:600; color:#1e1b4b; margin:0; }
        .act3d-estado { font-size:10px; background:rgba(99,102,241,.1); color:#4338ca; padding:1px 8px; border-radius:10px; font-weight:600; }
        .act3d-accion { font-size:10px; color:#9ca3af; }
        .act3d-comment{ font-size:11px; color:#9ca3af; font-style:italic; margin:4px 0 0; }
        .act3d-time   { font-size:10px; color:#d1d5db; margin:4px 0 0; }
        .sgi-empty    { text-align:center; padding:2.5rem 0; }
        .sgi-empty-icon{ font-size:32px; display:block; margin-bottom:8px; opacity:.5; }
        .sgi-empty p  { font-size:13px; color:#9ca3af; margin:0; }

        /* ── Quick Links ── */
        .ql3d { display:flex; align-items:center; gap:10px; padding:12px 14px; border-radius:14px; background:rgba(255,255,255,.55); border:1px solid rgba(255,255,255,.85); font-size:13px; font-weight:500; color:#374151; text-decoration:none; backdrop-filter:blur(8px); box-shadow:0 2px 8px rgba(0,0,0,.04); transition:all .25s ease; }
        .ql3d:hover { background:rgba(255,255,255,.85); border-color:rgba(99,102,241,.3); color:#4338ca; box-shadow:0 6px 20px rgba(99,102,241,.12); transform:translateY(-2px); }
        .ql3d:hover .ql3d-arr { transform:translateX(4px); opacity:1; }
        .ql3d-primary { background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(124,58,237,.1)); border-color:rgba(99,102,241,.35); color:#4338ca; }
        .ql3d-primary:hover { background:linear-gradient(135deg,rgba(99,102,241,.25),rgba(124,58,237,.18)); }
        .ql3d-amber { background:rgba(254,243,199,.7); border-color:rgba(253,211,77,.4); color:#92400e; }
        .ql3d-amber:hover { border-color:rgba(245,158,11,.5); color:#78350f; }
        .ql3d-teal  { background:rgba(204,251,241,.7); border-color:rgba(94,234,212,.4); color:#065f46; }
        .ql3d-teal:hover { border-color:rgba(20,184,166,.5); color:#064e3b; }
        .ql3d-iconbox { width:28px; height:28px; border-radius:8px; flex-shrink:0; background:linear-gradient(135deg,#818cf8,#4f46e5); color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:700; line-height:1; box-shadow:0 3px 10px rgba(79,70,229,.35); }
        .ql3d-emoji { font-size:16px; flex-shrink:0; }
        .ql3d-arr   { opacity:.35; transition:transform .2s, opacity .2s; margin-left:auto; }

        /* ── Info Cards ── */
        .info3d-card { position:relative; overflow:hidden; border-radius:22px; backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border:1px solid rgba(255,255,255,.9); box-shadow:0 4px 24px rgba(0,0,0,.07),0 1px 0 rgba(255,255,255,.8) inset; transition:transform .25s ease,box-shadow .25s ease; }
        .info3d-sky     { background:rgba(224,242,254,.72); }
        .info3d-violet  { background:rgba(237,233,254,.72); }
        .info3d-emerald { background:rgba(209,250,229,.72); }
        .info3d-body    { padding:24px 24px 20px; }
        .info3d-icon-wrap{ width:44px; height:44px; border-radius:14px; margin-bottom:14px; background:rgba(255,255,255,.7); border:1px solid rgba(0,0,0,.06); display:flex; align-items:center; justify-content:center; font-size:20px; box-shadow:0 2px 8px rgba(0,0,0,.07); }
        .info3d-title   { font-family:'Century Gothic',sans-serif; font-size:14px; font-weight:700; color:#1e1b4b; margin:0 0 8px; }
        .info3d-text    { font-size:12.5px; color:#4b5563; line-height:1.65; margin:0; }
        .info3d-bar     { height:3px; background:linear-gradient(90deg,rgba(99,102,241,.5),rgba(124,58,237,.3)); }
        .info3d-sky .info3d-bar     { background:linear-gradient(90deg,rgba(14,165,233,.5),rgba(56,189,248,.3)); }
        .info3d-emerald .info3d-bar { background:linear-gradient(90deg,rgba(5,150,105,.5),rgba(16,185,129,.3)); }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width:4px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:rgba(99,102,241,.2); border-radius:4px; }
        ::-webkit-scrollbar-thumb:hover { background:rgba(99,102,241,.35); }
    </style>

    <script>
        /* Toast */
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('sgi-toast');
            setTimeout(() => toast.classList.add('show'), 350);
            setTimeout(() => toast.classList.add('hide'), 3900);
        });

        /* Tilt 3D */
        document.querySelectorAll('.tilt-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top)  / r.height - 0.5;
                card.style.transform = `perspective(700px) rotateY(${x*10}deg) rotateX(${-y*8}deg) scale3d(1.02,1.02,1.02)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(700px) rotateY(0) rotateX(0) scale3d(1,1,1)';
            });
        });

        /* Gráfica */
        const diasLabels = @json($porDia->pluck('fecha'));
        const diasData   = @json($porDia->pluck('total'));

        Chart.defaults.font.family  = "'Outfit', sans-serif";
        Chart.defaults.font.size    = 12;
        Chart.defaults.color        = '#9ca3af';
        Chart.defaults.borderColor  = 'rgba(0,0,0,.05)';

        new Chart(document.getElementById('mainChart'), {
            type: 'line',
            data: {
                labels: diasLabels,
                datasets: [{
                    label: 'Solicitudes',
                    data: diasData,
                    tension: 0.45,
                    borderColor: '#4f46e5',
                    backgroundColor: ctx => {
                        const g = ctx.chart.ctx.createLinearGradient(0,0,0,ctx.chart.height);
                        g.addColorStop(0,   'rgba(79,70,229,.25)');
                        g.addColorStop(.6,  'rgba(124,58,237,.08)');
                        g.addColorStop(1,   'rgba(79,70,229,0)');
                        return g;
                    },
                    borderWidth: 2.5, fill: true,
                    pointRadius: 5, pointBackgroundColor: '#fff',
                    pointBorderColor: '#4f46e5', pointBorderWidth: 2.5,
                    pointHoverRadius: 7, pointHoverBackgroundColor: '#4f46e5',
                    pointHoverBorderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                interaction: { intersect:false, mode:'index' },
                plugins: {
                    legend: { display:false },
                    tooltip: {
                        backgroundColor: 'rgba(255,255,255,.95)',
                        titleColor: '#1e1b4b', bodyColor: '#4b5563',
                        borderColor: 'rgba(99,102,241,.25)', borderWidth:1,
                        padding:12, cornerRadius:12,
                        callbacks: {
                            title: items => items[0].label,
                            label: item  => ` ${item.raw} solicitudes`
                        }
                    }
                },
                scales: {
                    x: { grid:{display:false}, ticks:{maxTicksLimit:8,color:'#d1d5db'} },
                    y: { beginAtZero:true, grid:{color:'rgba(0,0,0,.04)'}, ticks:{color:'#d1d5db',padding:8} }
                }
            }
        });
    </script>

</x-app-layout>