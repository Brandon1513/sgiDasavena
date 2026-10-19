{{--
    Selector de SharePoint de finalize_form (Alpine: sharepointPicker en finalize_form).

    $modo = 'carpeta'  → elige la carpeta donde se publica (sp_carpeta_vigente_path)
                          y permite marcar archivos para Obsoletos.
    $modo = 'archivos' → solo marca archivos para Obsoletos (solicitudes de baja).

    Los archivos marcados viajan como sp_obsoletos_items[] (IDs de SharePoint);
    sp_obsoletos_meta solo sirve para restaurar la lista si el formulario
    regresa con errores de validación.
--}}
@php
    $config = [
        'modo' => $modo,
        'sugerida' => old('sp_carpeta_vigente_path', $carpetaSugerida),
        'rutaRaiz' => config('sharepoint.root_folder'),
        'urlBase' => route('sharepoint.carpetas'),
        'vigenteItemId' => $vigenteItemId,
        'crearSubcarpeta' => old('sp_crear_subcarpeta_codigo', '1') === '1',
        'marcados' => json_decode(old('sp_obsoletos_meta', '[]'), true) ?: [],
    ];
@endphp

<div x-data="sharepointPicker({{ json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP) }})" class="space-y-4">

    {{-- Valores que se envían --}}
    @if($modo === 'carpeta')
        <input type="hidden" name="sp_carpeta_vigente_path" :value="rutaSeleccionada">
    @endif
    <template x-for="m in marcados" :key="m.id">
        <input type="hidden" name="sp_obsoletos_items[]" :value="m.id">
    </template>
    <input type="hidden" name="sp_obsoletos_meta" :value="JSON.stringify(marcados)">

    @if($modo === 'carpeta')
        {{-- Ubicación --}}
        <div>
            <span class="c-label">Ubicación en SharePoint</span>
            <div class="flex flex-wrap items-center gap-2 rounded-2xl border p-3"
                 :class="rutaSeleccionada ? 'border-white/80 bg-white/60' : 'border-rose-200 bg-rose-50/70'">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-dasavena-purple/15 bg-dasavena-purple/10 text-dasavena-purple">
                    <i class="ti ti-folder text-[18px]"></i>
                </span>
                <span class="min-w-0 flex-1 truncate font-mono text-[12.5px] text-indigo-950" x-text="rutaSeleccionada || 'No se pudo sugerir una ubicación automática'"></span>
                <button type="button" @click="abrir()" :disabled="isRechazar()" class="c-btn-soft">
                    <i class="ti ti-folder-search text-[15px]"></i> Cambiar
                </button>
            </div>
            <p class="c-hint" :class="!rutaSeleccionada && 'text-rose-600 font-semibold'"
               x-text="rutaSeleccionada ? 'Sugerida automáticamente según el área y el tipo de documento. Puedes cambiarla.' : 'No se identificó el tipo de documento: elige la carpeta manualmente.'"></p>
            @error('sp_carpeta_vigente_path')
                <p class="c-hint text-rose-600 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-white/80 bg-white/50 p-3">
            {{-- El hidden con 0 va primero: si el checkbox queda desmarcado, se envía este 0. --}}
            <input type="hidden" name="sp_crear_subcarpeta_codigo" value="0">
            <input type="checkbox" name="sp_crear_subcarpeta_codigo" value="1" x-model="crearSubcarpeta" :disabled="isRechazar()" class="c-check mt-0.5">
            <span class="text-[13px] leading-snug text-gray-600">
                <span class="font-semibold text-indigo-950">Crear subcarpeta con el código del documento</span><br>
                <span class="text-[12px] text-gray-400" x-text="crearSubcarpeta ? 'El archivo se subirá en …/{código}/ dentro de la ubicación elegida.' : 'El archivo se subirá suelto, directo en la carpeta elegida.'"></span>
            </span>
        </label>
    @endif

    {{-- Archivos que pasan a Obsoletos --}}
    <div class="rounded-2xl border border-dasavena-gold/25 bg-dasavena-gold/[0.07] p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-dasavena-gold/25 bg-dasavena-gold/15 text-dasavena-gold-dark">
                    <i class="ti ti-archive text-[18px]"></i>
                </span>
                <div>
                    <p class="text-[13px] font-semibold text-indigo-950">Archivos a mover a Obsoletos</p>
                    <p class="text-[12px] leading-snug text-gray-500">
                        @if($vigenteItemId)
                            El archivo de la versión vigente ya está ligado y se moverá automáticamente.
                            Marca aquí solo otros archivos que también deban retirarse.
                        @else
                            La versión vigente no tiene un archivo ligado en SharePoint (se subió antes del sistema).
                            Búscalo y márcalo para que se mueva a Obsoletos al atender.
                        @endif
                    </p>
                </div>
            </div>
            <button type="button" @click="abrir()" :disabled="isRechazar()" class="c-btn-soft c-btn-gold">
                <i class="ti ti-file-search text-[15px]"></i> Buscar archivos
            </button>
        </div>

        <ul class="mt-3 space-y-2" x-show="marcados.length" x-cloak>
            <template x-for="m in marcados" :key="m.id">
                <li class="flex items-center gap-3 rounded-xl border border-white/80 bg-white/70 px-3 py-2">
                    <span x-text="iconoArchivo(m.name)"></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] font-medium text-indigo-950" x-text="m.name"></span>
                        <span class="block truncate font-mono text-[11px] text-gray-400" x-text="m.ruta"></span>
                    </span>
                    <button type="button" @click="desmarcar(m.id)" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 hover:bg-rose-50 hover:text-rose-600" :title="'Quitar ' + m.name">
                        <i class="ti ti-x text-[14px]"></i>
                    </button>
                </li>
            </template>
        </ul>
        <p class="mt-3 text-[12px] text-gray-400" x-show="!marcados.length">Ningún archivo marcado.</p>
    </div>

    {{-- Modal: navegador de carpetas y archivos reales. Se teletransporta al
         <body> porque los paneles usan backdrop-filter, que vuelve al panel el
         contenedor de los elementos "fixed": sin esto el modal quedaba
         atrapado dentro del panel en vez de cubrir la ventana. --}}
    <template x-teleport="body">
    <div x-show="modalAbierto" x-cloak x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-indigo-950/40 p-4 backdrop-blur-sm" style="display:none;"
         @keydown.escape.window="modalAbierto = false">
        <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-[28px] border border-white/80 bg-white/95 shadow-[0_24px_60px_rgba(74,30,82,0.25)]" @click.outside="modalAbierto = false">
            <div class="h-[3px] bg-gradient-to-r from-dasavena-purple to-dasavena-gold"></div>
            <div class="flex items-start justify-between gap-3 border-b border-black/[0.05] px-6 py-4">
                <div class="min-w-0">
                    <h4 class="font-display text-base font-bold text-indigo-950" x-text="modo === 'carpeta' ? 'Explorar SharePoint' : 'Buscar archivos para Obsoletos'"></h4>
                    <p class="mt-0.5 truncate font-mono text-[11.5px] text-gray-400" x-text="breadcrumbTexto()"></p>
                </div>
                <button type="button" @click="modalAbierto = false" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Cerrar">
                    <i class="ti ti-x text-[16px]"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-3">
                <button type="button" x-show="breadcrumb.length > 0" @click="subirNivel()" :disabled="cargando"
                        class="mb-1 flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-[13px] font-semibold text-dasavena-purple hover:bg-dasavena-purple/5">
                    <i class="ti ti-arrow-up-left text-[15px]"></i> Subir un nivel
                </button>

                <template x-if="cargando">
                    <div class="flex items-center gap-2 px-3 py-6 text-[13px] text-gray-400">
                        <span class="h-4 w-4 animate-spin rounded-full border-2 border-dasavena-purple/20 border-t-dasavena-purple"></span> Cargando…
                    </div>
                </template>
                <template x-if="!cargando && error">
                    <p class="px-3 py-4 text-[13px] font-semibold text-rose-600">No se pudo leer esta carpeta de SharePoint. Intenta de nuevo.</p>
                </template>
                <template x-if="!cargando && !error && carpetas.length === 0 && archivos.length === 0">
                    <p class="px-3 py-6 text-center text-[13px] text-gray-400">Esta carpeta está vacía.</p>
                </template>

                <div x-show="!cargando && carpetas.length" class="space-y-0.5">
                    <template x-for="carpeta in carpetas" :key="carpeta.id">
                        <button type="button" @click="entrar(carpeta)" :disabled="cargando"
                                class="flex w-full items-center justify-between gap-2 rounded-xl px-3 py-2 text-left text-[13px] text-gray-700 hover:bg-slate-50 disabled:opacity-50">
                            <span class="flex min-w-0 items-center gap-2.5">
                                <i class="ti ti-folder-filled text-[17px] text-dasavena-gold"></i>
                                <span class="truncate" x-text="carpeta.name"></span>
                            </span>
                            <i class="ti ti-chevron-right text-[14px] text-gray-300" x-show="carpeta.has_children"></i>
                        </button>
                    </template>
                </div>

                <div x-show="!cargando && archivos.length" class="mt-3">
                    <p class="mb-1 px-3 text-[10.5px] font-bold uppercase tracking-widest text-gray-400">
                        Documentos (<span x-text="archivos.length"></span>)
                    </p>
                    <div class="space-y-0.5">
                        <template x-for="archivo in archivos" :key="archivo.id">
                            <div class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-50" :class="estaMarcado(archivo.id) && 'bg-dasavena-gold/10 hover:bg-dasavena-gold/15'">
                                <span x-text="iconoArchivo(archivo.name)"></span>
                                <a :href="archivo.web_url" target="_blank" rel="noopener" class="min-w-0 flex-1" :title="'Abrir ' + archivo.name + ' en SharePoint'">
                                    <span class="block truncate text-[13px] text-gray-700 hover:text-dasavena-purple hover:underline" x-text="archivo.name"></span>
                                    <span class="block text-[11px] text-gray-400" x-text="detalleArchivo(archivo)"></span>
                                </a>
                                <template x-if="archivo.id === vigenteItemId">
                                    <span class="shrink-0 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10.5px] font-semibold text-emerald-700">Vigente · se mueve solo</span>
                                </template>
                                <template x-if="archivo.id !== vigenteItemId">
                                    <label class="flex shrink-0 cursor-pointer items-center gap-1.5 rounded-lg px-2 py-1 text-[11.5px] font-semibold"
                                           :class="estaMarcado(archivo.id) ? 'text-dasavena-gold-dark' : 'text-gray-400 hover:text-gray-600'">
                                        <input type="checkbox" class="c-check c-check-gold" :checked="estaMarcado(archivo.id)" @change="alternar(archivo)">
                                        A Obsoletos
                                    </label>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-black/[0.05] bg-slate-50/60 px-6 py-4">
                <span class="text-[12px] text-gray-500">
                    <span class="font-semibold text-dasavena-gold-dark" x-text="marcados.length"></span> archivo(s) para Obsoletos
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="modalAbierto = false" class="rounded-xl px-4 py-2 text-[13px] font-semibold text-gray-500 hover:bg-gray-100">
                        <span x-text="modo === 'carpeta' ? 'Cancelar' : 'Listo'"></span>
                    </button>
                    <button type="button" x-show="modo === 'carpeta'" @click="elegirCarpetaActual()" :disabled="cargando" class="c-btn-primary">
                        <i class="ti ti-check text-[15px]"></i> Usar esta carpeta
                    </button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>
