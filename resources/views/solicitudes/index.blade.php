<x-app-layout>

{{-- ─── PANTALLA DE CARGA ─── --}}
<div id="sgi-loading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-dasavena-purple-dark via-dasavena-purple to-dasavena-purple-light transition-opacity duration-500">
    <div class="flex flex-col items-center gap-4">
        <div class="relative w-20 h-20">
            <span class="absolute -inset-1.5 rounded-full border-2 border-transparent border-t-dasavena-gold border-r-dasavena-gold animate-spin"></span>
            <img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="Dasavena" class="w-20 h-20 object-contain animate-pulse">
        </div>
        <p class="text-white font-display font-bold tracking-wide">Sistema SGI</p>
        <p class="text-dasavena-gold font-mono text-[11px] tracking-wider">// cargando_solicitudes</p>
    </div>
</div>
<script>
window.addEventListener('load', () => {
    const el = document.getElementById('sgi-loading');
    if (!el) return;
    el.style.opacity = '0';
    el.style.pointerEvents = 'none';
    setTimeout(() => el.remove(), 650);
});
</script>

{{-- ─── FONDO + LUCES AMBIENTALES ─── --}}
<div class="relative min-h-screen bg-cover bg-center bg-fixed" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">
    <div class="pointer-events-none fixed inset-0 z-[1] bg-[radial-gradient(ellipse_90%_55%_at_50%_-5%,rgba(255,255,255,.55)_0%,transparent_65%)]"></div>
    <div class="pointer-events-none fixed -left-20 -top-24 z-[1] h-[420px] w-[420px] rounded-full bg-dasavena-purple/10 blur-3xl"></div>
    <div class="pointer-events-none fixed -right-24 top-[22%] z-[1] h-[380px] w-[380px] rounded-full bg-dasavena-gold/10 blur-3xl"></div>

    <div class="relative z-10 mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 sm:px-6 lg:px-8">

        {{-- ══════════════════════════════ ENCABEZADO ══════════════════════════════ --}}
        <div class="reveal flex flex-col gap-5 rounded-[28px] border border-white/80 bg-white/70 p-6 shadow-[0_10px_32px_rgba(74,30,82,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-1 rounded-full bg-gradient-to-b from-dasavena-purple to-dasavena-gold"></div>
                <div>
                    <h1 class="font-display text-2xl font-bold text-indigo-950">Solicitudes</h1>
                    <p class="text-xs text-gray-400">Administra tus solicitudes desde un solo lugar</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-3 rounded-2xl border border-white/80 bg-white/60 px-4 py-2 shadow-sm backdrop-blur-xl">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-dasavena-purple-light to-dasavena-purple-dark text-sm font-bold text-white shadow">
                        {{ $solicitudes->total() ?? 0 }}
                    </span>
                    <div class="flex flex-col leading-tight">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Total</span>
                        <span class="text-sm font-semibold text-indigo-950">solicitudes</span>
                    </div>
                </div>

                <a href="{{ route('solicitudes.create') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-dasavena-purple px-5 py-2.5 text-sm font-semibold text-white shadow-[0_4px_16px_rgba(106,44,117,0.3)] transition-all hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(106,44,117,0.4)] active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nueva solicitud
                </a>
            </div>
        </div>

        @if (session('success'))
        <div class="reveal flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm text-emerald-800 shadow-sm backdrop-blur-xl">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-200 text-xs font-bold text-emerald-700">✓</span>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
        @endif

        {{-- ══════════════════════════════ FILTROS ══════════════════════════════ --}}
        <x-dashboard.panel class="reveal p-6">
            <p class="mb-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Filtros de búsqueda</p>
            <form method="GET">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Nombre</label>
                        <input type="text" name="nombre" value="{{ request('nombre') }}" placeholder="Buscar…"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 placeholder:text-gray-400 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Estado</label>
                        <select name="estado"
                            class="w-full cursor-pointer rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                            <option value="">Todos</option>
                            @foreach (['pendiente','aprobado_jefe','rechazado_jefe','atendido','rechazado_sgi'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado')===$estado ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $estado)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Desde</label>
                        <input type="date" name="desde" value="{{ request('desde') }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Hasta</label>
                        <input type="date" name="hasta" value="{{ request('hasta') }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full rounded-xl bg-dasavena-gold px-4 py-2 text-sm font-bold text-dasavena-purple-dark shadow-[0_3px_10px_rgba(214,166,68,0.3)] transition-all hover:-translate-y-0.5 hover:shadow-[0_5px_16px_rgba(214,166,68,0.4)] active:scale-95">
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </x-dashboard.panel>

        {{-- ══════════════════════════════ TABLA ══════════════════════════════ --}}
        <x-dashboard.panel class="reveal flex flex-col gap-5 p-6">
            @if ($solicitudes->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-gray-50 text-gray-300">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </span>
                <p class="text-sm font-semibold text-indigo-950">No hay solicitudes para mostrar</p>
                <p class="mt-1 text-xs text-gray-400">Intenta con otros filtros o crea una nueva solicitud</p>
            </div>
            @else
            <div class="w-full overflow-x-auto rounded-2xl border border-white/80">
                <table class="w-full border-collapse bg-white/60 text-left text-sm">
                    <thead class="border-b border-black/[0.04] bg-white/40 text-[10px] font-bold uppercase tracking-wider text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5">#</th>
                            <th class="px-5 py-3.5">Solicitante</th>
                            <th class="px-5 py-3.5">Acción</th>
                            <th class="px-5 py-3.5">Estado</th>
                            <th class="px-5 py-3.5">Fecha</th>
                            <th class="px-5 py-3.5">Comentarios</th>
                            <th class="px-5 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] text-gray-700">
                        @foreach ($solicitudes as $solicitud)
                        @php
                            $estadoBadges = [
                                'pendiente'      => ['label' => 'Pendiente',       'class' => 'bg-dasavena-purple/10 border-dasavena-purple/20 text-dasavena-purple'],
                                'aprobado_jefe'  => ['label' => 'Aprobado jefe',   'class' => 'bg-dasavena-gold/20 border-dasavena-gold/30 text-dasavena-gold-dark'],
                                'atendido'       => ['label' => 'Atendido',       'class' => 'bg-emerald-50 border-emerald-200 text-emerald-700'],
                                'rechazado_jefe' => ['label' => 'Rechazado jefe', 'class' => 'bg-rose-50 border-rose-200 text-rose-700'],
                                'rechazado_sgi'  => ['label' => 'Rechazado SGI',  'class' => 'bg-rose-50 border-rose-200 text-rose-700'],
                            ];
                            $badge = $estadoBadges[$solicitud->estado] ?? ['label' => ucfirst(str_replace('_', ' ', $solicitud->estado)), 'class' => 'bg-gray-50 border-gray-200 text-gray-600'];
                        @endphp
                        <tr class="transition-colors hover:bg-white/70">
                            <td class="px-5 py-4 font-mono text-xs font-bold text-gray-400">#{{ $solicitud->id }}</td>

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-dasavena-purple to-dasavena-gold text-[10px] font-bold text-white">
                                        {{ strtoupper(mb_substr(optional($solicitud->usuario)->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="text-[13px] font-semibold text-indigo-950">
                                        {{ optional($solicitud->usuario)->name ?? 'Sin usuario' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-[13px] text-gray-500">{{ ucfirst($solicitud->accion) }}</td>

                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[11px] font-semibold {{ $badge['class'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $badge['label'] }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-[12.5px] text-gray-400">{{ $solicitud->created_at->format('d M Y') }}</td>

                            <td class="max-w-xs px-5 py-4">
                                @if($solicitud->comentarios)
                                <span class="block truncate text-[12.5px] text-gray-500" title="{{ $solicitud->comentarios }}">
                                    {{ Str::limit($solicitud->comentarios, 40) }}
                                </span>
                                @else
                                <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">

                                    @if (Route::has('solicitudes.show'))
                                    <a href="{{ route('solicitudes.show', $solicitud->id) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-dasavena-purple/15 bg-dasavena-purple/5 px-3 py-1.5 text-[10.5px] font-bold text-dasavena-purple transition-colors hover:bg-dasavena-purple/10">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver
                                    </a>
                                    @endif

                                    @if($solicitud->accion === 'actualizacion')
                                        @if($solicitud->documento_id)
                                        <a href="{{ route('solicitudes.solicitar_actualizacion.form', ['documento' => $solicitud->documento_id]) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-dasavena-gold/25 bg-dasavena-gold/10 px-3 py-1.5 text-[10.5px] font-bold text-dasavena-gold-dark transition-colors hover:bg-dasavena-gold/20">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Actualizar
                                        </a>
                                        @else
                                        <span class="text-[10.5px] text-gray-300">Sin documento</span>
                                        @endif
                                    @endif

                                    {{-- LÓGICA DE ELIMINACIÓN: Solo si no ha sido aprobada/rechazada por SGI --}}
                                    @if (Route::has('solicitudes.destroy') && !in_array($solicitud->estado, ['atendido', 'rechazado_sgi']))
                                    <form action="{{ route('solicitudes.destroy', $solicitud->id) }}" method="POST" class="inline-flex"
                                        onsubmit="return confirm('¿Está seguro de que desea eliminar esta solicitud?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                          class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-[10.5px] font-bold text-rose-700 transition-colors hover:bg-rose-100">
                                          <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"/>
                                          </svg>
                                          Eliminar
                                        </button>
                                    </form>
                                    @endif

                                    @if (auth()->user()->hasRole('jefe') && $solicitud->estado === 'pendiente' && Route::has('solicitudes.approval_form'))
                                    <a href="{{ route('solicitudes.approval_form', $solicitud->id) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[10.5px] font-bold text-emerald-700 transition-colors hover:bg-emerald-100">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Revisar
                                    </a>
                                    @endif

                                    @if (auth()->user()->hasRole('administrador_sgi') && $solicitud->estado === 'aprobado_jefe' && Route::has('solicitudes.finalize_form'))
                                    <a href="{{ route('solicitudes.finalize_form', $solicitud->id) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-dasavena-purple/15 bg-dasavena-purple/5 px-3 py-1.5 text-[10.5px] font-bold text-dasavena-purple transition-colors hover:bg-dasavena-purple/10">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

            <div class="flex flex-col items-center justify-between gap-3 pt-1 sm:flex-row">
                <span class="text-[12.5px] text-gray-400">
                    Mostrando <strong class="font-semibold text-gray-700">{{ $solicitudes->firstItem() }}–{{ $solicitudes->lastItem() }}</strong> de <strong class="font-semibold text-gray-700">{{ $solicitudes->total() }}</strong> resultados
                </span>
                <div class="pagination-glass">
                    {{ $solicitudes->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </x-dashboard.panel>

    </div>
</div>

<style>
:root {
    --purple: #6A2C75;
    --gold: #D6A644;
}
.reveal { opacity: 0; transform: translateY(24px); transition: opacity .6s cubic-bezier(.22,1,.36,1), transform .6s cubic-bezier(.22,1,.36,1); }
.reveal.is-visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) { .reveal { opacity: 1 !important; transform: none !important; transition: none !important; } }

.pagination-glass nav span[aria-current="page"] > span,
.pagination-glass nav a:hover {
    background-color: var(--purple) !important;
    border-color: var(--purple) !important;
    color: #fff !important;
}
.pagination-glass nav a, .pagination-glass nav span {
    color: var(--purple) !important;
    border-radius: 10px !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const targets = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry, idx) => {
                if (entry.isIntersecting) {
                    setTimeout(() => entry.target.classList.add('is-visible'), idx * 50);
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: .1, rootMargin: '0px 0px -40px 0px' });
        targets.forEach(el => io.observe(el));
    } else {
        targets.forEach(el => el.classList.add('is-visible'));
    }
});
</script>

</x-app-layout>
