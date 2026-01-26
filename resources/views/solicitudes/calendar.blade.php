<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                Calendario de Vencimientos
            </h2>
            <p class="mt-2 text-base text-gray-500">
                Gestiona versiones y revisiones por vencer con vista de calendario y lista detallada.
            </p>
        </div>
    </x-slot>

    <div class="py-12 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50 min-h-screen">

        {{-- STATS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="group relative bg-white backdrop-blur-xl border border-red-200/50 p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <p class="text-xs font-bold text-red-600 uppercase tracking-widest">Crítico</p>
                <p class="text-5xl font-black text-red-600 mt-3" id="statCriticoBig">0</p>
                <p class="text-xs text-red-500/70 mt-2 font-medium">≤ 30 días (incluye vencidos)</p>
            </div>

            <div class="group relative bg-white backdrop-blur-xl border border-amber-200/50 p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <p class="text-xs font-bold text-amber-600 uppercase tracking-widest">Alerta</p>
                <p class="text-5xl font-black text-amber-600 mt-3" id="statAlertaBig">0</p>
                <p class="text-xs text-amber-500/70 mt-2 font-medium">31–60 días</p>
            </div>

            <div class="group relative bg-white backdrop-blur-xl border border-cyan-200/50 p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <p class="text-xs font-bold text-cyan-600 uppercase tracking-widest">En Regla</p>
                <p class="text-5xl font-black text-cyan-600 mt-3" id="statReglaBig">0</p>
                <p class="text-xs text-cyan-500/70 mt-2 font-medium">&gt; 60 días</p>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-200/50 overflow-hidden">

            {{-- TOOLBAR --}}
            <div class="p-8 border-b border-gray-200/50 bg-gradient-to-r from-white via-blue-50/30 to-white">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                    <div class="flex items-center gap-4">
                        <button id="prevMonth" class="p-2.5 border border-gray-300/50 rounded-xl hover:bg-gradient-to-br hover:from-blue-100 hover:to-cyan-100 transition-all text-gray-700 font-bold text-lg">◀</button>
                        <button id="nextMonth" class="p-2.5 border border-gray-300/50 rounded-xl hover:bg-gradient-to-br hover:from-blue-100 hover:to-cyan-100 transition-all text-gray-700 font-bold text-lg">▶</button>
                        <h3 id="monthTitle" class="ml-8 text-3xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent"></h3>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <input id="q"
                               class="border border-gray-300/50 rounded-xl px-4 py-3 text-sm w-full sm:w-72 focus:outline-none focus:ring-2 focus:ring-cyan-500 bg-white/50 backdrop-blur-sm"
                               placeholder="Buscar código o nombre...">

                        <select id="estado"
                                class="border border-gray-300/50 rounded-xl px-4 py-3 text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-cyan-500 bg-white/50 backdrop-blur-sm font-medium">
                            <option value="all">Todos</option>
                            <option value="vencidos">Vencidos</option>
                            <option value="por_vencer">Por vencer (0–30 días)</option>
                            <option value="alerta">Alerta (31–60 días)</option>
                            <option value="en_regla">En regla (&gt; 60 días)</option>
                        </select>

                        <select id="tipo"
                                class="border border-gray-300/50 rounded-xl px-4 py-3 text-sm w-full sm:w-56 focus:outline-none focus:ring-2 focus:ring-cyan-500 bg-white/50 backdrop-blur-sm font-medium">
                            <option value="both">Versión + Revisión</option>
                            <option value="version">Solo Versión</option>
                            <option value="revision">Solo Revisión</option>
                        </select>

                        <div class="flex bg-gradient-to-r from-gray-100 to-gray-50 rounded-xl p-1.5 w-full sm:w-auto border border-gray-200/50">
                            <button id="tabMes" class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold rounded-lg bg-white shadow-md text-blue-600">Mes</button>
                            <button id="tabLista" class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold rounded-lg text-gray-600 hover:text-gray-900">Lista</button>
                        </div>

                        <button id="btnFiltrar"
                                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-xl text-sm font-bold hover:shadow-xl transition-all w-full sm:w-auto">
                            Filtrar
                        </button>
                    </div>

                </div>
            </div>

            {{-- VISTA MES --}}
            <div id="viewMes">
                <div class="grid grid-cols-7 bg-gradient-to-r from-gray-50 via-blue-50 to-gray-50 border-b border-gray-200/50">
                    @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                        <div class="text-center py-4 text-xs font-bold text-gray-600 uppercase tracking-wider">{{ $d }}</div>
                    @endforeach
                </div>

                <div id="calendarGrid" class="grid grid-cols-7 bg-gradient-to-br from-white to-blue-50/30"></div>
            </div>

            {{-- VISTA LISTA --}}
            <div id="viewLista" class="hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gradient-to-r from-gray-50 via-blue-50 to-cyan-50 border-b border-gray-200/50">
                        <tr class="text-left text-gray-700 font-bold">
                            <th class="p-4">Días</th>
                            <th class="p-4">Vence</th>
                            <th class="p-4">Tipo</th>
                            <th class="p-4">Código</th>
                            <th class="p-4">Nombre</th>
                            <th class="p-4">Documento</th>
                            <th class="p-4">EL/PA</th>
                            <th class="p-4">Folio</th>
                            <th class="p-4">Ubicación</th>
                            <th class="p-4">Acción</th>
                          
                        </tr>
                        </thead>
                        <tbody id="listBody" class="divide-y divide-gray-100/50"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        const DATA_URL = "{{ route('solicitudes.calendar.data') }}";
        const SHOW_URL_BASE = "{{ url('/solicitudes') }}";

        let current = new Date();
        current.setDate(1);

        const monthTitle = document.getElementById('monthTitle');
        const grid = document.getElementById('calendarGrid');

        const statCriticoBig = document.getElementById('statCriticoBig');
        const statAlertaBig  = document.getElementById('statAlertaBig');
        const statReglaBig   = document.getElementById('statReglaBig');

        const qEl = document.getElementById('q');
        const estadoEl = document.getElementById('estado');
        const tipoEl = document.getElementById('tipo');

        const tabMes = document.getElementById('tabMes');
        const tabLista = document.getElementById('tabLista');
        const viewMes = document.getElementById('viewMes');
        const viewLista = document.getElementById('viewLista');
        const listBody = document.getElementById('listBody');

        const months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        function yyyymm(date){
            return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}`;
        }

        function startGrid(date){
            const d = new Date(date);
            const day = d.getDay() || 7;
            d.setDate(d.getDate() - (day - 1));
            return d;
        }

        function badge(days){
            if (days < 0)  return 'bg-gray-200/80 text-gray-700 font-bold border border-gray-300/50';
            if (days <= 30) return 'bg-red-100/80 text-red-700 font-bold border border-red-200/50';
            if (days <= 60) return 'bg-amber-100/80 text-amber-700 font-bold border border-amber-200/50';
            return 'bg-cyan-100/80 text-cyan-700 font-bold border border-cyan-200/50';
        }

        function loadMonth(){
            const params = new URLSearchParams({
                month: yyyymm(current),
                q: qEl.value || '',
                tipo: tipoEl.value || 'both',
                estado: estadoEl.value || 'all',
            });

            fetch(`${DATA_URL}?${params.toString()}`)
                .then(r => r.json())
                .then(data => {
                    monthTitle.textContent = `${months[current.getMonth()]} ${current.getFullYear()}`;

                    statCriticoBig.textContent = data.stats?.critico ?? 0;
                    statAlertaBig.textContent  = data.stats?.alerta ?? 0;
                    statReglaBig.textContent   = data.stats?.regla ?? 0;

                    // ---- CALENDARIO (solo el mes visible) ----
                    grid.innerHTML = '';
                    let start = startGrid(current);

                    for(let i=0;i<42;i++){
                        const d = new Date(start);
                        d.setDate(start.getDate()+i);

                        const key = d.toISOString().split('T')[0];
                        const events = (data.eventsByDate && data.eventsByDate[key]) ? data.eventsByDate[key] : [];

                        let html = `
                            <div class="bg-white/70 backdrop-blur-sm min-h-[160px] p-4 flex flex-col gap-2 border border-gray-200/50 hover:shadow-xl hover:bg-white transition-all duration-300 ${d.getMonth()!==current.getMonth()?'opacity-40 bg-gray-50/50':''}">
                                <span class="text-sm font-bold text-gray-700">${d.getDate()}</span>
                        `;

                        events.slice(0,2).forEach(e => {
                            const color = e.severity === 'danger'
                                ? 'border-l-4 border-red-400 bg-gradient-to-br from-red-50 to-red-100/50 shadow-sm'
                                : e.severity === 'warning'
                                    ? 'border-l-4 border-amber-400 bg-gradient-to-br from-amber-50 to-amber-100/50 shadow-sm'
                                    : 'border-l-4 border-cyan-400 bg-gradient-to-br from-cyan-50 to-cyan-100/50 shadow-sm';

                            const tipoTxt = e.tipo === 'version' ? 'Versión' : 'Revisión';

                            html += `
                                <div class="${color} p-3 rounded-lg text-xs hover:shadow-lg transition-all duration-300">
                                    <div class="flex justify-between gap-2 items-center">
                                        <strong class="text-gray-900 font-bold">${e.codigo ?? ''}</strong>
                                        <span class="text-[10px] text-gray-600 bg-white/80 backdrop-blur-sm px-2 py-1 rounded-md font-semibold">${tipoTxt}</span>
                                    </div>
                                    <div class="text-gray-700 truncate text-[11px] mt-1 font-medium">${e.nombre ?? ''}</div>
                                    <div class="font-bold text-gray-800 mt-2">⏱ ${e.days_left} días</div>
                                </div>
                            `;
                        });

                        if (events.length > 2) {
                            html += `<div class="text-xs text-cyan-600 font-bold px-2">+ ${events.length-2} más</div>`;
                        }

                        html += `</div>`;
                        grid.insertAdjacentHTML('beforeend', html);
                    }

                    // ---- LISTA (aquí sí salen TODOS según el filtro) ----
                    listBody.innerHTML = '';
                    (data.list || []).forEach(row => {
                        const tipoTxt = row.vencimiento_tipo === 'version' ? 'Versión' : 'Revisión';
                        const b = badge(row.days_left);
                        const linkShow = `${SHOW_URL_BASE}/${row.id}`;

                        listBody.insertAdjacentHTML('beforeend', `
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 transition-all duration-300">
                                <td class="p-4">
                                    <span class="px-3 py-2 rounded-lg text-xs font-bold ${b}">
                                        ${row.days_left}
                                    </span>
                                </td>
                                <td class="p-4 text-gray-700 font-medium">${row.fecha_vencimiento ?? ''}</td>
                                <td class="p-4 text-gray-700 font-medium">${tipoTxt}</td>
                                <td class="p-4 font-bold text-gray-900">${row.codigo_documento ?? ''}</td>
                                <td class="p-4 text-gray-700 font-medium">${row.nombre_documento ?? ''}</td>
                                <td class="p-4 text-gray-600">${row.tipo_documento ?? ''}</td>
                                <td class="p-4 text-gray-600">${row.formato_el_pa ?? ''}</td>
                                <td class="p-4 text-gray-600">${row.folio_version ?? ''}</td>
                                <td class="p-4 text-gray-600 truncate max-w-[240px]">${row.lugar_almacenamiento ?? ''}</td>
                                <td class="p-4">
                                    <a href="${linkShow}" class="text-cyan-600 font-bold hover:text-cyan-800 hover:underline transition-colors duration-300">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        `);
                    });

                    // ✅ Si no es "all", te mando a Lista para que veas todo (no solo el mes)
                    if (estadoEl.value !== 'all') {
                        tabLista.click();
                    }
                })
                .catch(err => console.error(err));
        }

        document.getElementById('prevMonth').onclick = () => { current.setMonth(current.getMonth()-1); loadMonth(); };
        document.getElementById('nextMonth').onclick = () => { current.setMonth(current.getMonth()+1); loadMonth(); };
        document.getElementById('btnFiltrar').onclick = () => loadMonth();

        tabMes.onclick = () => {
            tabMes.className = "flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold rounded-lg bg-white shadow-md text-blue-600";
            tabLista.className = "flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold rounded-lg text-gray-600 hover:text-gray-900";
            viewMes.classList.remove('hidden');
            viewLista.classList.add('hidden');
        };

        tabLista.onclick = () => {
            tabLista.className = "flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold rounded-lg bg-white shadow-md text-blue-600";
            tabMes.className = "flex-1 sm:flex-none px-6 py-2.5 text-sm font-bold rounded-lg text-gray-600 hover:text-gray-900";
            viewLista.classList.remove('hidden');
            viewMes.classList.add('hidden');
        };

        qEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') loadMonth();
        });

        loadMonth();
    </script>
</x-app-layout>
