<x-app-layout>

{{-- ─── PANTALLA DE CARGA ─── --}}
<div id="sgi-loading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-dasavena-purple-dark via-dasavena-purple to-dasavena-purple-light transition-opacity duration-500">
    <div class="flex flex-col items-center gap-4">
        <div class="relative w-20 h-20">
            <span class="absolute -inset-1.5 rounded-full border-2 border-transparent border-t-dasavena-gold border-r-dasavena-gold animate-spin"></span>
            <img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="Dasavena" class="w-20 h-20 object-contain animate-pulse">
        </div>
        <p class="text-white font-display font-bold tracking-wide">Sistema SGI</p>
        <p class="text-dasavena-gold font-mono text-[11px] tracking-wider">// calendario_vencimientos</p>
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

    <div class="relative z-10 mx-auto flex max-w-[1650px] flex-col gap-6 px-4 py-10 sm:px-6 lg:px-8">

        {{-- ══════════════════════════════ ENCABEZADO ══════════════════════════════ --}}
        <div class="reveal flex flex-col gap-4 rounded-[28px] border border-white/80 bg-white/70 p-6 shadow-[0_10px_32px_rgba(74,30,82,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-1 rounded-full bg-gradient-to-b from-dasavena-purple to-dasavena-gold"></div>
                <div>
                    <h1 class="font-display text-2xl font-bold text-indigo-950">Calendario de vencimientos</h1>
                    <p class="text-xs text-gray-400">Gestión de versiones y revisiones con monitoreo preventivo de fechas</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-white/80 bg-white/60 px-4 py-2.5 shadow-sm backdrop-blur-xl">
                <span class="h-2.5 w-2.5 rounded-full bg-dasavena-gold" style="animation:pulse-dot 1.6s ease-in-out infinite"></span>
                <span id="liveClock" class="text-sm font-bold uppercase tracking-widest text-indigo-950">Cargando...</span>
            </div>
        </div>

        {{-- ══════════════════════════════ TARJETAS DE RESUMEN ══════════════════════════════ --}}
        <div class="reveal grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="group relative overflow-hidden rounded-[24px] border border-white/80 bg-white/70 p-6 shadow-[0_8px_24px_rgba(74,30,82,0.05),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_32px_rgba(74,30,82,0.1)]">
                <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-dasavena-purple/5"></div>
                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Vencidos</p>
                <p id="statVencidos" class="font-mono text-4xl font-extrabold tracking-tight text-dasavena-purple">0</p>
                <div class="mt-3 flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 text-dasavena-purple/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    <p class="text-[10px] font-semibold text-gray-400">Fecha ya superada</p>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-[24px] border border-rose-200/70 bg-white/70 p-6 shadow-[0_8px_24px_rgba(74,30,82,0.05),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_32px_rgba(190,30,30,0.1)]">
                <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-rose-50"></div>
                <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-rose-400 to-rose-600"></div>
                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-rose-600/70">Estado crítico</p>
                <p id="statCriticoBig" class="font-mono text-4xl font-extrabold tracking-tight text-rose-600">0</p>
                <div class="mt-3 flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <p class="text-[10px] font-semibold text-rose-500/70">Menos de 30 días</p>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-[24px] border border-amber-200/70 bg-white/70 p-6 shadow-[0_8px_24px_rgba(74,30,82,0.05),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_32px_rgba(214,166,68,0.12)]">
                <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-amber-50"></div>
                <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-amber-400 to-amber-500"></div>
                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-amber-600/70">Alerta preventiva</p>
                <p id="statAlertaBig" class="font-mono text-4xl font-extrabold tracking-tight text-amber-600">0</p>
                <div class="mt-3 flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-[10px] font-semibold text-amber-600/70">31 a 60 días</p>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-[24px] border border-emerald-200/70 bg-white/70 p-6 shadow-[0_8px_24px_rgba(74,30,82,0.05),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_32px_rgba(16,185,129,0.12)]">
                <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-emerald-50"></div>
                <div class="absolute inset-x-0 top-0 h-[3px] bg-gradient-to-r from-emerald-400 to-emerald-600"></div>
                <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-emerald-600/70">En regla / óptimo</p>
                <p id="statReglaBig" class="font-mono text-4xl font-extrabold tracking-tight text-emerald-600">0</p>
                <div class="mt-3 flex items-center gap-2">
                    <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-[10px] font-semibold text-emerald-600/70">Mayor a 60 días</p>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════ CALENDARIO / LISTA ══════════════════════════════ --}}
        <x-dashboard.panel class="reveal flex flex-col">
            <div class="border-b border-black/[0.04] p-6">
                <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-end">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-1 rounded-xl border border-white/80 bg-white/60 p-1 backdrop-blur-xl">
                            <button id="prevMonth" class="rounded-lg p-2.5 text-dasavena-purple transition-colors hover:bg-dasavena-purple/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button id="nextMonth" class="rounded-lg p-2.5 text-dasavena-purple transition-colors hover:bg-dasavena-purple/10">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                        <h3 id="monthTitle" class="min-w-[240px] bg-gradient-to-r from-dasavena-purple to-dasavena-gold bg-clip-text font-display text-xl font-extrabold text-transparent"></h3>
                        <div class="flex items-center gap-1 rounded-xl border border-white/80 bg-white/60 p-1 backdrop-blur-xl">
                            <button id="tabMes" class="tab-active rounded-lg px-5 py-2 text-xs font-bold transition-all">Mes</button>
                            <button id="tabLista" class="rounded-lg px-5 py-2 text-xs font-bold text-gray-400 transition-all hover:text-dasavena-purple">Lista</button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-end gap-3">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Buscar</label>
                            <input id="q" type="text" placeholder="Código o nombre…"
                                class="w-52 rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 placeholder:text-gray-400 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Área</label>
                            <select id="area" class="cursor-pointer rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                                <option value="all">Todas las áreas</option>
                                <option value="Administración">Administración</option>
                                <option value="Dirección ">Dirección</option>
                                <option value="Almacén">Almacén</option>
                                <option value="Innovación">Innovación</option>
                                <option value="Higiene y seguridad industrial">Higiene y seg</option>
                                <option value="Seguridad">Seguridad</option>
                                <option value="">Mejora continua</option>
                                <option value="Producción">Producción</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Recursos Humanos">RH</option>
                                <option value="Sistema de gestión">Sgi</option>
                                <option value="sistemas">Sistemas</option>
                                <option value="Compras">Compras</option>
                                <option value="Ventas">Ventas</option>
                                <option value="Calidad">Calidad</option>
                                <option value="Sensorial">Sensorial</option>
                                <option value="Marketing">Marketing</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Estado</label>
                            <select id="estado" class="cursor-pointer rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                                <option value="all">Todos</option>
                                <option value="vencidos">Vencidos</option>
                                <option value="por_vencer">Por vencer (0–30 días)</option>
                                <option value="alerta">Alerta (31–60 días)</option>
                                <option value="en_regla">En regla (&gt;60 días)</option>
                                <option value="baja">Dados de baja</option>
                                <option value="obsoleto">Obsoletos</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500">Tipo</label>
                            <select id="tipo" class="cursor-pointer rounded-xl border border-white/80 bg-white/60 px-3.5 py-2 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                                <option value="both">Versión + Revisión</option>
                                <option value="version">Solo Versión</option>
                                <option value="revision">Solo Revisión</option>
                            </select>
                        </div>
                        <label class="flex cursor-pointer items-center gap-2 self-end rounded-xl border border-white/80 bg-white/60 px-4 py-2 backdrop-blur-xl transition-all hover:bg-white/80">
                            <input id="historicos" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-dasavena-purple focus:ring-dasavena-purple/30">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-600">Históricos</span>
                        </label>
                        <button type="button" id="btnObsoletos"
                            class="inline-flex items-center gap-2 self-end rounded-xl border border-white/80 bg-white/60 px-4 py-2 text-xs font-bold text-gray-600 shadow-sm backdrop-blur-xl transition-all hover:-translate-y-0.5 hover:bg-white/80 active:scale-95">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                            </svg>
                            Obsoletos
                            <span id="statObsoletosBadge" class="inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-gray-500 px-1 text-[10px] font-bold text-white">0</span>
                        </button>
                        <button id="btnFiltrar"
                            class="self-end rounded-xl bg-dasavena-gold px-6 py-2 text-xs font-bold text-dasavena-purple-dark shadow-[0_3px_10px_rgba(214,166,68,0.3)] transition-all hover:-translate-y-0.5 hover:shadow-[0_5px_16px_rgba(214,166,68,0.4)] active:scale-95">
                            Aplicar filtros
                        </button>
                    </div>
                </div>
            </div>

            <div id="viewMes">
                <div class="grid grid-cols-7 border-b border-black/[0.04] bg-white/40">
                    @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                    <div class="py-3.5 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">{{ $d }}</div>
                    @endforeach
                </div>
                <div id="calendarGrid" class="grid grid-cols-7"></div>
            </div>

            <div id="viewLista" class="hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-black/[0.04] bg-white/40">
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Vencimiento</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Tipo</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Código</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Nombre</th>
                                <th class="px-5 py-3.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Área / Ubicación</th>
                                <th class="px-5 py-3.5 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="listBody" class="divide-y divide-black/[0.04]"></tbody>
                    </table>
                </div>
            </div>
        </x-dashboard.panel>
    </div>
