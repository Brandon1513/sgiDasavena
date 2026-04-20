<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Solicitar actualización</h2>
            <p class="text-sm text-gray-500 mt-1">Sube el archivo actualizado y describe qué cambió. SGI decide si es Versión o Revisión.</p>
        </div>
    </x-slot>

    <div class="py-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('solicitudes.solicitar_actualizacion.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl border shadow-sm p-6 space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase">Documento</label>
                <select name="documento_id" required
                        class="mt-2 w-full rounded-xl border border-gray-300/60 px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Selecciona --</option>
                    @foreach($documentos as $d)
                        <option value="{{ $d->id }}"
                            @selected(old('documento_id', $documentoSeleccionado) == $d->id)>
                            {{ $d->codigo }} — {{ $d->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase">Archivo actualizado</label>
                <input type="file" name="archivo" required
                       class="mt-2 w-full rounded-xl border border-gray-300/60 px-4 py-3 text-sm">
                <p class="text-xs text-gray-500 mt-2">Formatos permitidos: PDF, Word, Excel.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase">Comentarios</label>
                <textarea name="comentarios" rows="4" required
                          class="mt-2 w-full rounded-xl border border-gray-300/60 px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500"
                          placeholder="Describe qué cambió...">{{ old('comentarios') }}</textarea>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('solicitudes.index') }}"
                   class="px-5 py-3 rounded-xl border bg-white hover:bg-gray-50 text-sm font-bold">
                    ← Volver
                </a>

                <button class="px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">
                    Enviar solicitud
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
