<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Finalizar Solicitud #{{ $solicitud->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl p-6 mx-auto space-y-4 bg-white rounded shadow">
            @if ($errors->any())
                <div class="p-4 mb-4 text-red-700 bg-red-100 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <p><strong>Acción:</strong> {{ ucfirst($solicitud->accion) }}</p>

            <form action="{{ route('solicitudes.finalize', $solicitud->id) }}" method="POST">
            @csrf
            
            <!-- Elaboró -->
            <div class="mb-4">
                <label class="block font-bold text-gray-700">Elaboró</label>
                <input type="text" value="{{ $solicitud->usuario->name }}" class="w-full p-2 bg-gray-100 border rounded" readonly>
            </div>

            <!-- Aprobó -->
            <div class="mb-4">
                <label class="block font-bold text-gray-700">Aprobó</label>
                <input type="text" value="{{ $solicitud->jefe ? $solicitud->jefe->name : 'No asignado' }}" class="w-full p-2 bg-gray-100 border rounded" readonly>
            </div>

            <!-- Revisó -->
            <div class="mb-4">
                <label class="block font-bold text-gray-700">Revisó</label>
                <input type="text" value="{{ auth()->user()->name }}" class="w-full p-2 bg-gray-100 border rounded" readonly>
            </div>

            <!-- Alta en el sistema de gestión (editable) -->
            <div class="mb-4">
                <label class="block font-bold text-gray-700">Alta en el sistema de gestión</label>
                <input type="date" name="fecha_alta_sgi" value="{{ now()->format('Y-m-d') }}" class="w-full p-2 border rounded">
            </div>

            <!-- Número de Revisión Actual -->
            <div class="mb-4">
                <label class="block font-bold text-gray-700">Número de Revisión Actual</label>
                <input type="text" name="revision_actual" class="w-full p-2 border rounded">
            </div>

            <!-- Número de Revisión Anterior -->
            <div class="mb-4">
                <label class="block font-bold text-gray-700">Número de Revisión Anterior (opcional)</label>
                <input type="text" name="revision_anterior" class="w-full p-2 border rounded">
            </div>

            <!-- Medio de archivo -->
            <div class="mb-4">
                <label class="block font-bold text-gray-700">Medio de archivo (Liga)</label>
                <input type="text" name="liga_archivo" class="w-full p-2 border rounded">
            </div>

            <!-- Observaciones -->
            <div class="mb-4">
                <label class="block font-bold text-gray-700">Observaciones</label>
                <textarea name="observaciones_sgi" class="w-full p-2 border rounded"></textarea>
            </div>
            <!-- Buscador de usuarios para notificación -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700">Seleccionar usuarios para notificación</label>
                <input type="text" id="busqueda-usuario" placeholder="Buscar usuario..." class="w-full mt-2 mb-2 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">

                <div class="py-1 my-1">
                    <input type="checkbox" id="seleccionar-todos-usuarios">
                    <label for="seleccionar-todos-usuarios">Seleccionar Todos</label>
                </div>

                <div id="lista-usuarios" class="h-64 overflow-y-auto border border-gray-300 rounded-md">
                    @foreach($usuarios as $usuario)
                        <div class="usuario-item" data-name="{{ $usuario->name }}" data-clave="{{ $usuario->clave_empleado }}" data-departamento="{{ $usuario->departamento->name ?? 'Sin departamento' }}">
                            <input type="checkbox" name="usuarios_notificados[]" value="{{ $usuario->id }}" class="usuario-checkbox">
                            <label>{{ $usuario->name }} - Clave: {{ $usuario->clave_empleado }} - Departamento: {{ $usuario->departamento->name ?? 'Sin departamento' }}</label>
                        </div>
                    @endforeach
                </div>
            </div>


            <!-- Acciones -->
            <div class="flex justify-between mt-4">
                <button type="submit" name="accion" value="rechazar" class="px-4 py-2 font-bold text-white bg-red-600 rounded hover:bg-red-700">
                    Rechazar
                </button>

                <button type="submit" name="accion" value="atender" class="px-4 py-2 font-bold text-white bg-green-600 rounded hover:bg-green-700">
                    Marcar como Atendido
                </button>
            </div>
        </form>

        </div>
    </div>
</x-app-layout>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const busqueda = document.getElementById('busqueda-usuario');
        const lista = document.getElementById('lista-usuarios');
        const seleccionarTodos = document.getElementById('seleccionar-todos-usuarios');
        const checkboxes = document.querySelectorAll('.usuario-checkbox');

        busqueda.addEventListener('input', function () {
            const filtro = busqueda.value.toLowerCase();
            document.querySelectorAll('.usuario-item').forEach(item => {
                const nombre = item.getAttribute('data-name').toLowerCase();
                const clave = item.getAttribute('data-clave').toLowerCase();
                const depto = item.getAttribute('data-departamento').toLowerCase();

                item.style.display = (nombre.includes(filtro) || clave.includes(filtro) || depto.includes(filtro)) ? '' : 'none';
            });
        });

        seleccionarTodos.addEventListener('change', function () {
            const activo = seleccionarTodos.checked;
            checkboxes.forEach(c => c.checked = activo);
        });
    });
</script>