</div>

{{-- Carpeta: Obsoletos --}}
<div id="modalObsoletos" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div id="modalObsoletosBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative flex max-h-[80vh] w-full max-w-4xl flex-col overflow-hidden rounded-[28px] border border-white/80 bg-white/90 shadow-2xl backdrop-blur-2xl">
        <div class="flex items-center justify-between border-b border-black/[0.04] bg-white/50 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-dasavena-purple/10 text-dasavena-purple">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-display text-lg font-bold text-indigo-950">Carpeta: Obsoletos</h3>
                    <p class="text-xs text-gray-400">Versiones y revisiones marcadas como obsoletas</p>
                </div>
            </div>
            <button id="closeObsoletos" class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="sticky top-0 border-b border-black/[0.04] bg-white/90">
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Tipo</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Código</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Nombre</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Área</th>
                        <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody id="obsoletosBody" class="divide-y divide-black/[0.04]"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Detalle del día --}}
<div id="modalDia" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4">
    <div id="modalDiaBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative flex max-h-[80vh] w-full max-w-3xl flex-col overflow-hidden rounded-[28px] border border-white/80 bg-white/90 shadow-2xl backdrop-blur-2xl">
        <div class="flex items-center justify-between border-b border-black/[0.04] bg-white/50 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-dasavena-purple/10 text-dasavena-purple">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 id="modalDiaTitulo" class="font-display text-lg font-bold text-indigo-950">Vencimientos del día</h3>
                    <p class="text-xs text-gray-400">Versiones y revisiones con vencimiento en esta fecha</p>
                </div>
            </div>
            <button id="closeModalDia" class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="sticky top-0 border-b border-black/[0.04] bg-white/90">
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Estado</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Tipo</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Código</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Nombre</th>
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Área</th>
                        <th class="px-5 py-3 text-center text-[10px] font-bold uppercase tracking-widest text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody id="modalDiaBody" class="divide-y divide-black/[0.04]"></tbody>
            </table>
        </div>
    </div>
