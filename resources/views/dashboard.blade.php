<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard SGI
        </h2>
    </x-slot>

  @php
    $user = auth()->user(); // o \Illuminate\Support\Facades\Auth::user();
    $roles = $user && method_exists($user, 'getRoleNames')
        ? $user->getRoleNames()->implode(', ')
        : '';
@endphp

    <!-- Fondo pantalla completa -->
    <div class="min-h-screen relative bg-fixed bg-center bg-cover"
         style="background-image: url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">

        <!-- Marca de agua / logo -->
        <img src="https://dasavenasite.domcloud.dev/images/dasavena-logo.png"
             alt="Dasavena Watermark"
             class="pointer-events-none absolute inset-0 m-auto opacity-10 w-80 h-80 md:w-96 md:h-96 object-contain z-0"
             style="mix-blend-mode: multiply;">

        <!-- Contenido principal -->
        <div class="relative z-10 min-h-screen flex items-start justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-7xl bg-white/70 backdrop-blur-md border border-white/40 rounded-2xl shadow-2xl overflow-hidden">

                {{-- HERO SUPERIOR --}}
                <div class="flex flex-col md:flex-row items-start md:items-center gap-8 p-6 md:p-8 border-b border-white/40 bg-gradient-to-r from-white/60 to-white/20">
                    <div class="flex-1 space-y-3">
                        <p class="text-xs uppercase tracking-wide text-indigo-600 font-semibold">Sistema de Gestión Integral</p>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">
                            Hola, {{ $user->name }}  👋
                        </h1>
                        <p class="text-sm text-gray-700">
                            Bienvenido a <span class="font-semibold">DasavenaSGI</span>. Aquí puedes visualizar el estado de tus
                            solicitudes de formatos, aprobaciones y la actividad reciente del sistema.
                        </p>
                        @if($roles)
                            <p class="text-xs text-gray-500">
                                Roles asignados: <span class="font-semibold text-gray-700">{{ $roles }}</span>
                            </p>
                        @endif
                    </div>

                    {{-- Resumen rápido / KPIs mini --}}
                    <div class="w-full md:w-72 grid grid-cols-2 gap-3">
                        <div class="bg-white/90 rounded-lg p-3 shadow-sm border border-gray-100">
                            <div class="text-[11px] text-gray-500 uppercase tracking-wide">Solicitudes pendientes</div>
                            <div class="mt-1 text-2xl font-bold text-indigo-700">
                                {{ $pendientes ?? '—' }}
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1">
                                En espera de revisión
                            </div>
                        </div>

                        <div class="bg-white/90 rounded-lg p-3 shadow-sm border border-gray-100">
                            <div class="text-[11px] text-gray-500 uppercase tracking-wide">Atendidas (SGI)</div>
                            <div class="mt-1 text-2xl font-bold text-emerald-700">
                                {{ $atendidas ?? '—' }}
                            </div>
                            <div class="text-[11px] text-gray-500 mt-1">
                                Con formato actualizado
                            </div>
                        </div>

                        <div class="bg-white/90 rounded-lg p-3 shadow-sm border border-gray-100 col-span-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-[11px] text-gray-500 uppercase tracking-wide">Total solicitudes</div>
                                    <div class="mt-1 text-xl font-bold text-gray-900">
                                        {{ $total ?? '—' }}
                                    </div>
                                </div>
                                <div class="text-[11px] text-gray-500 text-right">
                                    Últimos<br>30 días
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GRID PRINCIPAL --}}
                <div class="p-6 md:p-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Columna izquierda: métricas / “chart” --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Resumen por estado --}}
                        <div class="bg-white/90 rounded-xl p-5 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base md:text-lg font-semibold text-gray-900">
                                    Estado de solicitudes de formato
                                </h3>
                                <span class="text-xs text-gray-500">
                                    Vista general
                                </span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                                <div class="bg-indigo-50 rounded-lg p-3">
                                    <div class="text-[11px] text-indigo-700 uppercase tracking-wide">Pendientes</div>
                                    <div class="mt-1 text-xl font-bold text-indigo-900">
                                        {{ $pendientes ?? '—' }}
                                    </div>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-3">
                                    <div class="text-[11px] text-amber-700 uppercase tracking-wide">Aprobado jefe</div>
                                    <div class="mt-1 text-xl font-bold text-amber-900">
                                        {{ $aprobadoJefe ?? '—' }}
                                    </div>
                                </div>
                                <div class="bg-emerald-50 rounded-lg p-3">
                                    <div class="text-[11px] text-emerald-700 uppercase tracking-wide">Atendidas</div>
                                    <div class="mt-1 text-xl font-bold text-emerald-900">
                                        {{ $atendidas ?? '—' }}
                                    </div>
                                </div>
                                <div class="bg-rose-50 rounded-lg p-3">
                                    <div class="text-[11px] text-rose-700 uppercase tracking-wide">Rechazadas</div>
                                    <div class="mt-1 text-xl font-bold text-rose-900">
                                        {{ $rechazadas ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Placeholder para gráfica real --}}
                            <div class="mt-6 w-full h-48 rounded-lg bg-gradient-to-r from-indigo-50 via-white to-emerald-50 flex items-center justify-center border border-dashed border-gray-200">
                                <div class="mt-6 w-full rounded-lg bg-white/60 border p-4">
    <canvas id="estadoChart"></canvas>
</div>

<div class="mt-6 w-full rounded-lg bg-white/60 border p-4">
    <canvas id="diaChart"></canvas>
