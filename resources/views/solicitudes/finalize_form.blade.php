<x-app-layout>
@php
    $doc = $solicitud->documento;
    $verVigente = $doc?->versionVigente;
    $esBaja = $solicitud->accion === 'baja';

    $acciones = [
        'actualizacion'   => ['label' => 'Actualización de documento', 'icon' => 'ti-refresh',   'class' => 'border-white/25 bg-white/10 text-white'],
        'baja'            => ['label' => 'Baja de documento',          'icon' => 'ti-trash',     'class' => 'border-rose-300/40 bg-rose-400/20 text-rose-100'],
        'nuevo_documento' => ['label' => 'Alta de documento nuevo',    'icon' => 'ti-file-plus', 'class' => 'border-dasavena-gold/40 bg-dasavena-gold/20 text-dasavena-gold'],
    ];
    $accionInfo = $acciones[$solicitud->accion] ?? ['label' => ucfirst((string) $solicitud->accion), 'icon' => 'ti-file', 'class' => 'border-white/25 bg-white/10 text-white'];

    $codigo = $doc->codigo ?? $solicitud->codigo_documento;
    $nombre = $doc->nombre ?? $solicitud->nombre_documento;
    $notificadosOld = collect(old('usuarios_notificados', []))->map(fn ($id) => (int) $id)->all();
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">