</div>

<style>
@keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.5)} }

.reveal { opacity: 0; transform: translateY(24px); transition: opacity .6s cubic-bezier(.22,1,.36,1), transform .6s cubic-bezier(.22,1,.36,1); }
.reveal.is-visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) { .reveal { opacity: 1 !important; transform: none !important; transition: none !important; } }

/* Tab activo */
.tab-active {
    background: linear-gradient(135deg, #6A2C75, #8e3d9e) !important;
    color: #fff !important;
    box-shadow: 0 4px 14px rgba(106,44,117,.3);
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
        }, { threshold: .08, rootMargin: '0px 0px -40px 0px' });
        targets.forEach(el => io.observe(el));
    } else {
        targets.forEach(el => el.classList.add('is-visible'));
    }
});

const DATA_URL = "{{ route('solicitudes.calendar.data') }}";
let current = new Date();
current.setDate(1);
let eventsByDateCache = {};

function estadoBadge(severity, days) {
    days = parseInt(days);
    if (severity === 'baja')     return { badge: 'bg-gray-100 text-gray-600 border-gray-200',                       dot: 'bg-gray-400',          label: 'Baja' };
    if (severity === 'obsoleto') return { badge: 'bg-slate-100 text-slate-600 border-slate-200',                   dot: 'bg-slate-400',         label: 'Obsoleto' };
    if (days < 0)                return { badge: 'bg-dasavena-purple/10 text-dasavena-purple border-dasavena-purple/25', dot: 'bg-dasavena-purple', label: 'Vencido' };
    if (days <= 30)              return { badge: 'bg-rose-100 text-rose-700 border-rose-200',                      dot: 'bg-rose-500',          label: days + ' días' };
    if (days <= 60)              return { badge: 'bg-amber-100 text-amber-700 border-amber-200',                   dot: 'bg-amber-500',         label: days + ' días' };
    return { badge: 'bg-emerald-100 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500', label: days + ' días' };
}

