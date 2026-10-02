<x-app-layout>

@php
    $vigente = $documento->versionVigente
        ?? $documento->versiones()->latest('id')->first();

    $today = now()->startOfDay();

    $daysLeftIfVigente = function($estatus, $d) use ($today) {
        if (($estatus ?? null) !== 'vigente') {
            return null;
        }
        if (!$d) {
            return null;
        }
        return $today->diffInDays(
            \Carbon\Carbon::parse($d)->startOfDay(),
            false
        );
    };

    $daysVersion = $daysLeftIfVigente(
        $vigente?->estatus,
        $vigente?->fecha_vencimiento_version
    );

    $daysRevision = $daysLeftIfVigente(
        $vigente?->estatus,
        $vigente?->fecha_vencimiento_revision
    );
@endphp

{{-- ─── PANTALLA DE CARGA ─── --}}
<div id="sgi-loading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-dasavena-purple-dark via-dasavena-purple to-dasavena-purple-light transition-opacity duration-500">
    <div class="flex flex-col items-center gap-4">
        <div class="relative w-20 h-20">
            <span class="absolute -inset-1.5 rounded-full border-2 border-transparent border-t-dasavena-gold border-r-dasavena-gold animate-spin"></span>
            <img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="Dasavena" class="w-20 h-20 object-contain animate-pulse">
        </div>
        <p class="text-white font-display font-bold tracking-wide">Sistema SGI</p>
        <p class="text-dasavena-gold font-mono text-[11px] tracking-wider">// editar_datos_oficiales</p>
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

    <div class="relative z-10 mx-auto flex max-w-4xl flex-col gap-6 px-4 py-10 sm:px-6 lg:px-8">

        {{-- ══════════════════════════════ ENCABEZADO ══════════════════════════════ --}}
        <div class="reveal flex items-center justify-between gap-4 rounded-[28px] border border-white/80 bg-white/70 p-6 shadow-[0_10px_32px_rgba(74,30,82,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl">
            <div class="flex items-center gap-3">
                <div class="h-9 w-1 rounded-full bg-gradient-to-b from-dasavena-purple to-dasavena-gold"></div>
                <div>
                    <h1 class="font-display text-2xl font-bold text-indigo-950">
                        Editando <span class="text-dasavena-purple">{{ $documento->codigo }}</span>
                    </h1>
                    <p class="text-xs text-gray-400">Datos maestros del registro oficial — los cambios son inmediatos</p>
                </div>
            </div>
            <a href="{{ route('documentos.show', $documento->id) }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-2xl border border-white/80 bg-white/70 px-4 py-2 text-sm font-semibold text-gray-600 shadow-sm backdrop-blur-xl transition-all hover:bg-white">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver al detalle
            </a>
        </div>

        <form action="{{ route('documentos.update', $documento->id) }}" method="POST" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            {{-- SECCIÓN 01 --}}
            <x-dashboard.panel title="Identificación básica" subtitle="Código único y nombre descriptivo del documento" class="reveal p-6">
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500" for="codigo">Código</label>
                        <input id="codigo" type="text" name="codigo" value="{{ old('codigo', $documento->codigo) }}" autocomplete="off"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                        @error('codigo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500" for="nombre">Nombre del documento</label>
                        <input id="nombre" type="text" name="nombre" value="{{ old('nombre', $documento->nombre) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                </div>
            </x-dashboard.panel>

            {{-- SECCIÓN 02 --}}
            <x-dashboard.panel title="Clasificación y estatus" subtitle="Tipo de formato y estado de vigencia del documento" class="reveal p-6">
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500" for="tipo_doc">Tipo de documento</label>
                        <input id="tipo_doc" type="text" name="tipo_documento" value="{{ old('tipo_documento', $documento->tipo_documento) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500" for="el_pa">EL / PA</label>
                        <input id="el_pa" type="text" name="formato_el_pa" value="{{ old('formato_el_pa', $documento->formato_el_pa) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-amber-600" for="estatus">
                            Estatus maestro
                            <span class="rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[9.5px] font-bold uppercase tracking-wider text-amber-700">Importante</span>
                        </label>
                        <select id="estatus" name="estatus"
                            class="w-full cursor-pointer rounded-xl border border-amber-200 bg-amber-50/70 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] transition-all focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
                            <option value="vigente" {{ $documento->estatus == 'vigente' ? 'selected' : '' }}>Vigente</option>
                            <option value="baja" {{ $documento->estatus == 'baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                        <p class="mt-1.5 text-[11px] italic text-gray-400">"Baja" desactiva todas las alertas de este documento.</p>
                    </div>
                </div>
            </x-dashboard.panel>

            {{-- SECCIÓN 03 --}}
            <x-dashboard.panel title="Ubicación y referencias" subtitle="Departamento responsable y ruta de almacenamiento" class="reveal p-6">
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500" for="area">Área / Departamento</label>
                        <input id="area" type="text" name="area" value="{{ old('area', $documento->area) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500" for="sharepoint">Carpeta SharePoint</label>
                        <input id="sharepoint" type="url" name="sharepoint_folder" value="{{ old('sharepoint_folder', $documento->sharepoint_folder) }}" placeholder="https://…"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 placeholder:text-gray-400 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                </div>
            </x-dashboard.panel>

            {{-- SECCIÓN 04 --}}
            <x-dashboard.panel title="Información SGI / SharePoint" subtitle="Datos utilizados para calendario, dashboard y sincronización documental" class="reveal p-6">
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">SharePoint Path</label>
                        <input type="text" name="sharepoint_path" value="{{ old('sharepoint_path', $documento->versionVigente?->sharepoint_path) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">SharePoint File ID</label>
                        <input type="text" name="sharepoint_file_id" value="{{ old('sharepoint_file_id', $documento->versionVigente?->sharepoint_file_id) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">SP Drive ID</label>
                        <input type="text" name="sp_drive_id" value="{{ old('sp_drive_id', $documento->versionVigente?->sp_drive_id) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">SP Item ID</label>
                        <input type="text" name="sp_item_id" value="{{ old('sp_item_id', $documento->versionVigente?->sp_item_id) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">SP Web URL</label>
                        <input type="url" name="sp_web_url" value="{{ old('sp_web_url', $documento->versionVigente?->sp_web_url) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">SP Folder Path</label>
                        <input type="text" name="sp_folder_path" value="{{ old('sp_folder_path', $documento->versionVigente?->sp_folder_path) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Liga archivo</label>
                        <input type="url" name="liga_archivo" value="{{ old('liga_archivo', $documento->versionVigente?->liga_archivo) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Lugar de almacenamiento</label>
                        <input type="text" name="lugar_almacenamiento" value="{{ old('lugar_almacenamiento', $documento->versionVigente?->lugar_almacenamiento) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Versión / folio</label>
                        <input type="text" name="version" value="{{ old('version', $documento->versionVigente?->version) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Fecha revisión</label>
                        <input type="date" name="fecha_revision" value="{{ old('fecha_revision', optional($documento->versionVigente?->fecha_revision)->format('Y-m-d')) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Vigencia versión (días)</label>
                        <input type="number" name="vigencia_version_dias" value="{{ old('vigencia_version_dias', $documento->versionVigente?->vigencia_version_dias) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Vigencia revisión (días)</label>
                        <input type="number" name="vigencia_revision_dias" value="{{ old('vigencia_revision_dias', $documento->versionVigente?->vigencia_revision_dias) }}"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>

                    <div class="sm:col-span-2 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-white/80 bg-white/50 p-3 backdrop-blur-xl">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Días versión</p>
                            <p class="mt-0.5 font-mono text-sm font-bold text-indigo-950">{{ $daysVersion !== null ? $daysVersion.' días' : '—' }}</p>
                        </div>
                        <div class="rounded-xl border border-white/80 bg-white/50 p-3 backdrop-blur-xl">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Días revisión</p>
                            <p class="mt-0.5 font-mono text-sm font-bold text-indigo-950">{{ $daysRevision !== null ? $daysRevision.' días' : '—' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Observaciones SGI</label>
                    <textarea name="observaciones_sgi" rows="5"
                        class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">{{ old('observaciones_sgi', $documento->versionVigente?->observaciones_sgi) }}</textarea>
                </div>
            </x-dashboard.panel>

            {{-- BOTONES --}}
            <div class="reveal flex items-center justify-between gap-4 rounded-[28px] border border-white/80 bg-white/70 p-5 shadow-[0_10px_32px_rgba(74,30,82,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl">
                <a href="{{ route('documentos.show', $documento->id) }}"
                    class="text-sm font-semibold text-gray-400 transition-colors hover:text-rose-600">
                    Descartar cambios
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-dasavena-purple px-7 py-2.5 text-sm font-semibold text-white shadow-[0_4px_16px_rgba(106,44,117,0.3)] transition-all hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(106,44,117,0.4)] active:scale-95">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Actualizar documento
                </button>
            </div>
        </form>

        {{-- ══════════════════════════════ NOTA ══════════════════════════════ --}}
        <div class="reveal flex items-start gap-3 rounded-2xl border border-dasavena-purple/15 bg-dasavena-purple/5 p-4 backdrop-blur-xl">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-dasavena-purple" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p class="text-[13px] leading-relaxed text-gray-500">
                Estás editando el <strong class="font-semibold text-indigo-950">registro maestro</strong>.
                Para subir una nueva versión o revisión, usa la sección <em class="font-semibold not-italic text-dasavena-purple">Timeline</em> en la vista de detalle —
                esto preserva el historial completo del documento.
            </p>
        </div>

    </div>
</div>

<style>
.reveal { opacity: 0; transform: translateY(24px); transition: opacity .6s cubic-bezier(.22,1,.36,1), transform .6s cubic-bezier(.22,1,.36,1); }
.reveal.is-visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) { .reveal { opacity: 1 !important; transform: none !important; transition: none !important; } }
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
</script>

</x-app-layout>
