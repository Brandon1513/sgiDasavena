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
                    tipoCambio: '{{ old('tipo_cambio', 'revision') }}',
                    isAtender() { return this.accionFinal === 'atender' },
                    isRechazar() { return this.accionFinal === 'rechazar' },
                    isSoloVersion() { return this.tipoCambio === 'version' },
                    isCambioRevision() { return this.tipoCambio === 'revision' },
                }">
                @csrf

                {{-- SECCIÓN 1: RESUMEN --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Resumen de la Solicitud</h3>
                    </div>

                    <div class="px-6 py-5 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Acción</label>
                                <input type="text" value="{{ strtoupper($solicitud->accion) }}" readonly class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Elaboró</label>
                                <input type="text" value="{{ $solicitud->usuario->name }}" readonly class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Aprobó (Jefe)</label>
                                <input type="text" value="{{ $solicitud->jefe ? $solicitud->jefe->name : 'No asignado' }}" readonly class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Comentarios del solicitante</label>
                            <textarea readonly rows="3" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed resize-none">{{ $solicitud->comentarios }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 2: DATOS DE SGI --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Datos Oficiales (SGI)</h3>
                        <p class="text-xs text-gray-600 mt-1">Solo se requieren al “Atender”.</p>
                    </div>

                    <div class="px-6 py-5 space-y-6">
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de cambio realizado por SGI</label>
                            <select name="tipo_cambio" x-model="tipoCambio" :disabled="isRechazar()" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 transition">
                                <option value="revision">Cambió revisión (y también la versión)</option>
                                <option value="version">Solo cambió la versión</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alta en el sistema de gestión</label>
                            <input type="date" name="fecha_alta_sgi" value="{{ old('fecha_alta_sgi', optional($solicitud->fecha_alta_sgi)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" :disabled="isRechazar()" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="isCambioRevision()" x-transition>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Número de Revisión Actual <span class="text-red-500" x-show="isAtender()">*</span></label>
                                <input type="text" name="revision_actual" value="{{ old('revision_actual', $solicitud->revision_actual) }}" :disabled="isRechazar() || isSoloVersion()" :required="isAtender() && isCambioRevision()" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Número de Revisión Anterior</label>
                                <input type="text" name="revision_anterior" value="{{ old('revision_anterior', $solicitud->revision_anterior) }}" :disabled="isRechazar() || isSoloVersion()" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Código de documento <span class="text-red-500" x-show="isAtender()">*</span></label>
                            <input type="text" name="codigo_documento" value="{{ old('codigo_documento', $solicitud->codigo_documento) }}" :disabled="isRechazar()" :required="isAtender()" placeholder="Ej. SGI-PR-001" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de versión <span class="text-red-500" x-show="isAtender()">*</span></label>
                                <input type="date" name="fecha_version" id="fecha_version" value="{{ old('fecha_version', optional($solicitud->fecha_version)->format('Y-m-d')) }}" :required="isAtender()" :disabled="isRechazar()" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div x-show="isCambioRevision()" x-transition>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Fecha de revisión</label>
                                <input type="date" name="fecha_revision" id="fecha_revision" value="{{ old('fecha_revision', optional($solicitud->fecha_revision)->format('Y-m-d')) }}" :disabled="isRechazar() || mismaFechaRevision || isSoloVersion()" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Vigencia versión (días) <span class="text-red-500" x-show="isAtender()">*</span></label>
                                <input type="number" name="vigencia_version_dias" value="{{ old('vigencia_version_dias', $solicitud->vigencia_version_dias ?? 365) }}" :required="isAtender()" :disabled="isRechazar()" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div x-show="isCambioRevision()" x-transition>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Vigencia revisión (días) <span class="text-red-500" x-show="isAtender() && isCambioRevision()">*</span></label>
                                <input type="number" name="vigencia_revision_dias" value="{{ old('vigencia_revision_dias', $solicitud->vigencia_revision_dias ?? 730) }}" :required="isAtender() && isCambioRevision()" :disabled="isRechazar() || isSoloVersion()" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medio de archivo (Liga SharePoint) <span class="text-red-500" x-show="isAtender()">*</span></label>
                            <input type="text" name="liga_archivo" value="{{ old('liga_archivo', $solicitud->liga_archivo) }}" :disabled="isRechazar()" :required="isAtender()" placeholder="https://..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 3: NOTIFICACIONES --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-amber-50 to-amber-100 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Notificación a Usuarios</h3>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <input type="text" id="busqueda-usuario" placeholder="Buscar usuario..." class="w-full border-gray-300 rounded-lg shadow-sm">
                        <div id="lista-usuarios" class="h-64 overflow-y-auto border border-gray-300 rounded-lg p-3 bg-slate-50">
                            @foreach($usuarios as $usuario)
                            <div class="usuario-item py-1" data-name="{{ $usuario->name }}" data-clave="{{ $usuario->clave_empleado }}">
                                <input type="checkbox" name="usuarios_notificados[]" value="{{ $usuario->id }}" class="usuario-checkbox">
                                <label class="text-sm text-gray-700">{{ $usuario->name }} - {{ $usuario->clave_empleado }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="flex items-center justify-between pt-4">
                    {{-- Botón de Rechazo con formnovalidate --}}
                    <button type="submit" name="accion" value="rechazar" 
                        @click="accionFinal='rechazar'" 
                        formnovalidate
                        class="px-6 py-3 text-white bg-red-600 rounded-lg hover:bg-red-700 font-medium">
                        Rechazar
                    </button>

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
        const busqueda = document.getElementById('busqueda-usuario');
        busqueda?.addEventListener('input', function() {
            const filtro = busqueda.value.toLowerCase();
            document.querySelectorAll('.usuario-item').forEach(item => {
                const nombre = (item.getAttribute('data-name') || '').toLowerCase();
                const clave = (item.getAttribute('data-clave') || '').toLowerCase();
                item.style.display = (nombre.includes(filtro) || clave.includes(filtro)) ? '' : 'none';
            });
        });

        const form = document.querySelector('form');
        form?.addEventListener('submit', function(e) {
            // Solo aplicamos lógica de autocompletado si NO es rechazo
            const accion = document.activeElement.getAttribute('value');
            if(accion === 'atender') {
                const fechaV = document.getElementById('fecha_version');
                const fechaR = document.getElementById('fecha_revision');
                if (fechaR && fechaV && !fechaR.value && fechaV.value) {
                    fechaR.value = fechaV.value;
                }
            }
        });
    });
</script>