function formatearFechaLarga(key) {
    const [y, m, d] = key.split('-').map(Number);
    return new Date(y, m - 1, d).toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' });
}

const months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

function yyyymm(d) { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`; }
function ymdLocal(d) { return d.toISOString().split('T')[0]; }

function loadData() {
    const params = new URLSearchParams({
        month:      yyyymm(current),
        q:          document.getElementById('q').value || '',
        tipo:       document.getElementById('tipo').value || 'both',
        estado:     document.getElementById('estado').value || 'all',
        area:       document.getElementById('area').value || 'all',
        historicos: document.getElementById('historicos').checked ? '1' : '0',
    });

    fetch(`${DATA_URL}?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('monthTitle').textContent = `${months[current.getMonth()]} ${current.getFullYear()}`;

            document.getElementById('statVencidos').innerText   = data.stats?.vencidos   !== undefined ? data.stats.vencidos : 0;
            document.getElementById('statCriticoBig').innerText = data.stats?.critico    !== undefined ? data.stats.critico  : 0;
            document.getElementById('statAlertaBig').innerText  = data.stats?.alerta     !== undefined ? data.stats.alerta   : 0;
            document.getElementById('statReglaBig').innerText   = data.stats?.regla      !== undefined ? data.stats.regla    : 0;

            renderCalendar(data);
            renderList(data.list || []);
        })
        .catch(err => console.error("Error cargando datos:", err));
}

