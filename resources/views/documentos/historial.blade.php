<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
            <div>
                <h2 class="text-3xl font-extrabold text-[#6A2C75] tracking-tight">
                    Historial: {{ $documento->codigo }}
                </h2>
                <p class="mt-1 text-sm font-medium text-gray-500">
                    {{ $documento->nombre }} • Registro de cambios y trazabilidad
                </p>
            </div>

            <a href="{{ route('documentos.show', $documento->id) }}"
                class="bg-white border border-[#6A2C75] text-[#6A2C75] hover:bg-[#6A2C75]/5 font-semibold py-2 px-5 rounded-xl shadow-sm transition-all duration-300 flex items-center gap-2 shrink-0">
                <i class="ti ti-arrow-left"></i> Volver al documento
            </a>
        </div>
    </x-slot>

    @php
    // Nombres legibles de los campos que se auditan.
    $etiquetas = [
        'codigo' => 'Código',
        'nombre' => 'Nombre',
        'tipo_documento' => 'Tipo de documento',
        'formato_el_pa' => 'EL / PA',
        'area' => 'Área',
        'sharepoint_site' => 'Sitio SharePoint',
        'sharepoint_folder' => 'Carpeta SharePoint',
        'estatus' => 'Estatus',
        'version_vigente_id' => 'Versión vigente (ID)',
        'documento_id' => 'Documento (ID)',
        'documento_version_id' => 'Versión (ID)',
        'version' => 'Versión',
        'revision_actual' => 'Revisión actual',
        'revision_anterior' => 'Revisión anterior',
        'fecha_version' => 'Fecha de versión',
        'fecha_revision' => 'Fecha de revisión',
        'vigencia_version_dias' => 'Vigencia de versión (días)',
        'vigencia_revision_dias' => 'Vigencia de revisión (días)',
        'fecha_vencimiento_version' => 'Vence versión',
        'fecha_vencimiento_revision' => 'Vence revisión',
        'alerta_version_enviada_para' => 'Alerta de versión enviada para',
        'alerta_revision_enviada_para' => 'Alerta de revisión enviada para',
        'liga_archivo' => 'Liga del archivo',
        'lugar_almacenamiento' => 'Lugar de almacenamiento',
        'archivo_storage' => 'Archivo local',
        'sharepoint_file_id' => 'ID de archivo SharePoint',
        'sharepoint_path' => 'Ruta SharePoint',
        'publicado_por' => 'Publicado por (ID)',
        'publicado_en' => 'Publicado en',
        'observaciones_sgi' => 'Observaciones SGI',
        'sp_drive_id' => 'Biblioteca SharePoint',
        'sp_item_id' => 'Elemento SharePoint',
        'sp_web_url' => 'URL en SharePoint',
        'sp_folder_path' => 'Carpeta destino SharePoint',
        'sp_estado' => 'Estado de publicación',
        'sp_error' => 'Error de publicación',
        'fecha_publicacion' => 'Fecha de publicación',
        'registrado_por' => 'Registrado por (ID)',
        'registrado_en' => 'Registrado en',
    ];

    $tipos = [
        \App\Models\Documento::class => 'Documento',
        \App\Models\DocumentoVersion::class => 'Versión',
        \App\Models\DocumentoRevision::class => 'Revisión',
    ];

    // Formato legible de un valor guardado en el log.
    $valor = function ($v) {
        if ($v === null || $v === '') return '—';
        if (is_bool($v)) return $v ? 'Sí' : 'No';
        if (is_array($v)) return implode(', ', array_map(fn ($x) => is_scalar($x) ? $x : json_encode($x), $v));

        // Fechas serializadas por los casts (2026-11-11T00:00:00.000000Z)
        if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}(T|\s)?/', $v)) {
            try {
                $f = \Carbon\Carbon::parse($v)->timezone('America/Mexico_City');
                return str_contains($v, '00:00:00') ? $f->format('d/m/Y') : $f->format('d/m/Y H:i');
            } catch (\Throwable $e) {
                return $v;
            }
        }

        return (string) $v;
    };

    $estilo = fn ($evento) => match ($evento) {
        'created' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'ti-plus'],
        'updated' => ['bg-amber-50 text-amber-700 border-amber-200', 'ti-pencil'],
        'deleted' => ['bg-red-50 text-red-700 border-red-200', 'ti-trash'],
        default   => ['bg-purple-50 text-[#6A2C75] border-purple-200', 'ti-flag'],
    };
    @endphp

    <div class="py-8">
        <div class="max-w-6xl px-4 mx-auto space-y-6 sm:px-6 lg:px-8">

            <div class="flex gap-3 px-5 py-4 text-sm border bg-cyan-50 border-cyan-200 text-cyan-800 rounded-2xl">
                <i class="ti ti-info-circle mt-0.5"></i>
                <span>
                    El registro de cambios está disponible a partir del <strong>11 de noviembre de 2026</strong>.
                    Las modificaciones anteriores a esa fecha no quedaron registradas.
                </span>
            </div>

            @forelse($actividades as $a)
                @php
                    [$clase, $icono] = $estilo($a->event);
                    $props = $a->properties ?? collect();
                    $nuevos = $props['attributes'] ?? [];
                    $viejos = $props['old'] ?? [];

                    $tipo = $tipos[$a->subject_type] ?? class_basename($a->subject_type);
                    $sujeto = match ($a->subject_type) {
                        \App\Models\DocumentoVersion::class => 'Versión ' . ($a->subject?->version ?? '#' . $a->subject_id),
                        \App\Models\DocumentoRevision::class => 'Revisión ' . ($a->subject?->revision_actual ?? '#' . $a->subject_id),
                        default => null,
                    };
                @endphp

                <div class="p-5 bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-xl border flex items-center justify-center {{ $clase }}">
                                <i class="ti {{ $icono }}"></i>
                            </span>
                            <div>
                                <p class="font-bold text-gray-800">
                                    {{ $a->description }}
                                    @if($sujeto)
                                        <span class="font-medium text-gray-500">· {{ $sujeto }}</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">
                                    <i class="mr-1 ti ti-user"></i>{{ $a->causer?->name ?? 'Sistema' }}
                                    <span class="mx-1">•</span>
                                    <i class="mr-1 ti ti-clock"></i>{{ $a->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        @if(!empty($props['solicitud_id']))
                            <a href="{{ route('solicitudes.show', $props['solicitud_id']) }}"
                                class="text-sm font-semibold text-[#6A2C75] hover:underline shrink-0">
                                SOL-{{ str_pad($props['solicitud_id'], 5, '0', STR_PAD_LEFT) }}
                                <i class="ml-1 text-xs ti ti-external-link"></i>
                            </a>
                        @endif
                    </div>

                    {{-- Cambios campo por campo (altas, modificaciones y eliminaciones) --}}
                    @if(!empty($nuevos) || !empty($viejos))
                        <details class="mt-4" @if($a->event === 'updated') open @endif>
                            <summary class="cursor-pointer text-sm font-semibold text-gray-600 hover:text-[#6A2C75]">
                                {{ $a->event === 'updated' ? 'Ver cambios' : 'Ver datos registrados' }}
                            </summary>

                            <div class="mt-3 overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-xs tracking-wide text-left text-gray-500 uppercase border-b">
                                            <th class="py-2 pr-4">Campo</th>
                                            @if($a->event === 'updated')
                                                <th class="py-2 pr-4">Antes</th>
                                            @endif
                                            <th class="py-2">{{ $a->event === 'updated' ? 'Después' : 'Valor' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(($a->event === 'deleted' ? $viejos : $nuevos) as $campo => $nuevo)
                                            <tr class="align-top border-b last:border-0">
                                                <td class="py-2 pr-4 font-medium text-gray-700 whitespace-nowrap">{{ $etiquetas[$campo] ?? $campo }}</td>
                                                @if($a->event === 'updated')
                                                    <td class="py-2 pr-4 text-red-700 break-all">{{ $valor($viejos[$campo] ?? null) }}</td>
                                                @endif
                                                <td class="py-2 break-all text-emerald-700">{{ $valor($nuevo) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @elseif($props->except('solicitud_id')->isNotEmpty())
                        {{-- Registros resumen (baja, nueva versión publicada) --}}
                        <div class="mt-3 space-y-1 text-sm text-gray-600">
                            @if(isset($props['tipo_cambio']))
                                <p><span class="font-medium">Tipo de cambio:</span> {{ ucfirst($props['tipo_cambio']) }}</p>
                            @endif
                            @if(!empty($props['versiones_obsoletas']) || !empty($props['versiones']))
                                <p><span class="font-medium">Versiones afectadas (ID):</span> {{ $valor($props['versiones_obsoletas'] ?? $props['versiones']) }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center text-gray-500 bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <i class="mb-3 text-3xl text-gray-300 ti ti-history"></i>
                    <p>Este documento aún no tiene cambios registrados.</p>
                </div>
            @endforelse

            <div>
                {{ $actividades->links() }}
            </div>
        </div>
    </div>
</x-app-layout>