<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-extrabold text-[#6A2C75] tracking-tight">Documentos</h2>
            <p class="text-sm text-gray-500 mt-1 font-medium">Consulta rápida de los documentos reales por departamento — solo lectura.</p>
        </div>
    </x-slot>

    <div class="py-10 bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen"
        x-data="sharepointExplorador(
            @json($departamentos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
            @json($raizId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)
        )"
        x-init="init()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                {{-- SIDEBAR: departamentos --}}
                <aside class="lg:col-span-1">
                    <div class="bg-white/70 backdrop-blur-2xl rounded-[28px] border border-white/80 shadow-[0_8px_30px_rgba(0,0,0,0.06)] p-5 sticky top-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Departamentos</h3>
                            <span class="text-[10px] font-bold text-gray-300" x-text="departamentos.length"></span>
                        </div>

                        <input type="text" x-model="filtroDepartamento" placeholder="Buscar departamento…"
                            class="w-full mb-3 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#6A2C75]/30 focus:border-[#6A2C75]/40">

                        <template x-if="departamentos.length === 0">
                            <p class="text-xs text-gray-400 py-4">No se encontró la carpeta raíz configurada.</p>
                        </template>

                        <template x-if="departamentos.length > 0 && departamentosFiltrados.length === 0">
                            <p class="text-xs text-gray-400 py-4">Sin resultados para "<span x-text="filtroDepartamento"></span>".</p>
                        </template>

                        <ul class="space-y-1 max-h-[70vh] overflow-y-auto pr-1">
                            <template x-for="depto in departamentosFiltrados" :key="depto.id">
                                <li>
                                    <button type="button" @click="navegarA(depto, [])"
                                        class="w-full flex items-center gap-2 text-left px-3 py-2.5 rounded-xl text-sm transition"
                                        :class="activoId === depto.id || breadcrumb[0]?.id === depto.id ? 'bg-[#6A2C75] text-white font-bold shadow-sm' : 'text-gray-700 hover:bg-slate-50'">
                                        <span class="shrink-0">📁</span>
                                        <span class="truncate" x-text="depto.name"></span>
                                    </button>
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
                            <button type="button" @click="irAlInicio()" class="hover:text-[#6A2C75] hover:underline font-bold">🏠 Documentos</button>
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
                                <p class="text-sm text-gray-400">Selecciona un departamento del panel izquierdo para ver sus documentos.</p>
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
        function sharepointExplorador(departamentos, raizId) {
            return {
                departamentos: departamentos || [],
                raizId: raizId,
                filtroDepartamento: '',
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

                init() {},

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

                // Navega a "item" y, si esa carpeta solo contiene OTRA carpeta
                // (muy común en esta estructura: Área/Tipo/Código, donde los
                // niveles intermedios no tienen nada más), sigue entrando sola
                // hasta llegar a donde realmente hay archivos o varias
                // opciones — así se ve "rápido" sin tener que ir clic por clic.
                async navegarA(item, prefijoNuevo = null) {
                    this.activoId = item.id;
                    this.cargandoPanel = true;

                    let ruta = prefijoNuevo
                        ? [...prefijoNuevo.map(p => ({ id: p.id, name: p.name })), { id: item.id, name: item.name }]
                        : this.extenderBreadcrumb(item);

                    let folderId = item.id;
                    let items = await this.cargar(folderId);

                    while (items.length === 1 && items[0].type === 'folder') {
                        const unica = items[0];
                        ruta = [...ruta, { id: unica.id, name: unica.name }];
                        folderId = unica.id;
                        items = await this.cargar(folderId);
                    }

                    this.breadcrumb = ruta;
                    this.activoId = folderId;
                    this.items = items;
                    this.cargandoPanel = false;
                },

                extenderBreadcrumb(item) {
                    const yaExiste = this.breadcrumb.findIndex(p => p.id === item.id);
                    const base = yaExiste !== -1 ? this.breadcrumb.slice(0, yaExiste) : [...this.breadcrumb];
                    return [...base, { id: item.id, name: item.name }];
                },

                irABreadcrumb(idx) {
                    const paso = this.breadcrumb[idx];
                    this.navegarA(paso, this.breadcrumb.slice(0, idx));
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
