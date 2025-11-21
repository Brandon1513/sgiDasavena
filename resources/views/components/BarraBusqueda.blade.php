<div class="w-full max-w-3xl mx-auto space-y-4">
    <!-- Encabezado de la plataforma -->
    <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">SGI — Sistema de Gestión Integral</h1>
            <p class="text-sm text-gray-600 mt-1">En esta plataforma se aprueban o rechazan documentos. Administre solicitudes y revise el estado de cada documento.</p>
            <p class="text-xs text-gray-500 mt-2">Estados: <span class="font-medium text-green-600">Aprobadas</span>, <span class="font-medium text-red-600">Rechazadas</span>, <span class="font-medium text-yellow-600">Pendientes</span></p>
        </div>

        <!-- Toggle de vistas -->
        <div class="flex items-center space-x-2">
            <span class="text-xs text-gray-500 mr-2">Vistas:</span>
            <a href="{{ route('solicitudes.index', array_merge(request()->query(), ['view' => 'list'])) }}"
               class="px-3 py-1 rounded-md border border-gray-200 bg-gray-50 text-sm text-gray-700 hover:bg-gray-100">
                Lista
            </a>
            <a href="{{ route('solicitudes.index', array_merge(request()->query(), ['view' => 'grid'])) }}"
               class="px-3 py-1 rounded-md border border-gray-200 bg-gray-50 text-sm text-gray-700 hover:bg-gray-100">
                Tarjetas
            </a>
        </div>
    </div>

    <!-- Barra de búsqueda -->
    <div class="flex items-center justify-center w-full">
        <form action="{{ route('solicitudes.index') }}" method="GET" class="w-full">
            <div class="relative max-w-md mx-auto">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar..."
                    class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm transition">
                <span class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </span>
            </div>
        </form>
    </div>

    <!-- Resultados de búsqueda / Información -->
    @if(request('q'))
    <div class="mt-2 w-full bg-white rounded-lg shadow p-4 hover:shadow-lg transition divide-y divide-gray-200">
        <h2 class="text-gray-700 text-sm mb-2">Resultados para: <span class="font-semibold">{{ request('q') }}</span></h2>

        <p class="text-sm text-gray-600 mb-3">Recuerde: cada solicitud puede ser <span class="font-medium text-green-600">Aprobada</span> o <span class="font-medium text-red-600">Rechazada</span>. Revise la documentación antes de tomar una decisión.</p>

        <ul class="divide-y divide-gray-200">
            <!-- Resultados dinamicos  -->
            <li class="py-2 text-gray-600 hover:bg-gray-50 cursor-pointer rounded transition">{{ __('Profile') }}</li>
            <li class="py-2 text-gray-600 hover:bg-gray-50 cursor-pointer rounded transition">Resultado 1</li>
            <li class="py-2 text-gray-600 hover:bg-gray-50 cursor-pointer rounded transition">Resultado 2</li>
            <li class="py-2 text-gray-600 hover:bg-gray-50 cursor-pointer rounded transition">Resultado 3</li>
        </ul>

        <div class ="py-2 mt-4 text-center text-gray-500 text-sm">
            <a href="{{ route('solicitudes.index') }}" class="text-blue-600 hover:underline">Ver todas las solicitudes</a>
        </div>
    </div>
    @else
    <!-- Vista por defecto cuando no hay búsqueda -->
    <div class="w-full bg-white rounded-lg shadow p-4">
        <h3 class="text-gray-700 font-medium">Panel rápido</h3>
        <p class="text-sm text-gray-600 mt-2">Use la barra de búsqueda para encontrar solicitudes. Cambie de vista a "Lista" o "Tarjetas" para organizar la presentación de las solicitudes.</p>
    </div>
    @endif
</div>