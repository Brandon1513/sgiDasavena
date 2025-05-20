<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Crear Nuevo Usuario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl p-6 mx-auto bg-white rounded shadow-sm sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('usuarios.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700">Nombre</label>
                    <input type="text" name="name" class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Correo Electrónico</label>
                    <input type="email" name="email" class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Área</label>
                    <input type="text" name="area" class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Puesto</label>
                    <input type="text" name="puesto" class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Contraseña</label>
                    <input type="password" name="password" class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" class="w-full p-2 border border-gray-300 rounded" required>
                </div>
                <!-- Jefe -->
                <div class="mb-4">
                    <label class="block text-gray-700">Jefe</label>
                    <select name="jefe_id" class="w-full p-2 border border-gray-300 rounded">
                        <option value="">Sin Jefe</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>


                <div class="mb-4">
                    <label class="block text-gray-700">Roles</label>
                    @foreach ($roles as $rol)
                        <div class="flex items-center mb-2">
                            <input type="checkbox" name="roles[]" value="{{ $rol->name }}" class="mr-2">
                            <span>{{ ucfirst(str_replace('_', ' ', $rol->name)) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                        Crear Usuario
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