{{-- ─── FONDO + LUCES AMBIENTALES (igual que el dashboard) ─── --}}
<div class="relative min-h-screen bg-cover bg-center bg-fixed" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');">
    <div class="pointer-events-none fixed inset-0 z-[1] bg-[radial-gradient(ellipse_90%_55%_at_50%_-5%,rgba(255,255,255,.55)_0%,transparent_65%)]"></div>
    <div class="pointer-events-none fixed -left-20 -top-24 z-[1] h-[420px] w-[420px] rounded-full bg-dasavena-purple/10 blur-3xl"></div>
    <div class="pointer-events-none fixed -right-24 top-[22%] z-[1] h-[380px] w-[380px] rounded-full bg-dasavena-gold/10 blur-3xl"></div>

    <div class="relative z-10 mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 sm:px-6 lg:px-8">

        {{-- ══════════════ HERO ══════════════ --}}
        <header class="relative overflow-hidden rounded-[28px] border border-white/15 bg-gradient-to-br from-dasavena-purple-dark via-dasavena-purple to-[#250a2c] p-7 shadow-[0_12px_40px_rgba(74,30,82,0.3),inset_0_1px_2px_rgba(255,255,255,0.25)] sm:p-9">
            <div class="absolute inset-x-0 top-0 h-[2px] bg-gradient-to-r from-transparent via-dasavena-gold to-transparent"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[.06]" style="background-image:radial-gradient(rgba(255,255,255,.9) 1.5px, transparent 1.5px); background-size:22px 22px;"></div>
            <div class="pointer-events-none absolute -bottom-20 -right-20 h-72 w-72 rounded-full bg-dasavena-gold/15 blur-3xl"></div>

            <div class="relative z-[1] flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex flex-col gap-3">
                    <a href="{{ route('solicitudes.index') }}" class="inline-flex w-fit items-center gap-1.5 text-[12px] font-semibold text-white/60 transition hover:text-white">
                        <i class="ti ti-arrow-left text-[14px]"></i> Volver a solicitudes
                    </a>
                    <div class="inline-flex w-fit items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[.18em] text-dasavena-gold backdrop-blur-2xl">
                        <span class="h-[7px] w-[7px] rounded-full bg-dasavena-gold shadow-[0_0_8px_#D6A644] animate-pulse"></span>
                        Validación SGI
                    </div>
                    <h1 class="font-display text-[clamp(1.6rem,3.2vw,2.3rem)] font-extrabold leading-tight tracking-tight text-white/95">
                        Finalizar solicitud <span class="text-dasavena-gold">#{{ $solicitud->id }}</span>
                    </h1>
                    <p class="max-w-2xl text-sm leading-relaxed text-white/70">
                        @if($esBaja)
                            Última validación antes de dar de baja el documento y retirar sus archivos a Obsoletos.
                        @else
                            Captura los datos oficiales, publica el archivo en SharePoint y retira a Obsoletos lo que queda sustituido.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-[12px] font-semibold {{ $accionInfo['class'] }}">
                        <i class="ti {{ $accionInfo['icon'] }} text-[14px]"></i> {{ $accionInfo['label'] }}
                    </span>
                    @if($codigo)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 font-mono text-[12px] text-white">
                            <i class="ti ti-hash text-[14px] text-white/60"></i>{{ $codigo }}
                        </span>
                    @endif
                </div>
            </div>
        </header>

        {{-- ══════════════ ERRORES ══════════════ --}}
        @if ($errors->any())
            <div class="flex gap-3 rounded-[22px] border border-rose-200 bg-rose-50/90 p-4 shadow-sm backdrop-blur-xl">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-rose-200 bg-white text-rose-600"><i class="ti ti-alert-circle text-[18px]"></i></span>
                <div>
                    <p class="text-[13px] font-bold text-rose-700">Revisa lo siguiente antes de continuar</p>
                    <ul class="mt-1 list-inside list-disc text-[13px] text-rose-700/90">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="form-finalizar" action="{{ route('solicitudes.finalize', $solicitud->id) }}" method="POST" enctype="multipart/form-data"
              class="grid grid-cols-1 gap-6 lg:grid-cols-12"
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

            {{-- ═════════════ COLUMNA PRINCIPAL ═════════════ --}}
            <div class="flex flex-col gap-6 lg:col-span-8">

                @if($esBaja)
                    {{-- ─── BAJA: documento a decomisionar ─── --}}
                    <x-dashboard.panel class="p-6">
                        <div class="c-section-head">
                            <span class="c-section-icon bg-rose-50 border-rose-200 text-rose-600"><i class="ti ti-file-off"></i></span>
                            <div>
                                <h2 class="font-display text-base font-bold text-indigo-950">Documento a dar de baja</h2>
                                <p class="text-xs text-gray-400">Al atender, el documento y su versión/revisión vigentes quedarán como baja/obsoletos.</p>
                            </div>
                        </div>

                        <dl class="mt-5 grid grid-cols-2 gap-3 md:grid-cols-3">
                            <div class="c-field"><dt>Código</dt><dd class="font-mono">{{ $codigo ?? '—' }}</dd></div>
                            <div class="c-field col-span-2"><dt>Nombre</dt><dd>{{ $nombre ?? '—' }}</dd></div>
                            <div class="c-field"><dt>Área</dt><dd>{{ $doc->area ?? '—' }}</dd></div>
                            <div class="c-field"><dt>Versión vigente</dt><dd class="font-mono">{{ $verVigente->version ?? '—' }}</dd></div>
                            <div class="c-field"><dt>Revisión actual</dt><dd class="font-mono">{{ $verVigente->revision_actual ?? '—' }}</dd></div>
                            <div class="c-field"><dt>Estatus actual</dt><dd>{{ strtoupper($doc->estatus ?? '—') }}</dd></div>
                            <div class="c-field"><dt>Vence versión</dt><dd class="font-mono">{{ optional($verVigente?->fecha_vencimiento_version)->format('Y-m-d') ?? '—' }}</dd></div>
                            <div class="c-field"><dt>Vence revisión</dt><dd class="font-mono">{{ optional($verVigente?->fecha_vencimiento_revision)->format('Y-m-d') ?? '—' }}</dd></div>
                        </dl>

                        @if($verVigente?->liga_archivo)
                            <a href="{{ $verVigente->liga_archivo }}" target="_blank" rel="noopener" class="mt-3 flex items-center gap-2 truncate rounded-2xl border border-white/80 bg-white/60 px-4 py-3 text-[13px] text-dasavena-purple hover:underline">
                                <i class="ti ti-link text-[15px]"></i><span class="truncate">{{ $verVigente->liga_archivo }}</span>
                            </a>
                        @endif

                        <div class="mt-3 rounded-2xl border border-rose-100 bg-rose-50/50 p-4">
                            <p class="text-[10.5px] font-bold uppercase tracking-widest text-rose-400">Motivo de la baja (solicitante)</p>
                            <p class="mt-1 whitespace-pre-line text-[13px] leading-relaxed text-gray-700">{{ $solicitud->motivo_baja ?: '—' }}</p>
                        </div>
                    </x-dashboard.panel>

                    <x-dashboard.panel class="p-6">
                        <div class="c-section-head">
                            <span class="c-section-icon bg-dasavena-gold/15 border-dasavena-gold/25 text-dasavena-gold-dark"><i class="ti ti-brand-onedrive"></i></span>
                            <div>
                                <h2 class="font-display text-base font-bold text-indigo-950">Archivos en SharePoint</h2>
                                <p class="text-xs text-gray-400">Lo que se retira a «Sistema de Gestión Obsoleto» al dar de baja.</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            @include('solicitudes.partials.sharepoint-picker', ['modo' => 'archivos', 'carpetaSugerida' => $carpetaSugerida, 'vigenteItemId' => $verVigente?->sp_item_id])
                        </div>
                    </x-dashboard.panel>
                @else
                    {{-- ─── DATOS OFICIALES ─── --}}
                    <x-dashboard.panel class="p-6">
                        <div class="c-section-head">
                            <span class="c-section-icon bg-dasavena-purple/10 border-dasavena-purple/15 text-dasavena-purple"><i class="ti ti-clipboard-check"></i></span>
                            <div>
                                <h2 class="font-display text-base font-bold text-indigo-950">Datos oficiales (SGI)</h2>
                                <p class="text-xs text-gray-400">Solo se requieren al atender. Alimentan el calendario de vencimientos.</p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-5">
                            {{-- Tipo de cambio --}}
                            <div>
                                <span class="c-label">Tipo de cambio realizado por SGI</span>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="c-choice" :class="isCambioRevision() && 'is-on'">
                                        <input type="radio" name="tipo_cambio" value="revision" x-model="tipoCambio" :disabled="isRechazar()" class="sr-only">
                                        <i class="ti ti-git-commit text-[20px]"></i>
                                        <span><strong>Cambió la revisión</strong><small>y también la versión</small></span>
                                    </label>
                                    <label class="c-choice" :class="isSoloVersion() && 'is-on'">
                                        <input type="radio" name="tipo_cambio" value="version" x-model="tipoCambio" :disabled="isRechazar()" class="sr-only">
                                        <i class="ti ti-versions text-[20px]"></i>
                                        <span><strong>Solo cambió la versión</strong><small>la revisión se conserva</small></span>
                                    </label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="c-label" for="codigo_documento">Código de documento <span class="text-rose-500" x-show="isAtender()">*</span></label>
                                    <input type="text" id="codigo_documento" name="codigo_documento" value="{{ old('codigo_documento', $solicitud->codigo_documento ?? $doc?->codigo) }}" :disabled="isRechazar()" :required="isAtender()" placeholder="Ej. SGI-PR-001" class="c-input font-mono">
                                </div>
                                <div>
                                    <label class="c-label" for="fecha_alta_sgi">Alta en el sistema de gestión</label>
                                    <input type="date" id="fecha_alta_sgi" name="fecha_alta_sgi" value="{{ old('fecha_alta_sgi', optional($solicitud->fecha_alta_sgi)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" :disabled="isRechazar()" class="c-input">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" x-show="isCambioRevision()" x-transition>
                                <div>
                                    <label class="c-label" for="revision_actual">Revisión actual <span class="text-rose-500" x-show="isAtender()">*</span></label>
                                    <input type="text" id="revision_actual" name="revision_actual" value="{{ old('revision_actual', $solicitud->revision_actual ?? $verVigente?->revision_actual) }}" :disabled="isRechazar() || isSoloVersion()" :required="isAtender() && isCambioRevision()" class="c-input font-mono">
                                </div>
                                <div>
                                    <label class="c-label" for="revision_anterior">Revisión anterior</label>
                                    <input type="text" id="revision_anterior" name="revision_anterior" value="{{ old('revision_anterior', $solicitud->revision_anterior ?? $verVigente?->revision_anterior) }}" :disabled="isRechazar() || isSoloVersion()" class="c-input font-mono">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="c-label" for="fecha_version">Fecha de versión <span class="text-rose-500" x-show="isAtender()">*</span></label>
                                    <input type="date" name="fecha_version" id="fecha_version" value="{{ old('fecha_version', optional($solicitud->fecha_version ?? $verVigente?->fecha_version)->format('Y-m-d')) }}" :required="isAtender()" :disabled="isRechazar()" class="c-input">
                                </div>
                                <div x-show="isCambioRevision()" x-transition>
                                    <div class="mb-1.5 flex items-center justify-between gap-2">
                                        <label class="c-label !mb-0" for="fecha_revision">Fecha de revisión <span class="text-rose-500" x-show="isAtender() && isCambioRevision()">*</span></label>
                                        <label class="inline-flex cursor-pointer items-center gap-1.5 text-[11.5px] font-medium text-gray-500">
                                            <input type="checkbox" x-model="mismaFechaRevision" class="c-check"> Igual a la de versión
                                        </label>
                                    </div>
                                    <input type="date" name="fecha_revision" id="fecha_revision" value="{{ old('fecha_revision', optional($solicitud->fecha_revision ?? $verVigente?->fecha_revision)->format('Y-m-d')) }}" :disabled="isRechazar() || mismaFechaRevision || isSoloVersion()" :required="isAtender() && isCambioRevision()" class="c-input">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="c-label" for="vigencia_version_dias">Vigencia versión (días) <span class="text-rose-500" x-show="isAtender()">*</span></label>
                                    <input type="number" id="vigencia_version_dias" name="vigencia_version_dias" value="{{ old('vigencia_version_dias', $solicitud->vigencia_version_dias ?? $verVigente?->vigencia_version_dias ?? 365) }}" :required="isAtender()" :disabled="isRechazar()" class="c-input font-mono">
                                </div>
                                <div x-show="isCambioRevision()" x-transition>
                                    <label class="c-label" for="vigencia_revision_dias">Vigencia revisión (días) <span class="text-rose-500" x-show="isAtender() && isCambioRevision()">*</span></label>
                                    <input type="number" id="vigencia_revision_dias" name="vigencia_revision_dias" value="{{ old('vigencia_revision_dias', $solicitud->vigencia_revision_dias ?? $verVigente?->vigencia_revision_dias ?? 730) }}" :required="isAtender() && isCambioRevision()" :disabled="isRechazar() || isSoloVersion()" class="c-input font-mono">
                                </div>
                                <div>
                                    <label class="c-label" for="formato_el_pa">Formato <span class="text-rose-500" x-show="isAtender()">*</span></label>
                                    @php $formatoActual = old('formato_el_pa', $solicitud->formato_el_pa ?? $doc?->formato_el_pa); @endphp
                                    <select id="formato_el_pa" name="formato_el_pa" :disabled="isRechazar()" :required="isAtender()" class="c-input">
                                        <option value="">Seleccionar…</option>
                                        <option value="EL" {{ $formatoActual === 'EL' ? 'selected' : '' }}>EL · Electrónico</option>
                                        <option value="PA" {{ $formatoActual === 'PA' ? 'selected' : '' }}>PA · Papel</option>
                                        <option value="EL/PA" {{ $formatoActual === 'EL/PA' ? 'selected' : '' }}>EL/PA · Ambos</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="c-label" for="liga_archivo">Medio de archivo (liga SharePoint) <span class="text-rose-500" x-show="isAtender()">*</span></label>
                                <input type="text" id="liga_archivo" name="liga_archivo" value="{{ old('liga_archivo', $solicitud->liga_archivo ?? $verVigente?->liga_archivo) }}" :disabled="isRechazar()" :required="isAtender()" placeholder="https://…" class="c-input">
                                <p class="c-hint">Solo como referencia externa; no se usa para publicar el archivo en SharePoint.</p>
                            </div>
                        </div>
                    </x-dashboard.panel>

                    {{-- ─── PUBLICACIÓN EN SHAREPOINT ─── --}}
                    <x-dashboard.panel class="p-6">
                        <div class="c-section-head">
                            <span class="c-section-icon bg-dasavena-gold/15 border-dasavena-gold/25 text-dasavena-gold-dark"><i class="ti ti-brand-onedrive"></i></span>
                            <div>
                                <h2 class="font-display text-base font-bold text-indigo-950">Publicación en SharePoint</h2>
                                <p class="text-xs text-gray-400">Qué archivo se publica, dónde, y qué se retira a Obsoletos.</p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-5">
                            <div x-data="{ usarAdjunto: {{ $solicitud->archivo_adjunto ? 'true' : 'false' }} }">
                                <span class="c-label">Archivo oficial de esta versión</span>

                                @if($solicitud->archivo_adjunto)
                                    <div x-show="usarAdjunto" class="flex items-center gap-3 rounded-2xl border border-white/80 bg-white/60 p-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-600"><i class="ti ti-paperclip text-[18px]"></i></span>
                                        <a href="{{ Storage::url($solicitud->archivo_adjunto) }}" target="_blank" rel="noopener" class="min-w-0 flex-1 truncate text-[13px] font-medium text-dasavena-purple hover:underline">
                                            {{ basename($solicitud->archivo_adjunto) }}
                                        </a>
                                        <button type="button" @click="usarAdjunto = false" :disabled="isRechazar()" class="c-btn-soft">
                                            <i class="ti ti-replace text-[15px]"></i> Reemplazar
                                        </button>
                                    </div>
                                    <p class="c-hint" x-show="usarAdjunto">Se publicará en SharePoint este archivo, el que el solicitante ya adjuntó con su solicitud.</p>
                                @endif

                                <div x-show="!usarAdjunto" @if($solicitud->archivo_adjunto) x-cloak style="display:none;" @endif>
                                    <label class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border-2 border-dashed border-dasavena-purple/20 bg-white/50 px-4 py-6 text-center transition hover:border-dasavena-purple/40 hover:bg-white/70"
                                           x-data="{ nombre: '' }">
                                        <i class="ti ti-cloud-upload text-[26px] text-dasavena-purple"></i>
                                        <span class="text-[13px] font-semibold text-indigo-950" x-text="nombre || 'Selecciona el archivo oficial'"></span>
                                        <span class="text-[11.5px] text-gray-400">PDF, Word, Excel o imagen · hasta 100 MB</span>
                                        <input type="file" name="archivo_oficial" :disabled="isRechazar()" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="sr-only"
                                               @change="nombre = $event.target.files[0]?.name || ''">
                                    </label>
                                    @if($solicitud->archivo_adjunto)
                                        <button type="button" @click="usarAdjunto = true" class="mt-1.5 text-[12px] font-bold text-dasavena-purple hover:underline">
                                            ← Usar mejor el archivo de la solicitud
                                        </button>
                                    @else
                                        <p class="c-hint font-semibold text-rose-600">La solicitud no tiene un archivo adjunto: si no subes uno aquí, la versión queda sin publicar en SharePoint.</p>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="c-label" for="sp_nombre_archivo">Nombre del archivo en SharePoint</label>
                                <input type="text" id="sp_nombre_archivo" name="sp_nombre_archivo" value="{{ old('sp_nombre_archivo') }}" :disabled="isRechazar()" placeholder="Se autogenera si lo dejas vacío (código_V_R_fecha)" class="c-input">
                                <p class="c-hint">La extensión real del archivo se conserva aunque no la escribas.</p>
                            </div>

                            @include('solicitudes.partials.sharepoint-picker', ['modo' => 'carpeta', 'carpetaSugerida' => $carpetaSugerida, 'vigenteItemId' => $verVigente?->sp_item_id])
                        </div>
                    </x-dashboard.panel>
                @endif

                {{-- ─── NOTIFICACIÓN ─── --}}
                <x-dashboard.panel class="p-6" x-data="{ marcados: {{ count($notificadosOld) }} }">
                    <div class="c-section-head">
                        <span class="c-section-icon bg-indigo-50 border-indigo-200 text-indigo-600"><i class="ti ti-mail-forward"></i></span>
                        <div class="flex-1">
                            <h2 class="font-display text-base font-bold text-indigo-950">Notificación a usuarios</h2>
                            <p class="text-xs text-gray-400">Recibirán un aviso por correo al atender la solicitud.</p>
                        </div>
                        <span class="rounded-full border border-dasavena-purple/15 bg-dasavena-purple/10 px-3 py-1 font-mono text-[12px] font-bold text-dasavena-purple" x-text="marcados + ' seleccionados'"></span>
                    </div>

                    <div class="relative mt-5">
                        <i class="ti ti-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[16px] text-gray-400"></i>
                        <input type="text" id="busqueda-usuario" placeholder="Buscar por nombre o clave de empleado…" class="c-input !pl-10">
                    </div>
                    <div id="lista-usuarios" class="mt-3 grid max-h-72 grid-cols-1 gap-1 overflow-y-auto rounded-2xl border border-white/80 bg-white/50 p-2 sm:grid-cols-2"
                         @change="marcados = $el.querySelectorAll('input:checked').length">
                        @foreach($usuarios as $usuario)
                            <label class="usuario-item flex cursor-pointer items-center gap-2.5 rounded-xl px-3 py-2 hover:bg-white/80" data-name="{{ $usuario->name }}" data-clave="{{ $usuario->clave_empleado }}">
                                <input type="checkbox" name="usuarios_notificados[]" value="{{ $usuario->id }}" class="usuario-checkbox c-check" @checked(in_array($usuario->id, $notificadosOld, true))>
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-dasavena-purple-light to-dasavena-purple-dark text-[11px] font-bold text-white">{{ mb_strtoupper(mb_substr($usuario->name, 0, 1)) }}</span>
                                <span class="min-w-0">
                                    <span class="block truncate text-[13px] text-indigo-950">{{ $usuario->name }}</span>
                                    <span class="block font-mono text-[11px] text-gray-400">{{ $usuario->clave_empleado }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </x-dashboard.panel>
            </div>

            {{-- ═════════════ COLUMNA LATERAL ═════════════ --}}
            <aside class="flex flex-col gap-6 lg:col-span-4">
                <div class="flex flex-col gap-6 lg:sticky lg:top-6">
                    {{-- Resumen --}}
                    <x-dashboard.panel class="p-6">
                        <div class="c-section-head">
                            <span class="c-section-icon bg-dasavena-purple/10 border-dasavena-purple/15 text-dasavena-purple"><i class="ti ti-file-description"></i></span>
                            <div>
                                <h2 class="font-display text-base font-bold text-indigo-950">Resumen</h2>
                                <p class="text-xs text-gray-400">Solicitud recibida {{ optional($solicitud->created_at)->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        <dl class="mt-5 space-y-2">
                            @if($nombre)
                                <div class="c-field"><dt>Documento</dt><dd>{{ $nombre }}</dd></div>
                            @endif
                            <div class="grid grid-cols-2 gap-2">
                                <div class="c-field"><dt>Elaboró</dt><dd>{{ $solicitud->usuario->name ?? '—' }}</dd></div>
                                <div class="c-field"><dt>Aprobó (jefe)</dt><dd>{{ $solicitud->jefe->name ?? 'No asignado' }}</dd></div>
                            </div>
                            @if($verVigente)
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="c-field"><dt>Versión vigente</dt><dd class="font-mono">{{ $verVigente->version ?? '—' }}</dd></div>
                                    <div class="c-field"><dt>Revisión</dt><dd class="font-mono">{{ $verVigente->revision_actual ?? '—' }}</dd></div>
                                </div>
                            @endif
                        </dl>

                        <div class="mt-2 rounded-2xl border border-white/80 bg-white/50 p-4">
                            <p class="text-[10.5px] font-bold uppercase tracking-widest text-gray-400">Comentarios del solicitante</p>
                            <p class="mt-1 max-h-40 overflow-y-auto whitespace-pre-line text-[13px] leading-relaxed text-gray-700">{{ $solicitud->comentarios ?: '—' }}</p>
                        </div>
                    </x-dashboard.panel>

                    {{-- Decisión --}}
                    <x-dashboard.panel class="p-6">
                        <h2 class="font-display text-base font-bold text-indigo-950">Decisión</h2>
                        <p class="mt-0.5 text-xs text-gray-400">
                            @if($esBaja)
                                Atender da de baja el documento y mueve sus archivos a Obsoletos.
                            @else
                                Atender publica la versión y retira la anterior a Obsoletos.
                            @endif
                        </p>

                        <div class="mt-5 flex flex-col gap-2.5">
                            <button type="submit" name="accion" value="atender" @click="accionFinal='atender'"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-dasavena-purple-light to-dasavena-purple-dark px-5 py-3.5 text-sm font-bold text-white shadow-[0_10px_28px_rgba(106,44,117,0.35)] transition hover:-translate-y-0.5 hover:shadow-[0_14px_34px_rgba(106,44,117,0.45)]">
                                <i class="ti ti-circle-check text-[18px]"></i> Marcar como atendida
                            </button>
                            {{-- formnovalidate: rechazar no exige los campos obligatorios --}}
                            <button type="submit" name="accion" value="rechazar" @click="accionFinal='rechazar'" formnovalidate
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-white/70 px-5 py-3 text-sm font-bold text-rose-600 transition hover:bg-rose-50">
                                <i class="ti ti-circle-x text-[18px]"></i> Rechazar
                            </button>
                        </div>
                    </x-dashboard.panel>
                </div>
            </aside>
        </form>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .c-label { display: block; margin-bottom: .4rem; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .c-hint { margin-top: .35rem; font-size: 12px; color: #9ca3af; }
    .c-input {
        width: 100%; border-radius: 14px; border: 1px solid rgba(106,44,117,.14); background: rgba(255,255,255,.8);
        padding: .65rem .9rem; font-size: 14px; color: #1e1b4b; box-shadow: inset 0 1px 2px rgba(74,30,82,.04);
        transition: border-color .2s, box-shadow .2s, background .2s;
    }
    .c-input:focus { outline: none; border-color: #6A2C75; background: #fff; box-shadow: 0 0 0 4px rgba(106,44,117,.12); }
    .c-input:disabled { background: rgba(243,244,246,.7); color: #9ca3af; cursor: not-allowed; }
    .c-check { width: 1rem; height: 1rem; border-radius: 5px; border-color: rgba(106,44,117,.3); color: #6A2C75; }
    .c-check:focus { box-shadow: 0 0 0 3px rgba(106,44,117,.15); }
    .c-check-gold { color: #b38600; border-color: rgba(179,134,0,.4); }
    .c-section-head { display: flex; align-items: flex-start; gap: .85rem; }
    .c-section-icon { display: flex; height: 2.5rem; width: 2.5rem; flex-shrink: 0; align-items: center; justify-content: center; border-radius: 14px; border-width: 1px; font-size: 19px; box-shadow: inset 0 1px 1px rgba(255,255,255,.8); }
    .c-field { border-radius: 14px; border: 1px solid rgba(255,255,255,.8); background: rgba(255,255,255,.55); padding: .6rem .85rem; }
    .c-field dt { font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #9ca3af; }
    .c-field dd { margin-top: .15rem; font-size: 13px; font-weight: 600; color: #1e1b4b; overflow-wrap: anywhere; }
    .c-choice { display: flex; align-items: center; gap: .75rem; cursor: pointer; border-radius: 18px; border: 1px solid rgba(106,44,117,.12); background: rgba(255,255,255,.55); padding: .85rem 1rem; color: #9ca3af; transition: all .2s; }
    .c-choice span { display: flex; flex-direction: column; font-size: 13px; color: #374151; }
    .c-choice small { font-size: 11.5px; color: #9ca3af; }
    .c-choice:hover { border-color: rgba(106,44,117,.3); }
    .c-choice.is-on { border-color: #6A2C75; background: rgba(106,44,117,.07); color: #6A2C75; box-shadow: 0 0 0 3px rgba(106,44,117,.1); }
    .c-choice:focus-within { box-shadow: 0 0 0 3px rgba(106,44,117,.2); }
    .c-btn-soft { display: inline-flex; align-items: center; gap: .35rem; border-radius: 12px; background: rgba(106,44,117,.09); padding: .45rem .8rem; font-size: 12px; font-weight: 700; color: #6A2C75; transition: background .2s; white-space: nowrap; }
    .c-btn-soft:hover { background: rgba(106,44,117,.16); }
    .c-btn-soft:disabled { opacity: .5; cursor: not-allowed; }
    .c-btn-gold { background: rgba(214,166,68,.18); color: #8a6700; }
    .c-btn-gold:hover { background: rgba(214,166,68,.28); }
    .c-btn-primary { display: inline-flex; align-items: center; gap: .35rem; border-radius: 12px; background: #6A2C75; padding: .55rem 1rem; font-size: 13px; font-weight: 700; color: #fff; transition: background .2s; }
    .c-btn-primary:hover { background: #53225c; }
    .c-btn-primary:disabled { opacity: .5; }
</style>

<script>
    /**
     * Selector de SharePoint (partials/sharepoint-picker): navega carpetas
     * reales, elige la ubicación de publicación (modo "carpeta") y marca
     * archivos previos al sistema para moverlos a Obsoletos al atender.
     */
    function sharepointPicker(cfg) {
        return {
            modo: cfg.modo,
            rutaRaiz: cfg.rutaRaiz || '',
            rutaSeleccionada: cfg.sugerida || '',
            sugerida: cfg.sugerida || '',
            urlBase: cfg.urlBase,
            vigenteItemId: cfg.vigenteItemId,
            crearSubcarpeta: cfg.crearSubcarpeta,
            marcados: Array.isArray(cfg.marcados) ? cfg.marcados : [], // [{id, name, ruta}]
            modalAbierto: false,
            cargando: false,
            error: false,
            carpetas: [],
            archivos: [],
            breadcrumb: [], // [{id, name}]

            abrir() {
                this.breadcrumb = [];
                this.carpetas = [];
                this.archivos = [];
                this.modalAbierto = true;
                // Abre directo en la ubicación elegida/sugerida: ahí suele estar
                // el archivo de la versión que queda obsoleta.
                const ruta = this.rutaSeleccionada || this.sugerida;
                this.cargar(null, ruta);
            },

            cargar(folderId, ruta = null) {
                this.cargando = true;
                this.error = false;
                const params = new URLSearchParams();
                if (folderId) params.set('folder_id', folderId);
                else if (ruta) params.set('ruta', ruta);
                const url = params.toString() ? `${this.urlBase}?${params}` : this.urlBase;

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
                    .then(data => {
                        if (Array.isArray(data.breadcrumb)) this.breadcrumb = data.breadcrumb;
                        this.carpetas = data.folders || [];
                        this.archivos = data.files || [];
                        this.cargando = false;
                    })
                    .catch(() => { this.cargando = false; this.error = true; this.carpetas = []; this.archivos = []; });
            },

            entrar(carpeta) {
                // Un doble clic dispara dos clics antes de que la lista se
                // refresque; sin esta guarda la misma carpeta quedaba dos veces
                // en el breadcrumb y terminaba creando una subcarpeta duplicada
                // real en SharePoint (p. ej. "Sistemas/Sistemas").
                if (this.cargando) return;
                const ultimo = this.breadcrumb[this.breadcrumb.length - 1];
                if (ultimo && ultimo.id === carpeta.id) return;

                this.breadcrumb.push({ id: carpeta.id, name: carpeta.name });
                this.cargar(carpeta.id);
            },

            subirNivel() {
                if (this.cargando) return;
                this.breadcrumb.pop();
                const anterior = this.breadcrumb.length ? this.breadcrumb[this.breadcrumb.length - 1].id : null;
                this.cargar(anterior);
            },

            breadcrumbTexto() {
                return [this.rutaRaiz, ...this.breadcrumb.map(b => b.name)].filter(Boolean).join('/') || '/';
            },

            elegirCarpetaActual() {
                this.rutaSeleccionada = this.breadcrumbTexto();
                this.modalAbierto = false;
            },

            estaMarcado(id) {
                return this.marcados.some(m => m.id === id);
            },

            alternar(archivo) {
                if (this.estaMarcado(archivo.id)) {
                    this.desmarcar(archivo.id);
                } else {
                    this.marcados.push({ id: archivo.id, name: archivo.name, ruta: this.breadcrumbTexto() });
                }
            },

            desmarcar(id) {
                this.marcados = this.marcados.filter(m => m.id !== id);
            },

            iconoArchivo(nombre) {
                const ext = (nombre.split('.').pop() || '').toLowerCase();
                if (ext === 'pdf') return '📕';
                if (['doc', 'docx'].includes(ext)) return '📘';
                if (['xls', 'xlsx', 'csv'].includes(ext)) return '📗';
                if (['ppt', 'pptx'].includes(ext)) return '📙';
                return '📄';
            },

            detalleArchivo(archivo) {
                const partes = [];
                if (archivo.modified_at) {
                    partes.push(new Date(archivo.modified_at).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }));
                }
                if (archivo.size != null) {
                    const kb = archivo.size / 1024;
                    partes.push(kb < 1024 ? `${Math.max(1, Math.round(kb))} KB` : `${(kb / 1024).toFixed(1)} MB`);
                }
                return partes.join(' · ');
            },
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const busqueda = document.getElementById('busqueda-usuario');
        busqueda?.addEventListener('input', function () {
            const filtro = busqueda.value.toLowerCase();
            document.querySelectorAll('.usuario-item').forEach(item => {
                const nombre = (item.getAttribute('data-name') || '').toLowerCase();
                const clave = (item.getAttribute('data-clave') || '').toLowerCase();
                item.style.display = (nombre.includes(filtro) || clave.includes(filtro)) ? '' : 'none';
            });
        });

        // Se busca el formulario por id: querySelector('form') tomaba el
        // primer formulario de la página (p. ej. el de cerrar sesión de la
        // navegación), así que este autocompletado nunca corría.
        const form = document.getElementById('form-finalizar');
        form?.addEventListener('submit', function (e) {
            if (e.submitter?.value === 'atender') {
                const fechaV = document.getElementById('fecha_version');
                const fechaR = document.getElementById('fecha_revision');
                if (fechaR && fechaV && !fechaR.value && fechaV.value) {
                    fechaR.value = fechaV.value;
                }
            }
        });
    });
</script>
</x-app-layout>
