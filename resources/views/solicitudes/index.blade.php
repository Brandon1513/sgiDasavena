<x-app-layout>
<style>
    @import url('https://fonts.cdnfonts.com/css/century-gothic');
    * { font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif; }

    @keyframes fadeIn  { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
    @keyframes slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
    .anim-fade  { animation: fadeIn  0.45s ease both; }
    .anim-slide { animation: slideDown 0.3s ease both; }

    /* Línea dorada superior en la card */
    .card-gold::before {
        content:''; position:absolute; top:0; left:0; right:0;
        height:3px;
        background: linear-gradient(90deg, #6A2C75, #D4A018, #6A2C75);
        border-radius:12px 12px 0 0;
    }

    /* Select arrow custom */
    .select-custom {
        background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236A2C75%22 stroke-width=%222%22%3e%3cpolyline points=%226 9 12 15 18 9%3e%3c/polyline%3e%3c/svg%3e');
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.1em 1.1em;
        padding-right: 2.5rem;
        appearance: none;
    }

    /* Scroll tabla */
    .table-scroll::-webkit-scrollbar { height:5px; }
    .table-scroll::-webkit-scrollbar-track { background:#faf7fb; }
    .table-scroll::-webkit-scrollbar-thumb { background:#c9a0d6; border-radius:4px; }
</style>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 anim-fade">
            <!-- Título -->
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#6A2C75] to-[#D4A018]"></div>
                    <h2 class="text-3xl font-bold text-[#2d1033] tracking-tight">
                        Solicitudes
                    </h2>
                </div>
                <p class="text-sm text-[#6A2C75]/60 pl-4">Gestiona el estado de todas tus solicitudes</p>
            </div>

            <!-- Badge total -->
            <div class="flex items-center gap-4 px-5 py-3.5 bg-white border border-[#6A2C75]/15 rounded-2xl shadow-sm">
                <span class="flex items-center justify-center w-11 h-11 rounded-full bg-gradient-to-br from-[#6A2C75] to-[#8e3d9e] text-white text-sm font-bold shadow-md">
                    {{ $solicitudes->total() ?? 0 }}
                </span>
                <div class="flex flex-col">
                    <span class="text-[10px] text-[#6A2C75]/50 font-semibold uppercase tracking-widest">Total</span>
                    <span class="text-sm text-[#2d1033] font-semibold">{{ $solicitudes->total() ?? 0 }} solicitudes</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#faf7fb] min-h-screen">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- ── SUBHEADER ── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 anim-fade">
                <p class="text-sm text-[#5a4a65]">Administra tus solicitudes desde un solo lugar</p>
                <a href="{{ route('solicitudes.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-[#6A2C75] to-[#8e3d9e] text-white rounded-lg font-semibold text-sm shadow-md hover:shadow-lg hover:-translate-y-0.5 active:scale-95 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva Solicitud
                </a>
            </div>

            <!-- ── ALERTA ── -->
            @if (session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800 shadow-sm anim-slide">
                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-200 text-emerald-700 font-bold text-xs flex-shrink-0">✓</span>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            @endif

            <!-- ── CARD PRINCIPAL ── -->
            <div class="relative bg-white rounded-2xl shadow-sm border border-[#6A2C75]/10 overflow-hidden card-gold anim-fade" style="animation-delay:80ms">

                <!-- ── FILTROS ── -->
                <div class="border-b border-[#6A2C75]/08 p-6 bg-[#faf7fb]/60">
                    <p class="text-[10px] font-bold text-[#6A2C75]/50 uppercase tracking-widest mb-4">Filtros de búsqueda</p>
                    <form method="GET">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">

                            <!-- Nombre -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Nombre</label>
                                <input type="text" name="nombre" value="{{ request('nombre') }}"
                                    placeholder="Buscar..."
                                    class="w-full px-3.5 py-2 border border-[#6A2C75]/20 rounded-lg text-sm text-[#2d1033] placeholder-[#6A2C75]/30 bg-white focus:outline-none focus:ring-2 focus:ring-[#6A2C75]/30 focus:border-[#6A2C75]/50 transition-all">
                            </div>

                            <!-- Estado -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Estado</label>
                                <select name="estado"
                                    class="select-custom w-full px-3.5 py-2 border border-[#6A2C75]/20 rounded-lg text-sm text-[#2d1033] bg-white focus:outline-none focus:ring-2 focus:ring-[#6A2C75]/30 focus:border-[#6A2C75]/50 transition-all cursor-pointer">
                                    <option value="">Todos</option>
                                    @foreach (['pendiente','aprobado_jefe','rechazado_jefe','atendido','rechazado_sgi'] as $estado)
                                    <option value="{{ $estado }}" {{ request('estado')===$estado ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Desde -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Desde</label>
                                <input type="date" name="desde" value="{{ request('desde') }}"
                                    class="w-full px-3.5 py-2 border border-[#6A2C75]/20 rounded-lg text-sm text-[#2d1033] bg-white focus:outline-none focus:ring-2 focus:ring-[#6A2C75]/30 focus:border-[#6A2C75]/50 transition-all">
                            </div>

                            <!-- Hasta -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Hasta</label>
                                <input type="date" name="hasta" value="{{ request('hasta') }}"
                                    class="w-full px-3.5 py-2 border border-[#6A2C75]/20 rounded-lg text-sm text-[#2d1033] bg-white focus:outline-none focus:ring-2 focus:ring-[#6A2C75]/30 focus:border-[#6A2C75]/50 transition-all">
                            </div>

                            <!-- Botón filtrar -->
                            <div class="flex items-end">
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-gradient-to-r from-[#D4A018] to-[#f0c84a] text-[#2d1033] rounded-lg font-bold text-sm shadow-sm hover:shadow-md hover:-translate-y-0.5 active:scale-95 transition-all duration-200">
                                    Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ── TABLA / EMPTY ── -->
                @if ($solicitudes->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 rounded-full bg-[#6A2C75]/08 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#6A2C75]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <p class="text-[#2d1033] font-semibold text-sm">No hay solicitudes para mostrar</p>
                    <p class="text-[#6A2C75]/40 text-xs mt-1">Intenta con otros filtros o crea una nueva solicitud</p>
                </div>

                @else
                <div class="overflow-x-auto table-scroll">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#faf7fb] border-b border-[#6A2C75]/10">
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">#</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Solicitante</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Acción</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Estado</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Comentarios</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#6A2C75]/06">
                            @foreach ($solicitudes as $i => $solicitud)
                            <tr class="hover:bg-[#6A2C75]/03 transition-colors duration-150 anim-fade" style="animation-delay: {{ $i * 40 }}ms">

                                <!-- ID -->
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-[#6A2C75]/40">#{{ $solicitud->id }}</span>
                                </td>

                                <!-- Solicitante -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#6A2C75] to-[#D4A018] flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                                            {{ substr(optional($solicitud->usuario)->name ?? 'S', 0, 1) }}
                                        </div>
                                        <span class="text-xs font-semibold text-[#2d1033]">
                                            {{ optional($solicitud->usuario)->name ?? 'Sin usuario' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Acción -->
                                <td class="px-6 py-4 text-xs text-[#5a4a65] font-medium">
                                    {{ ucfirst($solicitud->accion) }}
                                </td>

                                <!-- Estado -->
                                <td class="px-6 py-4">
                                    @php
                                        $estadoMap = [
                                            'pendiente'       => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'aprobado_jefe'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'rechazado_jefe'  => 'bg-red-100 text-red-800 border-red-200',
                                            'atendido'        => 'bg-[#6A2C75]/10 text-[#6A2C75] border-[#6A2C75]/20',
                                            'rechazado_sgi'   => 'bg-red-100 text-red-800 border-red-200',
                                        ];
                                        $dotMap = [
                                            'pendiente'       => 'bg-amber-500',
                                            'aprobado_jefe'   => 'bg-emerald-500',
                                            'rechazado_jefe'  => 'bg-red-500',
                                            'atendido'        => 'bg-[#6A2C75]',
                                            'rechazado_sgi'   => 'bg-red-500',
                                        ];
                                        $clases = $estadoMap[$solicitud->estado] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                        $dot    = $dotMap[$solicitud->estado]    ?? 'bg-gray-400';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold border {{ $clases }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dot }} flex-shrink-0"></span>
                                        {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                                    </span>
                                </td>

                                <!-- Comentarios -->
                                <td class="px-6 py-4 max-w-xs">
                                    @if($solicitud->comentarios)
                                    <span class="block truncate text-xs text-[#5a4a65]" title="{{ $solicitud->comentarios }}">
                                        {{ Str::limit($solicitud->comentarios, 40) }}
                                    </span>
                                    @else
                                    <span class="text-[#6A2C75]/20 text-xs">—</span>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 flex-wrap">

                                        @if (Route::has('solicitudes.show'))
                                        <a href="{{ route('solicitudes.show', $solicitud->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#6A2C75]/08 text-[#6A2C75] hover:bg-[#6A2C75]/15 text-[10px] font-bold transition-colors border border-[#6A2C75]/15">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver
                                        </a>
                                        @endif

                                        @if($solicitud->accion === 'actualizacion')
                                            @if($solicitud->documento_id)
                                            <a href="{{ route('solicitudes.solicitar_actualizacion.form', ['documento' => $solicitud->documento_id]) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#D4A018]/10 text-[#b38600] hover:bg-[#D4A018]/20 text-[10px] font-bold transition-colors border border-[#D4A018]/25">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                Actualizar
                                            </a>
                                            @else
                                            <span class="text-[#6A2C75]/25 text-[10px]">Sin documento</span>
                                            @endif
                                        @endif

                                        @if (auth()->user()->hasRole('jefe') && $solicitud->estado === 'pendiente' && Route::has('solicitudes.approval_form'))
                                        <a href="{{ route('solicitudes.approval_form', $solicitud->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-[10px] font-bold transition-colors border border-emerald-200">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Revisar
                                        </a>
                                        @endif

                                        @if (auth()->user()->hasRole('administrador_sgi') && $solicitud->estado === 'aprobado_jefe' && Route::has('solicitudes.finalize_form'))
                                        <a href="{{ route('solicitudes.finalize_form', $solicitud->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#6A2C75]/08 text-[#6A2C75] hover:bg-[#6A2C75]/15 text-[10px] font-bold transition-colors border border-[#6A2C75]/15">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Finalizar
                                        </a>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- ── PAGINACIÓN ── -->
                <div class="px-6 py-5 border-t border-[#6A2C75]/08 bg-[#faf7fb]/60 flex items-center justify-between gap-4">
                    <p class="text-xs text-[#6A2C75]/50">
                        Mostrando {{ $solicitudes->firstItem() }}–{{ $solicitudes->lastItem() }} de {{ $solicitudes->total() }} resultados
                    </p>
                    <div class="pagination-purple">
                        {{ $solicitudes->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    <style>
        /* Paginación con colores de marca */
        .pagination-purple nav span[aria-current="page"] > span,
        .pagination-purple nav a:hover {
            background-color: #6A2C75 !important;
            border-color: #6A2C75 !important;
            color: #fff !important;
        }
        .pagination-purple nav a {
            color: #6A2C75 !important;
            border-color: #6A2C75]/20 !important;
        }
    </style>

</x-app-layout>