<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">
                    Nueva Solicitud
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Solicitud de Actualización de Formato y Documentos
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('solicitudes.store') }}"
                  enctype="multipart/form-data"
                                    x-data="{
                                        accion: '{{ old('accion', '') }}',
                                        searchDocumento: '{{ old('searchDocumento', '') }}',
                                        isActualizacion() { return this.accion === 'actualizacion' },
                                        isBaja() { return this.accion === 'baja' },
                                        isNuevo() { return this.accion === 'nuevo_documento' }
                                    }"
                  onsubmit="this.querySelector('button[type=submit]').disabled = true;"
                  class="space-y-8">
                @csrf

                {{-- SECCIÓN 1 --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Información del Solicitante
                        </h3>
                    </div>

                    <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                            <input type="text" value="{{ Auth::user()->name }}" readonly
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Área</label>
                            <input type="text" value="{{ Auth::user()->area ?? 'Sin asignar' }}" readonly
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Puesto</label>
                            <input type="text" value="{{ Auth::user()->puesto ?? 'Sin asignar' }}" readonly
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2 --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Tipo de Solicitud
                        </h3>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Acción a realizar <span class="text-red-500">*</span>
                            </label>

                            <select name="accion"
                                    required
                                    x-model="accion"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Seleccione una opción --</option>
                                <option value="actualizacion" @selected(old('accion') === 'actualizacion')>📝 Actualización</option>
                                <option value="nuevo_documento" @selected(old('accion') === 'nuevo_documento')>✨ Nuevo documento</option>
                                <option value="baja" @selected(old('accion') === 'baja')>❌ Baja</option>
                            </select>
                        </div>
                    </div>
                </div>


                {{-- SECCIÓN 3 --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
            Información del Documento
        </h3>
    </div>

    <div class="px-6 py-5 space-y-6">

        {{-- SELECT DE DOCUMENTO (Se muestra en Actualización y en Baja) --}}
        <div x-show="isActualizacion() || isBaja()" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Selecciona el documento <span class="text-red-500">*</span>
            </label>

            <input type="text"
                   x-model="searchDocumento"
                   placeholder="Buscar documento..."
                   class="w-full px-4 py-2 mb-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"/>

            <select name="documento_id"
                    x-bind:disabled="!isActualizacion() && !isBaja()"
                    x-bind:required="isActualizacion() || isBaja()"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 shadow-sm">
                <option value="">-- Selecciona un documento del catálogo --</option>
                @foreach($documentos as $d)
                    <option value="{{ $d->id }}" @selected(old('documento_id') == $d->id)
                            x-bind:hidden="!('{{ strtolower($d->codigo.' '.$d->nombre.' '.$d->area) }}'.includes(searchDocumento.toLowerCase()))">
                        {{ $d->codigo }} — {{ $d->nombre }} 
                        ({{ $d->area }})
                    </option>
                @endforeach
            </select>

            <p class="text-xs text-gray-500 mt-2">
                <span x-show="isActualizacion()">Selecciona el documento que deseas modificar.</span>
                <span x-show="isBaja()" class="text-red-600 font-medium">Selecciona el documento que será retirado del sistema.</span>
            </p>
        </div>

        {{-- NUEVO DOCUMENTO (Solo si es nuevo) --}}
        <div x-show="isNuevo()" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Nombre del nuevo documento <span class="text-red-500">*</span>
            </label>

            <input type="text"
                   name="nombre_documento"
                   placeholder="Ej. Procedimiento de compras"
                   value="{{ old('nombre_documento') }}"
                   x-bind:required="isNuevo()"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>



        {{-- ####################################################################################bJA problemas en dar de baja --}}
        {{-- MOTIVO DE BAJA (Solo si es baja) --}}
        <div x-show="isBaja()" x-transition class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Justificación de la baja <span class="text-red-500">*</span>
            </label>

            <textarea name="motivo_baja"
                      rows="3"
                      placeholder="Explica por qué este documento ya no es necesario..."
                      x-bind:required="isBaja()"
                      class="w-full px-4 py-2 border border-red-200 rounded-lg focus:ring-2 focus:ring-red-500 bg-red-50/50">{{ old('motivo_baja') }}</textarea>
        </div>

    </div>
</div>
                
                {{-- SECCIÓN 4 --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Archivos y Detalles
                        </h3>
                    </div>

                    <div class="px-6 py-5 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Archivo adjunto (opcional)
                            </label>

                            <input type="file"
                                   name="archivo"
                                   class="w-full border border-gray-300 rounded-lg p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción / Cambios <span class="text-red-500">*</span>
                            </label>

                            <textarea name="comentarios"
                                      rows="4"
                                      required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('comentarios') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="flex justify-between pt-4">
                    <a href="{{ route('solicitudes.index') }}"
                       class="px-6 py-3 border border-gray-300 rounded-lg bg-white hover:bg-gray-50">
                        ← Cancelar
                    </a>

                    <button type="submit"
                            class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md">
                        ✓ Enviar Solicitud
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>