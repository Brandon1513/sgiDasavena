<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Solicitudes de Actualización de Formatos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form method="GET" class="flex flex-wrap items-end gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium">Nombre</label>
                        <input type="text" name="nombre" value="{{ request('nombre') }}" class="w-full p-2 border rounded">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Estado</label>
                        <select name="estado" class="w-full p-2 border rounded">
                            <option value="">-- Todos --</option>
                            @foreach (['pendiente', 'aprobado_jefe', 'rechazado_jefe', 'atendido', 'rechazado_sgi'] as $estado)
                                <option value="{{ $estado }}" {{ request('estado') === $estado ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Desde</label>
                        <input type="date" name="desde" value="{{ request('desde') }}" class="w-full p-2 border rounded">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Hasta</label>
                        <input type="date" name="hasta" value="{{ request('hasta') }}" class="w-full p-2 border rounded">
                    </div>

                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Filtrar
                    </button>
                </form>

                <div class="flex justify-end mb-4">
                    <a href="{{ route('solicitudes.create') }}" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Nueva Solicitud
                    </a>
                </div>

                @if ($solicitudes->isEmpty())
                    <p>No hay solicitudes para mostrar.</p>
                @else
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Solicitante</th>
                                <th class="px-4 py-2">Acción</th>
                                <th class="px-4 py-2">Estado</th>
                                <th class="px-4 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solicitudes as $solicitud)
                                <tr class="border-t">
                                    <td class="px-4 py-2">{{ $solicitud->id }}</td>
                                    <td class="px-4 py-2">{{ optional($solicitud->usuario)->name ?? 'Sin usuario' }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($solicitud->accion) }}</td>
                                    <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $solicitud->estado)) }}</td>
                                    <td class="px-4 py-2">
                                        @if (Route::has('solicitudes.show'))
                                            <a href="{{ route('solicitudes.show', $solicitud->id) }}" class="text-blue-600 hover:underline">Ver</a>
                                        @endif

                                        @if (auth()->user()->hasRole('jefe') && $solicitud->estado === 'pendiente' && Route::has('solicitudes.approval_form'))
                                            <a href="{{ route('solicitudes.approval_form', $solicitud->id) }}" class="ml-2 text-green-600 hover:underline">Aprobar/Rechazar</a>
                                        @endif

                                        @if (auth()->user()->hasRole('administrador_sgi') && $solicitud->estado === 'aprobado_jefe' && Route::has('solicitudes.finalize_form'))
                                            <a href="{{ route('solicitudes.finalize_form', $solicitud->id) }}" class="ml-2 text-orange-600 hover:underline">Finalizar</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $solicitudes->appends(request()->query())->links() }}

                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
