<!-- sp-explorador-build: carpetas-y-archivos-v1 -->
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-extrabold text-[#6A2C75] tracking-tight">Documentos</h2>
            <p class="text-sm text-gray-500 mt-1 font-medium">Carpetas reales de SharePoint — solo lectura.</p>
        </div>
    </x-slot>

    <div class="py-10 bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen"
        data-raiz-id="{{ $raizId }}"
        x-data="sharepointExplorador()">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white/70 backdrop-blur-2xl rounded-[28px] border border-white/80 shadow-[0_8px_30px_rgba(0,0,0,0.06)] p-6 min-h-[50vh]">

                {{-- Breadcrumb --}}
                <div class="flex items-center flex-wrap gap-1 text-xs text-gray-500 mb-5 font-mono pb-4 border-b border-gray-100">
                    <button type="button" @click="irAlInicio()" class="hover:text-[#6A2C75] hover:underline font-bold">🏠 Documentos</button>
                    <template x-for="(paso, idx) in breadcrumb" :key="idx">
                        <span class="flex items-center gap-1">
                            <span class="text-gray-300">/</span>
                            <button type="button" @click="irABreadcrumb(idx)" class="hover:text-[#6A2C75] hover:underline" x-text="paso.name"></button>
                        </span>
                    </template>
                </div>

                <template x-if="cargando">
                    <p class="text-sm text-gray-400 py-10 text-center">Cargando…</p>
                </template>

                <template x-if="!cargando && !raizId">
                    <p class="text-sm text-gray-400 py-10 text-center">No se encontró la carpeta raíz configurada.</p>
                </template>

                <template x-if="!cargando && raizId && items.length === 0">
                    <p class="text-sm text-gray-400 py-10 text-center">Esta carpeta está vacía.</p>
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="!cargando">
                    <template x-for="item in items" :key="item.id">
                        <template x-if="item.type === 'folder'">
                            <button type="button" @click="entrar(item)"
                                class="border border-gray-200 rounded-xl p-4 flex items-center gap-3 text-left hover:border-[#6A2C75]/30 hover:shadow-sm transition">
                                <span class="text-2xl shrink-0">📁</span>
                                <span class="text-sm text-gray-700 font-medium truncate" x-text="item.name"></span>
                            </button>
                        </template>
                        <template x-if="item.type === 'file'">
                            <a :href="item.web_url" target="_blank"
                                class="border border-gray-200 rounded-xl p-4 flex items-center gap-3 hover:border-[#6A2C75]/30 hover:shadow-sm transition group">
                                <span class="text-2xl shrink-0" x-text="iconoArchivo(item.name)"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm text-gray-700 font-medium truncate group-hover:text-[#6A2C75] group-hover:underline" x-text="item.name"></span>
                                    <span class="block text-xs text-gray-400" x-text="formatearTamano(item.size)"></span>
                                </span>
                            </a>
                        </template>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sharepointExplorador() {
            return {
                raizId: null,
                cargando: false,
                breadcrumb: [],
                items: [],
                urlBase: '{{ route('sharepoint.explorador.contenido') }}',

                init() {
                    this.raizId = this.$el.dataset.raizId || null;

                    if (this.raizId) {
                        this.cargarCarpeta(this.raizId);
                    }
                },

                cargarCarpeta(folderId) {
                    this.cargando = true;
                    fetch(`${this.urlBase}?folder_id=${encodeURIComponent(folderId)}`, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => { this.items = data.items || []; this.cargando = false; })
                        .catch(() => { this.items = []; this.cargando = false; });
                },

                entrar(carpeta) {
                    if (this.cargando) return;

                    this.breadcrumb.push({ id: carpeta.id, name: carpeta.name });
                    this.cargarCarpeta(carpeta.id);
                },

                irAlInicio() {
                    if (this.cargando) return;

                    this.breadcrumb = [];
                    this.cargarCarpeta(this.raizId);
                },

                irABreadcrumb(idx) {
                    if (this.cargando) return;

                    const destino = this.breadcrumb[idx];
                    this.breadcrumb = this.breadcrumb.slice(0, idx + 1);
                    this.cargarCarpeta(destino.id);
                },

                iconoArchivo(nombre) {
                    const ext = (nombre.split('.').pop() || '').toLowerCase();
                    const iconos = {
                        pdf: '📕',
                        doc: '📘', docx: '📘',
                        xls: '📗', xlsx: '📗',
                        ppt: '📙', pptx: '📙',
                        jpg: '🖼️', jpeg: '🖼️', png: '🖼️', gif: '🖼️',
                        zip: '🗜️', rar: '🗜️',
                    };
                    return iconos[ext] || '📄';
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
