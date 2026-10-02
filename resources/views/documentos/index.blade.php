<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#6A2C75] to-[#D4A018]"></div>
                    <h2 class="text-3xl font-bold text-[#2d1033] tracking-tight">Documentos</h2>
                </div>
                <p class="text-sm text-[#6A2C75]/60 pl-4">Catálogo de documentos vigentes y su vigencia</p>
            </div>

            <div class="flex items-center gap-4 px-5 py-3.5 bg-white border border-[#6A2C75]/15 rounded-2xl shadow-sm">
                <span class="flex items-center justify-center w-11 h-11 rounded-full bg-gradient-to-br from-[#6A2C75] to-[#8e3d9e] text-white text-sm font-bold shadow-md">
                    {{ $docs->total() }}
                </span>
                <div class="flex flex-col">
                    <span class="text-[10px] text-[#6A2C75]/50 font-semibold uppercase tracking-widest">Total</span>
                    <span class="text-sm text-[#2d1033] font-semibold">{{ $docs->total() }} documentos</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border border-[#6A2C75]/10 shadow-sm overflow-hidden">
                @if($docs->isEmpty())
                <div class="py-16 text-center">
                    <p class="text-sm text-gray-400">No hay documentos registrados.</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#faf7fb] text-[11px] font-bold uppercase tracking-wider text-[#6A2C75]/60 border-b border-[#6A2C75]/10">
                            <tr>
                                <th class="px-5 py-3.5">Código</th>
                                <th class="px-5 py-3.5">Nombre</th>
                                <th class="px-5 py-3.5">Área</th>
                                <th class="px-5 py-3.5">Tipo</th>
                                <th class="px-5 py-3.5">Revisiones</th>
                                <th class="px-5 py-3.5">Vigencia</th>
                                <th class="px-5 py-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#6A2C75]/5 text-sm text-[#2d1033]">
                            @foreach($docs as $doc)
                            @php
                                $badgeDoc = match($doc->semaforo_vencimiento ?? 'sin_fecha') {
                                    'baja'     => 'bg-gray-200 text-gray-700 border-gray-300',
                                    'vencido'  => 'bg-red-600 text-white border-red-700',
                                    'critico'  => 'bg-red-50 text-red-700 border-red-200',
                                    'alerta'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'en_regla' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                    default    => 'bg-slate-50 text-slate-600 border-slate-200',
                                };
                                // Todas las revisiones de todas las versiones del documento, en el
                                // orden en que se capturaron (no hay límite: si son muchas, la fila
                                // simplemente crece para mostrarlas todas).
                                $revisionesDoc = $doc->versiones->flatMap->revisiones->sortBy('id')->values();
                            @endphp
                            <tr class="hover:bg-[#faf7fb] transition-colors">
                                <td class="px-5 py-3.5 font-mono font-semibold text-[#6A2C75]">{{ $doc->codigo }}</td>
                                <td class="px-5 py-3.5">{{ $doc->nombre }}</td>
                                <td class="px-5 py-3.5 text-gray-500">{{ $doc->area ?: '—' }}</td>
                                <td class="px-5 py-3.5 text-gray-500">{{ $doc->tipo_documento ?: '—' }}</td>
                                <td class="px-5 py-3.5">
                                    @if($revisionesDoc->isEmpty())
                                    <span class="text-xs text-gray-300">—</span>
                                    @else
                                    <div class="flex flex-wrap gap-1.5 max-w-xs">
                                        @foreach($revisionesDoc as $idx => $rev)
                                        @php
                                            $esObsoleta = $rev->estatus === 'obsoleta';
                                            $claseRev = $esObsoleta
                                                ? 'border-red-300 bg-red-50 text-red-600'
                                                : 'border-[#6A2C75]/15 bg-[#6A2C75]/5 text-[#6A2C75]';
                                        @endphp
                                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[10.5px] font-bold {{ $claseRev }}"
                                              title="{{ ($rev->revision_actual ? 'Revisión: '.$rev->revision_actual.' — ' : '').($esObsoleta ? 'Obsoleta' : 'Vigente') }}">
                                            Rev.{{ $idx }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold border {{ $badgeDoc }}">
                                        {{ $doc->etiqueta_vencimiento ?? 'Sin fecha' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('documentos.show', $doc->id) }}"
                                       class="inline-flex items-center gap-1 text-xs font-semibold text-[#6A2C75] hover:underline">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-[#6A2C75]/10">
                    {{ $docs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