function renderCalendar(data) {
    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';
    eventsByDateCache = data.eventsByDate || {};
    const start = new Date(current);
    const firstDay = (start.getDay() === 0) ? 7 : start.getDay(); // Ajuste para que Lunes sea 1
    start.setDate(start.getDate() - (firstDay - 1));

    for (let i = 0; i < 42; i++) {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        const key    = ymdLocal(d);
        const events = (data.eventsByDate && data.eventsByDate[key]) ? data.eventsByDate[key] : [];
        const isCur  = d.getMonth() === current.getMonth();
        const tieneEventos = events.length > 0;

        let html = `
            <div class="min-h-[160px] p-3 border border-black/[0.04] transition-all ${tieneEventos ? 'cursor-pointer hover:bg-dasavena-purple/5 hover:ring-2 hover:ring-inset hover:ring-dasavena-purple/20' : 'hover:bg-white/70'} ${!isCur ? 'opacity-30 bg-gray-50/50' : 'bg-white/50'}"
                 ${tieneEventos ? `onclick="abrirDiaModal('${key}')"` : ''}>
                <span class="text-xs font-bold ${isCur ? 'text-indigo-950' : 'text-gray-400'}">${d.getDate()}</span>
                <div class="mt-2 space-y-1.5">
        `;

        events.slice(0,3).forEach(e => {
            const color = e.severity === 'baja'
                ? 'border-gray-400 bg-gray-100 text-gray-600'
                : e.severity === 'obsoleto'
                ? 'border-slate-400 bg-slate-100 text-slate-600'
                : e.severity === 'danger'
                ? 'border-dasavena-purple bg-dasavena-purple/10 text-dasavena-purple'
                : e.severity === 'warning'
                ? 'border-amber-500 bg-amber-50 text-amber-700'
                : 'border-emerald-500 bg-emerald-50 text-emerald-700';

            html += `
                <div class="border-l-[3px] ${color} px-2 py-1 rounded-r-lg text-[10px] leading-tight hover:scale-[1.02] transition-transform">
                    <div class="font-bold truncate">${e.codigo || 'S/C'}</div>
                    <div class="truncate opacity-75">${e.nombre || ''}</div>
                </div>
            `;
        });

        if (events.length > 3) {
            html += `<div class="text-[9px] font-bold text-dasavena-gold-dark ml-1">+ ${events.length - 3} más</div>`;
        }

        html += `</div></div>`;
        grid.insertAdjacentHTML('beforeend', html);
    }
}

function abrirDiaModal(key) {
    const events = eventsByDateCache[key] || [];
    document.getElementById('modalDiaTitulo').textContent = `Vencimientos del ${formatearFechaLarga(key)}`;
    renderModalDia(events);
    document.getElementById('modalDia').classList.remove('hidden');
}

function renderModalDia(events) {
    const body = document.getElementById('modalDiaBody');
    body.innerHTML = '';

    if (!events.length) {
        body.innerHTML = `<tr><td colspan="6" class="py-16 text-center text-sm text-gray-400">Sin registros para este día</td></tr>`;
        return;
    }

    events.forEach(e => {
        const { badge, dot, label } = estadoBadge(e.severity, e.days_left);

        body.insertAdjacentHTML('beforeend', `
            <tr class="hover:bg-white/70 transition-colors">
                <td class="px-5 py-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold border ${badge}">
                        <span class="w-1.5 h-1.5 rounded-full ${dot} flex-shrink-0"></span>
                        ${label}
                    </span>
                </td>
                <td class="px-5 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">${e.tipo || ''}</td>
                <td class="px-5 py-4 text-xs font-bold text-indigo-950">${e.codigo || ''}</td>
                <td class="px-5 py-4 text-xs font-semibold text-indigo-950">${e.nombre || ''}</td>
                <td class="px-5 py-4 text-xs text-gray-500">${e.area || 'N/A'}</td>
                <td class="px-5 py-4 text-center">
                    <a href="${e.url || '#'}" target="_blank"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-dasavena-purple/5 text-dasavena-purple hover:bg-dasavena-purple/10 text-[10px] font-bold transition-colors border border-dasavena-purple/15">
                        Ver
                    </a>
                </td>
            </tr>
        `);
    });
}

function renderList(list) {
    const body = document.getElementById('listBody');
    body.innerHTML = '';

    if (!list.length) {
        body.innerHTML = `<tr><td colspan="6" class="py-16 text-center text-sm text-gray-400">Sin registros</td></tr>`;
        return;
    }

    list.forEach(row => {
        const { badge, dot, label } = estadoBadge(row.severity, row.days_left);

        body.insertAdjacentHTML('beforeend', `
            <tr class="hover:bg-white/70 transition-colors">
                <td class="px-5 py-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold border ${badge}">
                        <span class="w-1.5 h-1.5 rounded-full ${dot} flex-shrink-0"></span>
                        ${label}
                    </span>
                </td>
                <td class="px-5 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">${row.vencimiento_tipo || ''}</td>
                <td class="px-5 py-4 text-xs font-bold text-indigo-950">${row.codigo_documento || ''}</td>
                <td class="px-5 py-4">
                    <p class="text-xs font-semibold text-indigo-950">${row.nombre_documento || ''}</p>
                    <p class="text-[10px] text-gray-400 uppercase mt-0.5">${row.tipo_documento || ''}</p>
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">${row.area || 'N/A'}</td>
                <td class="px-5 py-4 text-center">
                    <a href="${row.url_documento || '#'}" target="_blank"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-dasavena-purple/5 text-dasavena-purple hover:bg-dasavena-purple/10 text-[10px] font-bold transition-colors border border-dasavena-purple/15">
                        Ver
                    </a>
                </td>
            </tr>
        `);
    });
}

