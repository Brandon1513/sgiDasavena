<x-app-layout>

    <div class="ac-root relative min-h-screen">
        <div class="ac-bg pointer-events-none fixed inset-0 z-0" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');"></div>
        <div class="ac-veil pointer-events-none fixed inset-0 z-[1]"></div>

        <div class="relative z-10 mx-auto flex max-w-[1500px] flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="ac-alert ac-alert-success reveal-3d" role="alert">
            <i class="ti ti-circle-check"></i>
            <span class="flex-1">{{ session('success') }}</span>
            <button type="button" class="ac-alert-close" onclick="this.closest('.ac-alert').remove()" aria-label="Cerrar">
                <i class="ti ti-x"></i>
            </button>
        </div>
        @endif

        @if($errors->any())
        <div class="ac-alert ac-alert-danger reveal-3d" role="alert">
            <i class="ti ti-alert-triangle"></i>
            <div class="flex-1">
                <div class="mb-1 font-bold">No es posible continuar</div>
                <ul class="mb-0 list-disc pl-4">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="ac-alert-close" onclick="this.closest('.ac-alert').remove()" aria-label="Cerrar">
                <i class="ti ti-x"></i>
            </button>
        </div>
        @endif

        @php
        $estadoActual = $accionCorrectiva->estado?->codigo;

        $estados = [
        'borrador' => 'Borrador',
        'abierta' => 'Abierta',
        'contencion' => 'Contención',
        'analisis' => 'Análisis',
        'validacion_causa' => 'Validación de causa',
        'plan_accion' => 'Plan de acción',
        'ejecucion' => 'Ejecución',
        'verificacion_cierre' => 'Verificación de cierre',
        'espera_eficacia' => 'Espera de eficacia',
        'verificacion_eficacia' => 'Verificación de eficacia',
        'cerrada' => 'Cerrada',
        ];

        $estadoOrden = array_search($estadoActual, array_keys($estados));

        $estadoClase = match ($estadoActual) {
        'borrador' => 'secondary',
        'abierta' => 'primary',
        'contencion' => 'warning',
        'analisis',
        'validacion_causa' => 'info',
        'plan_accion',
        'ejecucion' => 'primary',
        'verificacion_cierre',
        'espera_eficacia',
        'verificacion_eficacia' => 'warning',
        'cerrada' => 'success',
        default => 'secondary',
        };

        $avance = min(
        100,
        max(
        0,
        (float) $accionCorrectiva->porcentaje_avance
        )
        );

        $cicloActual = $accionCorrectiva->analisis
        ->where('ciclo', $accionCorrectiva->ciclo_actual)
        ->first();

        $planActual = $accionCorrectiva->planesAccion
        ->where('ciclo', $accionCorrectiva->ciclo_actual)
        ->first();

        $causaActual = $cicloActual?->causasRaiz
        ?->sortByDesc('id')
        ->first();

        $verificacionCierreActual = $accionCorrectiva->verificacionesCierre
        ->where('ciclo', $accionCorrectiva->ciclo_actual)
        ->sortByDesc('id')
        ->first();

        $esperaActual = $accionCorrectiva->esperasEficacia
        ->where('ciclo', $accionCorrectiva->ciclo_actual)
        ->sortByDesc('id')
        ->first();

        $verificacionEficaciaActual = $accionCorrectiva->verificacionesEficacia
        ->where('ciclo', $accionCorrectiva->ciclo_actual)
        ->sortByDesc('id')
        ->first();

       
        $verificacionCicloAnterior = $accionCorrectiva->ciclo_actual > 1
        ? $accionCorrectiva->verificacionesEficacia->where('ciclo', $accionCorrectiva->ciclo_actual - 1)->sortByDesc('id')->first()
        : null;

        $contencionesCompletadas = $accionCorrectiva->contenciones->where('estado', 'completada')->count();
        $actividadesPendientes = $planActual?->actividades->where('estado', '!=', 'completada')->count() ?? 0;
        $causasValidadas = $cicloActual?->causasRaiz?->where('estado_validacion', 'aprobada')->count() ?? 0;

        
        $badgeMap = [
        'secondary' => 'badge-slate',
        'primary' => 'badge-purple',
        'warning' => 'badge-amber',
        'info' => 'badge-sky',
        'success' => 'badge-emerald',
        'danger' => 'badge-rose',
        ];

        $btnMap = [
        'primary' => 'btn-glass-primary',
        'success' => 'btn-glass-success',
        'warning' => 'btn-glass-warning',
        'danger' => 'btn-glass-danger',
        'secondary' => 'btn-glass-light',
        'info' => 'btn-glass-info',
        ];

        $accionIcon = [
        'abrir' => 'ti-door-enter',
        'contencion' => 'ti-shield-plus',
        'analisis' => 'ti-search',
        'validacion_causa' => 'ti-checkbox',
        'plan_accion' => 'ti-clipboard-list',
        'ejecucion' => 'ti-player-play',
        'verificacion_cierre' => 'ti-clipboard-check',
        'espera_eficacia' => 'ti-hourglass-high',
        'nuevo_ciclo' => 'ti-arrow-repeat',
        'cerrar' => 'ti-lock-check',
        ];
        @endphp

        {{-- =========================================================
            BREADCRUMB
        ========================================================== --}}
        <nav class="reveal-3d flex items-center gap-1.5 text-xs font-semibold text-gray-500">
            <a href="{{ route('acciones-correctivas.index') }}" class="ac-crumb-link"><i class="ti ti-shield-check"></i> SGI Calidad</a>
            <i class="ti ti-chevron-right text-gray-300"></i>
            <a href="{{ route('acciones-correctivas.index') }}" class="ac-crumb-link">Acciones Correctivas</a>
            <i class="ti ti-chevron-right text-gray-300"></i>
            <span class="text-dasavena-purple">Expediente {{ $accionCorrectiva->codigo }}</span>
        </nav>

        {{-- =========================================================
            ENCABEZADO
        ========================================================== --}}
        <header class="ac-card ac-card-hero tilt-card reveal-3d relative overflow-hidden">
            <div class="gold-gleam absolute inset-x-0 top-0 z-[2] h-[3px]"></div>
            <div class="ac-hero-dotgrid pointer-events-none absolute inset-0" aria-hidden="true"></div>

            <div class="relative z-[1] flex flex-col gap-5 p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="ac-eyebrow">Expediente SGI</span>
                            <span class="ac-code-chip">{{ $accionCorrectiva->codigo }}</span>
                            <span class="badge-pill {{ $badgeMap[$estadoClase] ?? 'badge-slate' }}">
                                {{ $accionCorrectiva->estado?->nombre ?? 'Sin estado' }}
                            </span>
                            <span class="badge-pill badge-gold-outline">
                                <i class="ti ti-arrow-repeat"></i> Ciclo {{ $accionCorrectiva->ciclo_actual }}
                            </span>
                        </div>

                        <h1 class="mt-3 max-w-2xl font-display text-xl font-extrabold leading-snug text-white sm:text-2xl">
                            {{ $accionCorrectiva->descripcion }}
                        </h1>

                        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-white/60">
                            <span><i class="ti ti-user-circle text-dasavena-gold"></i> Responsable: <strong class="text-white">{{ $accionCorrectiva->responsable?->name ?? 'Sin responsable' }}</strong></span>
                            <span><i class="ti ti-calendar-event text-dasavena-gold"></i> Apertura: <strong class="text-white">{{ $accionCorrectiva->fecha_apertura?->format('d/m/Y') }}</strong></span>
                            <span><i class="ti ti-search text-dasavena-gold"></i> Origen: <strong class="text-white">{{ $accionCorrectiva->origen?->nombre ?? 'Sin origen' }}</strong></span>
                        </div>
                    </div>

                    @if(!empty($acciones))
                    <a href="#acciones-seccion" class="ac-hero-cta">
                        <i class="ti ti-bolt"></i> Gestionar proceso
                    </a>
                    @endif
                </div>
            </div>
        </header>

        {{-- =========================================================
            AVISO DE REAPERTURA (solo con datos reales del ciclo anterior)
        ========================================================== --}}
        @if($verificacionCicloAnterior && !$verificacionCicloAnterior->resultado_eficaz)
        <div class="ac-alert ac-alert-warning reveal-3d">
            <i class="ti ti-alert-triangle"></i>
            <div class="flex-1 text-sm">
                <div class="mb-0.5 font-bold text-amber-900">Expediente en ciclo {{ $accionCorrectiva->ciclo_actual }}</div>
                La verificación de eficacia del <strong>Ciclo {{ $accionCorrectiva->ciclo_actual - 1 }} resultó No Eficaz</strong>
                @if($verificacionCicloAnterior->fecha_verificacion) ({{ $verificacionCicloAnterior->fecha_verificacion->format('d/m/Y') }}) @endif.
                Se activó el <strong>Ciclo {{ $accionCorrectiva->ciclo_actual }}</strong> para redefinir el análisis de causa raíz y validar nuevas acciones.
            </div>
        </div>
        @endif

        {{-- =========================================================
            RESUMEN EJECUTIVO (KPIs)
        ========================================================== --}}
        <section class="reveal-3d grid grid-cols-2 gap-3.5 sm:grid-cols-3 xl:grid-cols-6">
            <div class="ac-kpi-card tilt-card">
                <span class="ac-kpi-icon ac-kpi-icon-purple"><i class="ti ti-chart-pie"></i></span>
                <div class="ac-kpi-label">Progreso</div>
                <div class="ac-kpi-value">{{ number_format($avance, 0) }}%</div>
                <div class="ac-kpi-sub">Ciclo en curso</div>
            </div>
            <div class="ac-kpi-card tilt-card">
                <span class="ac-kpi-icon ac-kpi-icon-gold"><i class="ti ti-arrow-repeat"></i></span>
                <div class="ac-kpi-label">Ciclo actual</div>
                <div class="ac-kpi-value">{{ $accionCorrectiva->ciclo_actual }}</div>
                <div class="ac-kpi-sub">{{ $accionCorrectiva->ciclo_actual > 1 ? 'Reapertura previa' : 'Ciclo inicial' }}</div>
            </div>
            <div class="ac-kpi-card tilt-card">
                <span class="ac-kpi-icon ac-kpi-icon-emerald"><i class="ti ti-shield-check"></i></span>
                <div class="ac-kpi-label">Contenciones</div>
                <div class="ac-kpi-value">{{ $contencionesCompletadas }} / {{ $accionCorrectiva->contenciones->count() }}</div>
                <div class="ac-kpi-sub">Implementadas</div>
            </div>
            <div class="ac-kpi-card tilt-card">
                <span class="ac-kpi-icon ac-kpi-icon-sky"><i class="ti ti-bulb"></i></span>
                <div class="ac-kpi-label">Causas raíz</div>
                <div class="ac-kpi-value">{{ $cicloActual?->causasRaiz?->count() ?? 0 }}</div>
                <div class="ac-kpi-sub">{{ $causasValidadas }} validadas</div>
            </div>
            <div class="ac-kpi-card tilt-card">
                <span class="ac-kpi-icon ac-kpi-icon-amber"><i class="ti ti-checkbox"></i></span>
                <div class="ac-kpi-label">Actividades</div>
                <div class="ac-kpi-value">{{ $planActual?->actividades->count() ?? 0 }}</div>
                <div class="ac-kpi-sub">{{ $actividadesPendientes }} pendientes</div>
            </div>
            <div class="ac-kpi-card tilt-card">
                <span class="ac-kpi-icon {{ $verificacionCicloAnterior ? ($verificacionCicloAnterior->resultado_eficaz ? 'ac-kpi-icon-emerald' : 'ac-kpi-icon-rose') : 'ac-kpi-icon-slate' }}"><i class="ti ti-hourglass-high"></i></span>
                <div class="ac-kpi-label">Eficacia anterior</div>
                <div class="ac-kpi-value {{ $verificacionCicloAnterior ? ($verificacionCicloAnterior->resultado_eficaz ? 'text-emerald-600' : 'text-rose-600') : '' }}">
                    {{ $verificacionCicloAnterior ? ($verificacionCicloAnterior->resultado_eficaz ? 'Eficaz' : 'No eficaz') : '—' }}
                </div>
                <div class="ac-kpi-sub">{{ $verificacionCicloAnterior ? 'Ciclo ' . ($accionCorrectiva->ciclo_actual - 1) : 'Sin ciclos previos' }}</div>
            </div>
        </section>

        {{-- =========================================================
            LÍNEA DE PROCESO
        ========================================================== --}}
        <section class="ac-card tilt-card reveal-3d">
            <div class="ac-card-header">
                <div>
                    <div class="ac-card-title"><i class="ti ti-route text-dasavena-purple"></i> Flujo de la Acción Correctiva</div>
                    <p class="ac-card-sub">Etapa {{ $estadoOrden + 1 }} de {{ count($estados) }} · Estado actual del proceso.</p>
                </div>
                <span class="badge-pill badge-emerald">Avance global: {{ number_format($avance, 0) }}%</span>
            </div>
            <div class="p-6 pt-4">
                <div class="ac-stepper">
                    <div class="ac-stepper-line">
                        <div class="ac-stepper-line-fill" style="width: {{ $estadoOrden > 0 ? ($estadoOrden / (count($estados) - 1)) * 100 : 0 }}%;"></div>
                    </div>
                    <div class="ac-stepper-items">
                        @foreach($estados as $codigo => $nombre)
                        @php
                        $indice = array_search($codigo, array_keys($estados));
                        $completado = $indice < $estadoOrden;
                        $actual = $indice === $estadoOrden;
                        @endphp
                        <div class="ac-stepper-item">
                            @if($completado)
                            <span class="ac-step-icon ac-step-icon-done"><i class="ti ti-check"></i></span>
                            @elseif($actual)
                            <span class="ac-step-icon ac-step-icon-active"><i class="ti ti-point-filled"></i></span>
                            @else
                            <span class="ac-step-icon ac-step-icon-pending">{{ $indice + 1 }}</span>
                            @endif
                            <span class="ac-step-label {{ $actual ? 'ac-step-label-active' : '' }}">{{ $nombre }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
            DOS COLUMNAS: PRINCIPAL + PANEL LATERAL
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            {{-- ═══════════════════ COLUMNA PRINCIPAL ═══════════════════ --}}
            <div class="flex flex-col gap-6 lg:col-span-8">

                {{-- INFORMACIÓN GENERAL --}}
                <section class="ac-card tilt-card reveal-3d">
                    <div class="ac-card-header">
                        <div class="ac-card-title"><i class="ti ti-info-circle text-dasavena-purple"></i> Información general del expediente</div>
                        <span class="badge-pill badge-slate">ID #{{ $accionCorrectiva->id }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 p-6 pt-4 text-sm sm:grid-cols-4">
                        <div>
                            <div class="ac-mini-label">Código SGI</div>
                            <div class="mt-1 font-mono font-bold text-dasavena-purple">{{ $accionCorrectiva->codigo }}</div>
                        </div>
                        <div>
                            <div class="ac-mini-label">Estado actual</div>
                            <div class="mt-1"><span class="badge-pill {{ $badgeMap[$estadoClase] ?? 'badge-slate' }}">{{ $accionCorrectiva->estado?->nombre ?? 'Sin estado' }}</span></div>
                        </div>
                        <div>
                            <div class="ac-mini-label">Responsable</div>
                            <div class="mt-1 font-semibold text-indigo-950">{{ $accionCorrectiva->responsable?->name ?? 'Sin responsable' }}</div>
                        </div>
                        <div>
                            <div class="ac-mini-label">Origen</div>
                            <div class="mt-1 font-semibold text-indigo-950">{{ $accionCorrectiva->origen?->nombre ?? 'Sin origen' }}</div>
                        </div>
                        <div>
                            <div class="ac-mini-label">Fecha de apertura</div>
                            <div class="mt-1 font-semibold text-indigo-950">{{ $accionCorrectiva->fecha_apertura?->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <div class="ac-mini-label">Fecha de cierre</div>
                            <div class="mt-1 font-semibold text-indigo-950">{{ $accionCorrectiva->fecha_cierre?->format('d/m/Y') ?? 'Pendiente' }}</div>
                        </div>
                        <div>
                            <div class="ac-mini-label">Ciclo actual</div>
                            <div class="mt-1 font-semibold text-dasavena-purple">Ciclo {{ $accionCorrectiva->ciclo_actual }}</div>
                        </div>
                        <div>
                            <div class="ac-mini-label">Avance ponderado</div>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="font-bold text-emerald-600">{{ number_format($avance, 0) }}%</span>
                                <div class="ac-progress-track ac-progress-track-sm flex-1">
                                    <div class="ac-progress-fill {{ $avance >= 100 ? 'ac-progress-success' : '' }}" style="width: {{ $avance }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- RESUMEN DEL CICLO ACTUAL --}}
                <section class="ac-card tilt-card reveal-3d">
                    <div class="ac-card-header">
                        <div>
                            <div class="ac-card-title">Ciclo {{ $accionCorrectiva->ciclo_actual }}</div>
                            <p class="ac-card-sub">Resumen del ciclo actual.</p>
                        </div>
                        <span class="badge-pill badge-purple">Actual</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 p-6 pt-4 lg:grid-cols-3">
                        {{-- Análisis --}}
                        <div class="ac-flow-stage {{ $estadoActual === 'analisis' ? 'ac-flow-stage-active' : '' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="ac-mini-label">Análisis</div>
                                    <div class="mt-0.5 font-semibold text-indigo-950">Análisis de causa raíz</div>
                                </div>
                                @if($cicloActual)
                                <span class="badge-pill badge-emerald">Registrado</span>
                                @else
                                <span class="badge-pill badge-slate">Pendiente</span>
                                @endif
                            </div>
                            @if($cicloActual)
                            <div class="mt-3 text-xs text-gray-400">Inicio</div>
                            <div class="font-semibold text-indigo-950">{{ $cicloActual->fecha_inicio?->format('d/m/Y') }}</div>
                            @endif
                        </div>

                        {{-- Causa raíz --}}
                        <div class="ac-flow-stage {{ $estadoActual === 'validacion_causa' ? 'ac-flow-stage-active' : '' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="ac-mini-label">Causa raíz</div>
                                    <div class="mt-0.5 font-semibold text-indigo-950">Validación</div>
                                </div>
                                @if($causaActual?->estado_validacion === 'aprobada')
                                <span class="badge-pill badge-emerald">Aprobada</span>
                                @elseif($causaActual?->estado_validacion === 'rechazada')
                                <span class="badge-pill badge-rose">Rechazada</span>
                                @elseif($causaActual)
                                <span class="badge-pill badge-amber">Pendiente</span>
                                @else
                                <span class="badge-pill badge-slate">Pendiente</span>
                                @endif
                            </div>
                            @if($causaActual)
                            <div class="mt-3 text-xs text-gray-400">Descripción</div>
                            <div class="mt-1 text-sm text-gray-600">{{ $causaActual->descripcion }}</div>
                            @endif
                        </div>

                        {{-- Plan --}}
                        <div class="ac-flow-stage {{ in_array($estadoActual, ['plan_accion', 'ejecucion'], true) ? 'ac-flow-stage-active' : '' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="ac-mini-label">Plan de acción</div>
                                    <div class="mt-0.5 font-semibold text-indigo-950">Ejecución</div>
                                </div>
                                @if($planActual?->estado === 'completado')
                                <span class="badge-pill badge-emerald">Completado</span>
                                @elseif($planActual)
                                <span class="badge-pill badge-amber">En proceso</span>
                                @else
                                <span class="badge-pill badge-slate">Pendiente</span>
                                @endif
                            </div>
                            @if($planActual)
                            <div class="mt-3 text-xs text-gray-400">Actividades</div>
                            <div class="font-semibold text-indigo-950">{{ $planActual->actividades->count() }}</div>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- CONTENCIONES --}}
                <section class="ac-card tilt-card reveal-3d">
                    <div class="ac-card-header">
                        <div>
                            <div class="ac-card-title"><i class="ti ti-shield-check text-emerald-600"></i> Contenciones</div>
                            <p class="ac-card-sub">Medidas temporales implementadas.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge-pill badge-slate">{{ $accionCorrectiva->contenciones->count() }}</span>
                            @if(in_array($estadoActual, ['abierta', 'contencion'], true))
                            <button type="button" class="btn-glass-primary btn-glass-sm" onclick="toggleContencion()">
                                <i class="ti ti-plus"></i> Agregar contención
                            </button>
                            @endif
                        </div>
                    </div>

                    <div class="p-6 pt-4">
                        @forelse($accionCorrectiva->contenciones as $contencion)
                        <div class="ac-timeline-row">
                            <div class="ac-timeline-node {{ $contencion->estado === 'completada' ? 'ac-timeline-node-done' : '' }}">
                                <i class="ti {{ $contencion->estado === 'completada' ? 'ti-check' : 'ti-clock' }}"></i>
                            </div>
                            <div class="ac-subcard flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="font-semibold text-indigo-950">{{ $contencion->descripcion }}</div>
                                    <span class="badge-pill {{ $contencion->estado === 'completada' ? 'badge-emerald' : 'badge-amber' }}">
                                        {{ ucfirst(str_replace('_', ' ', $contencion->estado)) }}
                                    </span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400">
                                    <span><i class="ti ti-user"></i> {{ $contencion->responsable?->name ?? 'Sin responsable' }}</span>
                                    <span><i class="ti ti-calendar-event"></i> {{ $contencion->fecha_implementacion?->format('d/m/Y') }}</span>
                                </div>
                                @if($contencion->observaciones)
                                <div class="mt-2 text-sm text-gray-600">{{ $contencion->observaciones }}</div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="ac-empty"><i class="ti ti-shield-check"></i> No existen contenciones registradas.</div>
                        @endforelse
                    </div>

                    @if(in_array($estadoActual, ['abierta', 'contencion'], true))
                    <div id="formContencion" class="px-6 pb-6" style="display:none;">
                        <div class="ac-form-panel">
                            <div class="mb-1 font-semibold text-indigo-950">Registrar acción de contención</div>
                            <p class="ac-card-sub mb-4">Registra la medida temporal utilizada para controlar el problema.</p>

                            <form method="POST" action="{{ route('acciones-correctivas.contenciones.crear', $accionCorrectiva) }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                    <div class="md:col-span-12">
                                        <label class="ac-label">Descripción</label>
                                        <textarea name="descripcion" class="ac-field" rows="3" required
                                            placeholder="Describe la acción de contención implementada...">{{ old('descripcion') }}</textarea>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label class="ac-label">Responsable</label>
                                        <select name="responsable_id" class="ac-field" required>
                                            <option value="">Seleccionar responsable</option>
                                            @foreach(\App\Models\User::query()->where('activo', true)->orderBy('name')->get() as $usuario)
                                            <option value="{{ $usuario->id }}" @selected(old('responsable_id')==$usuario->id)>{{ $usuario->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label class="ac-label">Fecha de implementación</label>
                                        <input type="date" name="fecha_implementacion" class="ac-field"
                                            value="{{ old('fecha_implementacion', now()->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label class="ac-label">Estado</label>
                                        <select name="estado" class="ac-field" required>
                                            <option value="pendiente" @selected(old('estado', 'pendiente')==='pendiente')>Pendiente</option>
                                            <option value="completada" @selected(old('estado')==='completada')>Completada</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-12">
                                        <label class="ac-label">Observaciones</label>
                                        <textarea name="observaciones" class="ac-field" rows="2" placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" class="btn-glass-light" onclick="toggleContencion()">Cancelar</button>
                                    <button type="submit" class="btn-glass-primary">Guardar contención</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                </section>

                {{-- ANÁLISIS --}}
                <section class="ac-card tilt-card reveal-3d">
                    <div class="ac-card-header">
                        <div>
                            <div class="ac-card-title"><i class="ti ti-search text-sky-600"></i> Análisis de causa raíz</div>
                            <p class="ac-card-sub">Historial de análisis realizados por ciclo.</p>
                        </div>
                        <span class="badge-pill badge-slate">{{ $accionCorrectiva->analisis->count() }}</span>
                    </div>
                    <div class="p-6 pt-4">
                        @forelse($accionCorrectiva->analisis->sortByDesc('ciclo') as $analisis)
                        <div class="ac-subcard mb-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-indigo-950">Ciclo {{ $analisis->ciclo }}</div>
                                    <div class="mt-1 text-xs text-gray-400">Inicio: {{ $analisis->fecha_inicio?->format('d/m/Y') }}</div>
                                </div>
                                <span class="badge-pill {{ $analisis->estado === 'completado' ? 'badge-emerald' : 'badge-amber' }}">
                                    {{ ucfirst(str_replace('_', ' ', $analisis->estado)) }}
                                </span>
                            </div>
                            @if($analisis->ideas?->count())
                            <div class="mt-3 text-xs text-gray-400">Ideas identificadas: <strong class="text-indigo-950">{{ $analisis->ideas->count() }}</strong></div>
                            @endif
                            @if($analisis->cincoPorques?->count())
                            <div class="mt-1 text-xs text-gray-400">Análisis de 5 Porqués: <strong class="text-indigo-950">{{ $analisis->cincoPorques->count() }}</strong></div>
                            @endif
                            @if($analisis->causasRaiz?->count())
                            <div class="mt-1 text-xs text-gray-400">Causas raíz: <strong class="text-indigo-950">{{ $analisis->causasRaiz->count() }}</strong></div>
                            @endif
                        </div>
                        @empty
                        <div class="ac-empty"><i class="ti ti-search"></i> No existen análisis registrados.</div>
                        @endforelse
                    </div>
                </section>

                {{-- PLANES DE ACCIÓN --}}
                <section class="ac-card tilt-card reveal-3d">
                    <div class="ac-card-header">
                        <div>
                            <div class="ac-card-title"><i class="ti ti-clipboard-list text-dasavena-purple"></i> Planes de acción</div>
                            <p class="ac-card-sub">Actividades y evidencias de cada ciclo.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge-pill badge-slate">{{ $accionCorrectiva->planesAccion->count() }}</span>
                            @if($estadoActual === 'plan_accion' && !$planActual)
                            <button type="button" class="btn-glass-primary btn-glass-sm" onclick="togglePlanAccion()">
                                <i class="ti ti-plus"></i> Crear plan de acción
                            </button>
                            @endif
                        </div>
                    </div>

                    @if($estadoActual === 'plan_accion' && !$planActual)
                    <div id="formPlanAccion" class="px-6 pb-2 pt-4" style="display:none;">
                        <div class="ac-form-panel">
                            <div class="mb-1 font-semibold text-indigo-950">Crear plan de acción</div>
                            <p class="ac-card-sub mb-4">Define las acciones necesarias para eliminar la causa raíz y evitar su recurrencia.</p>
                            <form method="POST" action="{{ route('acciones-correctivas.planes.crear', $accionCorrectiva) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="ac-label">Observaciones</label>
                                    <textarea name="observaciones" class="ac-field" rows="4"
                                        placeholder="Describe de manera general cómo se abordará la causa raíz...">{{ old('observaciones') }}</textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" class="btn-glass-light" onclick="togglePlanAccion()">Cancelar</button>
                                    <button type="submit" class="btn-glass-primary">Crear plan de acción</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <div class="p-6 pt-4">
                        @if($estadoActual === 'plan_accion' && $planActual)
                        <div id="formGestionPlan" class="mb-4">
                            <div class="ac-form-panel">
                                <div class="mb-1 font-semibold text-indigo-950">Gestionar plan de acción</div>
                                <p class="ac-card-sub mb-4">Agrega una actividad para implementar la acción correctiva.</p>
                                <form method="POST" action="{{ route('acciones-correctivas.planes.actividades.crear', $accionCorrectiva) }}">
                                    @csrf
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                        <div class="md:col-span-12">
                                            <label class="ac-label">Actividad</label>
                                            <textarea name="descripcion" class="ac-field" rows="3" required
                                                placeholder="Describe la acción que se realizará...">{{ old('descripcion') }}</textarea>
                                        </div>
                                        <div class="md:col-span-6">
                                            <label class="ac-label">Responsable</label>
                                            <select name="responsable_id" class="ac-field" required>
                                                <option value="">Seleccionar responsable</option>
                                                @foreach(\App\Models\User::query()->where('activo', true)->orderBy('name')->get() as $usuario)
                                                <option value="{{ $usuario->id }}" @selected(old('responsable_id')==$usuario->id)>{{ $usuario->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="md:col-span-6">
                                            <label class="ac-label">Fecha de compromiso</label>
                                            <input type="date" name="fecha_compromiso" class="ac-field" value="{{ old('fecha_compromiso') }}" required>
                                        </div>
                                        <div class="md:col-span-12">
                                            <label class="ac-label">Observaciones</label>
                                            <textarea name="observaciones" class="ac-field" rows="2" placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end gap-2">
                                        <a href="{{ url()->current() }}" class="btn-glass-light">Cancelar</a>
                                        <button type="submit" class="btn-glass-primary">Agregar actividad</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif

                        @forelse($accionCorrectiva->planesAccion->sortByDesc('ciclo') as $plan)
                        <div class="ac-plan-block mb-5">
                            <div class="ac-plan-head">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-semibold text-indigo-950">Plan de acción · Ciclo {{ $plan->ciclo }}</div>
                                        <div class="mt-1 text-xs text-gray-400">
                                            Inicio: {{ $plan->fecha_inicio?->format('d/m/Y') }}
                                            @if($plan->fecha_cierre) · Cierre: {{ $plan->fecha_cierre->format('d/m/Y') }} @endif
                                        </div>
                                    </div>
                                    <span class="badge-pill {{ $plan->estado === 'completado' ? 'badge-emerald' : 'badge-amber' }}">
                                        {{ ucfirst(str_replace('_', ' ', $plan->estado)) }}
                                    </span>
                                </div>
                                @if($plan->observaciones)
                                <div class="mt-2 text-sm text-gray-600">{{ $plan->observaciones }}</div>
                                @endif
                            </div>

                            <div class="p-4">
                                @forelse($plan->actividades as $actividad)
                                <div class="ac-subcard mb-3">

                                    {{-- Información de la actividad --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-gray-400">Actividad</div>
                                            <div class="mt-1 font-semibold text-indigo-950">{{ $actividad->descripcion }}</div>
                                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
                                                <span><i class="ti ti-user"></i> {{ $actividad->responsable?->name ?? 'Sin responsable' }}</span>
                                                <span><i class="ti ti-calendar-event"></i> Compromiso: {{ $actividad->fecha_compromiso?->format('d/m/Y') ?? '—' }}</span>
                                                @if($actividad->fecha_cumplimiento)
                                                <span><i class="ti ti-calendar-check"></i> Cumplida: {{ $actividad->fecha_cumplimiento->format('d/m/Y') }}</span>
                                                @endif
                                            </div>
                                            @if($actividad->observaciones)
                                            <div class="mt-2 text-sm text-gray-600">{{ $actividad->observaciones }}</div>
                                            @endif
                                        </div>
                                        <span class="badge-pill {{ $actividad->estado === 'completada' ? 'badge-emerald' : 'badge-amber' }}">
                                            {{ ucfirst(str_replace('_', ' ', $actividad->estado)) }}
                                        </span>
                                    </div>

                                    {{-- Evidencias existentes --}}
                                    @if($actividad->evidencias->isNotEmpty())
                                    <div class="mt-3 border-t border-black/5 pt-3">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-400">Evidencias</div>
                                        @foreach($actividad->evidencias as $evidencia)
                                        <div class="ac-evidence-row">
                                            <div class="flex items-center gap-2.5">
                                                <span class="ac-evidence-icon"><i class="ti ti-file-check"></i></span>
                                                <div>
                                                    <div class="text-sm font-semibold text-indigo-950">{{ $evidencia->nombre_original }}</div>
                                                    @if($evidencia->descripcion)
                                                    <div class="text-xs text-gray-400">{{ $evidencia->descripcion }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-400">{{ number_format($evidencia->tamano / 1024, 1) }} KB</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif

                                    {{-- Gestión de actividad --}}
                                    @if($actividad->estado !== 'completada')
                                    <div class="mt-3 border-t border-black/5 pt-3">
                                        <div class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">Gestión de actividad</div>

                                        {{-- Subir evidencia --}}
                                        <form method="POST" action="{{ route('acciones-correctivas.actividades.evidencias.crear', [$accionCorrectiva, $actividad]) }}"
                                            enctype="multipart/form-data" class="mb-3">
                                            @csrf
                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                                <div>
                                                    <label class="ac-label">Evidencia</label>
                                                    <input type="file" name="archivo" class="ac-field ac-field-file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                                                    <div class="mt-1 text-[11px] text-gray-400">Máximo 20 MB.</div>
                                                </div>
                                                <div>
                                                    <label class="ac-label">Descripción</label>
                                                    <input type="text" name="descripcion" class="ac-field" placeholder="Describe la evidencia...">
                                                </div>
                                            </div>
                                            <div class="mt-3 flex justify-end">
                                                <button type="submit" class="btn-glass-outline">Subir evidencia</button>
                                            </div>
                                        </form>

                                        {{-- Completar actividad --}}
                                        @if($actividad->evidencias->isNotEmpty())
                                        <form method="POST" action="{{ route('acciones-correctivas.actividades.completar', [$accionCorrectiva, $actividad]) }}">
                                            @csrf
                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end">
                                                <div class="md:col-span-9">
                                                    <label class="ac-label">Observaciones de cumplimiento</label>
                                                    <textarea name="observaciones" class="ac-field" rows="2" placeholder="Observaciones sobre el cumplimiento..."></textarea>
                                                </div>
                                                <div class="md:col-span-3">
                                                    <button type="submit" class="btn-glass-success w-full justify-center">
                                                        <i class="ti ti-check"></i> Completar actividad
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                        @else
                                        <div class="ac-warning-inline">Debes registrar al menos una evidencia antes de completar esta actividad.</div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <div class="ac-empty">Este plan no tiene actividades.</div>
                                @endforelse
                            </div>
                        </div>
                        @empty
                        <div class="ac-empty"><i class="ti ti-clipboard-list"></i> No existen planes de acción.</div>
                        @endforelse
                    </div>
                </section>

                {{-- VERIFICACIÓN DE CIERRE --}}
                <section class="ac-card ac-audit-card tilt-card reveal-3d">
                    <div class="ac-card-header">
                        <div>
                            <div class="ac-card-title"><i class="ti ti-clipboard-check text-dasavena-purple"></i> Verificación de cierre</div>
                            <p class="ac-card-sub">Confirmación de la implementación del plan.</p>
                        </div>
                        <span class="badge-pill badge-slate">{{ $accionCorrectiva->verificacionesCierre->count() }}</span>
                    </div>

                    @if($estadoActual === 'ejecucion')
                    <div class="px-6 pb-2 pt-4">
                        <div class="ac-form-panel">
                            <div class="mb-1 font-semibold text-indigo-950">Registrar verificación de cierre</div>
                            <p class="ac-card-sub mb-4">Confirma que el plan de acción fue implementado correctamente y que cuenta con las evidencias necesarias.</p>
                            <form method="POST" action="{{ route('acciones-correctivas.verificaciones-cierre.crear', $accionCorrectiva) }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <label class="ac-check-card">
                                        <input type="checkbox" name="acciones_implementadas" value="1" id="acciones_implementadas" required>
                                        <span class="ac-check-box"></span>
                                        <span>
                                            <span class="block font-semibold text-indigo-950">Acciones implementadas</span>
                                            <span class="mt-1 block text-xs text-gray-400">Confirma que las acciones del plan fueron ejecutadas.</span>
                                        </span>
                                    </label>
                                    <label class="ac-check-card">
                                        <input type="checkbox" name="evidencias_completas" value="1" id="evidencias_completas" required>
                                        <span class="ac-check-box"></span>
                                        <span>
                                            <span class="block font-semibold text-indigo-950">Evidencias completas</span>
                                            <span class="mt-1 block text-xs text-gray-400">Confirma que cada actividad cuenta con evidencia.</span>
                                        </span>
                                    </label>
                                    <label class="ac-check-card">
                                        <input type="checkbox" name="implementacion_conforme" value="1" id="implementacion_conforme" required>
                                        <span class="ac-check-box"></span>
                                        <span>
                                            <span class="block font-semibold text-indigo-950">Implementación conforme</span>
                                            <span class="mt-1 block text-xs text-gray-400">Confirma que la implementación corresponde al plan aprobado.</span>
                                        </span>
                                    </label>
                                    <div class="md:col-span-3">
                                        <label class="ac-label">Resultado</label>
                                        <textarea name="resultado" class="ac-field" rows="3" placeholder="Describe el resultado de la verificación...">{{ old('resultado') }}</textarea>
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="ac-label">Observaciones</label>
                                        <textarea name="observaciones" class="ac-field" rows="3" placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <button type="submit" class="btn-glass-success">Registrar verificación</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <div class="p-6 pt-4">
                        @forelse($accionCorrectiva->verificacionesCierre->sortByDesc('ciclo') as $verificacion)
                        <div class="ac-subcard mb-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-indigo-950">Ciclo {{ $verificacion->ciclo }}</div>
                                    <div class="mt-1 text-xs text-gray-400">{{ $verificacion->fecha_verificacion?->format('d/m/Y') }}</div>
                                </div>
                                <span class="badge-pill badge-emerald">Aprobada</span>
                            </div>
                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                                <div>
                                    <div class="text-xs text-gray-400">Acciones implementadas</div>
                                    <div class="font-semibold text-emerald-600">✓ Sí</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Evidencias completas</div>
                                    <div class="font-semibold text-emerald-600">✓ Sí</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Implementación conforme</div>
                                    <div class="font-semibold text-emerald-600">✓ Sí</div>
                                </div>
                            </div>
                            @if($verificacion->resultado)
                            <div class="mt-3">
                                <div class="text-xs text-gray-400">Resultado</div>
                                <div class="text-sm text-gray-700">{{ $verificacion->resultado }}</div>
                            </div>
                            @endif
                            @if($verificacion->observaciones)
                            <div class="mt-3">
                                <div class="text-xs text-gray-400">Observaciones</div>
                                <div class="text-sm text-gray-700">{{ $verificacion->observaciones }}</div>
                            </div>
                            @endif
                            <div class="mt-3 border-t border-black/5 pt-3 text-xs text-gray-400">
                                Verificado por: <strong class="text-indigo-950">{{ $verificacion->verificadoPor?->name ?? 'Sin usuario' }}</strong>
                            </div>
                        </div>
                        @empty
                        <div class="ac-empty">No existe una verificación de cierre registrada.</div>
                        @endforelse
                    </div>
                </section>

                {{-- ESPERA DE EFICACIA --}}
                <section class="ac-card tilt-card reveal-3d">
                    <div class="ac-card-header">
                        <div>
                            <div class="ac-card-title"><i class="ti ti-hourglass-high text-dasavena-purple"></i> Espera de eficacia</div>
                            <p class="ac-card-sub">Periodo establecido para comprobar la efectividad.</p>
                        </div>
                        <span class="badge-pill badge-slate">{{ $accionCorrectiva->esperasEficacia->count() }}</span>
                    </div>

                    @if($estadoActual === 'verificacion_cierre' && !$esperaActual)
                    <div class="px-6 pb-2 pt-4">
                        <div class="ac-form-panel">
                            <div class="mb-1 font-semibold text-indigo-950">Iniciar espera de eficacia</div>
                            <p class="ac-card-sub mb-4">Define el periodo durante el cual se esperará antes de realizar la verificación de eficacia de la Acción Correctiva.</p>
                            <form method="POST" action="{{ route('acciones-correctivas.esperas-eficacia.crear', $accionCorrectiva) }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="ac-label">Responsable</label>
                                        <select name="responsable_id" class="ac-field" required>
                                            <option value="">Seleccionar responsable</option>
                                            @foreach(\App\Models\User::query()->where('activo', true)->orderBy('name')->get() as $usuario)
                                            <option value="{{ $usuario->id }}" @selected(old('responsable_id')==$usuario->id)>{{ $usuario->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="ac-label">Días de espera</label>
                                        <input type="number" name="dias_espera" class="ac-field" min="1" value="{{ old('dias_espera', 30) }}" required>
                                        <div class="mt-1 text-[11px] text-gray-400">La fecha de verificación se calculará automáticamente.</div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="ac-label">Observaciones</label>
                                        <textarea name="observaciones" class="ac-field" rows="3" placeholder="Observaciones sobre el periodo de espera...">{{ old('observaciones') }}</textarea>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <button type="submit" class="btn-glass-primary">Iniciar espera de eficacia</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <div class="p-6 pt-4">
                        @forelse($accionCorrectiva->esperasEficacia->sortByDesc('ciclo') as $espera)
                        <div class="ac-subcard mb-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-indigo-950">Ciclo {{ $espera->ciclo }}</div>
                                    <div class="mt-1 text-xs text-gray-400">
                                        {{ $espera->fecha_inicio?->format('d/m/Y') }} → {{ $espera->fecha_verificacion?->format('d/m/Y') }}
                                    </div>
                                </div>
                                <span class="badge-pill {{ $espera->estado === 'completada' ? 'badge-emerald' : 'badge-amber' }}">
                                    {{ ucfirst(str_replace('_', ' ', $espera->estado)) }}
                                </span>
                            </div>
                            <div class="mt-3 text-xs text-gray-400">
                                Responsable: <strong class="text-indigo-950">{{ $espera->responsable?->name ?? 'Sin responsable' }}</strong>
                            </div>
                            @if($espera->observaciones)
                            <div class="mt-2 text-sm text-gray-600">{{ $espera->observaciones }}</div>
                            @endif
                        </div>
                        @empty
                        <div class="ac-empty">No existe un periodo de espera registrado.</div>
                        @endforelse
                    </div>
                </section>

                {{-- VERIFICACIÓN DE EFICACIA --}}
                @php
                $ultimaVerifEficacia = $accionCorrectiva->verificacionesEficacia->sortByDesc('ciclo')->first();
                $huboEficaciaNegativa = $ultimaVerifEficacia && !$ultimaVerifEficacia->resultado_eficaz;
                @endphp
                <section class="ac-card tilt-card reveal-3d {{ $huboEficaciaNegativa ? 'ac-audit-card ac-audit-card-negative' : '' }}">
                    <div class="ac-card-header">
                        <div>
                            <div class="ac-card-title"><i class="ti ti-shield-{{ $huboEficaciaNegativa ? 'x' : 'check' }} {{ $huboEficaciaNegativa ? 'text-rose-600' : 'text-dasavena-purple' }}"></i> Verificación de eficacia</div>
                            <p class="ac-card-sub">Resultado de la evaluación de eficacia.</p>
                        </div>
                        <span class="badge-pill badge-slate">{{ $accionCorrectiva->verificacionesEficacia->count() }}</span>
                    </div>

                    @if($estadoActual === 'verificacion_eficacia' && !$verificacionEficaciaActual)
                    <div class="px-6 pb-2 pt-4">
                        <div class="ac-form-panel">
                            <div class="mb-1 font-semibold text-indigo-950">Registrar verificación de eficacia</div>
                            <p class="ac-card-sub mb-4">Evalúa si las acciones implementadas fueron eficaces después del periodo de espera.</p>
                            <form method="POST" action="{{ route('acciones-correctivas.verificaciones-eficacia.crear', $accionCorrectiva) }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="ac-label">Criterios cumplidos</label>
                                        <select name="criterios_cumplidos" class="ac-field" required>
                                            <option value="">Seleccionar</option>
                                            <option value="1" @selected(old('criterios_cumplidos')==='1')>Sí</option>
                                            <option value="0" @selected(old('criterios_cumplidos')==='0')>No</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="ac-label">Resultado eficaz</label>
                                        <select name="resultado_eficaz" class="ac-field" required>
                                            <option value="">Seleccionar</option>
                                            <option value="1" @selected(old('resultado_eficaz')==='1')>Sí, fue eficaz</option>
                                            <option value="0" @selected(old('resultado_eficaz')==='0')>No, no fue eficaz</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="ac-label">Resultado</label>
                                        <textarea name="resultado" class="ac-field" rows="4" required
                                            placeholder="Describe el resultado de la verificación de eficacia...">{{ old('resultado') }}</textarea>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="ac-label">Observaciones</label>
                                        <textarea name="observaciones" class="ac-field" rows="3" placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <button type="submit" class="btn-glass-primary">Registrar verificación</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <div class="p-6 pt-4">
                        @forelse($accionCorrectiva->verificacionesEficacia->sortByDesc('ciclo') as $verificacion)
                        @php $eficaz = (bool) $verificacion->resultado_eficaz; @endphp
                        <div class="ac-subcard mb-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-indigo-950">Ciclo {{ $verificacion->ciclo }}</div>
                                    <div class="mt-1 text-xs text-gray-400">{{ $verificacion->fecha_verificacion?->format('d/m/Y') }}</div>
                                </div>
                                <span class="badge-pill {{ $eficaz ? 'badge-emerald' : 'badge-rose' }}">{{ $eficaz ? 'Eficaz' : 'No eficaz' }}</span>
                            </div>
                            <div class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <div class="text-xs text-gray-400">Criterios cumplidos</div>
                                    <div class="font-semibold {{ $verificacion->criterios_cumplidos ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $verificacion->criterios_cumplidos ? '✓ Sí' : '✕ No' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-400">Resultado</div>
                                    <div class="font-semibold {{ $eficaz ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $eficaz ? 'Las acciones fueron eficaces' : 'Las acciones no fueron eficaces' }}
                                    </div>
                                </div>
                            </div>
                            @if($verificacion->resultado)
                            <div class="mt-3">
                                <div class="text-xs text-gray-400">Resultado de la verificación</div>
                                <div class="text-sm text-gray-700">{{ $verificacion->resultado }}</div>
                            </div>
                            @endif
                            @if($verificacion->observaciones)
                            <div class="mt-3">
                                <div class="text-xs text-gray-400">Observaciones</div>
                                <div class="text-sm text-gray-700">{{ $verificacion->observaciones }}</div>
                            </div>
                            @endif
                            @if(!$eficaz)
                            <div class="mt-3 flex items-center justify-between rounded-xl bg-rose-50 px-3 py-2 text-xs text-rose-700">
                                <span><i class="ti ti-arrow-back-up"></i> <strong>No eficaz</strong> → dio origen a un nuevo ciclo.</span>
                            </div>
                            @endif
                            <div class="mt-3 border-t border-black/5 pt-3 text-xs text-gray-400">
                                Verificado por: <strong class="text-indigo-950">{{ $verificacion->verificadoPor?->name ?? 'Sin usuario' }}</strong>
                            </div>
                        </div>
                        @empty
                        <div class="ac-empty">No existe una verificación de eficacia registrada.</div>
                        @endforelse
                    </div>
                </section>

                {{-- HISTORIAL DE CICLOS (ACORDEÓN) --}}
                <section class="ac-card tilt-card reveal-3d">
                    <div class="ac-card-header">
                        <div>
                            <div class="ac-card-title"><i class="ti ti-history text-dasavena-purple"></i> Historial de ciclos</div>
                            <p class="ac-card-sub">Evolución de la Acción Correctiva.</p>
                        </div>
                        <span class="badge-pill badge-slate">{{ $accionCorrectiva->analisis->count() }} ciclos</span>
                    </div>
                    <div class="flex flex-col gap-2 p-6 pt-4">
                        @forelse($accionCorrectiva->analisis->sortByDesc('ciclo') as $analisis)
                        @php
                        $verificacion = $accionCorrectiva->verificacionesEficacia
                        ->where('ciclo', $analisis->ciclo)
                        ->sortByDesc('id')
                        ->first();
                        $esActual = $analisis->ciclo == $accionCorrectiva->ciclo_actual;
                        @endphp
                        <details class="ac-accordion-item" {{ $esActual ? 'open' : '' }}>
                            <summary class="ac-accordion-summary">
                                <span class="ac-timeline-node {{ $esActual ? 'ac-timeline-node-active' : '' }}">{{ $analisis->ciclo }}</span>
                                <span class="flex-1">
                                    <span class="block font-semibold text-indigo-950">Ciclo {{ $analisis->ciclo }}</span>
                                    <span class="block text-xs text-gray-400">Inicio: {{ $analisis->fecha_inicio?->format('d/m/Y') }}</span>
                                </span>
                                @if($verificacion)
                                <span class="badge-pill {{ $verificacion->resultado_eficaz ? 'badge-emerald' : 'badge-rose' }}">
                                    {{ $verificacion->resultado_eficaz ? 'Eficaz' : 'No eficaz' }}
                                </span>
                                @elseif($esActual)
                                <span class="badge-pill badge-purple">En curso</span>
                                @else
                                <span class="badge-pill badge-slate">Sin verificación</span>
                                @endif
                                <i class="ti ti-chevron-down ac-accordion-chevron"></i>
                            </summary>
                            <div class="ac-accordion-body">
                                <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                    <div><span class="text-xs text-gray-400">Estado del análisis:</span> <strong class="text-indigo-950">{{ ucfirst(str_replace('_', ' ', $analisis->estado)) }}</strong></div>
                                    <div><span class="text-xs text-gray-400">Causas raíz registradas:</span> <strong class="text-indigo-950">{{ $analisis->causasRaiz?->count() ?? 0 }}</strong></div>
                                </div>
                                @if($verificacion && !$verificacion->resultado_eficaz)
                                <div class="mt-3 text-xs text-rose-600">El ciclo fue rechazado en la verificación de eficacia y dio origen a un nuevo ciclo.</div>
                                @endif
                            </div>
                        </details>
                        @empty
                        <div class="ac-empty">No existe historial de ciclos.</div>
                        @endforelse
                    </div>
                </section>

            </div>

            {{-- ═══════════════════ PANEL LATERAL (STICKY) ═══════════════════ --}}
            <div class="flex flex-col gap-6 lg:col-span-4">
                <div class="ac-sidebar-sticky flex flex-col gap-6">

                    {{-- PANEL DE CONTROL --}}
                    <section class="ac-card ac-panel-control tilt-card reveal-3d overflow-hidden">
                        <div class="ac-panel-control-head">
                            <span class="ac-card-title text-white"><i class="ti ti-compass"></i> Panel de control</span>
                            <span class="badge-pill badge-gold-solid">Ciclo {{ $accionCorrectiva->ciclo_actual }}</span>
                        </div>
                        <div class="p-6">
                            <div class="mb-4 text-center">
                                <div class="text-[10.5px] font-bold uppercase tracking-widest text-gray-400">Fase operativa</div>
                                <div class="mt-1 font-display text-lg font-extrabold text-dasavena-purple">{{ $estados[$estadoActual] ?? 'Sin estado' }}</div>
                            </div>

                            <div class="mb-4">
                                <div class="mb-1 flex items-center justify-between text-xs font-semibold text-gray-500">
                                    <span>Avance de etapas</span>
                                    <span>{{ $estadoOrden }} de {{ count($estados) - 1 }}</span>
                                </div>
                                <div class="ac-progress-track">
                                    <div class="ac-progress-fill" style="width: {{ $estadoOrden > 0 ? ($estadoOrden / (count($estados) - 1)) * 100 : 0 }}%;"></div>
                                </div>
                            </div>

                            @php
                            $siguienteEstado = array_slice(array_keys($estados), $estadoOrden + 1, 1)[0] ?? null;
                            @endphp
                            @if($siguienteEstado)
                            <div class="ac-next-hint">
                                <div class="text-[10.5px] font-bold uppercase tracking-widest text-gray-400"><i class="ti ti-arrow-right-circle text-dasavena-purple"></i> Próximo hito</div>
                                <div class="mt-1 font-semibold text-indigo-950">{{ $estados[$siguienteEstado] }}</div>
                            </div>
                            @else
                            <div class="ac-next-hint ac-next-hint-done">
                                <i class="ti ti-circle-check"></i> Expediente cerrado
                            </div>
                            @endif
                        </div>
                    </section>

                    {{-- ACCIONES DISPONIBLES --}}
                    <section id="acciones-seccion" class="ac-card tilt-card reveal-3d">
                        <div class="ac-card-header">
                            <div>
                                <div class="ac-card-title"><i class="ti ti-bolt text-dasavena-gold-dark"></i> Acciones disponibles</div>
                                <p class="ac-card-sub">Según el estado actual del proceso.</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2.5 p-6 pt-4">
                            @if(!empty($acciones))
                            @foreach($acciones as $accion)

                            {{-- Borrador → abrir  --}}
                            @if(
                            $accion['accion'] === 'abrir' &&
                            isset($accion['estado_destino'])
                            )
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card {{ $accion['tipo'] === 'primary' ? 'ac-action-card-primary' : '' }}">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Nuevo estado: {{ $estados[$accion['estado_destino']] ?? '' }}</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>

                            {{-- Gestionar / finalizar contención --}}
                            @elseif($accion['accion'] === 'contencion')

                            @if(
                            $estadoActual === 'abierta' &&
                            $accionCorrectiva->contenciones->isNotEmpty()
                            )
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="contencion">
                                <button type="submit" class="ac-action-card ac-action-card-primary">
                                    <span class="ac-action-icon"><i class="ti ti-check"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">Finalizar contención y continuar</span>
                                        <span class="block text-xs opacity-70">Avanza a: Análisis</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>
                            @else
                            <button type="button" class="ac-action-card {{ $accion['tipo'] === 'primary' ? 'ac-action-card-primary' : '' }}" onclick="toggleContencion()">
                                <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                <span class="flex-1 text-left">
                                    <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                    <span class="block text-xs opacity-70">Registrar medida temporal</span>
                                </span>
                                <i class="ti ti-chevron-right ac-action-chevron"></i>
                            </button>
                            @endif

                            {{-- Contención → Análisis --}}
                            @elseif(
                            $accion['accion'] === 'analisis' &&
                            $estadoActual === 'contencion'
                            )
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card {{ $accion['tipo'] === 'primary' ? 'ac-action-card-primary' : '' }}">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Nuevo estado: {{ $estados[$accion['estado_destino']] ?? '' }}</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>

                            {{-- Gestionar análisis --}}
                            @elseif(
                            $accion['accion'] === 'analisis' &&
                            $estadoActual === 'analisis'
                            )
                            <a href="{{ route('acciones-correctivas.analisis', $accionCorrectiva) }}" class="ac-action-card ac-action-card-primary">
                                <span class="ac-action-icon"><i class="ti ti-search"></i></span>
                                <span class="flex-1 text-left">
                                    <span class="block font-semibold">Gestionar análisis</span>
                                    <span class="block text-xs opacity-70">5 Porqués, ideas y causa raíz</span>
                                </span>
                                <i class="ti ti-chevron-right ac-action-chevron"></i>
                            </a>

                            @elseif(
                            $accion['accion'] === 'validacion_causa' &&
                            isset($accion['estado_destino'])
                            )
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card {{ $accion['tipo'] === 'primary' ? 'ac-action-card-primary' : '' }}">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Nuevo estado: {{ $estados[$accion['estado_destino']] ?? '' }}</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>

                            {{-- Validación de causa → Plan de acción --}}
                            @elseif($accion['accion'] === 'plan_accion')
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card {{ $accion['tipo'] === 'primary' ? 'ac-action-card-primary' : '' }}">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Nuevo estado: {{ $estados[$accion['estado_destino']] ?? '' }}</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>

                            {{-- Plan de acción → Ejecución --}}
                            @elseif($accion['accion'] === 'ejecucion')
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card {{ $accion['tipo'] === 'primary' ? 'ac-action-card-primary' : '' }}">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Nuevo estado: {{ $estados[$accion['estado_destino']] ?? '' }}</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>

                            @elseif(
                            $accion['accion'] === 'verificacion_cierre' &&
                            isset($accion['estado_destino'])
                            )
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card {{ $accion['tipo'] === 'primary' ? 'ac-action-card-primary' : '' }}">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Nuevo estado: {{ $estados[$accion['estado_destino']] ?? '' }}</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>

                            @elseif(
                            $accion['accion'] === 'espera_eficacia' &&
                            isset($accion['estado_destino'])
                            )

                            @if(
                            $estadoActual === 'espera_eficacia' &&
                            $esperaActual?->fecha_verificacion &&
                            now()->startOfDay()->lt($esperaActual->fecha_verificacion)
                            )
                            <span class="ac-action-card ac-action-card-disabled">
                                <span class="ac-action-icon"><i class="ti ti-hourglass-low"></i></span>
                                <span class="flex-1 text-left">
                                    <span class="block font-semibold">Disponible el {{ $esperaActual->fecha_verificacion->format('d/m/Y') }}</span>
                                    <span class="block text-xs opacity-70">En espera de eficacia</span>
                                </span>
                            </span>
                            @else
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card {{ $accion['tipo'] === 'primary' ? 'ac-action-card-primary' : '' }}">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Nuevo estado: {{ $estados[$accion['estado_destino']] ?? '' }}</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>
                            @endif

                            {{-- Eficacia negativa → Nuevo ciclo --}}
                            @elseif(
                            $accion['accion'] === 'nuevo_ciclo' &&
                            isset($accion['estado_destino'])
                            )
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card ac-action-card-danger">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Reinicia el análisis en un nuevo ciclo</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>

                            {{-- Verificación de eficacia → Cierre --}}
                            @elseif(
                            $accion['accion'] === 'cerrar' &&
                            isset($accion['estado_destino'])
                            )
                            <form method="POST" action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="estado" value="{{ $accion['estado_destino'] }}">
                                <button type="submit" class="ac-action-card ac-action-card-success">
                                    <span class="ac-action-icon"><i class="ti {{ $accionIcon[$accion['accion']] ?? 'ti-arrow-right' }}"></i></span>
                                    <span class="flex-1 text-left">
                                        <span class="block font-semibold">{{ $accion['texto'] }}</span>
                                        <span class="block text-xs opacity-70">Cierra el expediente</span>
                                    </span>
                                    <i class="ti ti-chevron-right ac-action-chevron"></i>
                                </button>
                            </form>
                            @endif

                            @endforeach
                            @else
                            <div class="ac-empty">No hay acciones disponibles en este momento.</div>
                            @endif
                        </div>
                    </section>

                </div>
            </div>

        </div>

        </div>
    </div>

    {{-- ─────────────────────────────────────────────
         ESTILOS — Liquid glass + 3D (paleta Dasavena)
    ───────────────────────────────────────────── --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css">
    <style>
    :root {
        --purple: #6A2C75;
        --purple-light: #8E3D9E;
        --purple-dark: #4a1f55;
        --gold: #D6A644;
        --gold-dark: #b38600;
    }

    .ac-bg { background-size: cover; background-position: center; background-attachment: fixed; opacity: .55; }
    .ac-veil {
        background: radial-gradient(ellipse 90% 55% at 50% -5%, rgba(255,255,255,.65) 0%, transparent 65%),
                    linear-gradient(180deg, rgba(250,246,251,.55) 0%, rgba(240,232,243,.7) 100%);
    }

    /* ── Reveal 3D al hacer scroll ── */
    .reveal-3d {
        opacity: 0;
        transform: perspective(1200px) translateY(36px) rotateX(5deg);
        transform-origin: top center;
        transition: opacity .65s cubic-bezier(.22,1,.36,1), transform .65s cubic-bezier(.22,1,.36,1);
    }
    .reveal-3d.is-visible { opacity: 1; transform: perspective(1200px) translateY(0) rotateX(0deg); }

    .tilt-card { transition: transform .25s cubic-bezier(.22,1,.36,1), box-shadow .25s ease; will-change: transform; }

    /* ── Breadcrumb ── */
    .ac-crumb-link { display:inline-flex; align-items:center; gap:4px; color: inherit; text-decoration:none; }
    .ac-crumb-link:hover { color: var(--purple); }

    /* ── Tarjetas liquid glass ── */
    .ac-card {
        background: rgba(255,255,255,.68);
        border: 1px solid rgba(255,255,255,.7);
        border-radius: 24px;
        backdrop-filter: blur(20px) saturate(160%);
        -webkit-backdrop-filter: blur(20px) saturate(160%);
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 8px 32px rgba(106,44,117,.08);
    }
    .ac-card-hero {
        background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 55%, var(--purple-light) 100%);
        border-color: rgba(255,255,255,.15);
        box-shadow: 0 12px 44px rgba(106,44,117,.3);
    }
    .ac-hero-dotgrid {
        background-image: radial-gradient(rgba(255,255,255,.9) 1.5px, transparent 1.5px);
        background-size: 22px 22px;
        opacity: .06;
    }
    .gold-gleam {
        background: linear-gradient(90deg, transparent 0%, var(--gold) 30%, rgba(255,255,255,.8) 50%, var(--gold) 70%, transparent 100%);
        background-size: 200% 100%;
        animation: gold-shimmer 3.5s ease-in-out infinite;
    }
    @keyframes gold-shimmer { 0%{background-position:100% 0} 100%{background-position:-100% 0} }

    .ac-eyebrow { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .14em; color: rgba(255,255,255,.55); }
    .ac-code-chip { font-family: ui-monospace, 'SFMono-Regular', Consolas, monospace; font-size: 12.5px; font-weight: 800; padding: 3px 10px; border-radius: 8px; background: rgba(255,255,255,.12); color: #fff; border: 1px solid rgba(255,255,255,.2); }

    .ac-hero-cta {
        display:inline-flex; align-items:center; gap:8px; padding: 11px 20px; border-radius: 14px;
        font-size: 13px; font-weight: 700; color: var(--purple-dark);
        background: linear-gradient(135deg, #fff, #f3e7d8);
        box-shadow: 0 10px 26px rgba(0,0,0,.22);
        transition: transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s ease;
        text-decoration: none;
    }
    .ac-hero-cta:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(0,0,0,.28); }

    .ac-card-header {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
        padding: 22px 24px 4px;
    }
    .ac-card-title { font-family: inherit; font-size: 15.5px; font-weight: 800; color: #1e1b4b; display:flex; align-items:center; gap:8px; }
    .ac-card-sub { font-size: 12.5px; color: #9ca3af; margin-top: 2px; }

    .ac-kpi-card {
        background: rgba(255,255,255,.72);
        border: 1px solid rgba(255,255,255,.8);
        border-radius: 18px;
        backdrop-filter: blur(14px) saturate(150%);
        -webkit-backdrop-filter: blur(14px) saturate(150%);
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 4px 18px rgba(106,44,117,.06);
        padding: 16px;
    }
    .ac-kpi-icon { display:inline-flex; align-items:center; justify-content:center; width: 34px; height: 34px; border-radius: 10px; font-size: 15px; margin-bottom: 8px; }
    .ac-kpi-icon-purple  { background: rgba(106,44,117,.1); color: var(--purple); }
    .ac-kpi-icon-gold    { background: rgba(214,166,68,.15); color: var(--gold-dark); }
    .ac-kpi-icon-emerald { background: rgba(5,150,105,.1); color: #059669; }
    .ac-kpi-icon-sky     { background: rgba(14,165,233,.1); color: #0284c7; }
    .ac-kpi-icon-amber   { background: rgba(217,119,6,.1); color: #d97706; }
    .ac-kpi-icon-rose    { background: rgba(220,38,38,.1); color: #dc2626; }
    .ac-kpi-icon-slate   { background: rgba(148,163,184,.15); color: #64748b; }
    .ac-kpi-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; }
    .ac-kpi-value { margin-top: 3px; font-family: ui-monospace, monospace; font-size: 22px; font-weight: 800; color: #1e1b4b; }
    .ac-kpi-sub { margin-top: 2px; font-size: 11px; color: #9ca3af; }

    .ac-mini-card, .ac-subcard, .ac-plan-block, .ac-form-panel, .ac-flow-stage {
        background: rgba(255,255,255,.7);
        border: 1px solid rgba(255,255,255,.8);
        border-radius: 18px;
        backdrop-filter: blur(14px) saturate(150%);
        -webkit-backdrop-filter: blur(14px) saturate(150%);
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 4px 18px rgba(106,44,117,.06);
        padding: 18px;
    }
    .ac-mini-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #9ca3af; }
    .ac-flow-stage-active { border-color: rgba(106,44,117,.35); background: rgba(106,44,117,.07); box-shadow: 0 4px 16px rgba(106,44,117,.1); }

    .ac-form-panel { background: rgba(255,255,255,.55); border-style: dashed; border-color: rgba(106,44,117,.25); }

    .ac-plan-block { padding: 0; overflow: hidden; }
    .ac-plan-head { background: rgba(106,44,117,.05); padding: 16px 18px; border-bottom: 1px solid rgba(0,0,0,.05); }

    .ac-evidence-row {
        display:flex; align-items:center; justify-content:space-between; gap:10px;
        border: 1px solid rgba(0,0,0,.06); border-radius: 12px; padding: 8px 12px; margin-bottom: 8px;
        background: rgba(255,255,255,.6);
    }
    .ac-evidence-icon { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; background: rgba(106,44,117,.08); color: var(--purple); font-size: 13px; }

    .ac-empty { text-align:center; padding: 28px 12px; color: #9ca3af; font-size: 13.5px; }

    .ac-warning-inline {
        border-radius: 12px; background: rgba(217,119,6,.1); border: 1px solid rgba(217,119,6,.25);
        color: #92400e; padding: 10px 14px; font-size: 12.5px;
    }

    /* ── Progreso ── */
    .ac-progress-track { height: 10px; border-radius: 9999px; background: rgba(106,44,117,.08); overflow: hidden; }
    .ac-progress-track-sm { height: 6px; }
    .ac-progress-fill { height: 100%; border-radius: 9999px; background: linear-gradient(90deg, var(--purple-light), var(--purple)); transition: width 1s cubic-bezier(.22,1,.36,1); }
    .ac-progress-success { background: linear-gradient(90deg, #34d399, #059669); }

    /* ── Stepper horizontal ── */
    .ac-stepper { position: relative; overflow-x: auto; padding-bottom: 4px; }
    .ac-stepper-line { position: relative; height: 3px; border-radius: 9999px; background: rgba(0,0,0,.08); margin: 0 20px 18px; }
    .ac-stepper-line-fill { position:absolute; inset:0; border-radius: 9999px; background: linear-gradient(90deg, var(--purple-light), var(--purple)); transition: width 1s cubic-bezier(.22,1,.36,1); }
    .ac-stepper-items { display:flex; justify-content: space-between; gap: 6px; min-width: 760px; }
    .ac-stepper-item { display:flex; flex: 1 1 0; flex-direction: column; align-items:center; gap:6px; text-align:center; }
    .ac-step-icon { display:inline-flex; align-items:center; justify-content:center; width: 30px; height: 30px; border-radius: 50%; font-size: 12px; font-weight: 800; flex-shrink:0; }
    .ac-step-icon-done { background: #059669; color: #fff; }
    .ac-step-icon-active { background: var(--purple); color: #fff; box-shadow: 0 0 0 5px rgba(106,44,117,.18); }
    .ac-step-icon-pending { background: rgba(0,0,0,.06); color: #9ca3af; }
    .ac-step-label { font-size: 10.5px; font-weight: 700; color: #6b7280; max-width: 88px; }
    .ac-step-label-active { color: var(--purple); }

    /* ── Timeline vertical (contenciones) ── */
    .ac-timeline-row { display:flex; gap: 12px; margin-bottom: 12px; }
    .ac-timeline-node {
        flex-shrink:0; width: 34px; height: 34px; border-radius: 50%;
        display:flex; align-items:center; justify-content:center; font-weight: 800; font-size: 13px;
        background: rgba(0,0,0,.04); border: 1px solid rgba(0,0,0,.08); color: #9ca3af;
    }
    .ac-timeline-node-done { background: rgba(5,150,105,.12); color: #059669; border-color: rgba(5,150,105,.3); }
    .ac-timeline-node-active { background: var(--purple); color: #fff; border-color: transparent; box-shadow: 0 4px 14px rgba(106,44,117,.35); }

    /* ── Auditoría (verificación de cierre / eficacia) ── */
    .ac-audit-card { border-left: 4px solid var(--purple); border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
    .ac-audit-card-negative { border-left-color: #dc2626; background: rgba(254,242,242,.5); }

    /* ── Acordeón (historial de ciclos) ── */
    .ac-accordion-item { border: 1px solid rgba(0,0,0,.06); border-radius: 14px; background: rgba(255,255,255,.5); overflow: hidden; }
    .ac-accordion-summary { list-style: none; cursor: pointer; display:flex; align-items:center; gap: 12px; padding: 14px 16px; }
    .ac-accordion-summary::-webkit-details-marker { display:none; }
    .ac-accordion-chevron { transition: transform .2s ease; color: #9ca3af; }
    details[open] > .ac-accordion-summary .ac-accordion-chevron { transform: rotate(180deg); }
    .ac-accordion-body { padding: 0 16px 16px 62px; }

    /* ── Panel de control (sidebar) ── */
    .ac-sidebar-sticky { position: static; }
    @media (min-width: 1024px) { .ac-sidebar-sticky { position: sticky; top: 24px; align-self: flex-start; } }
    .ac-panel-control-head {
        display:flex; align-items:center; justify-content:space-between; gap:10px;
        padding: 18px 22px; background: linear-gradient(135deg, var(--purple-dark), var(--purple));
    }
    .badge-gold-solid { background: var(--gold); color: #4a1f55; border: none; }
    .ac-next-hint { border-radius: 14px; padding: 12px 14px; background: rgba(106,44,117,.06); border: 1px solid rgba(106,44,117,.15); }
    .ac-next-hint-done { display:flex; align-items:center; gap:6px; color: #059669; font-weight: 700; background: rgba(5,150,105,.08); border-color: rgba(5,150,105,.2); }

    /* ── Acciones disponibles (hub) ── */
    .ac-action-card {
        display:flex; align-items:center; gap:12px; width: 100%; text-align: left;
        padding: 13px 14px; border-radius: 14px; border: 1px solid rgba(0,0,0,.07);
        background: rgba(255,255,255,.65); color: #1e293b; text-decoration:none; cursor:pointer;
        transition: transform .2s cubic-bezier(.22,1,.36,1), border-color .2s ease, background .2s ease;
    }
    .ac-action-card:hover { transform: translateX(4px); border-color: rgba(106,44,117,.3); background: rgba(255,255,255,.9); }
    .ac-action-card-primary { background: rgba(106,44,117,.08); border-color: rgba(106,44,117,.25); color: var(--purple-dark); }
    .ac-action-card-success { background: rgba(5,150,105,.08); border-color: rgba(5,150,105,.25); color: #065f46; }
    .ac-action-card-danger { background: rgba(220,38,38,.08); border-color: rgba(220,38,38,.25); color: #991b1b; }
    .ac-action-card-disabled { opacity: .6; cursor: default; }
    .ac-action-card-disabled:hover { transform: none; }
    .ac-action-icon { flex-shrink:0; display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:11px; background: rgba(0,0,0,.05); font-size:16px; }
    .ac-action-card-primary .ac-action-icon { background: rgba(106,44,117,.15); }
    .ac-action-card-success .ac-action-icon { background: rgba(5,150,105,.15); }
    .ac-action-card-danger .ac-action-icon { background: rgba(220,38,38,.15); }
    .ac-action-chevron { flex-shrink:0; opacity: .35; transition: transform .2s ease, opacity .2s ease; }
    .ac-action-card:hover .ac-action-chevron { opacity: 1; transform: translateX(2px); }

    /* ── Badges (pill) ── */
    .badge-pill {
        display:inline-flex; align-items:center; gap:5px; padding: 4px 12px; border-radius: 9999px;
        font-size: 11.5px; font-weight: 700; white-space: nowrap; border: 1px solid transparent;
    }
    .badge-purple  { background: rgba(106,44,117,.1); color: var(--purple); border-color: rgba(106,44,117,.2); }
    .badge-gold-outline { background: rgba(214,166,68,.15); color: var(--gold-dark); border-color: rgba(214,166,68,.35); }
    .badge-slate   { background: rgba(148,163,184,.15); color: #475569; border-color: rgba(148,163,184,.3); }
    .badge-sky     { background: rgba(14,165,233,.1); color: #0369a1; border-color: rgba(14,165,233,.25); }
    .badge-amber   { background: rgba(217,119,6,.1); color: #92400e; border-color: rgba(217,119,6,.25); }
    .badge-emerald { background: rgba(5,150,105,.1); color: #065f46; border-color: rgba(5,150,105,.25); }
    .badge-rose    { background: rgba(220,38,38,.1); color: #991b1b; border-color: rgba(220,38,38,.25); }

    /* ── Botones liquid glass ── */
    .btn-glass-primary, .btn-glass-success, .btn-glass-warning, .btn-glass-danger, .btn-glass-info,
    .btn-glass-light, .btn-glass-outline, .btn-glass-disabled {
        display:inline-flex; align-items:center; justify-content:center; gap:7px;
        padding: 9px 16px; border-radius: 12px; font-size: 12.5px; font-weight: 700;
        border: 1px solid transparent; cursor: pointer; text-decoration:none; white-space:nowrap;
        transition: transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s ease, filter .2s ease;
    }
    .btn-glass-sm { padding: 6px 12px; font-size: 11.5px; }
    .btn-glass-primary { background: linear-gradient(135deg, var(--purple-light), var(--purple-dark)); color:#fff; box-shadow: 0 6px 18px rgba(106,44,117,.3); }
    .btn-glass-success  { background: linear-gradient(135deg, #34d399, #059669); color:#fff; box-shadow: 0 6px 18px rgba(5,150,105,.28); }
    .btn-glass-warning  { background: linear-gradient(135deg, #fbbf24, #d97706); color:#fff; box-shadow: 0 6px 18px rgba(217,119,6,.28); }
    .btn-glass-danger   { background: linear-gradient(135deg, #f87171, #dc2626); color:#fff; box-shadow: 0 6px 18px rgba(220,38,38,.28); }
    .btn-glass-info     { background: linear-gradient(135deg, #38bdf8, #0284c7); color:#fff; box-shadow: 0 6px 18px rgba(2,132,199,.28); }
    .btn-glass-light {
        background: rgba(255,255,255,.6); color: #475569; border-color: rgba(0,0,0,.08);
        backdrop-filter: blur(8px);
    }
    .btn-glass-outline {
        background: rgba(106,44,117,.06); color: var(--purple); border-color: rgba(106,44,117,.25);
    }
    .btn-glass-disabled {
        background: rgba(217,119,6,.08); color: #92400e; border-color: rgba(217,119,6,.25); cursor: default;
    }
    .btn-glass-primary:hover, .btn-glass-success:hover, .btn-glass-warning:hover,
    .btn-glass-danger:hover, .btn-glass-info:hover { transform: translateY(-2px); filter: brightness(1.05); }
    .btn-glass-light:hover, .btn-glass-outline:hover { transform: translateY(-2px); background: rgba(255,255,255,.85); }

    /* ── Alertas ── */
    .ac-alert { display:flex; align-items:flex-start; gap:12px; padding: 14px 18px; border-radius: 16px; margin-bottom: 4px; font-size: 13.5px; }
    .ac-alert-success { background: rgba(5,150,105,.08); border: 1px solid rgba(5,150,105,.25); color: #065f46; }
    .ac-alert-danger  { background: rgba(220,38,38,.08); border: 1px solid rgba(220,38,38,.25); color: #991b1b; }
    .ac-alert-warning { background: rgba(217,119,6,.08); border: 1px solid rgba(217,119,6,.25); color: #92400e; }
    .ac-alert-close { background:none; border:none; cursor:pointer; opacity:.6; }
    .ac-alert-close:hover { opacity:1; }

    /* ── Campos de formulario liquid glass ── */
    .ac-label { display:block; margin-bottom: 6px; font-size: 12.5px; font-weight: 700; color: #374151; }
    .ac-field {
        width: 100%; border-radius: 12px; border: 1px solid rgba(106,44,117,.18);
        background: rgba(255,255,255,.75); padding: 9px 12px; font-size: 13.5px; color: #1f2937;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .ac-field:focus { outline:none; border-color: var(--purple); box-shadow: 0 0 0 3px rgba(106,44,117,.14); }
    .ac-field-file { padding: 7px 10px; }

    .ac-check-card {
        display:flex; align-items:flex-start; gap:10px; padding: 14px; border-radius: 14px;
        border: 1px solid rgba(0,0,0,.08); background: rgba(255,255,255,.6); cursor:pointer;
        transition: border-color .2s ease, background .2s ease;
    }
    .ac-check-card:has(input:checked) { border-color: rgba(106,44,117,.4); background: rgba(106,44,117,.06); }
    .ac-check-card input[type="checkbox"] { position:absolute; opacity:0; width:0; height:0; }
    .ac-check-box {
        flex-shrink:0; width: 18px; height: 18px; border-radius: 6px; border: 2px solid rgba(106,44,117,.35);
        margin-top: 2px; display:flex; align-items:center; justify-content:center; transition: all .2s ease;
    }
    .ac-check-card input:checked + .ac-check-box { background: var(--purple); border-color: var(--purple); }
    .ac-check-card input:checked + .ac-check-box::after { content:'✓'; color:#fff; font-size: 12px; font-weight: 800; }

    @media (prefers-reduced-motion: reduce) {
        .reveal-3d { opacity: 1 !important; transform: none !important; transition: none !important; }
        .tilt-card { transform: none !important; }
        .gold-gleam { animation: none !important; }
    }
    </style>

    {{-- ─────────────────────────────────────────────
         SCRIPTS
    ───────────────────────────────────────────── --}}
    <script>
        function toggleContencion() {
            const formulario = document.getElementById('formContencion');
            if (!formulario) return;
            formulario.style.display = formulario.style.display === 'none' ? 'block' : 'none';
        }
    </script>

    <script>
        function togglePlanAccion() {
            const formulario = document.getElementById('formPlanAccion');
            if (!formulario) return;
            formulario.style.display = formulario.style.display === 'none' ? 'block' : 'none';
        }
    </script>

    <script>
        function toggleGestionPlan() {
            const formulario = document.getElementById('formGestionPlan');
            if (!formulario) return;
            formulario.style.display = formulario.style.display === 'none' ? 'block' : 'none';
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const targets = document.querySelectorAll('.reveal-3d');
            if ('IntersectionObserver' in window) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry, idx) => {
                        if (entry.isIntersecting) {
                            setTimeout(() => entry.target.classList.add('is-visible'), idx * 25);
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: .06, rootMargin: '0px 0px -30px 0px' });
                targets.forEach(el => io.observe(el));
            } else {
                targets.forEach(el => el.classList.add('is-visible'));
            }

            if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
                document.querySelectorAll('.tilt-card').forEach(card => {
                    card.addEventListener('mousemove', (e) => {
                        const r = card.getBoundingClientRect();
                        const px = (e.clientX - r.left) / r.width - .5;
                        const py = (e.clientY - r.top) / r.height - .5;
                        card.style.transform = `perspective(1000px) rotateY(${px * 3}deg) rotateX(${-py * 2.2}deg) translateY(-2px)`;
                    });
                    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
                });
            }
        });
    </script>

</x-app-layout>
