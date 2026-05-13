<x-app-layout>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @php

    $vigente = $documento->versionVigente
        ?? $documento->versiones()->latest('id')->first();

    $today = now()->startOfDay();

    $daysLeftIfVigente = function($estatus, $d) use ($today) {

        if (($estatus ?? null) !== 'vigente') {
            return null;
        }

        if (!$d) {
            return null;
        }

        return $today->diffInDays(
            \Carbon\Carbon::parse($d)->startOfDay(),
            false
        );
    };

    $daysVersion = $daysLeftIfVigente(
        $vigente?->estatus,
        $vigente?->fecha_vencimiento_version
    );

    $daysRevision = $daysLeftIfVigente(
        $vigente?->estatus,
        $vigente?->fecha_vencimiento_revision
    );

@endphp

    <div class="af-root">

        {{-- Geometric accent shapes --}}
        <div class="af-geo af-geo-1" aria-hidden="true"></div>
        <div class="af-geo af-geo-2" aria-hidden="true"></div>
        <div class="af-geo af-geo-3" aria-hidden="true"></div>

        <div class="af-wrap">

            {{-- ═══════════ TOPBAR ═══════════ --}}
            <div class="af-topbar af-fade" style="--d:0s">
                <div class="af-topbar-left">
                    <div class="af-logo-mark">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <polyline points="14 2 14 8 20 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="none" />
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div>
                        <p class="af-topbar-label">SGI — Control Documental</p>
                        <p class="af-topbar-sub">Edición de registro maestro</p>
                    </div>
                </div>
                <a href="{{ route('documentos.show', $documento->id) }}" class="af-back-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Volver al detalle
                </a>
            </div>

            {{-- ═══════════ PAGE TITLE ═══════════ --}}
            <div class="af-title-block af-fade" style="--d:.06s">
                <h1 class="af-title">Editando <span class="af-title-code">{{ $documento->codigo }}</span></h1>
                <p class="af-title-sub">Modificando datos maestros del registro oficial. Los cambios son inmediatos.</p>
            </div>

            {{-- ═══════════ FORM ═══════════ --}}
            <form action="{{ route('documentos.update', $documento->id) }}" method="POST" class="af-form">
                @csrf
                @method('PUT')

                {{-- SECCIÓN A --}}
                <div class="af-section af-fade" style="--d:.12s">
                    <div class="af-section-head">
                        <span class="af-section-num">01</span>
                        <div>
                            <h2 class="af-section-title">Identificación Básica</h2>
                            <p class="af-section-sub">Código único y nombre descriptivo del documento</p>
                        </div>
                    </div>
                    <div class="af-fields af-fields-2">
                        <div class="af-field">
                            <label class="af-label" for="codigo">Código</label>
                            <input id="codigo" type="text" name="codigo"
                                value="{{ old('codigo', $documento->codigo) }}"
                                class="af-input" autocomplete="off">
                            @error('codigo')<p class="af-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="af-field">
                            <label class="af-label" for="nombre">Nombre del documento</label>
                            <input id="nombre" type="text" name="nombre"
                                value="{{ old('nombre', $documento->nombre) }}"
                                class="af-input">
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN B --}}
                <div class="af-section af-fade" style="--d:.18s">
                    <div class="af-section-head">
                        <span class="af-section-num">02</span>
                        <div>
                            <h2 class="af-section-title">Clasificación y Estatus</h2>
                            <p class="af-section-sub">Tipo de formato y estado de vigencia del documento</p>
                        </div>
                    </div>
                    <div class="af-fields af-fields-3">
                        <div class="af-field">
                            <label class="af-label" for="tipo_doc">Tipo de documento</label>
                            <input id="tipo_doc" type="text" name="tipo_documento"
                                value="{{ old('tipo_documento', $documento->tipo_documento) }}"
                                class="af-input">
                        </div>
                        <div class="af-field">
                            <label class="af-label" for="el_pa">EL / PA</label>
                            <input id="el_pa" type="text" name="formato_el_pa"
                                value="{{ old('formato_el_pa', $documento->formato_el_pa) }}"
                                class="af-input">
                        </div>

                        
                        <div class="af-field">
                            <label class="af-label af-label-warn" for="estatus">
                                Estatus maestro
                                <span class="af-label-badge">Importante</span>
                            </label>
                            <select id="estatus" name="estatus" class="af-input af-select-warn">
                                <option value="vigente" {{ $documento->estatus == 'vigente' ? 'selected' : '' }}>Vigente</option>
                                <option value="baja" {{ $documento->estatus == 'baja'    ? 'selected' : '' }}>Baja</option>
                            </select>
                            <p class="af-hint">"Baja" desactiva todas las alertas de este documento.</p>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN C --}}
                <div class="af-section af-fade" style="--d:.24s">
                    <div class="af-section-head">
                        <span class="af-section-num">03</span>
                        <div>
                            <h2 class="af-section-title">Ubicación y Referencias</h2>
                            <p class="af-section-sub">Departamento responsable y ruta de almacenamiento</p>
                        </div>
                    </div>
                    <div class="af-fields af-fields-2">
                        <div class="af-field">
                            <label class="af-label" for="area">Área / Departamento</label>
                            <input id="area" type="text" name="area"
                                value="{{ old('area', $documento->area) }}"
                                class="af-input">
                        </div>
                        <div class="af-field">
                            <label class="af-label" for="sharepoint">Carpeta SharePoint</label>
                            <input id="sharepoint" type="url" name="sharepoint_folder"
                                value="{{ old('sharepoint_folder', $documento->sharepoint_folder) }}"
                                placeholder="https://…"
                                class="af-input">
                        </div>
                    </div>
                </div>

                <div class="af-section af-fade" style="--d:.28s">

                    <div class="af-section-head">
                        <span class="af-section-num">04</span>

                        <div>
                            <h2 class="af-section-title">
                                Información SGI / SharePoint
                            </h2>

                            <p class="af-section-sub">
                                Datos utilizados para calendario, dashboard y sincronización documental
                            </p>
                        </div>
                    </div>

                    <div class="af-fields af-fields-2">

                        {{-- SHAREPOINT PATH --}}
                        <div class="af-field">
                            <label class="af-label">
                                SharePoint Path
                            </label>

                            <input
                                type="text"
                                name="sharepoint_path"
                                value="{{ old('sharepoint_path', $documento->versionVigente?->sharepoint_path) }}"
                                class="af-input">
                        </div>

                        {{-- SHAREPOINT FILE ID --}}
                        <div class="af-field">
                            <label class="af-label">
                                SharePoint File ID
                            </label>

                            <input
                                type="text"
                                name="sharepoint_file_id"
                                value="{{ old('sharepoint_file_id', $documento->versionVigente?->sharepoint_file_id) }}"
                                class="af-input">
                        </div>

                        {{-- SP DRIVE --}}
                        <div class="af-field">
                            <label class="af-label">
                                SP Drive ID
                            </label>

                            <input
                                type="text"
                                name="sp_drive_id"
                                value="{{ old('sp_drive_id', $documento->versionVigente?->sp_drive_id) }}"
                                class="af-input">
                        </div>

                        {{-- SP ITEM --}}
                        <div class="af-field">
                            <label class="af-label">
                                SP Item ID
                            </label>

                            <input
                                type="text"
                                name="sp_item_id"
                                value="{{ old('sp_item_id', $documento->versionVigente?->sp_item_id) }}"
                                class="af-input">
                        </div>

                        {{-- SP WEB URL --}}
                        <div class="af-field">
                            <label class="af-label">
                                SP Web URL
                            </label>

                            <input
                                type="url"
                                name="sp_web_url"
                                value="{{ old('sp_web_url', $documento->versionVigente?->sp_web_url) }}"
                                class="af-input">
                        </div>

                        {{-- SP FOLDER --}}
                        <div class="af-field">
                            <label class="af-label">
                                SP Folder Path
                            </label>

                            <input
                                type="text"
                                name="sp_folder_path"
                                value="{{ old('sp_folder_path', $documento->versionVigente?->sp_folder_path) }}"
                                class="af-input">
                        </div>

                        {{-- LIGA ARCHIVO --}}
                        <div class="af-field">
                            <label class="af-label">
                                Liga Archivo
                            </label>

                            <input
                                type="url"
                                name="liga_archivo"
                                value="{{ old('liga_archivo', $documento->versionVigente?->liga_archivo) }}"
                                class="af-input">
                        </div>

                        {{-- ALMACENAMIENTO --}}
                        <div class="af-field">
                            <label class="af-label">
                                Lugar de almacenamiento
                            </label>

                            <input
                                type="text"
                                name="lugar_almacenamiento"
                                value="{{ old('lugar_almacenamiento', $documento->versionVigente?->lugar_almacenamiento) }}"
                                class="af-input">
                        </div>

                        {{-- VERSION --}}
                        <div class="af-field">
                            <label class="af-label">
                                Versión/Folio
                            </label>

                            <input
                                type="text"
                                name="version"
                                value="{{ old('version', $documento->versionVigente?->version) }}"
                                class="af-input">
                        </div>


                        {{-- FECHA REVISION --}}
                        <div class="af-field">
                            <label class="af-label">
                                Fecha revisión
                            </label>




                            <input
                                type="date"
                                name="fecha_revision"
                                value="{{ old('fecha_revision', optional($documento->versionVigente?->fecha_revision)->format('Y-m-d')) }}"
                                class="af-input">
                        </div>

                        {{-- VIGENCIA VERSION --}}
                        <div class="af-field">
                            <label class="af-label">
                                Vigencia versión (días)
                            </label>

                            <input
                                type="number"
                                name="vigencia_version_dias"
                                value="{{ old('vigencia_version_dias', $documento->versionVigente?->vigencia_version_dias) }}"
                                class="af-input">
                        </div>

                        {{-- VIGENCIA REVISION --}}
                        <div class="af-field">
                            <label class="af-label">
                                Vigencia revisión (días)
                            </label>

                            <input
                                type="number"
                                name="vigencia_revision_dias"
                                value="{{ old('vigencia_revision_dias', $documento->versionVigente?->vigencia_revision_dias) }}"
                                class="af-input">
                        </div>
                        
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="bg-slate-50 p-2 rounded border border-slate-200">
                                    <p class="text-xs text-slate-600 font-medium">Días versión</p>
                                    <p class="text-sm text-slate-700 font-semibold">
                                        {{ $daysVersion !== null ? $daysVersion.' días' : '—' }}
                                    </p>
                                </div>
                                <div class="bg-slate-50 p-2 rounded border border-slate-200">
                                    <p class="text-xs text-slate-600 font-medium">Días revisión</p>
                                    <p class="text-sm text-slate-700 font-semibold">
                                        {{ $daysRevision !== null ? $daysRevision.' días' : '—' }}
                                    </p>
                                </div>
                            </div>


                    </div>

                    {{-- OBSERVACIONES --}}
                    <div style="padding:0 24px 24px">

                        <div class="af-field">
                            <label class="af-label">
                                Observaciones SGI
                            </label>

                            <textarea
                                name="observaciones_sgi"
                                rows="5"
                                class="af-input">{{ old('observaciones_sgi', $documento->versionVigente?->observaciones_sgi) }}</textarea>
                        </div>
                    </div>
                </div>





                {{-- ACTIONS --}}
                <div class="af-actions af-fade" style="--d:.30s">
                    <a href="{{ route('documentos.show', $documento->id) }}" class="af-discard">
                        Descartar cambios
                    </a>
                    <button type="submit" class="af-save-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Actualizar documento
                        <svg class="af-save-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12 5 19 12 12 19" />
                        </svg>
                    </button>
                </div>

            </form>

            {{-- ═══════════ INFO NOTE ═══════════ --}}
            <div class="af-note af-fade" style="--d:.36s">
                <div class="af-note-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </div>
                <p class="af-note-text">
                    Estás editando el <strong>registro maestro</strong>.
                    Para subir una nueva versión o revisión, usa la sección <em>Timeline</em> en la vista de detalle —
                    esto preserva el historial completo del documento.
                </p>
            </div>

        </div>{{-- /wrap --}}
    </div>{{-- /root --}}

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

        :root {
            --sky: #EFF6FF;
            --sky-2: #DBEAFE;
            --cobalt: #1D4ED8;
            --cobalt-d: #1E3A8A;
            --cobalt-l: #3B82F6;
            --cobalt-pale: #EFF6FF;
            --amber: #D97706;
            --amber-pale: #FFFBEB;
            --amber-border: #FDE68A;
            --ink: #0F172A;
            --ink-2: #1E293B;
            --ink-3: #475569;
            --ink-4: #94A3B8;
            --ink-5: #CBD5E1;
            --bg: #F1F5F9;
            --surface: #FFFFFF;
            --border: #E2E8F0;
            --font: 'Outfit', 'Trebuchet MS', sans-serif;
            --rad: 12px;
        }

        .af-root *,
        .af-root *::before,
        .af-root *::after {
            font-family: var(--font);
            box-sizing: border-box
        }

        .af-root {
            min-height: 100vh;
            background: var(--bg);
            position: relative;
            overflow-x: hidden;
            padding: 36px 0 80px
        }

        /* Geometric shapes */
        .af-geo {
            position: fixed;
            pointer-events: none;
            z-index: 0;
            border-radius: 50%
        }

        .af-geo-1 {
            width: 320px;
            height: 320px;
            top: -80px;
            right: -40px;
            background: radial-gradient(circle, rgba(29, 78, 216, .08) 0%, transparent 70%)
        }

        .af-geo-2 {
            width: 200px;
            height: 200px;
            bottom: 120px;
            left: -60px;
            background: radial-gradient(circle, rgba(59, 130, 246, .06) 0%, transparent 70%)
        }

        .af-geo-3 {
            width: 140px;
            height: 140px;
            top: 40%;
            right: 8%;
            background: radial-gradient(circle, rgba(217, 119, 6, .07) 0%, transparent 70%)
        }

        .af-wrap {
            position: relative;
            z-index: 1;
            max-width: 840px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            flex-direction: column;
            gap: 18px
        }

        /* Fade */
        .af-fade {
            opacity: 0;
            transform: translateY(12px);
            animation: af-in .5s cubic-bezier(.22, 1, .36, 1) forwards;
            animation-delay: var(--d, 0s)
        }

        @keyframes af-in {
            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        /* TOPBAR */
        .af-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--rad);
            box-shadow: 0 1px 4px rgba(15, 23, 42, .05)
        }

        .af-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px
        }

        .af-logo-mark {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: var(--cobalt);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0
        }

        .af-topbar-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--ink-2);
            margin: 0;
            letter-spacing: .01em
        }

        .af-topbar-sub {
            font-size: 11.5px;
            color: var(--ink-4);
            margin: 2px 0 0
        }

        .af-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            background: var(--bg);
            color: var(--ink-3);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
            white-space: nowrap
        }

        .af-back-btn:hover {
            border-color: var(--ink-4);
            color: var(--ink);
            background: var(--surface)
        }

        /* TITLE */
        .af-title-block {
            padding: 8px 4px
        }

        .af-title {
            font-size: clamp(1.5rem, 3vw, 2rem);
            font-weight: 800;
            color: var(--ink);
            margin: 0 0 6px;
            letter-spacing: -.02em
        }

        .af-title-code {
            color: var(--cobalt)
        }

        .af-title-sub {
            font-size: 14px;
            color: var(--ink-3);
            margin: 0;
            font-weight: 400
        }

        /* SECTIONS */
        .af-form {
            display: flex;
            flex-direction: column;
            gap: 4px
        }

        .af-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--rad);
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(15, 23, 42, .04);
            transition: box-shadow .25s
        }

        .af-section:hover {
            box-shadow: 0 4px 20px rgba(15, 23, 42, .07)
        }

        .af-section-head {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 22px 24px 0
        }

        .af-section-num {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            color: rgba(29, 78, 216, .08);
            line-height: 1;
            flex-shrink: 0;
            margin-top: -6px;
            letter-spacing: -.04em
        }

        .af-section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
            margin: 0 0 3px;
            letter-spacing: -.01em
        }

        .af-section-sub {
            font-size: 12.5px;
            color: var(--ink-4);
            margin: 0
        }

        /* Fields grid */
        .af-fields {
            display: grid;
            gap: 16px;
            padding: 20px 24px 24px
        }

        .af-fields-2 {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr))
        }

        .af-fields-3 {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr))
        }

        @media(max-width:540px) {

            .af-fields-2,
            .af-fields-3 {
                grid-template-columns: 1fr
            }
        }

        /* Field */
        .af-field {
            display: flex;
            flex-direction: column;
            gap: 7px
        }

        .af-label {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--ink-3);
            text-transform: uppercase;
            letter-spacing: .08em;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .af-label-warn {
            color: var(--amber)
        }

        .af-label-badge {
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
            background: var(--amber-pale);
            color: var(--amber);
            border: 1px solid var(--amber-border);
            text-transform: uppercase;
            letter-spacing: .06em
        }

        .af-input {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: 11px 14px;
            font-size: 14px;
            font-weight: 500;
            color: var(--ink);
            background: var(--bg);
            font-family: var(--font);
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s
        }

        .af-input:focus {
            border-color: var(--cobalt-l);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .12)
        }

        .af-input:hover:not(:focus) {
            border-color: var(--ink-5)
        }

        .af-input::placeholder {
            color: var(--ink-5)
        }

        .af-select-warn {
            border-color: var(--amber-border);
            background: var(--amber-pale)
        }

        .af-select-warn:focus {
            border-color: var(--amber);
            box-shadow: 0 0 0 3px rgba(217, 119, 6, .12)
        }

        .af-hint {
            font-size: 11.5px;
            color: var(--ink-4);
            margin: 0;
            font-style: italic
        }

        .af-error {
            font-size: 11.5px;
            color: #DC2626;
            margin: 0
        }

        /* ACTIONS */
        .af-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 24px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--rad);
            box-shadow: 0 1px 6px rgba(15, 23, 42, .04)
        }

        .af-discard {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink-4);
            text-decoration: none;
            transition: color .2s
        }

        .af-discard:hover {
            color: #DC2626
        }

        .af-save-btn {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 12px 26px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--cobalt) 0%, var(--cobalt-l) 100%);
            color: #fff;
            font-size: 14.5px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all .25s;
            box-shadow: 0 4px 18px rgba(29, 78, 216, .3), 0 1px 0 rgba(255, 255, 255, .15) inset;
            letter-spacing: -.01em
        }

        .af-save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(29, 78, 216, .4), 0 1px 0 rgba(255, 255, 255, .15) inset
        }

        .af-save-btn:active {
            transform: translateY(0)
        }

        .af-save-arrow {
            transition: transform .2s
        }

        .af-save-btn:hover .af-save-arrow {
            transform: translateX(3px)
        }

        /* NOTE */
        .af-note {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 16px 20px;
            background: var(--cobalt-pale);
            border: 1px solid var(--sky-2);
            border-radius: var(--rad)
        }

        .af-note-icon {
            flex-shrink: 0;
            color: var(--cobalt-l);
            margin-top: 1px
        }

        .af-note-text {
            font-size: 13px;
            color: var(--ink-3);
            line-height: 1.6;
            margin: 0
        }

        .af-note-text strong {
            color: var(--ink-2);
            font-weight: 700
        }

        .af-note-text em {
            color: var(--cobalt);
            font-style: normal;
            font-weight: 600
        }

        ::-webkit-scrollbar {
            width: 4px;
            height: 4px
        }

        ::-webkit-scrollbar-track {
            background: transparent
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--ink-5)
        }
    </style>

</x-app-layout>