function loadObsoletos() {
    const params = new URLSearchParams({
        month:      yyyymm(current),
        historicos: '1',
        estado:     'obsoleto',
        tipo:       'both',
    });

    fetch(`${DATA_URL}?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            const list = data.list || [];
            document.getElementById('statObsoletosBadge').innerText = list.length;
            renderObsoletos(list);
        })
        .catch(err => console.error('Error cargando obsoletos:', err));
}

function renderObsoletos(list) {
    const body = document.getElementById('obsoletosBody');
    body.innerHTML = '';

    if (!list.length) {
        body.innerHTML = `<tr><td colspan="5" class="py-16 text-center text-gray-400 text-sm">No hay elementos obsoletos.</td></tr>`;
        return;
    }

    list.forEach(row => {
        body.insertAdjacentHTML('beforeend', `
            <tr class="hover:bg-white/60 transition-colors">
                <td class="px-5 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">${row.vencimiento_tipo || ''}</td>
                <td class="px-5 py-4 text-xs font-bold text-indigo-950">${row.codigo_documento || ''}</td>
                <td class="px-5 py-4">
                    <p class="text-xs font-semibold text-indigo-950">${row.nombre_documento || ''}</p>
                    <p class="text-[10px] text-gray-400 uppercase mt-0.5">${row.tipo_documento || ''}</p>
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">${row.area || 'N/A'}</td>
                <td class="px-5 py-4 text-center">
                    <a href="${row.url_documento || '#'}" target="_blank"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 text-[10px] font-bold transition-colors border border-gray-200">
                        Ver
                    </a>
                </td>
            </tr>
        `);
    });
}

const modalObsoletos = document.getElementById('modalObsoletos');
document.getElementById('btnObsoletos').onclick = () => {
    modalObsoletos.classList.remove('hidden');
    loadObsoletos();
};
document.getElementById('closeObsoletos').onclick = () => modalObsoletos.classList.add('hidden');
document.getElementById('modalObsoletosBackdrop').onclick = () => modalObsoletos.classList.add('hidden');

const modalDia = document.getElementById('modalDia');
document.getElementById('closeModalDia').onclick = () => modalDia.classList.add('hidden');
document.getElementById('modalDiaBackdrop').onclick = () => modalDia.classList.add('hidden');

document.getElementById('prevMonth').onclick = () => { current.setMonth(current.getMonth()-1); loadData(); };
document.getElementById('nextMonth').onclick = () => { current.setMonth(current.getMonth()+1); loadData(); };
document.getElementById('btnFiltrar').onclick = () => loadData();

const viewMes   = document.getElementById('viewMes');
const viewLista = document.getElementById('viewLista');
const tabMes    = document.getElementById('tabMes');
const tabLista  = document.getElementById('tabLista');

tabMes.onclick = () => {
    viewMes.classList.remove('hidden');
    viewLista.classList.add('hidden');
    tabMes.classList.add('tab-active');
    tabLista.classList.remove('tab-active');
};
tabLista.onclick = () => {
    viewLista.classList.remove('hidden');
    viewMes.classList.add('hidden');
    tabLista.classList.add('tab-active');
    tabMes.classList.remove('tab-active');
};

setInterval(() => {
    document.getElementById('liveClock').innerText = new Date().toLocaleTimeString('es-MX');
}, 1000);

loadData();
loadObsoletos();
</script>
</x-app-layout>
