<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Usuarios Registrados') }}
        </h2>
    </x-slot>

    <div class="py-12" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                @if (session('success'))
                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="p-6 bg-white border border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <form method="GET" action="{{ route('usuarios.index') }}" class="w-full md:max-w-md">
                            <label for="search" class="sr-only">Buscar</label>
                            <div class="flex items-center rounded-md border border-gray-200 shadow-sm overflow-hidden">
                                <span class="px-3 text-gray-400 bg-gray-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"></path></svg>
                                </span>
                                <input id="search" type="text" name="search" placeholder="Buscar empleado..." value="{{ request('search') }}"
                                       class="w-full px-4 py-2 text-sm placeholder-gray-400 focus:outline-none">
                            </div>
                        </form>

                        <a href="{{ route('usuarios.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            {{ __('Agregar Usuario') }}
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($users as $user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                @php
                                                    $parts = preg_split('/\s+/', trim($user->name));
                                                    $initials = '';
                                                    foreach (array_slice($parts, 0, 2) as $p) { $initials .= strtoupper(mb_substr($p, 0, 1)); }
                                                @endphp
                                                <div class="flex items-center justify-center w-10 h-10 text-sm font-medium text-white bg-indigo-500 rounded-full">
                                                    {{ $initials }}
                                                </div>
                                                <div class="text-sm">
                                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                                            <div class="text-sm text-gray-600">{{ $user->email }}</div>
                                        </td>

                                        <td class="px-6 py-4 align-middle whitespace-nowrap">
                                            @if($user->activo)
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Activo</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">Inactivo</span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 align-middle whitespace-nowrap text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('usuarios.edit', $user->id) }}" class="px-3 py-1 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-md hover:bg-yellow-200 focus:outline-none">
                                                    Editar
                                                </a>

                                                <form action="{{ route('usuarios.toggleEstado', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirmToggle('{{ $user->activo ? 'inactivar' : 'activar' }}', '{{ $user->name }}');">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="px-3 py-1 text-sm font-medium text-white rounded-md {{ $user->activo ? 'bg-gray-600 hover:bg-gray-700' : 'bg-green-600 hover:bg-green-700' }}">
                                                        {{ $user->activo ? 'Inactivar' : 'Activar' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if($users->isEmpty())
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                            No se encontraron usuarios.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function confirmToggle(action, userName) {
        return confirm(`¿Estás seguro de que deseas ${action} al usuario ${userName}?`);
    }
</script>
