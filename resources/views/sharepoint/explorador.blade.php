<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-extrabold text-[#6A2C75] tracking-tight">Explorador de SharePoint</h2>
            <p class="text-sm text-gray-500 mt-1 font-medium">Solo lectura — navega la estructura real de documentos por departamento.</p>
        </div>
    </x-slot>

    <div class="py-10 bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen"
        x-data="sharepointExplorador(
            @json($departamentosVigente, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            @json($departamentosObsoleto, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            @json($raizVigenteId),
            @json($raizObsoletoId)
        )"
        x-init="init()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Contexto fijo: dónde vive todo esto dentro de SharePoint --}}
            <div class="flex items-center gap-2 mb-6 text-xs font-bold text-gray-400 uppercase tracking-widest">
                <span>📚 {{ $driveName }}</span>
                <span class="text-gray-300">›</span>
                <span x-text="contexto === 'vigente' ? @js($rootFolderLabel) : @js($rootFolderLabel . ' › SGI › Sistema de Gestión Obsoleto')"></span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                {{-- SIDEBAR: departamentos --}}
                <aside class="lg:col-span-1">
                    <div class="bg-white/70 backdrop-blur-2xl rounded-[28px] border border-white/80 shadow-[0_8px_30px_rgba(0,0,0,0.06)] p-5 sticky top-6">
                        <div class="flex gap-2 mb-4">
                            <button type="button" @click="cambiarContexto('vigente')"
                                class="flex-1 px-3 py-2 rounded-xl text-xs font-bold transition"
                                :class="contexto === 'vigente' ? 'bg-[#6A2C75] text-white shadow-sm' : 'bg-[#6A2C75]/10 text-[#6A2C75] hover:bg-[#6A2C75]/20'">
                                📗 Vigentes
                            </button>
                            <button type="button" @click="cambiarContexto('obsoleto')"
                                class="flex-1 px-3 py-2 rounded-xl text-xs font-bold transition"
                                :class="contexto === 'obsoleto' ? 'bg-slate-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                                🗄️ Obsoletos
                            </button>
                        </div>

                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Departamentos</h3>
                            <span class="text-[10px] font-bold text-gray-300" x-text="departamentos.length"></span>
                        </div>

                        <input type="text" x-model="filtroDepartamento" placeholder="Buscar departamento…"
                            class="w-full mb-3 px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#6A2C75]/30 focus:border-[#6A2C75]/40">

                        <template x-if="departamentos.length === 0">
                            <p class="text-xs text-gray-400 py-4">No se encontró la carpeta raíz configurada.</p>
                        </template>

                        <template x-if="departamentos.length > 0 && departamentosFiltrados.length === 0">
                            <p class="text-xs text-gray-400 py-4">Sin resultados para "<span x-text="filtroDepartamento"></span>".</p>
                        </template>

                        <ul class="space-y-1 max-h-[65vh] overflow-y-auto pr-1">
                            <template x-for="depto in departamentosFiltrados" :key="depto.id">
                                <li>
                                    <button type="button" @click="abrirDepartamento(depto)"
                                        class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 rounded-lg text-sm transition"
                                        :class="activoId === depto.id ? 'bg-[#6A2C75]/10 text-[#6A2C75] font-bold' : 'text-gray-700 hover:bg-slate-50'">
                                        <span class="truncate">📁 <span x-text="depto.name"></span></span>
                                        <span class="text-gray-300 shrink-0 text-[10px]" x-text="expandidos[depto.id] ? '▾' : '▸'"></span>
                                    </button>

                                    <ul class="ml-5 mt-1 space-y-0.5 border-l border-gray-100 pl-2" x-show="expandidos[depto.id]" x-cloak>
                                        <template x-if="cargandoDepto === depto.id">
                                            <li class="text-xs text-gray-400 px-2 py-1">Cargando…</li>
                                        </template>
                                        <template x-if="cargandoDepto !== depto.id && (hijosDepto[depto.id] || []).length === 0">
                                            <li class="text-xs text-gray-400 px-2 py-1">Sin subcarpetas.</li>
                                        </template>
                                        <template x-for="carpeta in (hijosDepto[depto.id] || [])" :key="carpeta.id">
                                            <li>
                                                <button type="button" @click="navegarA(carpeta, [depto])"
                                                    class="w-full text-left px-2 py-1 rounded-lg text-xs transition"
                                                    :class="activoId === carpeta.id ? 'bg-[#6A2C75]/10 text-[#6A2C75] font-bold' : 'text-gray-500 hover:bg-slate-50'">
                                                    📂 <span x-text="carpeta.name"></span>
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                </li>
                            </template>
                        </ul>
                    </div>
                </aside>

                {{-- PANEL PRINCIPAL --}}
                <section class="lg:col-span-3">
                    <div class="bg-white/70 backdrop-blur-2xl rounded-[28px] border border-white/80 shadow-[0_8px_30px_rgba(0,0,0,0.06)] p-6 min-h-[60vh]">
                        {{-- Breadcrumb --}}
                        <div class="flex items-center flex-wrap gap-1 text-xs text-gray-500 mb-5 font-mono pb-4 border-b border-gray-100">
                            <button type="button" @click="irAlInicio()" class="hover:text-[#6A2C75] hover:underline font-bold">🏠 Inicio</button>
                            <template x-if="breadcrumb.length"><span class="text-gray-300">/</span></template>
                            <template x-for="(paso, idx) in breadcrumb" :key="idx">
                                <span class="flex items-center gap-1">
                                    <button type="button" @click="irABreadcrumb(idx)" class="hover:text-[#6A2C75] hover:underline" x-text="paso.name"></button>
                                    <span x-show="idx < breadcrumb.length - 1" class="text-gray-300">/</span>
                                </span>
                            </template>
                        </div>

                        <template x-if="cargandoPanel">
                            <p class="text-sm text-gray-400 py-10 text-center">Cargando…</p>
                        </template>

                        <template x-if="!cargandoPanel && breadcrumb.length === 0">
                            <div class="text-center py-16">
                                <p class="text-4xl mb-3">🗂️</p>
                                <p class="text-sm text-gray-400">Selecciona un departamento del panel izquierdo para empezar a navegar.</p>
                            </div>
                        </template>

                        <template x-if="!cargandoPanel && breadcrumb.length > 0 && items.length === 0">
                            <p class="text-sm text-gray-400 py-10 text-center">Esta carpeta está vacía.</p>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3" x-show="!cargandoPanel">
                            <template x-for="item in items" :key="item.id">
                                <div class="border border-gray-200 rounded-xl p-3 flex items-center justify-between gap-2 hover:border-[#6A2C75]/30 hover:shadow-sm transition">
                                    <template x-if="item.type === 'folder'">
                                        <button type="button" @click="navegarA(item)" class="flex-1 text-left flex items-center gap-2 min-w-0">
                                            <span class="text-lg shrink-0">📁</span>
                                            <span class="text-sm text-gray-700 truncate font-medium" x-text="item.name"></span>
                                        </button>
                                    </template>
                                    <template x-if="item.type === 'file'">
                                        <a :href="item.web_url" target="_blank" class="flex-1 flex items-center gap-2 min-w-0 group">
                                            <span class="text-lg shrink-0" x-text="iconoArchivo(item.extension)"></span>
                                            <span class="text-sm text-gray-700 truncate group-hover:text-[#6A2C75] group-hover:underline" x-text="item.name"></span>
                                        </a>
                                    </template>
                                    <span class="text-[10px] text-gray-300 shrink-0" x-text="formatearTamano(item.size)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        function sharepointExplorador(departamentosVigente, departamentosObsoleto, raizVigenteId, raizObsoletoId) {
            return {
                contexto: 'vigente',
                raices: { vigente: raizVigenteId, obsoleto: raizObsoletoId },
                todosDepartamentos: { vigente: departamentosVigente || [], obsoleto: departamentosObsoleto || [] },
                departamentos: [],
                filtroDepartamento: '',
                expandidos: {},
                hijosDepto: {},
                cargandoDepto: null,
                cargandoPanel: false,
                activoId: null,
                breadcrumb: [],
                items: [],
                urlBase: '{{ route('sharepoint.explorador.contenido') }}',

                get departamentosFiltrados() {
                    const filtro = this.filtroDepartamento.trim().toLowerCase();
                    if (!filtro) return this.departamentos;
                    return this.departamentos.filter(d => d.name.toLowerCase().includes(filtro));
                },

                init() {
                    this.departamentos = this.todosDepartamentos[this.contexto];
                },

                cambiarContexto(contexto) {
                    if (this.contexto === contexto) return;
                    this.contexto = contexto;
                    this.departamentos = this.todosDepartamentos[contexto];
                    this.filtroDepartamento = '';
                    this.expandidos = {};
                    this.hijosDepto = {};
                    this.activoId = null;
                    this.breadcrumb = [];
                    this.items = [];
                },

                irAlInicio() {
                    this.activoId = null;
                    this.breadcrumb = [];
                    this.items = [];
                },

                cargar(folderId) {
                    return fetch(`${this.urlBase}?folder_id=${encodeURIComponent(folderId)}`, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => data.items || [])
                        .catch(() => []);
                },

                abrirDepartamento(depto) {
                    if (!this.expandidos[depto.id] && !this.hijosDepto[depto.id]) {
                        this.cargandoDepto = depto.id;
                        this.cargar(depto.id).then(items => {
                            this.hijosDepto[depto.id] = items.filter(i => i.type === 'folder');
                            this.cargandoDepto = null;
                        });
                    }

                    this.expandidos[depto.id] = !this.expandidos[depto.id];
                    // prefijo=[] fuerza un breadcrumb nuevo (no acumula el
                    // departamento anterior si se cambia de uno a otro).
                    this.navegarA(depto, []);
                },

                navegarA(item, prefijo = null) {
                    this.activoId = item.id;
                    this.cargandoPanel = true;

                    if (prefijo) {
                        this.breadcrumb = [...prefijo.map(p => ({ id: p.id, name: p.name })), { id: item.id, name: item.name }];
                    } else if (this.breadcrumb.some(p => p.id === item.id)) {
                        // Ya estaba en el breadcrumb (p. ej. se reabrió desde el sidebar): no lo duplica.
                        this.breadcrumb = this.breadcrumb.slice(0, this.breadcrumb.findIndex(p => p.id === item.id) + 1);
                    } else {
                        this.breadcrumb = [...this.breadcrumb, { id: item.id, name: item.name }];
                    }

                    this.cargar(item.id).then(items => {
                        this.items = items;
                        this.cargandoPanel = false;
                    });
                },

                irABreadcrumb(idx) {
                    const paso = this.breadcrumb[idx];
                    this.breadcrumb = this.breadcrumb.slice(0, idx + 1);
                    this.activoId = paso.id;
                    this.cargandoPanel = true;
                    this.cargar(paso.id).then(items => {
                        this.items = items;
                        this.cargandoPanel = false;
                    });
                },

                iconoArchivo(extension) {
                    const iconos = {
                        pdf: '📕',
                        doc: '📘', docx: '📘',
                        xls: '📗', xlsx: '📗',
                        ppt: '📙', pptx: '📙',
                        jpg: '🖼️', jpeg: '🖼️', png: '🖼️', gif: '🖼️',
                        zip: '🗜️', rar: '🗜️',
                    };
                    return iconos[extension] || '📄';
                },

                formatearTamano(bytes) {
                    if (!bytes) return '';
                    const kb = bytes / 1024;
                    if (kb < 1024) return Math.round(kb) + ' KB';
                    return (kb / 1024).toFixed(1) + ' MB';
                },
            };
        }
    </script>
</x-app-layout>
