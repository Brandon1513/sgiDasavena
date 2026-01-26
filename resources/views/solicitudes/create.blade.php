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

            <form method="POST" action="{{ route('solicitudes.store') }}" enctype="multipart/form-data"
                  x-data="{
                    accion: '',
                    isActualizacion() { return this.accion === 'actualizacion' },
                    isBaja() { return this.accion === 'baja' },
                    isNuevo() { return this.accion === 'nuevo_documento' }
                  }"
                  class="space-y-8">
                @csrf

                {{-- SECCIÓN 1: INFORMACIÓN DEL USUARIO --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Información del Solicitante
                        </h3>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                                <input type="text" value="{{ Auth::user()->name }}" readonly
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Área</label>
                                <input type="text" value="{{ Auth::user()->area ?? 'Sin asignar' }}" readonly
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Puesto</label>
                                <input type="text" value="{{ Auth::user()->puesto ?? 'Sin asignar' }}" readonly
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: TIPO DE SOLICITUD --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-purple-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"/>
                            </svg>
                            Tipo de Solicitud
                        </h3>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Acción a realizar <span class="text-red-500">*</span>
                            </label>
                            <select name="accion" required x-model="accion"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <option value="">-- Seleccione una opción --</option>
                                <option value="actualizacion">📝 Actualización de documento existente</option>
                                <option value="nuevo_documento">✨ Nuevo documento</option>
                                <option value="baja">❌ Dar de baja un documento</option>
                            </select>
                        </div>

                        <div class="p-4 rounded-lg bg-blue-50 border border-blue-200" x-show="isActualizacion()">
                            <p class="text-sm text-blue-900">
                                <strong>Actualización:</strong> Solicita cambios de un documento existente (SGI capturará datos oficiales al final).
                            </p>
                        </div>

                        <div class="p-4 rounded-lg bg-green-50 border border-green-200" x-show="isNuevo()">
                            <p class="text-sm text-green-900">
                                <strong>Nuevo Documento:</strong> Propón el nombre del documento. SGI asignará folio, fecha oficial y vencimientos.
                            </p>
                        </div>

                        <div class="p-4 rounded-lg bg-red-50 border border-red-200" x-show="isBaja()">
                            <p class="text-sm text-red-900">
                                <strong>Baja:</strong> Indica el motivo. SGI validará y cerrará el proceso.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: DATOS MÍNIMOS QUE CAPTURA EL USUARIO --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000 2H6a6 6 0 100 12H5a1 1 0 100 2h1a2 2 0 002-2v-2.586A1 1 0 0010.586 12H13a1 1 0 100-2h-2.414l1.657-1.657a1 1 0 00-1.414-1.414l-1.657 1.657V5z" clip-rule="evenodd"/>
                            </svg>
                            Información del Documento
                        </h3>
                    </div>

                    <div class="px-6 py-5 space-y-6">

                        {{-- Nombre del documento (solo para nuevo) --}}
                        <div x-show="isNuevo()" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del documento <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                name="nombre_documento"
                                value="{{ old('nombre_documento') }}"
                                placeholder="Ej. Instructivo de uso de Rayos X"
                                :required="isNuevo()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <p class="mt-2 text-xs text-gray-500">
                                Obligatorio para <strong>Nuevo Documento</strong>.
                            </p>
                        </div>

                        {{-- Motivo de baja (solo baja) --}}
                        <div x-show="isBaja()" x-transition class="animate-in">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo de baja <span class="text-red-500">*</span>
                            </label>
                            <textarea name="motivo_baja"
                                rows="4"
                                :required="isBaja()"
                                placeholder="Explica por qué se da de baja (obsoleto, reemplazado por..., etc.)"
                                class="w-full px-4 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition resize-none bg-red-50">{{ old('motivo_baja') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 4: ARCHIVO Y COMENTARIOS --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                            </svg>
                            Archivos y Detalles
                        </h3>
                    </div>

                    <div class="px-6 py-5 space-y-6">

                        {{-- Archivo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Archivo Adjunto (opcional)
                            </label>

                            <div class="flex items-center justify-center w-full">
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <p class="text-sm text-gray-700"><span class="font-semibold">Click para cargar</span> o arrastra</p>
                                        <p class="text-xs text-gray-600 mt-1">PDF, Word, Excel</p>
                                    </div>
                                    <input type="file" name="archivo" class="hidden">
                                </label>
                            </div>
                        </div>

                        {{-- Comentarios --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Cambios realizados / Descripción <span class="text-red-500">*</span>
                            </label>
                            <textarea name="comentarios"
                                rows="4"
                                required
                                placeholder="Describe qué cambió, el motivo y lo que se modificó..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none">{{ old('comentarios') }}</textarea>
                        </div>

                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="flex items-center justify-between pt-4">
                    <a href="{{ route('solicitudes.index') }}"
                       class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">
                        ← Cancelar
                    </a>
                    <button type="submit"
                        class="px-8 py-3 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition font-medium shadow-md">
                        ✓ Enviar Solicitud
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
