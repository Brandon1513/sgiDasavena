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

    <!-- Animated background -->
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-900 to-slate-900 relative overflow-hidden">
        <!-- Animated shapes -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>

        <!-- Watermark -->
        <img src="https://dasavenasite.domcloud.dev/images/dasavena-logo.png"
             alt="Dasavena"
             class="absolute inset-0 m-auto opacity-5 w-96 h-96 pointer-events-none z-0">

        <!-- Main content -->
        <div class="relative z-10 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">

                {{-- HERO SECTION --}}
                <div class="mb-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Welcome Card -->
                        <div class="lg:col-span-2 bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-2xl p-8 shadow-2xl">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs uppercase tracking-widest text-indigo-300 font-semibold">Bienvenido de vuelta</p>
                                    <h1 class="text-3xl md:text-4xl font-bold text-white">
                                        ¡Hola, {{ $user->name }}! 👋
                                    </h1>
                                </div>
                            </div>
                            <p class="text-white/70 text-sm md:text-base leading-relaxed mb-4">
                                Gestiona tus solicitudes de formatos, monitorea aprobaciones y visualiza la actividad del sistema en tiempo real.
                            </p>
                            @if($roles)
                                <div class="flex flex-wrap gap-2">
                                    @foreach(explode(', ', $roles) as $role)
                                        <span class="inline-block px-3 py-1 text-xs font-semibold text-indigo-200 bg-indigo-500/30 rounded-full border border-indigo-400/50">
                                            {{ ucfirst($role) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Quick Stats -->
                        <div class="space-y-3">
                            <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 backdrop-blur-xl border border-blue-400/30 rounded-xl p-4 shadow-lg hover:shadow-xl transition-all">
                                <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide">Pendientes</p>
                                <p class="text-3xl font-bold text-white mt-2">{{ $pendientes ?? 0 }}</p>
                                <p class="text-blue-200 text-xs mt-1">En espera de revisión</p>
                            </div>
                            <div class="bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 backdrop-blur-xl border border-emerald-400/30 rounded-xl p-4 shadow-lg hover:shadow-xl transition-all">
                                <p class="text-emerald-200 text-xs font-semibold uppercase tracking-wide">Atendidas</p>
                                <p class="text-3xl font-bold text-white mt-2">{{ $atendidas ?? 0 }}</p>
                                <p class="text-emerald-200 text-xs mt-1">Completadas</p>
                            </div>
                            <div class="bg-gradient-to-br from-purple-500/20 to-purple-600/20 backdrop-blur-xl border border-purple-400/30 rounded-xl p-4 shadow-lg hover:shadow-xl transition-all">
                                <p class="text-purple-200 text-xs font-semibold uppercase tracking-wide">Total</p>
                                <p class="text-3xl font-bold text-white mt-2">{{ $total ?? 0 }}</p>
                                <p class="text-purple-200 text-xs mt-1">Últimos 30 días</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MAIN GRID --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                    {{-- Left Column: Charts & Stats --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Status Overview --}}
                        <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-2xl p-6 shadow-2xl">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                    <svg class="w-6 h-6 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                    </svg>
                                    Estado de solicitudes
                                </h3>
                                <span class="text-xs text-white/50">Vista general</span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                                <div class="bg-gradient-to-br from-yellow-500/20 to-yellow-600/20 border border-yellow-400/30 rounded-lg p-4 text-center hover:shadow-lg transition-all">
                                    <p class="text-yellow-200 text-xs font-semibold uppercase">Pendientes</p>
                                    <p class="text-2xl font-bold text-white mt-2">{{ $pendientes ?? 0 }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-amber-500/20 to-amber-600/20 border border-amber-400/30 rounded-lg p-4 text-center hover:shadow-lg transition-all">
                                    <p class="text-amber-200 text-xs font-semibold uppercase">Aprobado</p>
                                    <p class="text-2xl font-bold text-white mt-2">{{ $aprobadoJefe ?? 0 }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 border border-emerald-400/30 rounded-lg p-4 text-center hover:shadow-lg transition-all">
                                    <p class="text-emerald-200 text-xs font-semibold uppercase">Atendidas</p>
                                    <p class="text-2xl font-bold text-white mt-2">{{ $atendidas ?? 0 }}</p>
                                </div>
                                <div class="bg-gradient-to-br from-rose-500/20 to-rose-600/20 border border-rose-400/30 rounded-lg p-4 text-center hover:shadow-lg transition-all">
                                    <p class="text-rose-200 text-xs font-semibold uppercase">Rechazadas</p>
                                    <p class="text-2xl font-bold text-white mt-2">{{ $rechazadas ?? 0 }}</p>
                                </div>
                            </div>

                            <!-- Charts -->
                            <div class="space-y-6">
                                <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-white mb-3">Solicitudes por estado</h4>
                                    <canvas id="estadoChart" class="max-h-64"></canvas>
                                </div>
                                <div class="bg-white/5 border border-white/10 rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-white mb-3">Últimos 30 días</h4>
                                    <canvas id="diaChart" class="max-h-64"></canvas>
                                </div>
                            </div>
                        </div>

                        {{-- Info Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-xl p-4 shadow-lg hover:shadow-xl transition-all hover:border-indigo-400/50 group cursor-pointer">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="font-semibold text-white text-sm">Solicitudes de Formatos</h4>
                                        <p class="text-xs text-white/60 mt-1">Altas, bajas y actualizaciones</p>
                                    </div>
                                    <svg class="w-5 h-5 text-indigo-400 group-hover:translate-y-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-xl p-4 shadow-lg hover:shadow-xl transition-all hover:border-purple-400/50 group cursor-pointer">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="font-semibold text-white text-sm">Documentos SGI</h4>
                                        <p class="text-xs text-white/60 mt-1">Formatos vigentes</p>
                                    </div>
                                    <svg class="w-5 h-5 text-purple-400 group-hover:translate-y-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-xl p-4 shadow-lg hover:shadow-xl transition-all hover:border-emerald-400/50 group cursor-pointer">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="font-semibold text-white text-sm">Auditorías</h4>
                                        <p class="text-xs text-white/60 mt-1">Evidencias y trazabilidad</p>
                                    </div>
                                    <svg class="w-5 h-5 text-emerald-400 group-hover:translate-y-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Activity & Quick Access --}}
                    <div class="space-y-6">

                        {{-- Recent Activity --}}
                        <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-2xl p-6 shadow-2xl max-h-96 overflow-hidden flex flex-col">
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5.951-1.488 5.951 1.488a1 1 0 001.169-1.409l-7-14z"/>
                                </svg>
                                Actividad Reciente
                            </h3>

                            @if(!empty($ultimasSolicitudes) && count($ultimasSolicitudes))
                                <div class="space-y-3 overflow-y-auto flex-1">
                                    @foreach($ultimasSolicitudes as $item)
                                        <div class="flex gap-3 p-3 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-all">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-lg">
                                                    {{ strtoupper(mb_substr($item->usuario->name ?? 'S', 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-white truncate">
                                                    {{ $item->usuario->name ?? 'Usuario' }}
                                                </p>
                                                <p class="text-xs text-white/60">
                                                    {{ ucfirst(str_replace('_', ' ', $item->estado)) }}
                                                </p>
                                                @if($item->comentarios)
                                                    <p class="text-xs text-white/50 mt-1 line-clamp-2">
                                                        {{ Illuminate\Support\Str::limit($item->comentarios, 50) }}
                                                    </p>
                                                @endif
                                                <p class="text-[11px] text-white/40 mt-1">
                                                    {{ $item->created_at?->diffForHumans() }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex items-center justify-center py-8 text-center">
                                    <div>
                                        <svg class="w-12 h-12 text-white/20 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6M6 12a6 6 0 11-6-6m6 6a6 6 0 10-6-6"/>
                                        </svg>
                                        <p class="text-sm text-white/50">Sin actividad reciente</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Quick Actions --}}
                        <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-2xl p-6 shadow-2xl">
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                </svg>
                                Acciones Rápidas
                            </h3>
                            <div class="space-y-2">
                                <a href="{{ route('solicitudes.create') }}"
                                   class="flex items-center justify-between px-4 py-3 rounded-lg bg-gradient-to-r from-indigo-500 to-indigo-600 text-white font-medium text-sm hover:shadow-lg hover:shadow-indigo-500/50 transition-all hover:-translate-y-0.5 group">
                                    <span>Nueva solicitud</span>
                                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                                </a>

                                <a href="{{ route('solicitudes.index') }}"
                                   class="flex items-center justify-between px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white font-medium text-sm hover:bg-white/20 transition-all hover:-translate-y-0.5 group">
                                    <span>Ver todas</span>
                                    <span class="group-hover:translate-x-1 transition-transform">→</span>
                                </a>

                                @if($user->hasRole('jefe'))
                                    <a href="{{ route('solicitudes.index', ['estado' => 'pendiente']) }}"
                                       class="flex items-center justify-between px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white font-medium text-sm hover:bg-white/20 transition-all hover:-translate-y-0.5 group">
                                        <span>Por aprobar</span>
                                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                                    </a>
                                @endif

                                @if($user->hasRole('administrador_sgi'))
                                    <a href="{{ route('solicitudes.index', ['estado' => 'aprobado_jefe']) }}"
                                       class="flex items-center justify-between px-4 py-3 rounded-lg bg-white/10 border border-white/20 text-white font-medium text-sm hover:bg-white/20 transition-all hover:-translate-y-0.5 group">
                                        <span>Listas para SGI</span>
                                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Info --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-xl p-6 shadow-lg">
                        <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            Trazabilidad
                        </h4>
                        <p class="text-white/70 text-xs">
                            Usa las solicitudes como evidencia de control documental para cumplimiento normativo.
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-xl p-6 shadow-lg">
                        <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v4h8v-4zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                            </svg>
                            Estandarización
                        </h4>
                        <p class="text-white/70 text-xs">
                            Centraliza formatos en un solo flujo aprobado por jefes y SGI.
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 rounded-xl p-6 shadow-lg">
                        <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h.01a1 1 0 110 2H12zm-2 2a1 1 0 100-2H9.01a1 1 0 000 2H10zm4 0a1 1 0 100-2h-.01a1 1 0 000 2h.01zm2 2a1 1 0 100-2h-.01a1 1 0 000 2h.01zM8 9a1 1 0 11-2 0 1 1 0 012 0zm6 0a1 1 0 11-2 0 1 1 0 012 0zm-4 4a1 1 0 100-2H9.01a1 1 0 000 2H10zm4 0a1 1 0 100-2h-.01a1 1 0 000 2h.01z"/>
                            </svg>
                            Mejora Continua
                        </h4>
                        <p class="text-white/70 text-xs">
                            Analiza datos para detectar áreas con más cambios y mejoras.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>

    @php
        $chartEstadoLabels = json_encode($porEstado->keys());
        $chartEstadoData = json_encode($porEstado->values());
        $chartDiaLabels = json_encode($porDia->pluck('fecha'));
        $chartDiaData = json_encode($porDia->pluck('total'));
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx1 = document.getElementById('estadoChart');
            const ctx2 = document.getElementById('diaChart');

            if (ctx1) {
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: {!! $chartEstadoLabels !!},
                        datasets: [{
                            label: 'Solicitudes por estado',
                            data: {!! $chartEstadoData !!},
                            backgroundColor: ['#fbbf24', '#f97316', '#10b981', '#f87171'],
                            borderColor: ['#f59e0b', '#ea580c', '#059669', '#dc2626'],
                            borderWidth: 2,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { labels: { color: '#fff' } }
                        },
                        scales: {
                            y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                            x: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
                        }
                    }
                });
            }

            if (ctx2) {
                new Chart(ctx2, {
                    type: 'line',
                    data: {
                        labels: {!! $chartDiaLabels !!},
                        datasets: [{
                            label: 'Solicitudes últimos 30 días',
                            data: {!! $chartDiaData !!},
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointBackgroundColor: '#6366f1',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { labels: { color: '#fff' } }
                        },
                        scales: {
                            y: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } },
                            x: { ticks: { color: '#fff' }, grid: { color: 'rgba(255,255,255,0.1)' } }
                        }
                    }
                });
            }
        });
        
    </script>
</x-app-layout>
