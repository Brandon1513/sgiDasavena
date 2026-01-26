<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard SGI
        </h2>
    </x-slot>

  @php
    $user = auth()->user();
    $roles = $user && method_exists($user, 'getRoleNames')
        ? $user->getRoleNames()->implode(', ')
        : '';
@endphp

    <div class="min-h-screen relative bg-fixed bg-center bg-cover"
         style="background-image: url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">

        <img src="https://dasavenasite.domcloud.dev/images/dasavena-logo.png"
             alt="Dasavena Watermark"
             class="pointer-events-none absolute inset-0 m-auto opacity-10 w-80 h-80 md:w-96 md:h-96 object-contain z-0 animate-pulse"
             style="mix-blend-mode: multiply;">

        <div class="relative z-10 min-h-screen flex items-start justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-7xl bg-white/70 backdrop-blur-md border border-white/40 rounded-2xl shadow-2xl overflow-hidden transition-all duration-500 hover:shadow-3xl hover:border-white/60">

                {{-- HERO SUPERIOR --}}
                <div class="flex flex-col md:flex-row items-start md:items-center gap-8 p-6 md:p-8 border-b border-white/40 bg-gradient-to-r from-white/60 to-white/20 animate-fade-in">
                    <div class="flex-1 space-y-3">
                        <p class="text-xs uppercase tracking-widest text-indigo-600 font-semibold animate-pulse">Sistema de Gestión Integral</p>
                        <h1 class="text-2xl md:text-4xl font-extrabold text-gray-900 bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Hola, {{ $user->name }}  👋
                        </h1>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Bienvenido a <span class="font-semibold text-indigo-600">DasavenaSGI</span>. Aquí puedes visualizar el estado de tus
                            solicitudes de formatos, aprobaciones y la actividad reciente del sistema.
                        </p>
                        @if($roles)
                            <p class="text-xs text-gray-500">
                                Roles asignados: <span class="font-semibold text-indigo-700 bg-indigo-50 px-2 py-1 rounded">{{ $roles }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- KPIs mini con animación --}}
                    <div class="w-full md:w-80 grid grid-cols-2 gap-4 animate-slide-in-right">
                        <div class="group bg-white/90 rounded-xl p-4 shadow-md border border-gray-100 transition-all duration-300 hover:shadow-lg hover:border-indigo-200 hover:-translate-y-1 cursor-pointer">
                            <div class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Solicitudes pendientes</div>
                            <div class="mt-2 text-3xl font-bold text-indigo-700 group-hover:scale-110 transition-transform">
                                {{ $pendientes ?? '—' }}
                            </div>
                            <div class="text-[11px] text-gray-500 mt-2">
                                En espera de revisión
                            </div>
                        </div>

                        <div class="group bg-white/90 rounded-xl p-4 shadow-md border border-gray-100 transition-all duration-300 hover:shadow-lg hover:border-emerald-200 hover:-translate-y-1 cursor-pointer">
                            <div class="text-[11px] text-gray-500 uppercase tracking-wider font-semibold">Atendidas (SGI)</div>
                            <div class="mt-2 text-3xl font-bold text-emerald-700 group-hover:scale-110 transition-transform">
                                {{ $atendidas ?? '—' }}
                            </div>
                            <div class="text-[11px] text-gray-500 mt-2">
                                Con formato actualizado
                            </div>
                        </div>

                        <div class="group col-span-2 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-4 shadow-md border border-indigo-100 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 cursor-pointer">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-[11px] text-gray-600 uppercase tracking-wider font-semibold">Total solicitudes</div>
                                    <div class="mt-2 text-2xl font-bold text-gray-900 group-hover:text-indigo-700 transition-colors">
                                        {{ $total ?? '—' }}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 text-right font-medium">
                                    Últimos<br>30 días
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GRID PRINCIPAL --}}
                <div class="p-6 md:p-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Columna izquierda: métricas --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Resumen por estado --}}
                        <div class="bg-white/90 rounded-xl p-6 shadow-md border border-gray-100 transition-all duration-300 hover:shadow-lg hover:border-gray-200 animate-fade-in">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <span class="w-1 h-6 bg-gradient-to-b from-indigo-600 to-purple-600 rounded-full"></span>
                                    Estado de solicitudes de formato
                                </h3>
                                <span class="text-xs font-medium text-gray-400 bg-gray-50 px-3 py-1 rounded-full">
                                    Vista general
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div class="group bg-gradient-to-br from-indigo-50 to-indigo-100/50 rounded-xl p-4 border border-indigo-200 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                                    <div class="text-[11px] text-indigo-700 uppercase tracking-wider font-bold">Pendientes</div>
                                    <div class="mt-2 text-2xl font-bold text-indigo-900 group-hover:text-indigo-600">
                                        {{ $pendientes ?? '—' }}
                                    </div>
                                </div>
                                <div class="group bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-xl p-4 border border-amber-200 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                                    <div class="text-[11px] text-amber-700 uppercase tracking-wider font-bold">Aprobado jefe</div>
                                    <div class="mt-2 text-2xl font-bold text-amber-900 group-hover:text-amber-600">
                                        {{ $aprobadoJefe ?? '—' }}
                                    </div>
                                </div>
                                <div class="group bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl p-4 border border-emerald-200 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                                    <div class="text-[11px] text-emerald-700 uppercase tracking-wider font-bold">Atendidas</div>
                                    <div class="mt-2 text-2xl font-bold text-emerald-900 group-hover:text-emerald-600">
                                        {{ $atendidas ?? '—' }}
                                    </div>
                                </div>
                                <div class="group bg-gradient-to-br from-rose-50 to-rose-100/50 rounded-xl p-4 border border-rose-200 transition-all duration-300 hover:shadow-md hover:-translate-y-1 cursor-pointer">
                                    <div class="text-[11px] text-rose-700 uppercase tracking-wider font-bold">Rechazadas</div>
                                    <div class="mt-2 text-2xl font-bold text-rose-900 group-hover:text-rose-600">
                                        {{ $rechazadas ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Gráficas con animación --}}
                            <div class="mt-8 space-y-6">
                                <div class="w-full rounded-lg bg-white/80 border border-gray-200 p-5 shadow-sm transition-all duration-300 hover:shadow-md">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-4">Distribución por estado</h4>
                                    <canvas id="estadoChart" class="animate-fade-in"></canvas>
                                </div>

                                <div class="w-full rounded-lg bg-white/80 border border-gray-200 p-5 shadow-sm transition-all duration-300 hover:shadow-md">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-4">Últimos 30 días</h4>
                                    <canvas id="diaChart" class="animate-fade-in"></canvas>
                                </div>
                            </div>
                        </div>

                        {{-- Detalles contextuales --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 animate-slide-in-up">
                            <details class="group peer bg-white/90 border border-gray-100 rounded-xl p-5 shadow-md cursor-pointer transition-all duration-300 hover:shadow-lg hover:border-indigo-200 open:ring-2 open:ring-indigo-300/50">
                                <summary class="flex items-center justify-between list-none select-none">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">📋</span>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">Solicitudes de formatos</div>
                                            <div class="text-xs text-gray-500">Altas, bajas y actualizaciones</div>
                                        </div>
                                    </div>
                                    <div class="text-indigo-600 group-open:rotate-180 transition-transform duration-300 text-xl">▾</div>
                                </summary>
                                <div class="mt-4 text-xs md:text-sm text-gray-600 space-y-2 animate-fade-in">
                                    <p class="flex items-center gap-2"><span class="text-indigo-600">✓</span> Visualiza el flujo completo: usuario → jefe → SGI.</p>
                                    <p class="flex items-center gap-2"><span class="text-indigo-600">✓</span> Revisa qué solicitudes están detenidas y en qué etapa.</p>
                                </div>
                            </details>

                            <details class="group bg-white/90 border border-gray-100 rounded-xl p-5 shadow-md cursor-pointer transition-all duration-300 hover:shadow-lg hover:border-purple-200 open:ring-2 open:ring-purple-300/50">
                                <summary class="flex items-center justify-between list-none select-none">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-lg">📄</span>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">Documentos SGI</div>
                                            <div class="text-xs text-gray-500">Formatos vigentes</div>
                                        </div>
                                    </div>
                                    <div class="text-purple-600 group-open:rotate-180 transition-transform duration-300 text-xl">▾</div>
                                </summary>
                                <div class="mt-4 text-xs md:text-sm text-gray-600 space-y-2 animate-fade-in">
                                    <p class="flex items-center gap-2"><span class="text-purple-600">✓</span> Conserva el control de versiones y fechas de alta en el SGI.</p>
                                    <p class="flex items-center gap-2"><span class="text-purple-600">✓</span> Relaciona ligas de documentos con tus solicitudes atendidas.</p>
                                </div>
                            </details>

                            <details class="group bg-white/90 border border-gray-100 rounded-xl p-5 shadow-md cursor-pointer transition-all duration-300 hover:shadow-lg hover:border-emerald-200 open:ring-2 open:ring-emerald-300/50">
                                <summary class="flex items-center justify-between list-none select-none">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">🔐</span>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">Auditorías y seguimiento</div>
                                            <div class="text-xs text-gray-500">Evidencias y trazabilidad</div>
                                        </div>
                                    </div>
                                    <div class="text-emerald-600 group-open:rotate-180 transition-transform duration-300 text-xl">▾</div>
                                </summary>
                                <div class="mt-4 text-xs md:text-sm text-gray-600 space-y-2 animate-fade-in">
                                    <p class="flex items-center gap-2"><span class="text-emerald-600">✓</span> Usa las solicitudes como evidencia de control documental para auditorías.</p>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- Columna derecha: actividad y accesos --}}
                    <div class="space-y-6">

                        {{-- Actividad reciente --}}
                        <div class="bg-white/90 rounded-xl p-6 shadow-md border border-gray-100 transition-all duration-300 hover:shadow-lg animate-fade-in">
                            <h3 class="text-lg font-bold mb-4 text-gray-900 flex items-center gap-2">
                                <span class="w-1 h-6 bg-gradient-to-b from-purple-600 to-indigo-600 rounded-full"></span>
                                Actividad reciente
                            </h3>

                            @if(!empty($ultimasSolicitudes) && count($ultimasSolicitudes))
                                <ul class="space-y-3 text-sm text-gray-700 max-h-72 overflow-y-auto pr-2 scroll-smooth">
                                    @foreach($ultimasSolicitudes as $item)
                                        <li class="flex items-start gap-3 p-3 rounded-lg bg-white/50 border border-gray-100 transition-all duration-300 hover:bg-indigo-50 hover:border-indigo-200 hover:shadow-sm hover:translate-x-1 group">
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-purple-400 text-white flex items-center justify-center text-xs font-bold shadow-md flex-shrink-0 group-hover:scale-110 transition-transform">
                                                {{ strtoupper(mb_substr($item->usuario->name ?? 'S', 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-gray-800">
                                                    {{ $item->usuario->name ?? 'Usuario' }}
                                                    <span class="text-xs text-indigo-600 font-medium bg-indigo-50 px-2 py-0.5 rounded ml-1">
                                                        {{ $item->accion }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    Estado: <span class="font-semibold text-gray-700">{{ ucfirst(str_replace('_', ' ', $item->estado)) }}</span>
                                                </div>
                                                @if($item->comentarios)
                                                    <div class="text-xs text-gray-600 mt-2 line-clamp-2 italic border-l-2 border-indigo-300 pl-2">
                                                        "{{ \Illuminate\Support\Str::limit($item->comentarios, 80) }}"
                                                    </div>
                                                @endif
                                                <div class="text-[11px] text-gray-400 mt-2 flex items-center gap-1">
                                                    🕐 {{ $item->created_at?->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500 text-center py-8 opacity-75">
                                    Aún no hay actividad reciente registrada
                                </p>
                            @endif
                        </div>

                        {{-- Accesos rápidos --}}
                        <div class="bg-white/90 rounded-xl p-6 shadow-md border border-gray-100 transition-all duration-300 hover:shadow-lg animate-fade-in">
                            <h3 class="text-lg font-bold mb-4 text-gray-900 flex items-center gap-2">
                                <span class="w-1 h-6 bg-gradient-to-b from-emerald-600 to-teal-600 rounded-full"></span>
                                Accesos rápidos
                            </h3>
                            <div class="grid grid-cols-1 gap-3 text-sm">
                                <a href="{{ route('solicitudes.create') }}"
                                   class="group flex items-center justify-between px-4 py-3 rounded-lg border-2 border-indigo-200 bg-gradient-to-r from-indigo-50 to-indigo-100 text-indigo-700 font-medium transition-all duration-300 hover:border-indigo-400 hover:shadow-md hover:-translate-y-1 active:scale-95">
                                    <span class="flex items-center gap-2">
                                        <span class="text-lg group-hover:rotate-90 transition-transform">＋</span>
                                        <span>Crear nueva solicitud</span>
                                    </span>
                                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                                </a>

                                <a href="{{ route('solicitudes.index') }}"
                                   class="group flex items-center justify-between px-4 py-3 rounded-lg border-2 border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-medium transition-all duration-300 hover:border-gray-400 hover:shadow-md hover:-translate-y-1 active:scale-95">
                                    <span class="flex items-center gap-2">
                                        <span class="text-lg">📋</span>
                                        <span>Ver todas las solicitudes</span>
                                    </span>
                                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                                </a>

                                @if($user->hasRole('jefe'))
                                    <a href="{{ route('solicitudes.index', ['estado' => 'pendiente']) }}"
                                       class="group flex items-center justify-between px-4 py-3 rounded-lg border-2 border-amber-200 bg-gradient-to-r from-amber-50 to-orange-100 text-amber-700 font-medium transition-all duration-300 hover:border-amber-400 hover:shadow-md hover:-translate-y-1 active:scale-95">
                                        <span class="flex items-center gap-2">
                                            <span class="text-lg group-hover:scale-125 transition-transform">✔</span>
                                            <span>Pendientes por aprobar</span>
                                        </span>
                                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                                    </a>
                                @endif

                                @if($user->hasRole('administrador_sgi'))
                                    <a href="{{ route('solicitudes.index', ['estado' => 'aprobado_jefe']) }}"
                                       class="group flex items-center justify-between px-4 py-3 rounded-lg border-2 border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-100 text-emerald-700 font-medium transition-all duration-300 hover:border-emerald-400 hover:shadow-md hover:-translate-y-1 active:scale-95">
                                        <span class="flex items-center gap-2">
                                            <span class="text-lg">📄</span>
                                            <span>Listas para alta SGI</span>
                                        </span>
                                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Pie con notas rápidas --}}
                <div class="px-6 pb-6 md:px-8 md:pb-8 border-t border-white/40 bg-gradient-to-r from-white/60 via-indigo-50/40 to-white/60">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="group p-5 rounded-lg border border-indigo-200/50 bg-indigo-50/50 transition-all duration-300 hover:shadow-md hover:border-indigo-300 hover:-translate-y-1">
                            <h4 class="font-bold mb-2 text-gray-900 flex items-center gap-2">
                                <span class="text-lg">🔍</span> Trazabilidad
                            </h4>
                            <p class="text-xs text-gray-700 leading-relaxed">
                                Usa las solicitudes como evidencia de control documental para cumplimiento normativo y auditorías.
                            </p>
                        </div>
                        <div class="group p-5 rounded-lg border border-purple-200/50 bg-purple-50/50 transition-all duration-300 hover:shadow-md hover:border-purple-300 hover:-translate-y-1">
                            <h4 class="font-bold mb-2 text-gray-900 flex items-center gap-2">
                                <span class="text-lg">⚙️</span> Estandarización
                            </h4>
                            <p class="text-xs text-gray-700 leading-relaxed">
                                Centraliza la creación, modificación y baja de formatos en un solo flujo aprobado por jefes y SGI.
                            </p>
                        </div>
                        <div class="group p-5 rounded-lg border border-emerald-200/50 bg-emerald-50/50 transition-all duration-300 hover:shadow-md hover:border-emerald-300 hover:-translate-y-1">
                            <h4 class="font-bold mb-2 text-gray-900 flex items-center gap-2">
                                <span class="text-lg">📈</span> Mejora continua
                            </h4>
                            <p class="text-xs text-gray-700 leading-relaxed">
                                Analiza los datos del dashboard para detectar áreas con más cambios y oportunidades de mejora.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Librerías --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    {{-- Estilos personalizados de animaciones --}}
    <style>
        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slide-in-right {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slide-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.6s ease-out;
        }
        .animate-slide-in-right {
            animation: slide-in-right 0.7s ease-out;
        }
        .animate-slide-in-up {
            animation: slide-in-up 0.7s ease-out;
        }
        /* Scrollbar personalizado */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>

<script>
    // === Gráfica de Estados ===
    const estadosLabels = {!! json_encode($porEstado->keys()) !!};
    const estadosData = {!! json_encode($porEstado->values()) !!};

    new Chart(document.getElementById('estadoChart'), {
        type: 'bar',
        data: {
            labels: estadosLabels,
            datasets: [{
                label: 'Solicitudes por estado',
                data: estadosData,
                backgroundColor: [
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(251, 146, 60, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    '#6366f1',
                    '#fb923c',
                    '#22c55e',
                    '#ef4444'
                ],
                borderWidth: 2,
                borderRadius: 8,
                hoverBackgroundColor: [
                    'rgba(99, 102, 241, 1)',
                    'rgba(251, 146, 60, 1)',
                    'rgba(34, 197, 94, 1)',
                    'rgba(239, 68, 68, 1)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 }, padding: 15 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                }
            }
        }
    });

    // === Gráfica últimos 30 días ===
    const diasLabels = {!! json_encode($porDia->pluck('fecha')) !!};
    const diasData = {!! json_encode($porDia->pluck('total')) !!};

    new Chart(document.getElementById('diaChart'), {
        type: 'line',
        data: {
            labels: diasLabels,
            datasets: [{
                label: 'Solicitudes últimos 30 días',
                data: diasData,
                tension: 0.4,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#6366f1',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#4f46e5'
            }]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: {
                    display: true,
                    labels: { font: { size: 12 }, padding: 15 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                }
            }
        }
    });
</script>

</x-app-layout>
