<x-app-layout>

{{-- ─── PANTALLA DE CARGA ─── --}}
<div id="sgi-loading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-dasavena-purple-dark via-dasavena-purple to-dasavena-purple-light transition-opacity duration-500">
    <div class="flex flex-col items-center gap-4">
        <div class="relative w-20 h-20">
            <span class="absolute -inset-1.5 rounded-full border-2 border-transparent border-t-dasavena-gold border-r-dasavena-gold animate-spin"></span>
            <img src="https://permisos.dasavena-intranet.com/images/logo.png" alt="Dasavena" class="w-20 h-20 object-contain animate-pulse">
        </div>
        <p class="text-white font-display font-bold tracking-wide">Sistema SGI</p>
        <p class="text-dasavena-gold font-mono text-[11px] tracking-wider">// nueva_solicitud</p>
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
        <div class="reveal flex items-center gap-3 rounded-[28px] border border-white/80 bg-white/70 p-6 shadow-[0_10px_32px_rgba(74,30,82,0.06),inset_0_1px_1px_rgba(255,255,255,0.9)] backdrop-blur-2xl">
            <div class="h-9 w-1 rounded-full bg-gradient-to-b from-dasavena-purple to-dasavena-gold"></div>
            <div>
                <h1 class="font-display text-2xl font-bold text-indigo-950">Nueva solicitud</h1>
                <p class="text-xs text-gray-400">Solicitud de actualización de formato y documentos</p>
            </div>
        </div>

        @if ($errors->any())
        <div class="reveal rounded-2xl border border-rose-200 bg-rose-50/80 p-4 text-sm text-rose-700 shadow-sm backdrop-blur-xl">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST"
            action="{{ route('solicitudes.store') }}"
            enctype="multipart/form-data"
            x-data="{
                accion: '{{ old('accion', '') }}',
                searchDocumento: '{{ old('searchDocumento', '') }}',
                isActualizacion() { return this.accion === 'actualizacion' },
                isBaja() { return this.accion === 'baja' },
                isNuevo() { return this.accion === 'nuevo_documento' }
            }"
            onsubmit="this.querySelector('button[type=submit]').disabled = true;"
            class="flex flex-col gap-6">
            @csrf

            {{-- SECCIÓN 1: SOLICITANTE --}}
            <x-dashboard.panel title="Información del solicitante" subtitle="Datos de tu perfil, de solo lectura" class="reveal p-6">
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Nombre</label>
                        <input type="text" value="{{ Auth::user()->name }}" readonly
                            class="w-full cursor-not-allowed rounded-xl border border-white/80 bg-white/40 px-3.5 py-2.5 text-sm text-gray-500 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)]">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Área</label>
                        <input type="text" value="{{ Auth::user()->area ?? 'Sin asignar' }}" readonly
                            class="w-full cursor-not-allowed rounded-xl border border-white/80 bg-white/40 px-3.5 py-2.5 text-sm text-gray-500 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)]">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-gray-500">Puesto</label>
                        <input type="text" value="{{ Auth::user()->puesto ?? 'Sin asignar' }}" readonly
                            class="w-full cursor-not-allowed rounded-xl border border-white/80 bg-white/40 px-3.5 py-2.5 text-sm text-gray-500 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)]">
                    </div>
                </div>
            </x-dashboard.panel>

            {{-- SECCIÓN 2: TIPO DE SOLICITUD --}}
            <x-dashboard.panel title="Tipo de solicitud" class="reveal p-6">
                <div class="mt-4">
                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-gray-500">
                        Acción a realizar <span class="text-rose-500">*</span>
                    </label>
                    <select name="accion"
                        required
                        x-model="accion"
                        class="w-full cursor-pointer rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                        <option value="">-- Seleccione una opción --</option>
                        <option value="actualizacion" @selected(old('accion')==='actualizacion' )>📝 Actualización</option>
                        <option value="nuevo_documento" @selected(old('accion')==='nuevo_documento' )>✨ Nuevo documento</option>
                        <option value="baja" @selected(old('accion')==='baja' )>❌ Baja</option>
                    </select>
                </div>
            </x-dashboard.panel>

            {{-- SECCIÓN 3: INFORMACIÓN DEL DOCUMENTO --}}
            <x-dashboard.panel title="Información del documento" class="reveal p-6">
                <div class="mt-4 space-y-6">

                    {{-- SELECT DE DOCUMENTO (Se muestra en Actualización y en Baja) --}}
                    <div x-show="isActualizacion() || isBaja()" x-transition>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-gray-500">
                            Selecciona el documento <span class="text-rose-500">*</span>
                        </label>

                        <div class="relative mb-3">
                            <svg class="absolute left-3 top-3 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input type="text"
                                x-model="searchDocumento"
                                @input="$dispatch('search-input')"
                                placeholder="Buscar por código, nombre o área…"
                                class="w-full rounded-xl border border-white/80 bg-white/60 py-2.5 pl-10 pr-4 text-sm text-indigo-950 placeholder:text-gray-400 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20" />
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-white/80 bg-white/60 backdrop-blur-xl">
                            <div class="max-h-64 overflow-y-auto">
                                <div class="space-y-0">
                                    @foreach($documentos as $d)
                                    <div x-show="'{{ strtolower($d->codigo.' '.$d->nombre.' '.$d->area) }}'.includes(searchDocumento.toLowerCase())"
                                        class="border-b border-black/[0.04] transition-colors last:border-b-0 hover:bg-dasavena-purple/5"
                                        @click="document.querySelector('select[name=documento_id]').value = '{{ $d->id }}'; document.querySelector('select[name=documento_id]').dispatchEvent(new Event('change'))">
                                        <label class="flex cursor-pointer items-start p-3.5 transition-colors hover:bg-dasavena-purple/5">
                                            <input type="radio"
                                                name="documento_id"
                                                value="{{ $d->id }}"
                                                @checked(old('documento_id')==$d->id)
                                            x-bind:required="isActualizacion() || isBaja()"
                                            class="mt-1 h-4 w-4 border-gray-300 text-dasavena-purple focus:ring-2 focus:ring-dasavena-purple/40"/>
                                            <div class="ml-3 flex-1">
                                                <div class="mb-1 flex items-center gap-2">
                                                    <span class="inline-block rounded-md bg-dasavena-purple px-2 py-0.5 text-[10.5px] font-bold text-white">
                                                        {{ $d->codigo }}
                                                    </span>
                                                    <span class="text-[13px] font-semibold text-indigo-950">{{ $d->nombre }}</span>
                                                </div>
                                                <p class="text-[11px] text-gray-400">
                                                    <span class="inline-block rounded-md bg-gray-100 px-2 py-0.5 text-gray-600">Área: {{ $d->area }}</span>
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                <div x-show="![{{ implode(',', $documentos->pluck('id')->toArray()) }}].some(id => '{{ implode('|', $documentos->map(fn($d) => strtolower($d->codigo.' '.$d->nombre.' '.$d->area))->toArray()) }}'.includes(searchDocumento.toLowerCase()))"
                                    class="p-6 text-center text-sm text-gray-400">
                                    No se encontraron documentos
                                </div>
                            </div>
                        </div>

                        <select name="documento_id" class="hidden">
                            <option value="">-- Selecciona un documento del catálogo --</option>
                            @foreach($documentos as $d)
                            <option value="{{ $d->id }}" @selected(old('documento_id')==$d->id)>
                                {{ $d->codigo }} — {{ $d->nombre }} ({{ $d->area }})
                            </option>
                            @endforeach
                        </select>

                        <p class="mt-3 text-xs text-gray-400">
                            <span x-show="isActualizacion()">Selecciona el documento que deseas modificar.</span>
                            <span x-show="isBaja()" class="font-medium text-rose-600">Selecciona el documento que será retirado del sistema.</span>
                        </p>
                    </div>

                    {{-- NUEVO DOCUMENTO (Solo si es nuevo) --}}
                    <div x-show="isNuevo()" x-transition>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-gray-500">
                            Nombre del nuevo documento <span class="text-rose-500">*</span>
                        </label>
                        <input type="text"
                            name="nombre_documento"
                            placeholder="Ej. Procedimiento de compras"
                            value="{{ old('nombre_documento') }}"
                            x-bind:required="isNuevo()"
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 placeholder:text-gray-400 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">
                    </div>

                    {{-- MOTIVO DE BAJA (Solo si es baja) --}}
                    <div x-show="isBaja()" x-transition>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-gray-500">
                            Justificación de la baja <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="motivo_baja"
                            rows="3"
                            placeholder="Explica por qué este documento ya no es necesario…"
                            x-bind:required="isBaja()"
                            class="w-full rounded-xl border border-rose-200 bg-rose-50/50 px-3.5 py-2.5 text-sm text-indigo-950 placeholder:text-gray-400 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] transition-all focus:border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-200">{{ old('motivo_baja') }}</textarea>
                    </div>

                </div>
            </x-dashboard.panel>

            {{-- SECCIÓN 4: ARCHIVOS Y DETALLES --}}
            <x-dashboard.panel title="Archivos y detalles" class="reveal p-6">
                <div class="mt-4 space-y-5">
                    <div>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-gray-500">
                            Archivo adjunto (opcional)
                        </label>
                        <input type="file"
                            name="archivo"
                            class="w-full rounded-xl border border-white/80 bg-white/60 p-2 text-sm text-gray-600 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl file:mr-3 file:rounded-lg file:border-0 file:bg-dasavena-purple/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-dasavena-purple hover:file:bg-dasavena-purple/15">
                    </div>

                    <div>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-gray-500">
                            Descripción / cambios <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="comentarios"
                            rows="4"
                            required
                            class="w-full rounded-xl border border-white/80 bg-white/60 px-3.5 py-2.5 text-sm text-indigo-950 shadow-[inset_0_1px_1px_rgba(255,255,255,0.8)] backdrop-blur-xl transition-all focus:border-dasavena-purple/40 focus:outline-none focus:ring-2 focus:ring-dasavena-purple/20">{{ old('comentarios') }}</textarea>
                    </div>
                </div>
            </x-dashboard.panel>

            {{-- BOTONES --}}
            <div class="reveal flex items-center justify-between pt-1">
                <a href="{{ route('solicitudes.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-2xl border border-white/80 bg-white/70 px-5 py-2.5 text-sm font-semibold text-gray-600 shadow-sm backdrop-blur-xl transition-all hover:bg-white">
                    ← Cancelar
                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-2xl bg-dasavena-purple px-7 py-2.5 text-sm font-semibold text-white shadow-[0_4px_16px_rgba(106,44,117,0.3)] transition-all hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(106,44,117,0.4)] active:scale-95 disabled:opacity-60 disabled:hover:translate-y-0">
                    Enviar solicitud
                </button>
            </div>

        </form>
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
