<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Editar Usuario: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl p-6 mx-auto bg-white rounded shadow-sm sm:px-6 lg:px-8">

            <form method="POST" action="{{ route('usuarios.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-gray-700">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full p-2 border border-gray-300 rounded" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full p-2 border border-gray-300 rounded" required>
                </div>

               <!-- Nueva Contraseña -->
                <div class="mb-4">
                    <label class="block text-gray-700">Nueva Contraseña</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" class="w-full p-2 pr-10 border border-gray-300 rounded" placeholder="Dejar en blanco si no desea cambiarla">
                        <button type="button" onclick="togglePassword('password', 'iconPassword')" class="absolute inset-y-0 right-0 px-3 text-gray-600">
                            <svg id="iconPassword" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirmar Contraseña -->
                <div class="mb-4">
                    <label class="block text-gray-700">Confirmar Nueva Contraseña</label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" class="w-full p-2 pr-10 border border-gray-300 rounded" placeholder="Dejar en blanco si no desea cambiarla">
                        <button type="button" onclick="togglePassword('password_confirmation', 'iconPasswordConfirmation')" class="absolute inset-y-0 right-0 px-3 text-gray-600">
                            <svg id="iconPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>



                <div class="mb-4">
                    <label class="block text-gray-700">Área</label>
                    <input type="text" name="area" value="{{ old('area', $user->area) }}" class="w-full p-2 border border-gray-300 rounded">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Puesto</label>
                    <input type="text" name="puesto" value="{{ old('puesto', $user->puesto) }}" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <!-- Jefe -->
                <div class="mb-4">
                    <label class="block text-gray-700">Jefe</label>
                    <select name="jefe_id" class="w-full p-2 border border-gray-300 rounded">
                        <option value="">Sin Jefe</option>
                        @foreach ($usuarios as $usuarioJefe)
                            <option value="{{ $usuarioJefe->id }}" {{ $user->jefe_id == $usuarioJefe->id ? 'selected' : '' }}>
                                {{ $usuarioJefe->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Roles</label>
                    @foreach ($roles as $rol)
                        <div class="flex items-center mb-2">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $rol->name }}"
                                {{ $user->roles->pluck('name')->contains($rol->name) ? 'checked' : '' }}
                                class="mr-2"
                            >
                            <span>{{ ucfirst(str_replace('_', ' ', $rol->name)) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                        Actualizar Usuario
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>

<script>
    function togglePassword(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);

        if (field.type === 'password') {
            field.type = 'text';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.442-3.957m1.414-1.414A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-1.669 2.956m-1.414 1.414A9.956 9.956 0 0112 19c-1.219 0-2.38-.217-3.457-.625M9.88 9.88A3 3 0 0114.12 14.12M3 3l18 18"/>`;
        } else {
            field.type = 'password';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
        }
    }
</script>

