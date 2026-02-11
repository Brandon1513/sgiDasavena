<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">
                    Finalizar Solicitud #{{ $solicitud->id }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Última validación por SGI (captura de datos oficiales para calendario)
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

            <form action="{{ route('solicitudes.finalize', $solicitud->id) }}"
                method="POST"
                class="space-y-8"
                x-data="{
                    accionFinal: 'atender',
                    mismaFechaRevision: true,
                    isAtender() { return this.accionFinal === 'atender' },
                    isRechazar() { return this.accionFinal === 'rechazar' },
                  }">
                @csrf

                {{-- SECCIÓN 1: RESUMEN --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Resumen de la Solicitud
                        </h3>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Acción</label>
                                <input type="text" value="{{ strtoupper($solicitud->accion) }}" readonly
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Elaboró</label>
                                <input type="text" value="{{ $solicitud->usuario->name }}" readonly
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Aprobó (Jefe)</label>
                                <input type="text" value="{{ $solicitud->jefe ? $solicitud->jefe->name : 'No asignado' }}" readonly
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Comentarios del solicitante</label>
                            <textarea readonly rows="3"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed resize-none">{{ $solicitud->comentarios }}</textarea>
                        </div>

                        @if($solicitud->archivo_adjunto)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-slate-50 border border-slate-200">
                            <div class="text-sm text-gray-700">
                                <strong>Archivo adjunto:</strong> {{ basename($solicitud->archivo_adjunto) }}
                            </div>
                            <a href="{{ asset('storage/'.$solicitud->archivo_adjunto) }}" target="_blank"
                                class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                Ver archivo
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- SECCIÓN 2: DATOS DE SGI --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Datos Oficiales (SGI)
                        </h3>
                        <p class="text-xs text-gray-600 mt-1">
                            Estos datos alimentan el calendario de vencimientos (solo se requieren al “Atender”).
                        </p>
                    </div>

                    <div class="px-6 py-5 space-y-6">

                        {{-- Alta SGI --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alta en el sistema de gestión
                            </label>
                            <input type="date" name="fecha_alta_sgi"
                                value="{{ old('fecha_alta_sgi', optional($solicitud->fecha_alta_sgi)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                :disabled="isRechazar()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        {{-- Revisiones --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de Revisión Actual <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="revision_actual"
                                    value="{{ old('revision_actual', $solicitud->revision_actual) }}"
                                    :disabled="isRechazar()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de Revisión Anterior (opcional)
                                </label>
                                <input type="text" name="revision_anterior"
                                    value="{{ old('revision_anterior', $solicitud->revision_anterior) }}"
                                    :disabled="isRechazar()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                        </div>

                        {{-- Tipo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de documento
                            </label>
                            <input type="text" name="tipo_documento"
                                value="{{ old('tipo_documento', $solicitud->tipo_documento) }}"
                                :disabled="isRechazar()"
                                placeholder="Ej. Procedimiento, Instructivo, Formato"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        {{-- Nombre + Formato --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre del documento
                                    <span class="text-red-500" x-show="isAtender()">*</span>
                                </label>
                                <input type="text" name="nombre_documento"
                                    value="{{ old('nombre_documento', $solicitud->nombre_documento) }}"
                                    :disabled="isRechazar()"
                                    placeholder="Ej. Instructivo de uso de Rayos X"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <p class="mt-2 text-xs text-gray-500">
                                    Obligatorio para <strong>Nuevo Documento</strong>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Formato (EL/PA)
                                </label>
                                <select name="formato_el_pa"
                                    :disabled="isRechazar()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                    <option value="">-- Seleccione --</option>
                                    <option value="EL" @selected(old('formato_el_pa', $solicitud->formato_el_pa) === 'EL')>EL</option>
                                    <option value="PA" @selected(old('formato_el_pa', $solicitud->formato_el_pa) === 'PA')>PA</option>
                                    <option value="PA/EL" @selected(old('formato_el_pa', $solicitud->formato_el_pa) === 'PA/EL')>PA/EL (Ambos)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Folio + Código --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Versión / Folio
                                </label>
                                <input type="text" name="folio_version"
                                    value="{{ old('folio_version', $solicitud->folio_version) }}"
                                    :disabled="isRechazar()"
                                    placeholder="Ej. 0272025, 3282025"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <p class="mt-2 text-xs text-gray-500">Opcional</p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700">
                                    Código de documento
                                </label>
                                <input type="text" name="codigo_documento"
                                    value="{{ old('codigo_documento', $solicitud->codigo_documento) }}"
                                    :disabled="isRechazar()"
                                    placeholder="Ej. SGI-PR-001"
                                    class="w-full rounded-xl border border-gray-300/50 px-4 py-3"
                                    required>
                            </div>
                        </div>

                        {{-- Fechas versión / revisión (✅ FIX real) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha de versión
                                    <span class="text-red-500" x-show="isAtender()">*</span>
                                </label>
                                <input type="date" name="fecha_version" id="fecha_version"
                                    value="{{ old('fecha_version', optional($solicitud->fecha_version)->format('Y-m-d')) }}"
                                    :required="isAtender()"
                                    :disabled="isRechazar()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                <p class="mt-2 text-xs text-gray-500">Fecha oficial</p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-bold text-gray-700">Fecha de revisión</label>

                                    <label class="flex items-center gap-2 text-xs text-gray-600 select-none">
                                        <input type="checkbox" x-model="mismaFechaRevision" class="rounded">
                                        Usar misma fecha de versión
                                    </label>
                                </div>

                                <input type="date" name="fecha_revision" id="fecha_revision"
                                    value="{{ old('fecha_revision', optional($solicitud->fecha_revision)->format('Y-m-d')) }}"
                                    :disabled="isRechazar() || mismaFechaRevision"
                                    class="w-full rounded-xl border border-gray-300/50 px-4 py-3">

                                <p class="mt-2 text-xs text-gray-500">
                                    Si se deja vacío, se toma <strong>Fecha de versión</strong>.
                                </p>
                            </div>
                        </div>

                        {{-- Vigencias --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Vigencia versión (días)
                                    <span class="text-red-500" x-show="isAtender()">*</span>
                                </label>
                                <input type="number" min="1" name="vigencia_version_dias"
                                    value="{{ old('vigencia_version_dias', $solicitud->vigencia_version_dias ?? 365) }}"
                                    :required="isAtender()"
                                    :disabled="isRechazar()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Vigencia revisión (días)
                                    <span class="text-red-500" x-show="isAtender()">*</span>
                                </label>
                                <input type="number" min="1" name="vigencia_revision_dias"
                                    value="{{ old('vigencia_revision_dias', $solicitud->vigencia_revision_dias ?? 730) }}"
                                    :required="isAtender()"
                                    :disabled="isRechazar()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                        </div>

                        {{-- Lugar almacenamiento --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Lugar de almacenamiento
                            </label>
                            <input type="text" name="lugar_almacenamiento"
                                value="{{ old('lugar_almacenamiento', $solicitud->lugar_almacenamiento) }}"
                                :disabled="isRechazar()"
                                placeholder="Ej. SharePoint: /SGI/Calidad/Procedimientos"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <p class="mt-2 text-xs text-gray-500">Opcional</p>
                        </div>

                        {{-- Liga archivo --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Medio de archivo (Liga SharePoint) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="liga_archivo"
                                value="{{ old('liga_archivo', $solicitud->liga_archivo) }}"
                                :disabled="isRechazar()"
                                placeholder="https://...sharepoint.com/..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        </div>

                        {{-- Observaciones --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Observaciones SGI
                            </label>
                            <textarea name="observaciones_sgi" rows="3"
                                :disabled="isRechazar()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none">{{ old('observaciones_sgi', $solicitud->observaciones_sgi) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: NOTIFICACIONES --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Notificación a Usuarios
                        </h3>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <input type="text" id="busqueda-usuario" placeholder="Buscar usuario..."
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="seleccionar-todos-usuarios">
                            <label for="seleccionar-todos-usuarios" class="text-sm text-gray-700">Seleccionar Todos</label>
                        </div>

                        <div id="lista-usuarios" class="h-64 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-slate-50">
                            @foreach($usuarios as $usuario)
                            <div class="usuario-item py-1"
                                data-name="{{ $usuario->name }}"
                                data-clave="{{ $usuario->clave_empleado }}"
                                data-departamento="{{ $usuario->departamento->name ?? 'Sin departamento' }}">
                                <input type="checkbox" name="usuarios_notificados[]" value="{{ $usuario->id }}" class="usuario-checkbox">
                                <label class="text-sm text-gray-700">
                                    {{ $usuario->name }} - Clave: {{ $usuario->clave_empleado }} - Departamento: {{ $usuario->departamento->name ?? 'Sin departamento' }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="flex items-center justify-between pt-4">
                    <button type="submit" name="accion" value="rechazar"
                        @click="accionFinal='rechazar'"
                        class="px-6 py-3 text-white bg-red-600 rounded-lg hover:bg-red-700 font-medium">
                        Rechazar
                    </button>
                    <select name="sgi_tipo_actualizacion" required class="...">
                        <option value="revision">Solo Revisión</option>
                        <option value="version">Nueva Versión</option>
                    </select>

                    <button type="submit" name="accion" value="atender"
                        @click="accionFinal='atender'"
                        class="px-6 py-3 text-white bg-green-600 rounded-lg hover:bg-green-700 font-medium shadow">
                        Marcar como Atendido
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // ---- buscador usuarios ----
        const busqueda = document.getElementById('busqueda-usuario');
        const seleccionarTodos = document.getElementById('seleccionar-todos-usuarios');
        const checkboxes = document.querySelectorAll('.usuario-checkbox');

        busqueda?.addEventListener('input', function() {
            const filtro = busqueda.value.toLowerCase();
            document.querySelectorAll('.usuario-item').forEach(item => {
                const nombre = (item.getAttribute('data-name') || '').toLowerCase();
                const clave = (item.getAttribute('data-clave') || '').toLowerCase();
                const depto = (item.getAttribute('data-departamento') || '').toLowerCase();
                item.style.display = (nombre.includes(filtro) || clave.includes(filtro) || depto.includes(filtro)) ? '' : 'none';
            });
        });

        seleccionarTodos?.addEventListener('change', function() {
            const activo = seleccionarTodos.checked;
            checkboxes.forEach(c => c.checked = activo);
        });

        // ---- ✅ FIX: si fecha_revision viene vacía, usar fecha_version ----
        const form = document.querySelector('form[action*="/finalizar"]');
        const fechaV = document.getElementById('fecha_version');
        const fechaR = document.getElementById('fecha_revision');

        form?.addEventListener('submit', function() {
            // si fecha_revision está vacía, copia fecha_version
            if (fechaR && fechaV && (!fechaR.value || fechaR.value.trim() === '') && fechaV.value) {
                fechaR.value = fechaV.value;
            }
        });
    });
</script>