</div>

                            </div>
                        </div>

                        {{-- Detalles contextuales SGI --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <details class="group bg-white/90 border border-gray-100 rounded-lg p-4 shadow-sm cursor-pointer">
                                <summary class="flex items-center justify-between list-none">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">Solicitudes de formatos</div>
                                        <div class="text-xs text-gray-500">Altas, bajas y actualizaciones</div>
                                    </div>
                                    <div class="ml-4 text-indigo-600 group-open:rotate-180 transition-transform text-lg">▾</div>
                                </summary>
                                <div class="mt-3 text-xs md:text-sm text-gray-700 space-y-1">
                                    <p>Visualiza el flujo completo: usuario → jefe → SGI.</p>
                                    <p>Revisa qué solicitudes están detenidas y en qué etapa.</p>
                                </div>
                            </details>

                            <details class="group bg-white/90 border border-gray-100 rounded-lg p-4 shadow-sm cursor-pointer">
                                <summary class="flex items-center justify-between list-none">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">Documentos SGI</div>
                                        <div class="text-xs text-gray-500">Formatos vigentes</div>
                                    </div>
                                    <div class="ml-4 text-indigo-600 group-open:rotate-180 transition-transform text-lg">▾</div>
                                </summary>
                                <div class="mt-3 text-xs md:text-sm text-gray-700 space-y-1">
                                    <p>Conserva el control de versiones y fechas de alta en el SGI.</p>
                                    <p>Relaciona ligas de documentos con tus solicitudes atendidas.</p>
                                </div>
                            </details>

                            <details class="group bg-white/90 border border-gray-100 rounded-lg p-4 shadow-sm cursor-pointer">
                                <summary class="flex items-center justify-between list-none">
                                    <div>
                                        <div class="text-sm font-medium text-gray-800">Auditorías y seguimiento</div>
                                        <div class="text-xs text-gray-500">Evidencias y trazabilidad</div>
                                    </div>
                                    <div class="ml-4 text-indigo-600 group-open:rotate-180 transition-transform text-lg">▾</div>
                                </summary>
                                <div class="mt-3 text-xs md:text-sm text-gray-700 space-y-1">
                                    <p>Usa las solicitudes como evidencia de control documental para auditorías internas o externas.</p>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- Columna derecha: actividad reciente / accesos rápidos --}}
                    <div class="space-y-6">

                        {{-- Actividad reciente --}}
                        <div class="bg-white/90 rounded-xl p-5 shadow-sm border border-gray-100">
                            <h3 class="text-base md:text-lg font-semibold mb-3 text-gray-900">Actividad reciente</h3>

                            @if(!empty($ultimasSolicitudes) && count($ultimasSolicitudes))
                                <ul class="space-y-3 text-sm text-gray-700 max-h-64 overflow-y-auto">
                                    @foreach($ultimasSolicitudes as $item)
                                        <li class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-semibold">
                                                {{ strtoupper(mb_substr($item->usuario->name ?? 'S', 0, 1)) }}
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium">
                                                    {{ $item->usuario->name ?? 'Usuario' }}
                                                    <span class="text-xs text-gray-500">
                                                        ({{ $item->accion }})
                                                    </span>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Estado: {{ ucfirst(str_replace('_', ' ', $item->estado)) }}
                                                </div>
                                                @if($item->comentarios)
                                                    <div class="text-xs text-gray-600 mt-1 line-clamp-2">
                                                        "{{ \Illuminate\Support\Str::limit($item->comentarios, 80) }}"
                                                    </div>
                                                @endif
                                                <div class="text-[11px] text-gray-400 mt-1">
                                                    {{ $item->created_at?->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">
                                    Aún no hay actividad reciente registrada en las solicitudes de formato.
                                </p>
                            @endif
                        </div>

                        {{-- Accesos rápidos --}}
                        <div class="bg-white/90 rounded-xl p-5 shadow-sm border border-gray-100">
                            <h3 class="text-base md:text-lg font-semibold mb-3 text-gray-900">Accesos rápidos</h3>
                            <div class="grid grid-cols-1 gap-2 text-sm">
                                <a href="{{ route('solicitudes.create') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg border border-indigo-100 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                    <span>Crear nueva solicitud de formato</span>
                                    <span class="text-lg">＋</span>
                                </a>

                                <a href="{{ route('solicitudes.index') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition">
                                    <span>Ver todas las solicitudes</span>
                                    <span class="text-gray-400 text-lg">⟶</span>
                                </a>

                                @if($user->hasRole('jefe'))
                                    <a href="{{ route('solicitudes.index', ['estado' => 'pendiente']) }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-lg border border-amber-100 bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                                        <span>Solicitudes pendientes por aprobar</span>
                                        <span class="text-lg">✔</span>
                                    </a>
                                @endif

                                @if($user->hasRole('administrador_sgi'))
                                    <a href="{{ route('solicitudes.index', ['estado' => 'aprobado_jefe']) }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                        <span>Solicitudes listas para alta en SGI</span>
                                        <span class="text-lg">📄</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Pie con notas rápidas --}}
                <div class="px-6 pb-6 md:px-8 md:pb-8 border-t border-white/40 bg-white/60">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700">
                        <div>
                            <h4 class="font-semibold mb-1 text-gray-900">Trazabilidad</h4>
                            <p class="text-xs">
                                Usa las solicitudes como evidencia de control documental para cumplimiento normativo y auditorías.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-1 text-gray-900">Estandarización</h4>
                            <p class="text-xs">
                                Centraliza la creación, modificación y baja de formatos en un solo flujo aprobado por jefes y SGI.
                            </p>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-1 text-gray-900">Mejora continua</h4>
                            <p class="text-xs">
                                Analiza los datos del dashboard para detectar áreas con más cambios y oportunidades de mejora.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                backgroundColor: ['#facc15', '#fbbf24', '#4ade80', '#f87171'],
            }]
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
                tension: 0.3,
                borderColor: '#6366f1',
                fill: false
            }]
        }
    });
</script>

</x-app-layout>
