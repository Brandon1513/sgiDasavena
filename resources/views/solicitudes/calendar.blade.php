<x-app-layout>
<style>
    @import url('https://fonts.cdnfonts.com/css/century-gothic');
    * { font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif; }

    @keyframes fadeUp   { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
    @keyframes fadeIn   { from{opacity:0;transform:translateY(8px)}  to{opacity:1;transform:translateY(0)} }
    @keyframes pulse-dot{ 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.5)} }

    .anim-fade { animation: fadeUp .5s ease both; }
    .animate-fade-in { animation: fadeIn .4s ease both; }

    /* Línea dorada top card */
    .card-gold-top { position:relative; }
    .card-gold-top::before {
        content:''; position:absolute; top:0; left:0; right:0;
        height:3px; border-radius:16px 16px 0 0;
        background:linear-gradient(90deg,#6A2C75,#D4A018,#6A2C75);
    }

    /* Select custom arrow */
    .sel-custom {
        appearance:none;
        background-image:url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236A2C75%22 stroke-width=%222%22%3e%3cpolyline points=%226 9 12 15 18 9%3e%3c/polyline%3e%3c/svg%3e');
        background-repeat:no-repeat;
        background-position:right .75rem center;
        background-size:1.1em;
        padding-right:2.5rem;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width:4px; height:4px; }
    ::-webkit-scrollbar-track { background:transparent; }
    ::-webkit-scrollbar-thumb { background:rgba(106,44,117,.2); border-radius:4px; }
    ::-webkit-scrollbar-thumb:hover { background:rgba(106,44,117,.4); }

    /* Tab activo */
    .tab-active {
        background:linear-gradient(135deg,#6A2C75,#8e3d9e) !important;
        color:#fff !important;
        box-shadow:0 4px 14px rgba(106,44,117,.3);
    }
</style>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 anim-fade">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#6A2C75] to-[#D4A018]"></div>
                    <h2 class="text-3xl font-bold text-[#2d1033] tracking-tight">Calendario de Vencimientos</h2>
                </div>
                <p class="text-sm text-[#6A2C75]/60 pl-4">Gestión de versiones y revisiones con monitoreo preventivo de fechas.</p>
            </div>

            <div class="flex items-center gap-3 px-5 py-3 bg-white border border-[#6A2C75]/15 rounded-2xl shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-[#D4A018]" style="animation:pulse-dot 1.6s ease-in-out infinite"></span>
                <span class="text-sm font-bold text-[#4a2a55] uppercase tracking-widest" id="liveClock">Cargando...</span>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#faf7fb] min-h-screen">
        <div class="max-w-[1650px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
   {{--Filtro por estaodos --}} 
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 anim-fade">
                <div class="card-gold-top relative bg-white border border-[#6A2C75]/10 rounded-2xl p-6 shadow-sm overflow-hidden hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-5 -top-5 w-24 h-24 rounded-full bg-[#6A2C75]/05 pointer-events-none"></div>
                    <p class="text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest mb-3">Vencidos</p>
                    <p class="text-5xl font-black text-[#6A2C75] tracking-tight" id="statVencidos">0</p>
                    <div class="flex items-center gap-2 mt-3">
                        <svg class="w-3.5 h-3.5 text-[#6A2C75]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <p class="text-[10px] text-[#6A2C75]/50 font-semibold">Fecha ya superada</p>
                    </div>
                </div>

                <div class="relative bg-white border border-red-200/70 rounded-2xl p-6 shadow-sm overflow-hidden hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-5 -top-5 w-24 h-24 rounded-full bg-red-50 pointer-events-none"></div>
                    <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-2xl bg-gradient-to-r from-red-400 to-red-600"></div>
                    <p class="text-[10px] font-bold text-red-600/70 uppercase tracking-widest mb-3">Estado Crítico</p>
                    <p class="text-5xl font-black text-red-600 tracking-tight" id="statCriticoBig">0</p>
                    <div class="flex items-center gap-2 mt-3">
                        <svg class="w-3.5 h-3.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                        <p class="text-[10px] text-red-500/70 font-semibold">Menos de 30 días</p>
                    </div>
                </div>

                <div class="relative bg-white border border-amber-200/70 rounded-2xl p-6 shadow-sm overflow-hidden hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="absolute -right-5 -top-5 w-24 h-24 rounded-full bg-amber-50 pointer-events-none"></div>
                    <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-2xl bg-gradient-to-r from-amber-400 to-amber-500"></div>
                    <p class="text-[10px] font-bold text-amber-600/70 uppercase tracking-widest mb-3">Alerta Preventiva</p>
                    <p class="text-5xl font-black text-amber-600 tracking-tight" id="statAlertaBig">0</p>
                    <div class="flex items-center gap-2 mt-3">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-[10px] text-amber-600/70 font-semibold">31 a 60 días</p>
                    </div>
                </div>

                <div class="relative bg-white border border-emerald-200/70 rounded-2xl p-6 shadow-sm overflow-hidden hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    
                    <div class="absolute -right-5 -top-5 w-24 h-24 rounded-full bg-emerald-50 pointer-events-none"></div>
                    <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-2xl bg-gradient-to-r from-emerald-400 to-emerald-600">
                        </div>
                    <p class="text-[10px] font-bold text-emerald-600/70 uppercase tracking-widest mb-3">En Regla / Óptimo</p>
                    <p class="text-5xl font-black text-emerald-600 tracking-tight" id="statReglaBig">0</p>
                    <div class="flex items-center gap-2 mt-3">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-[10px] text-emerald-600/70 font-semibold">Mayor a 60 días</p>
                    </div>
                </div>
            </div>

            <div class="card-gold-top relative bg-white rounded-2xl shadow-sm border border-[#6A2C75]/10 overflow-hidden anim-fade" style="animation-delay:.1s">
                <div class="p-6 border-b border-[#6A2C75]/08 bg-[#faf7fb]/60">
                    <div class="flex flex-col xl:flex-row xl:items-end justify-between gap-5">
                        <div class="flex items-center gap-4 flex-wrap">
                            <div class="flex items-center bg-[#faf7fb] border border-[#6A2C75]/15 rounded-xl p-1 gap-1">
                                <button id="prevMonth" class="p-2.5 rounded-lg hover:bg-[#6A2C75]/08 text-[#6A2C75] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <button id="nextMonth" class="p-2.5 rounded-lg hover:bg-[#6A2C75]/08 text-[#6A2C75] transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                            <h3 id="monthTitle" class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-[#6A2C75] to-[#D4A018] min-w-[240px]"></h3>
                            <div class="flex bg-[#faf7fb] border border-[#6A2C75]/15 rounded-xl p-1 gap-1">
                                <button id="tabMes"   class="tab-active px-5 py-2 text-xs font-bold rounded-lg transition-all">Mes</button>
                                <button id="tabLista" class="px-5 py-2 text-xs font-bold rounded-lg text-[#6A2C75]/60 hover:text-[#6A2C75] transition-all">Lista</button>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-end gap-3">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Buscar</label>
                                <input id="q" type="text" placeholder="Código o nombre..."
                                    class="w-52 px-3.5 py-2 border border-[#6A2C75]/20 rounded-lg text-sm text-[#2d1033] placeholder-[#6A2C75]/30 bg-white focus:outline-none focus:ring-2 focus:ring-[#6A2C75]/25 focus:border-[#6A2C75]/40 transition-all">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Área</label>
                                <select id="area" class="sel-custom px-3.5 py-2 border border-[#6A2C75]/20 rounded-lg text-sm text-[#2d1033] bg-white focus:outline-none focus:ring-2 focus:ring-[#6A2C75]/25 transition-all cursor-pointer">
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
                                <label class="block text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Estado</label>
                                <select id="estado" class="sel-custom px-3.5 py-2 border border-[#6A2C75]/20 rounded-lg text-sm text-[#2d1033] bg-white focus:outline-none focus:ring-2 focus:ring-[#6A2C75]/25 transition-all cursor-pointer">
                                    <option value="all">Todos</option>
                                    <option value="vencidos">Vencidos</option>
                                    <option value="por_vencer">Por vencer (0–30 días)</option>
                                    <option value="alerta">Alerta (31–60 días)</option>
                                    <option value="en_regla">En regla (&gt;60 días)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Tipo</label>
                                <select id="tipo" class="sel-custom px-3.5 py-2 border border-[#6A2C75]/20 rounded-lg text-sm text-[#2d1033] bg-white focus:outline-none focus:ring-2 focus:ring-[#6A2C75]/25 transition-all cursor-pointer">
                                    <option value="both">Versión + Revisión</option>
                                    <option value="version">Solo Versión</option>
                                    <option value="revision">Solo Revisión</option>
                                </select>
                            </div>
                            <label class="flex items-center gap-2 px-4 py-2 bg-white border border-[#6A2C75]/20 rounded-lg cursor-pointer hover:bg-[#faf7fb] transition-all self-end">
                                <input id="historicos" type="checkbox" class="w-4 h-4 rounded border-[#6A2C75]/30 text-[#6A2C75] focus:ring-[#6A2C75]/30">
                                <span class="text-[10px] font-bold text-[#4a2a55] uppercase tracking-widest">Históricos</span>
                            </label>
                            <button id="btnFiltrar"
                                class="self-end px-6 py-2 bg-gradient-to-r from-[#D4A018] to-[#f0c84a] text-[#2d1033] rounded-lg text-xs font-bold shadow-sm hover:shadow-md hover:-translate-y-0.5 active:scale-95 transition-all duration-200">
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <div id="viewMes" class="animate-fade-in">
                    <div class="grid grid-cols-7 border-b border-[#6A2C75]/08 bg-[#faf7fb]/50">
                        @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                        <div class="text-center py-4 text-[10px] font-bold text-[#6A2C75]/50 uppercase tracking-widest">{{ $d }}</div>
                        @endforeach
                    </div>
                    <div id="calendarGrid" class="grid grid-cols-7"></div>
                </div>

                <div id="viewLista" class="hidden animate-fade-in">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-[#faf7fb] border-b border-[#6A2C75]/08">
                                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Vencimiento</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Tipo</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Código</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Nombre</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Área / Ubicación </th>
                                    <th class="px-6 py-4 text-center text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="listBody" class="divide-y divide-[#6A2C75]/05"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const DATA_URL = "{{ route('solicitudes.calendar.data') }}";
        let current = new Date();
        current.setDate(1);

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

                    // CORRECCIÓN: Asegurar que las stats se actualicen siempre, incluso si vienen nulas
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
            const start = new Date(current);
            const firstDay = (start.getDay() === 0) ? 7 : start.getDay(); // Ajuste para que Lunes sea 1
            start.setDate(start.getDate() - (firstDay - 1));

            for (let i = 0; i < 42; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                const key    = ymdLocal(d);
                const events = (data.eventsByDate && data.eventsByDate[key]) ? data.eventsByDate[key] : [];
                const isCur  = d.getMonth() === current.getMonth();

                let html = `
                    <div class="min-h-[160px] p-3 border border-[#6A2C75]/06 transition-all hover:bg-[#faf7fb] ${!isCur ? 'opacity-30 bg-gray-50/50' : 'bg-white/60'}">
                        <span class="text-xs font-bold ${isCur ? 'text-[#2d1033]' : 'text-gray-400'}">${d.getDate()}</span>
                        <div class="mt-2 space-y-1.5">
                `;

                events.slice(0,3).forEach(e => {
                    const color = e.severity === 'danger'
                        ? 'border-[#6A2C75] bg-[#6A2C75]/06 text-[#6A2C75]'
                        : e.severity === 'warning'
                        ? 'border-amber-500 bg-amber-50 text-amber-700'
                        : 'border-emerald-500 bg-emerald-50 text-emerald-700';

                    html += `
                        <div class="border-l-[3px] ${color} px-2 py-1 rounded-r-lg text-[10px] leading-tight cursor-help hover:scale-[1.02] transition-transform">
                            <div class="font-bold truncate">${e.codigo || 'S/C'}</div>
                            <div class="truncate opacity-75">${e.nombre || ''}</div>
                        </div>
                    `;
                });

                if (events.length > 3) {
                    html += `<div class="text-[9px] font-bold text-[#D4A018] ml-1">+ ${events.length - 3} más</div>`;
                }

                html += `</div></div>`;
                grid.insertAdjacentHTML('beforeend', html);
            }
        }

        function renderList(list) {
            const body = document.getElementById('listBody');
            body.innerHTML = '';

            if (!list.length) {
                body.innerHTML = `<tr><td colspan="6" class="py-16 text-center">... Sin registros ...</td></tr>`;
                return;
            }

            list.forEach(row => {
                // CORRECCIÓN: Forzar conversión a número para evitar fallos en la lógica de colores
                const days = parseInt(row.days_left);
                let badge, dot;

                if (days < 0) {
                    badge = 'bg-[#6A2C75]/10 text-[#6A2C75] border-[#6A2C75]/25';
                    dot   = 'bg-[#6A2C75]';
                } else if (days <= 30) {
                    badge = 'bg-red-100 text-red-700 border-red-200';
                    dot   = 'bg-red-500';
                } else if (days <= 60) {
                    badge = 'bg-amber-100 text-amber-700 border-amber-200';
                    dot   = 'bg-amber-500';
                } else {
                    badge = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                    dot   = 'bg-emerald-500';
                }

                body.insertAdjacentHTML('beforeend', `
                    <tr class="hover:bg-[#6A2C75]/03 transition-colors">
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold border ${badge}">
                                <span class="w-1.5 h-1.5 rounded-full ${dot} flex-shrink-0"></span>
                                ${days < 0 ? 'Vencido' : days + ' días'}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-[10px] font-bold text-[#6A2C75]/60 uppercase tracking-widest">${row.vencimiento_tipo || ''}</td>
                        <td class="px-6 py-4 text-xs font-bold text-[#2d1033]">${row.codigo_documento || ''}</td>
                        <td class="px-6 py-4">
                            <p class="text-xs font-semibold text-[#2d1033]">${row.nombre_documento || ''}</p>
                            <p class="text-[10px] text-[#6A2C75]/40 uppercase mt-0.5">${row.tipo_documento || ''}</p>
                        </td>
                        <td class="px-6 py-4 text-xs text-[#5a4a65]">${row.area || 'N/A'}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="${row.url_documento || '#'}" target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[#6A2C75]/08 text-[#6A2C75] hover:bg-[#6A2C75]/15 text-[10px] font-bold transition-colors border border-[#6A2C75]/15">
                                Ver
                            </a>
                        </td>
                    </tr>
                `);
            });
        }

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
    </script>
</x-app-layout>