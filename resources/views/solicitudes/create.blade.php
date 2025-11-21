<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Nueva Solicitud de Actualización de Formato
        </h2>
    </x-slot>

    <div class="py-12" style="background-image: url('{{ asset('images/background-pattern.png') }}');">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="p-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">

                <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-gray-700">Nombre</label>
                        <input type="text" value="{{ Auth::user()->name }}" readonly
                            class="w-full p-2 bg-gray-100 border border-gray-300 rounded">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Área</label>
                        <input type="text" value="{{ Auth::user()->area ?? '' }}" readonly
                            class="w-full p-2 bg-gray-100 border border-gray-300 rounded">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Puesto</label>
                        <input type="text" value="{{ Auth::user()->puesto ?? '' }}" readonly
                            class="w-full p-2 bg-gray-100 border border-gray-300 rounded">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Acción a realizar</label>
                        <select name="accion" required class="w-full p-2 border border-gray-300 rounded">
                            <option value="">Seleccione una opción</option>
                            <option value="actualizacion">Actualización</option>
                            <option value="baja">Baja</option>
                            <option value="nuevo_documento">Nuevo Documento</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Archivo Adjunto (Opcional)</label>
                        <input type="file" name="archivo" class="w-full p-2 border border-gray-300 rounded">
                    </div>

                    <div class="mb-4">

                        <label class="block text-gray-700">cambios (modificaciones)</label>
                        <textarea name="comentarios" rows="3"
                            class="w-full p-2 border border-gray-300 rounded"
                            placeholder="Escribe algún comentario adicional..."></textarea>
                    </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                    Enviar Solicitud
                </button>
            </div>
            </form>

        </div>
    </div>
    </div>
</x-app-layout>