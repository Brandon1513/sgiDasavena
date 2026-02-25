<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div class="space-y-2">
                <h2 class="text-4xl font-bold text-gray-900">
                    Solicitudes de Actualización
                </h2>
                <p class="text-gray-500 text-sm">Gestiona el estado de todas tus solicitudes</p>
            </div>
            <div class="hidden sm:flex items-center gap-4 px-6 py-4 bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 group">
                <span class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-blue-600 text-sm font-bold">
                    {{ $solicitudes->total() ?? 0 }}
                </span>
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-gray-500 font-medium uppercase">Total</span>
                    <span class="text-sm text-gray-900 font-semibold">{{ $solicitudes->total() ?? 0 }} solicitudes</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- HEADER CON BOTÓN -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4 animate-fadeIn">
                <p class="text-sm text-gray-600">Gestiona todas tus solicitudes en un solo lugar</p>
                <a href="{{ route('solicitudes.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 transition-all duration-200 font-medium text-sm shadow-sm hover:shadow-md">
                    <span>+</span> Nueva Solicitud
                </a>
            </div>

            <!-- ALERTS -->
            @if (session('success'))
            <div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-800 shadow-sm animate-slideDown">
                <p class="font-medium flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-200 text-emerald-700">✓</span>
                    {{ session('success') }}
                </p>
            </div>
            @endif

            <!-- CARD PRINCIPAL -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden animate-fadeIn" style="animation-delay: 100ms;">
                <!-- FILTROS -->
                <div class="border-b border-gray-200 p-6 bg-gray-50/50">
                    <form method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                                    Nombre
                                </label>
                                <input type="text" name="nombre" value="{{ request('nombre') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Buscar...">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                                    Estado
                                </label>
                                <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none cursor-pointer bg-white"
                                    style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22rgb(107, 114, 128)%22 stroke-width=%222%22%3e%3cpolyline points=%226 9 12 15 18 9%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em; padding-right: 2.5rem;">
                                    <option value="">Todos</option>
                                    @foreach (['pendiente', 'aprobado_jefe', 'rechazado_jefe', 'atendido', 'rechazado_sgi'] as $estado)
                                    <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Desde</label>
                                <input type="date" name="desde" value="{{ request('desde') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Hasta</label>
                                <input type="date" name="hasta" value="{{ request('hasta') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 transition-all duration-200 font-medium text-sm shadow-sm">
                                    Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TABLA O EMPTY STATE -->
                @if ($solicitudes->isEmpty())
                <div class="p-16 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4 animate-pulse">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 text-sm font-medium">No hay solicitudes para mostrar</p>
                    <p class="text-gray-400 text-xs mt-2">Intenta con otros filtros o crea una nueva solicitud</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase">#</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase">Solicitante</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase">Acción</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase">Estado</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase">Comentarios</th>
                                <th class="px-6 py-4 text-left font-semibold text-gray-700 text-xs uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($solicitudes as $solicitud)
                            <tr class="hover:bg-gray-50 transition-colors duration-150 animate-fadeIn">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $solicitud->id }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ optional($solicitud->usuario)->name ?? 'Sin usuario' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">{{ ucfirst($solicitud->accion) }}</td>
                                <td class="px-6 py-4">
                                    @php
                                    $estadoMap = [
                                    'pendiente' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    'aprobado_jefe' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'rechazado_jefe' => 'bg-red-100 text-red-800 border-red-300',
                                    'atendido' => 'bg-blue-100 text-blue-800 border-blue-300',
                                    'rechazado_sgi' => 'bg-red-100 text-red-800 border-red-300',
                                    ];
                                    $clases = $estadoMap[$solicitud->estado] ?? 'bg-gray-100 text-gray-800 border-gray-300';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-xs font-medium border {{ $clases }}">
                                        {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 text-xs max-w-xs">
                                    @if($solicitud->comentarios)
                                    <span class="truncate block hover:text-gray-900 transition-colors" title="{{ $solicitud->comentarios }}">
                                        {{ Str::limit($solicitud->comentarios, 40) }}
                                    </span>
                                    @else
                                    <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if (Route::has('solicitudes.show'))
                                        <a href="{{ route('solicitudes.show', $solicitud->id) }}"
                                            class="text-blue-600 hover:text-blue-700 transition-colors text-xs font-medium">
                                            Ver
                                        </a>

                                        @endif
                                    </div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4 animate-fadeIn">
                                    @if($solicitud->accion === 'actualizacion')
                                    @if($solicitud->documento_id)
                                    <a href="{{ route('solicitudes.solicitar_actualizacion.form', ['documento' => $solicitud->documento_id]) }}"
                                        class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                        Solicitar actualización
                                    </a>
                                    @else
                                    <span class="text-gray-400 text-xs">Sin documento ligado</span>
                                    @endif
                                    @endif


                </div>

            </div>


            @if (auth()->user()->hasRole('jefe') && $solicitud->estado === 'pendiente' && Route::has('solicitudes.approval_form'))
            <a href="{{ route('solicitudes.approval_form', $solicitud->id) }}"
                class="text-blue-600 hover:text-blue-700 transition-colors text-xs font-medium">
                Revisar
            </a>
            @endif

            @if (auth()->user()->hasRole('administrador_sgi') && $solicitud->estado === 'aprobado_jefe' && Route::has('solicitudes.finalize_form'))
            <a href="{{ route('solicitudes.finalize_form', $solicitud->id) }}"
                class="text-blue-600 hover:text-blue-700 transition-colors text-xs font-medium">
                Finalizar
            </a>
            @endif
        </div>
        </td>
        </tr>
        @endforeach
        </tbody>
        </table>
    </div>

    <!-- PAGINACIÓN -->
    <div class="px-6 py-5 border-t border-gray-200 bg-gray-50/50">
        {{ $solicitudes->appends(request()->query())->links() }}
    </div>
    @endif
    </div>
    </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.4s ease-out forwards;
        }

        .animate-slideDown {
            animation: slideDown 0.3s ease-out;
        }
    </style>
</x-app-layout>