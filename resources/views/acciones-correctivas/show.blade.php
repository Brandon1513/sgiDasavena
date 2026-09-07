<x-app-layout>

    <div class="container-fluid py-4">

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
        @endphp


        {{-- =========================================================
            ENCABEZADO
        ========================================================== --}}

        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">

            <div>

                <a
                    href="{{ route('acciones-correctivas.index') }}"
                    class="text-decoration-none text-muted small">
                    ← Acciones Correctivas
                </a>

                <div class="d-flex align-items-center gap-3 mt-2 flex-wrap">

                    <h1 class="h2 fw-bold mb-0">
                        {{ $accionCorrectiva->codigo }}
                    </h1>

                    <span class="badge bg-{{ $estadoClase }} px-3 py-2">
                        {{ $accionCorrectiva->estado?->nombre ?? 'Sin estado' }}
                    </span>

                </div>

                <p class="text-muted mt-2 mb-0">
                    {{ $accionCorrectiva->descripcion }}
                </p>

            </div>

            <div class="d-flex gap-2 flex-wrap">

                @if(!empty($acciones))

                @foreach($acciones as $accion)

                @if($accion['accion'] === 'contencion')

                <button
                    type="button"
                    class="btn btn-{{ $accion['tipo'] }}"
                    onclick="toggleContencion()">
                    {{ $accion['texto'] }}
                </button>

                @elseif(
                $accion['accion'] === 'analisis' &&
                $estadoActual === 'contencion'
                )

                <form
                    method="POST"
                    action="{{ route('acciones-correctivas.estado.cambiar', $accionCorrectiva) }}"
                    class="d-inline">
                    @csrf

                    <input
                        type="hidden"
                        name="estado"
                        value="{{ $accion['estado_destino'] }}">

                    <button
                        type="submit"
                        class="btn btn-{{ $accion['tipo'] }}">
                        {{ $accion['texto'] }}
                    </button>
                </form>

                @elseif(
                $accion['accion'] === 'analisis' &&
                $estadoActual === 'analisis'
                )

                <a
                    href="{{ route('acciones-correctivas.analisis', $accionCorrectiva) }}"
                    class="btn btn-{{ $accion['tipo'] }}">
                    Gestionar análisis
                </a>

                @endif

                @endforeach

                @endif

            </div>

        </div>


        {{-- =========================================================
            RESUMEN
        ========================================================== --}}

        <div class="row g-3 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Responsable
                        </div>
                        <div class="fw-semibold">
                            {{ $accionCorrectiva->responsable?->name ?? 'Sin responsable' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Origen
                        </div>
                        <div class="fw-semibold">
                            {{ $accionCorrectiva->origen?->nombre ?? 'Sin origen' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Fecha de apertura
                        </div>
                        <div class="fw-semibold">
                            {{ $accionCorrectiva->fecha_apertura?->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">
                            Ciclo actual
                        </div>
                        <div class="fw-semibold">
                            Ciclo {{ $accionCorrectiva->ciclo_actual }}
                        </div>
                    </div>
                </div>
            </div>

        </div>


        {{-- =========================================================
            AVANCE
        ========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-2">

                    <div>
                        <div class="fw-semibold">
                            Avance de la acción correctiva
                        </div>

                        <div class="text-muted small">
                            Progreso de las actividades del ciclo actual.
                        </div>
                    </div>

                    <div class="fw-bold fs-5">
                        {{ number_format($avance, 0) }}%
                    </div>

                </div>

                <div
                    class="progress"
                    style="height: 12px;">
                    <div
                        class="progress-bar bg-{{ $avance >= 100 ? 'success' : 'primary' }}"
                        role="progressbar"
                        style="width: {{ $avance }}%;"
                        aria-valuenow="{{ $avance }}"
                        aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>

            </div>

        </div>


        {{-- =========================================================
            LÍNEA DE PROCESO
        ========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white border-0 pt-4 px-4">

                <div class="fw-semibold">
                    Flujo de la Acción Correctiva
                </div>

                <div class="text-muted small mt-1">
                    Estado actual del proceso.
                </div>

            </div>

            <div class="card-body px-4 pb-4">

                <div class="row g-2">

                    @foreach($estados as $codigo => $nombre)

                    @php
                    $indice = array_search($codigo, array_keys($estados));

                    $completado = $indice < $estadoOrden;
                        $actual=$indice===$estadoOrden;
                        @endphp

                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">

                        <div
                            class="border rounded-3 p-3 h-100
                                {{ $actual ? 'border-primary bg-light' : '' }}
                                {{ $completado ? 'border-success-subtle' : '' }}">

                            <div class="d-flex align-items-center gap-2">

                                @if($completado)

                                <span class="text-success fw-bold">
                                    ✓
                                </span>

                                @elseif($actual)

                                <span class="text-primary fw-bold">
                                    ●
                                </span>

                                @else

                                <span class="text-muted">
                                    ○
                                </span>

                                @endif

                                <span class="small fw-semibold">
                                    {{ $nombre }}
                                </span>

                            </div>

                        </div>

                </div>

                @endforeach

            </div>

        </div>

    </div>


    {{-- =========================================================
            RESUMEN DEL CICLO ACTUAL
        ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <div class="fw-semibold">
                        Ciclo {{ $accionCorrectiva->ciclo_actual }}
                    </div>

                    <div class="text-muted small">
                        Resumen del ciclo actual.
                    </div>
                </div>

                <span class="badge bg-primary">
                    Actual
                </span>

            </div>

        </div>

        <div class="card-body px-4 pb-4">

            <div class="row g-3">

                {{-- Análisis --}}
                <div class="col-lg-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <div class="text-muted small">
                                    Análisis
                                </div>

                                <div class="fw-semibold mt-1">
                                    Análisis de causa raíz
                                </div>
                            </div>

                            @if($cicloActual)
                            <span class="badge bg-success">
                                Registrado
                            </span>
                            @else
                            <span class="badge bg-secondary">
                                Pendiente
                            </span>
                            @endif

                        </div>

                        @if($cicloActual)

                        <div class="small text-muted mt-3">
                            Inicio
                        </div>

                        <div class="fw-semibold">
                            {{ $cicloActual->fecha_inicio?->format('d/m/Y') }}
                        </div>

                        @endif

                    </div>

                </div>


                {{-- Causa raíz --}}
                <div class="col-lg-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <div class="text-muted small">
                                    Causa raíz
                                </div>

                                <div class="fw-semibold mt-1">
                                    Validación
                                </div>
                            </div>

                            @if($causaActual?->estado_validacion === 'aprobada')
                            <span class="badge bg-success">
                                Aprobada
                            </span>
                            @elseif($causaActual?->estado_validacion === 'rechazada')
                            <span class="badge bg-danger">
                                Rechazada
                            </span>
                            @elseif($causaActual)
                            <span class="badge bg-warning text-dark">
                                Pendiente
                            </span>
                            @else
                            <span class="badge bg-secondary">
                                Pendiente
                            </span>
                            @endif

                        </div>

                        @if($causaActual)

                        <div class="small text-muted mt-3">
                            Descripción
                        </div>

                        <div class="small mt-1">
                            {{ $causaActual->descripcion }}
                        </div>

                        @endif

                    </div>

                </div>


                {{-- Plan --}}
                <div class="col-lg-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="d-flex justify-content-between align-items-start">

                            <div>
                                <div class="text-muted small">
                                    Plan de acción
                                </div>

                                <div class="fw-semibold mt-1">
                                    Ejecución
                                </div>
                            </div>

                            @if($planActual?->estado === 'completado')
                            <span class="badge bg-success">
                                Completado
                            </span>
                            @elseif($planActual)
                            <span class="badge bg-warning text-dark">
                                En proceso
                            </span>
                            @else
                            <span class="badge bg-secondary">
                                Pendiente
                            </span>
                            @endif

                        </div>

                        @if($planActual)

                        <div class="small text-muted mt-3">
                            Actividades
                        </div>

                        <div class="fw-semibold">
                            {{ $planActual->actividades->count() }}
                        </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="card-header bg-white border-0 pt-4 px-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <div class="fw-semibold">
                    Contenciones
                </div>

                <div class="text-muted small">
                    Medidas temporales implementadas.
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">

                <span class="badge bg-secondary">
                    {{ $accionCorrectiva->contenciones->count() }}
                </span>

                @if(in_array($estadoActual, ['abierta', 'contencion'], true))
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    onclick="toggleContencion()">
                    + Agregar contención
                </button>
                @endif

            </div>

        </div>

    </div>


    {{-- =========================================================
            CONTENCIONES
        ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <div class="fw-semibold">
                        Contenciones
                    </div>

                    <div class="text-muted small">
                        Medidas temporales implementadas.
                    </div>
                </div>

                <span class="badge bg-secondary">
                    {{ $accionCorrectiva->contenciones->count() }}
                </span>

            </div>

        </div>

        <div class="card-body px-4 pb-4">

            @forelse($accionCorrectiva->contenciones as $contencion)

            <div class="border rounded-3 p-3 mb-3">

                <div class="d-flex justify-content-between gap-3">

                    <div>
                        <div class="fw-semibold">
                            {{ $contencion->descripcion }}
                        </div>

                        <div class="small text-muted mt-2">
                            Responsable:
                            {{ $contencion->responsable?->name ?? 'Sin responsable' }}
                        </div>
                    </div>

                    <span class="badge
                                {{ $contencion->estado === 'completada'
                                    ? 'bg-success'
                                    : 'bg-warning text-dark' }}
                            ">
                        {{ ucfirst(str_replace('_', ' ', $contencion->estado)) }}
                    </span>

                </div>

                <div class="small text-muted mt-3">
                    Implementación:
                    {{ $contencion->fecha_implementacion?->format('d/m/Y') }}
                </div>

                @if($contencion->observaciones)

                <div class="small mt-2">
                    {{ $contencion->observaciones }}
                </div>

                @endif

            </div>

            @empty

            <div class="text-center text-muted py-4">
                No existen contenciones registradas.
            </div>

            @endforelse

        </div>
        @if(in_array($estadoActual, ['abierta', 'contencion'], true))
        <div
            id="formContencion"
            class="mb-4"
            style="display: none;">

            <div class="border rounded-3 p-4 bg-light">

                <div class="fw-semibold mb-1">
                    Registrar acción de contención
                </div>

                <div class="text-muted small mb-4">
                    Registra la medida temporal utilizada para controlar el problema.
                </div>

                <form
                    method="POST"
                    action="{{ route('acciones-correctivas.contenciones.crear', $accionCorrectiva) }}">
                    @csrf

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Descripción
                            </label>

                            <textarea
                                name="descripcion"
                                class="form-control"
                                rows="3"
                                required
                                placeholder="Describe la acción de contención implementada...">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Responsable
                            </label>

                            <select
                                name="responsable_id"
                                class="form-select"
                                required>
                                <option value="">
                                    Seleccionar responsable
                                </option>

                                @foreach(\App\Models\User::query()->where('activo', true)->orderBy('name')->get() as $usuario)
                                <option
                                    value="{{ $usuario->id }}"
                                    @selected(old('responsable_id')==$usuario->id)
                                    >
                                    {{ $usuario->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Fecha de implementación
                            </label>

                            <input
                                type="date"
                                name="fecha_implementacion"
                                class="form-control"
                                value="{{ old('fecha_implementacion', now()->format('Y-m-d')) }}"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                Estado
                            </label>

                            <select
                                name="estado"
                                class="form-select"
                                required>
                                <option value="pendiente">
                                    Pendiente
                                </option>

                                <option
                                    value="completada"
                                    @selected(old('estado')==='completada' )>
                                    Completada
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Observaciones
                            </label>

                            <textarea
                                name="observaciones"
                                class="form-control"
                                rows="2"
                                placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">

                        <button
                            type="button"
                            class="btn btn-light"
                            onclick="toggleContencion()">
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary">
                            Guardar contención
                        </button>

                    </div>

                </form>

            </div>

        </div>
        @endif

    </div>


    {{-- =========================================================
            ANÁLISIS
        ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <div class="fw-semibold">
                        Análisis de causa raíz
                    </div>

                    <div class="text-muted small">
                        Historial de análisis realizados por ciclo.
                    </div>
                </div>

                <span class="badge bg-secondary">
                    {{ $accionCorrectiva->analisis->count() }}
                </span>

            </div>

        </div>

        <div class="card-body px-4 pb-4">

            @forelse($accionCorrectiva->analisis->sortByDesc('ciclo') as $analisis)

            <div class="border rounded-3 p-3 mb-3">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="fw-semibold">
                            Ciclo {{ $analisis->ciclo }}
                        </div>

                        <div class="small text-muted mt-1">
                            Inicio:
                            {{ $analisis->fecha_inicio?->format('d/m/Y') }}
                        </div>

                    </div>

                    <span class="badge
                                {{ $analisis->estado === 'completado'
                                    ? 'bg-success'
                                    : 'bg-warning text-dark' }}
                            ">
                        {{ ucfirst(str_replace('_', ' ', $analisis->estado)) }}
                    </span>

                </div>

                @if($analisis->ideas?->count())

                <div class="small text-muted mt-3">
                    Ideas identificadas:
                    <strong>{{ $analisis->ideas->count() }}</strong>
                </div>

                @endif

                @if($analisis->cincoPorques?->count())

                <div class="small text-muted mt-1">
                    Análisis de 5 Porqués:
                    <strong>{{ $analisis->cincoPorques->count() }}</strong>
                </div>

                @endif

                @if($analisis->causasRaiz?->count())

                <div class="small text-muted mt-1">
                    Causas raíz:
                    <strong>{{ $analisis->causasRaiz->count() }}</strong>
                </div>

                @endif

            </div>

            @empty

            <div class="text-center text-muted py-4">
                No existen análisis registrados.
            </div>

            @endforelse

        </div>

    </div>


    {{-- =========================================================
            PLANES DE ACCIÓN
        ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <div class="fw-semibold">
                        Planes de acción
                    </div>

                    <div class="text-muted small">
                        Actividades y evidencias de cada ciclo.
                    </div>
                </div>

                <span class="badge bg-secondary">
                    {{ $accionCorrectiva->planesAccion->count() }}
                </span>

            </div>

        </div>

        <div class="card-body px-4 pb-4">

            @forelse($accionCorrectiva->planesAccion->sortByDesc('ciclo') as $plan)

            <div class="border rounded-3 mb-4 overflow-hidden">

                <div class="bg-light p-3 border-bottom">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="fw-semibold">
                                Plan de acción · Ciclo {{ $plan->ciclo }}
                            </div>

                            <div class="small text-muted mt-1">
                                Inicio:
                                {{ $plan->fecha_inicio?->format('d/m/Y') }}

                                @if($plan->fecha_cierre)
                                · Cierre:
                                {{ $plan->fecha_cierre->format('d/m/Y') }}
                                @endif
                            </div>

                        </div>

                        <span class="badge
                                    {{ $plan->estado === 'completado'
                                        ? 'bg-success'
                                        : 'bg-warning text-dark' }}
                                ">
                            {{ ucfirst(str_replace('_', ' ', $plan->estado)) }}
                        </span>

                    </div>

                    @if($plan->observaciones)

                    <div class="small text-muted mt-2">
                        {{ $plan->observaciones }}
                    </div>

                    @endif

                </div>


                <div class="p-3">

                    @forelse($plan->actividades as $actividad)

                    <div class="border rounded-3 p-3 mb-3">

                        <div class="d-flex justify-content-between gap-3">

                            <div class="flex-grow-1">

                                <div class="fw-semibold">

                                    @if($actividad->estado === 'completada')

                                    <span class="text-success me-1">
                                        ✓
                                    </span>

                                    @endif

                                    {{ $actividad->descripcion }}

                                </div>

                                <div class="small text-muted mt-2">
                                    Responsable:
                                    {{ $actividad->responsable?->name ?? 'Sin responsable' }}
                                </div>

                            </div>

                            <span class="badge
                                            {{ $actividad->estado === 'completada'
                                                ? 'bg-success'
                                                : 'bg-secondary' }}
                                        ">
                                {{ ucfirst($actividad->estado) }}
                            </span>

                        </div>

                        <div class="row g-3 mt-2 small">

                            <div class="col-md-4">
                                <div class="text-muted">
                                    Compromiso
                                </div>
                                <div class="fw-semibold">
                                    {{ $actividad->fecha_compromiso?->format('d/m/Y') ?? 'Sin fecha' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted">
                                    Cumplimiento
                                </div>
                                <div class="fw-semibold">
                                    {{ $actividad->fecha_cumplimiento?->format('d/m/Y') ?? 'Pendiente' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted">
                                    Evidencias
                                </div>
                                <div class="fw-semibold">
                                    {{ $actividad->evidencias->count() }}
                                </div>
                            </div>

                        </div>

                        @if($actividad->evidencias->isNotEmpty())

                        <div class="border-top mt-3 pt-3">

                            <div class="small fw-semibold mb-2">
                                Evidencias
                            </div>

                            @foreach($actividad->evidencias as $evidencia)

                            <div class="d-flex justify-content-between align-items-center border rounded-3 p-2 mb-2">

                                <div>

                                    <div class="fw-semibold small">
                                        {{ $evidencia->nombre_original }}
                                    </div>

                                    @if($evidencia->descripcion)

                                    <div class="small text-muted">
                                        {{ $evidencia->descripcion }}
                                    </div>

                                    @endif

                                </div>

                                <span class="small text-muted">
                                    {{ number_format($evidencia->tamano / 1024, 1) }} KB
                                </span>

                            </div>

                            @endforeach

                        </div>

                        @endif

                    </div>

                    @empty

                    <div class="text-center text-muted py-3">
                        Este plan no tiene actividades.
                    </div>

                    @endforelse

                </div>

            </div>

            @empty

            <div class="text-center text-muted py-4">
                No existen planes de acción.
            </div>

            @endforelse

        </div>

    </div>


    {{-- =========================================================
            VERIFICACIÓN DE CIERRE
        ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <div class="fw-semibold">
                        Verificación de cierre
                    </div>

                    <div class="text-muted small">
                        Confirmación de la implementación del plan.
                    </div>
                </div>

                <span class="badge bg-secondary">
                    {{ $accionCorrectiva->verificacionesCierre->count() }}
                </span>

            </div>

        </div>

        <div class="card-body px-4 pb-4">

            @forelse($accionCorrectiva->verificacionesCierre->sortByDesc('ciclo') as $verificacion)

            <div class="border rounded-3 p-3 mb-3">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="fw-semibold">
                            Ciclo {{ $verificacion->ciclo }}
                        </div>

                        <div class="small text-muted mt-1">
                            {{ $verificacion->fecha_verificacion?->format('d/m/Y') }}
                        </div>

                    </div>

                    <span class="badge bg-success">
                        Aprobada
                    </span>

                </div>

                <div class="row g-3 mt-2 small">

                    <div class="col-md-4">
                        <div class="text-muted">
                            Acciones implementadas
                        </div>

                        <div class="fw-semibold text-success">
                            ✓ Sí
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted">
                            Evidencias completas
                        </div>

                        <div class="fw-semibold text-success">
                            ✓ Sí
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted">
                            Implementación conforme
                        </div>

                        <div class="fw-semibold text-success">
                            ✓ Sí
                        </div>
                    </div>

                </div>

                @if($verificacion->resultado)

                <div class="mt-3">
                    <div class="small text-muted">
                        Resultado
                    </div>

                    <div>
                        {{ $verificacion->resultado }}
                    </div>
                </div>

                @endif

                @if($verificacion->observaciones)

                <div class="mt-3">
                    <div class="small text-muted">
                        Observaciones
                    </div>

                    <div>
                        {{ $verificacion->observaciones }}
                    </div>
                </div>

                @endif

                <div class="border-top mt-3 pt-3 small text-muted">
                    Verificado por:
                    <strong>
                        {{ $verificacion->verificadoPor?->name ?? 'Sin usuario' }}
                    </strong>
                </div>

            </div>

            @empty

            <div class="text-center text-muted py-4">
                No existe una verificación de cierre registrada.
            </div>

            @endforelse

        </div>

    </div>


    {{-- =========================================================
            ESPERA DE EFICACIA
        ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <div class="fw-semibold">
                        Espera de eficacia
                    </div>

                    <div class="text-muted small">
                        Periodo establecido para comprobar la efectividad.
                    </div>
                </div>

                <span class="badge bg-secondary">
                    {{ $accionCorrectiva->esperasEficacia->count() }}
                </span>

            </div>

        </div>

        <div class="card-body px-4 pb-4">

            @forelse($accionCorrectiva->esperasEficacia->sortByDesc('ciclo') as $espera)

            <div class="border rounded-3 p-3 mb-3">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="fw-semibold">
                            Ciclo {{ $espera->ciclo }}
                        </div>

                        <div class="small text-muted mt-1">
                            {{ $espera->fecha_inicio?->format('d/m/Y') }}
                            →
                            {{ $espera->fecha_verificacion?->format('d/m/Y') }}
                        </div>

                    </div>

                    <span class="badge
                                {{ $espera->estado === 'completada'
                                    ? 'bg-success'
                                    : 'bg-warning text-dark' }}
                            ">
                        {{ ucfirst(str_replace('_', ' ', $espera->estado)) }}
                    </span>

                </div>

                <div class="small text-muted mt-3">
                    Responsable:
                    <strong class="text-dark">
                        {{ $espera->responsable?->name ?? 'Sin responsable' }}
                    </strong>
                </div>

                @if($espera->observaciones)

                <div class="small mt-2">
                    {{ $espera->observaciones }}
                </div>

                @endif

            </div>

            @empty

            <div class="text-center text-muted py-4">
                No existe un periodo de espera registrado.
            </div>

            @endforelse

        </div>

    </div>


    {{-- =========================================================
            VERIFICACIÓN DE EFICACIA
        ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="d-flex justify-content-between align-items-center">

                <div>
                    <div class="fw-semibold">
                        Verificación de eficacia
                    </div>

                    <div class="text-muted small">
                        Resultado de la evaluación de eficacia.
                    </div>
                </div>

                <span class="badge bg-secondary">
                    {{ $accionCorrectiva->verificacionesEficacia->count() }}
                </span>

            </div>

        </div>

        <div class="card-body px-4 pb-4">

            @forelse($accionCorrectiva->verificacionesEficacia->sortByDesc('ciclo') as $verificacion)

            @php
            $eficaz = (bool) $verificacion->resultado_eficaz;
            @endphp

            <div class="border rounded-3 p-3 mb-3">

                <div class="d-flex justify-content-between align-items-start">

                    <div>

                        <div class="fw-semibold">
                            Ciclo {{ $verificacion->ciclo }}
                        </div>

                        <div class="small text-muted mt-1">
                            {{ $verificacion->fecha_verificacion?->format('d/m/Y') }}
                        </div>

                    </div>

                    <span class="badge {{ $eficaz ? 'bg-success' : 'bg-danger' }}">
                        {{ $eficaz ? 'Eficaz' : 'No eficaz' }}
                    </span>

                </div>

                <div class="row g-3 mt-2 small">

                    <div class="col-md-6">

                        <div class="text-muted">
                            Criterios cumplidos
                        </div>

                        <div class="fw-semibold">

                            @if($verificacion->criterios_cumplidos)
                            <span class="text-success">
                                ✓ Sí
                            </span>
                            @else
                            <span class="text-danger">
                                ✕ No
                            </span>
                            @endif

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="text-muted">
                            Resultado
                        </div>

                        <div class="fw-semibold {{ $eficaz ? 'text-success' : 'text-danger' }}">
                            {{ $eficaz ? 'Las acciones fueron eficaces' : 'Las acciones no fueron eficaces' }}
                        </div>

                    </div>

                </div>

                @if($verificacion->resultado)

                <div class="mt-3">

                    <div class="small text-muted">
                        Resultado de la verificación
                    </div>

                    <div>
                        {{ $verificacion->resultado }}
                    </div>

                </div>

                @endif

                @if($verificacion->observaciones)

                <div class="mt-3">

                    <div class="small text-muted">
                        Observaciones
                    </div>

                    <div>
                        {{ $verificacion->observaciones }}
                    </div>

                </div>

                @endif

                <div class="border-top mt-3 pt-3 small text-muted">
                    Verificado por:
                    <strong class="text-dark">
                        {{ $verificacion->verificadoPor?->name ?? 'Sin usuario' }}
                    </strong>
                </div>

            </div>

            @empty

            <div class="text-center text-muted py-4">
                No existe una verificación de eficacia registrada.
            </div>

            @endforelse

        </div>

    </div>


    {{-- =========================================================
            HISTORIAL
        ========================================================== --}}

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 pt-4 px-4">

            <div class="fw-semibold">
                Historial de ciclos
            </div>

            <div class="text-muted small mt-1">
                Evolución de la Acción Correctiva.
            </div>

        </div>

        <div class="card-body px-4 pb-4">

            @forelse($accionCorrectiva->analisis->sortByDesc('ciclo') as $analisis)

            @php
            $verificacion = $accionCorrectiva->verificacionesEficacia
            ->where('ciclo', $analisis->ciclo)
            ->sortByDesc('id')
            ->first();
            @endphp

            <div class="d-flex gap-3 mb-3">

                <div class="flex-shrink-0">

                    @if($analisis->ciclo == $accionCorrectiva->ciclo_actual)

                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                        style="width: 40px; height: 40px;">
                        {{ $analisis->ciclo }}
                    </div>

                    @else

                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-muted"
                        style="width: 40px; height: 40px;">
                        {{ $analisis->ciclo }}
                    </div>

                    @endif

                </div>

                <div class="border rounded-3 p-3 flex-grow-1">

                    <div class="d-flex justify-content-between align-items-start gap-3">

                        <div>
                            <div class="fw-semibold">
                                Ciclo {{ $analisis->ciclo }}
                            </div>

                            <div class="small text-muted">
                                Inicio:
                                {{ $analisis->fecha_inicio?->format('d/m/Y') }}
                            </div>
                        </div>

                        @if($verificacion)

                        <span class="badge {{ $verificacion->resultado_eficaz ? 'bg-success' : 'bg-danger' }}">
                            {{ $verificacion->resultado_eficaz ? 'Eficaz' : 'No eficaz' }}
                        </span>

                        @elseif($analisis->ciclo == $accionCorrectiva->ciclo_actual)

                        <span class="badge bg-primary">
                            En curso
                        </span>

                        @else

                        <span class="badge bg-secondary">
                            Sin verificación
                        </span>

                        @endif

                    </div>

                    @if($verificacion && !$verificacion->resultado_eficaz)

                    <div class="small text-danger mt-2">
                        El ciclo fue rechazado en la verificación de eficacia
                        y dio origen a un nuevo ciclo.
                    </div>

                    @endif

                </div>

            </div>

            @empty

            <div class="text-center text-muted py-4">
                No existe historial de ciclos.
            </div>

            @endforelse

        </div>

    </div>


    {{-- =========================================================
            DATOS DE CONTROL
        ========================================================== --}}

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 pt-4 px-4">
            <div class="fw-semibold">
                Datos de control
            </div>
        </div>

        <div class="card-body px-4 pb-4">

            <div class="row g-4 small">

                <div class="col-md-3">
                    <div class="text-muted">
                        ID
                    </div>
                    <div class="fw-semibold">
                        {{ $accionCorrectiva->id }}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted">
                        Código
                    </div>
                    <div class="fw-semibold">
                        {{ $accionCorrectiva->codigo }}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted">
                        Ciclo actual
                    </div>
                    <div class="fw-semibold">
                        {{ $accionCorrectiva->ciclo_actual }}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted">
                        Fecha de cierre
                    </div>
                    <div class="fw-semibold">
                        {{ $accionCorrectiva->fecha_cierre?->format('d/m/Y') ?? 'Pendiente' }}
                    </div>
                </div>

            </div>

        </div>

    </div>



    </div>
    <script>
        function toggleContencion() {
            const formulario = document.getElementById('formContencion');

            if (!formulario) {
                return;
            }

            formulario.style.display =
                formulario.style.display === 'none' ?
                'block' :
                'none';
        }
    </script>

</x-app-layout>