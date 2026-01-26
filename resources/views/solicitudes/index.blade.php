<x-app-layout>
    <x-slot name="header">
        <h2 class="text-3xl font-bold leading-tight text-gray-900">
            📋 Solicitudes de Actualización de Formatos
        </h2>
    </x-slot>

    <div class="py-12" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-8 overflow-hidden bg-white shadow-xl sm:rounded-xl">
                @if (session('success'))
                <div class="p-4 mb-6 text-sm text-green-800 bg-green-50 border-l-4 border-green-500 rounded-lg animate-fade-in" role="alert">
                    <span class="font-semibold">✓ Éxito:</span> {{ session('success') }}
                </div>
                @endif

                <!-- FILTROS -->
                <form method="GET" class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-2 lg:grid-cols-5">
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre</label>
                        <input type="text" name="nombre" value="{{ request('nombre') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400"
                               placeholder="Buscar...">
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                        <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400">
                            <option value="">-- Todos --</option>
                            @foreach (['pendiente', 'aprobado_jefe', 'rechazado_jefe', 'atendido', 'rechazado_sgi'] as $estado)
                            <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $estado)) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Desde</label>
                        <input type="date" name="desde" value="{{ request('desde') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400">
                    </div>

                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Hasta</label>
                        <input type="date" name="hasta" value="{{ request('hasta') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 hover:border-gray-400">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full px-6 py-2 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:shadow-lg transform hover:scale-105 transition duration-200 font-semibold">
                            🔍 Filtrar
                        </button>
                    </div>
                </form>

                <!-- BOTÓN NUEVA SOLICITUD -->
                <div class="flex justify-end mb-6">
                    <a href="{{ route('solicitudes.create') }}" class="px-6 py-3 text-white bg-gradient-to-r from-green-500 to-green-600 rounded-lg hover:shadow-lg transform hover:scale-105 transition duration-200 font-semibold inline-flex items-center gap-2">
                        ➕ Nueva Solicitud
                    </a>
                </div>

                @if ($solicitudes->isEmpty())
                <div class="text-center py-12">
                    <p class="text-lg text-gray-500">📭 No hay solicitudes para mostrar.</p>
                </div>
                @else
                <!-- TABLA RESPONSIVA -->
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">#</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Solicitante</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Acción</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Estado</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Comentarios</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($solicitudes as $solicitud)
                            <tr class="hover:bg-blue-50 transition duration-200 group">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $solicitud->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full">
                                        {{ optional($solicitud->usuario)->name ?? 'Sin usuario' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ ucfirst($solicitud->accion) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $estadoClases = [
                                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                                            'aprobado_jefe' => 'bg-green-100 text-green-800',
                                            'rechazado_jefe' => 'bg-red-100 text-red-800',
                                            'atendido' => 'bg-blue-100 text-blue-800',
                                            'rechazado_sgi' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full font-semibold {{ $estadoClases[$solicitud->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    @if($solicitud->comentarios)
                                        <span title="{{ $solicitud->comentarios }}">{{ Str::limit($solicitud->comentarios, 50) }}</span>
                                    @else
                                        <span class="italic text-gray-400">Sin comentarios</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm space-x-2">
                                    @if (Route::has('solicitudes.show'))
                                        <a href="{{ route('solicitudes.show', $solicitud->id) }}" 
                                           class="inline-block px-3 py-1 text-blue-600 hover:bg-blue-100 rounded transition duration-200 font-semibold">
                                            👁️ Ver
                                        </a>
                                    @endif

                                    @if (auth()->user()->hasRole('jefe') && $solicitud->estado === 'pendiente' && Route::has('solicitudes.approval_form'))
                                        <a href="{{ route('solicitudes.approval_form', $solicitud->id) }}" 
                                           class="inline-block px-3 py-1 text-green-600 hover:bg-green-100 rounded transition duration-200 font-semibold">
                                            ✓ Revisar
                                        </a>
                                    @endif

                                    @if (auth()->user()->hasRole('administrador_sgi') && $solicitud->estado === 'aprobado_jefe' && Route::has('solicitudes.finalize_form'))
                                        <a href="{{ route('solicitudes.finalize_form', $solicitud->id) }}" 
                                           class="inline-block px-3 py-1 text-orange-600 hover:bg-orange-100 rounded transition duration-200 font-semibold">
                                            ✓ Finalizar
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACIÓN -->
                <div class="mt-8">
                    {{ $solicitudes->appends(request()->query())->links() }}
                </div>
                @endif

            </div>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
</x-app-layout>