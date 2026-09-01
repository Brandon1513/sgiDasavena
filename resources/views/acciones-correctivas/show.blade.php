<x-app-layout>

    <div class="container-fluid py-4">

        {{-- =========================================================
            ENCABEZADO
        ========================================================== --}}

        <div class="d-flex justify-content-between align-items-start mb-4">

            <div>

                <div class="d-flex align-items-center gap-2 mb-2">

                    <a
                        href="{{ route('acciones-correctivas.index') }}"
                        class="text-decoration-none">
                        ← Acciones Correctivas
                    </a>

                </div>

                <div class="d-flex align-items-center gap-3">

                    <h1 class="h3 mb-0">
                        {{ $accionCorrectiva->codigo }}
                    </h1>

                    @php
                    $estadoCodigo = $accionCorrectiva->estado?->codigo;

                    $estadoClase = match ($estadoCodigo) {
                    'cerrada' => 'bg-success',
                    'rechazada' => 'bg-danger',
                    'verificacion_eficacia',
                    'espera_eficacia',
                    'verificacion_cierre' => 'bg-warning text-dark',
                    default => 'bg-primary',
                    };
                    @endphp

                    <span class="badge {{ $estadoClase }}">
                        {{ $accionCorrectiva->estado?->nombre ?? 'Sin estado' }}
                    </span>

                </div>

                <p class="text-muted mt-2 mb-0">
                    {{ $accionCorrectiva->descripcion }}
                </p>
                @if(count($acciones))

                <div class="card border-0 shadow-sm mt-4">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <div class="fw-semibold">
                                    Acción disponible
                                </div>

                                <div class="text-muted small">
                                    Operación correspondiente al estado actual.
                                </div>

                            </div>

                            <div class="d-flex gap-2">

                                @foreach($acciones as $accion)

                                <button
                                    type="button"
                                    class="btn btn-{{ $accion['tipo'] }}"
                                    data-accion="{{ $accion['accion'] }}">
                                    {{ $accion['texto'] }}
                                </button>

                                @endforeach

                            </div>

                        </div>

                    </div>

                </div>

                @else

                @if($accionCorrectiva->estado?->codigo === 'cerrada')

                <div class="alert alert-success mt-4 mb-0">

                    <div class="fw-semibold">
                        ✓ Acción Correctiva cerrada
                    </div>

                    <div class="small mt-1">
                        La acción fue verificada y se confirmó la eficacia
                        de las acciones implementadas.
                    </div>

                </div>

                @endif

                @endif

            </div>

            <div class="text-end">

                <div class="small text-muted">
                    Ciclo actual
                </div>

                <div class="fs-4 fw-bold">
                    {{ $accionCorrectiva->ciclo_actual }}
                </div>

            </div>

        </div>


        {{-- =========================================================
            RESUMEN
        ========================================================== --}}

        <div class="row g-4 mb-4">

            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted small mb-1">
                            Estado
                        </div>

                        <div class="fw-semibold">
                            {{ $accionCorrectiva->estado?->nombre ?? 'Sin estado' }}
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

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


            <div class="col-md-3">

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


            <div class="col-md-3">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body">

                        <div class="text-muted small mb-1">
                            Avance
                        </div>

                        <div class="d-flex align-items-center gap-2">

                            <div
                                class="progress flex-grow-1"
                                style="height: 8px;">
                                <div
                                    class="progress-bar"
                                    style="width: {{ $accionCorrectiva->porcentaje_avance }}%"></div>
                            </div>

                            <strong>
                                {{ number_format($accionCorrectiva->porcentaje_avance, 0) }}%
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
            FECHAS
        ========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">

                        <div class="text-muted small">
                            Fecha de apertura
                        </div>

                        <div class="fw-semibold">
                            {{ optional($accionCorrectiva->fecha_apertura)->format('d/m/Y') }}
                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="text-muted small">
                            Fecha de cierre
                        </div>

                        <div class="fw-semibold">
                            @if($accionCorrectiva->fecha_cierre)
                            {{ $accionCorrectiva->fecha_cierre->format('d/m/Y') }}
                            @else
                            <span class="text-muted">
                                Pendiente
                            </span>
                            @endif
                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="text-muted small">
                            Ciclo
                        </div>

                        <div class="fw-semibold">
                            {{ $accionCorrectiva->ciclo_actual }}
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
            TIMELINE
        ========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">

                <h2 class="h5 mb-0">
                    Flujo de la Acción Correctiva
                </h2>

            </div>

            <div class="card-body">

                @php

                $estadosFlujo = [
                'borrador' => 'Borrador',
                'abierta' => 'Abierta',
                'contencion' => 'Contención',
                'analisis' => 'Análisis de causa raíz',
                'validacion_causa' => 'Validación de causa raíz',
                'plan_accion' => 'Plan de acción',
                'ejecucion' => 'Ejecución',
                'verificacion_cierre' => 'Verificación de cierre',
                'espera_eficacia' => 'Espera de eficacia',
                'verificacion_eficacia' => 'Verificación de eficacia',
                'cerrada' => 'Cerrada',
                ];

                $ordenActual = $accionCorrectiva->estado?->orden ?? 0;

                @endphp


                <div class="row g-2">

                    @foreach($estadosFlujo as $codigo => $nombre)

                    @php
                    $estadoActual = $accionCorrectiva->estado?->codigo === $codigo;
                    $completado = $ordenActual > (
                    collect($estadosFlujo)
                    ->keys()
                    ->search($codigo) + 1
                    );
                    @endphp

                    <div class="col">

                        <div
                            class="
                                    p-3
                                    rounded
                                    border
                                    h-100
                                    {{ $estadoActual ? 'border-primary bg-primary bg-opacity-10' : '' }}
                                    {{ $completado ? 'bg-light' : '' }}
                                ">

                            <div class="small text-muted mb-1">
                                {{ $loop->iteration }}
                            </div>

                            <div class="fw-semibold small">
                                {{ $nombre }}
                            </div>

                            @if($estadoActual)

                            <div class="mt-2">

                                <span class="badge bg-primary">
                                    Actual
                                </span>

                            </div>

                            @elseif($completado)

                            <div class="mt-2">

                                <span class="badge bg-success">
                                    ✓
                                </span>

                            </div>

                            @endif

                        </div>

                    </div>

                    @endforeach

                </div>

            </div>

        </div>


        {{-- =========================================================
            CONTENCIONES
        ========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between">

                    <h2 class="h5 mb-0">
                        Contenciones
                    </h2>

                    <span class="badge bg-secondary">
                        {{ $accionCorrectiva->contenciones->count() }}
                    </span>

                </div>

            </div>

            <div class="card-body">

                @forelse($accionCorrectiva->contenciones as $contencion)

                <div class="border rounded p-3 mb-3">

                    <div class="fw-semibold">
                        {{ $contencion->descripcion }}
                    </div>

                    @if($contencion->observaciones)

                    <div class="text-muted mt-2">
                        {{ $contencion->observaciones }}
                    </div>

                    @endif

                </div>

                @empty

                <div class="text-muted">
                    No hay contenciones registradas.
                </div>

                @endforelse

            </div>

        </div>


        {{-- =========================================================
            ANÁLISIS
        ========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between">

                    <h2 class="h5 mb-0">
                        Análisis de causa raíz
                    </h2>

                    <span class="badge bg-secondary">
                        {{ $accionCorrectiva->analisis->count() }}
                    </span>

                </div>

            </div>

            <div class="card-body">

                @forelse($accionCorrectiva->analisis as $analisis)

                <div class="border rounded p-3 mb-3">

                    <div class="d-flex justify-content-between">

                        <div>

                            <div class="fw-semibold">
                                Ciclo {{ $analisis->ciclo }}
                            </div>

                            <div class="text-muted small">
                                Estado:
                                {{ $analisis->estado }}
                            </div>

                        </div>

                        <span class="badge bg-secondary">
                            {{ $analisis->fecha_inicio?->format('d/m/Y') }}
                        </span>

                    </div>

                    @if($analisis->observaciones)

                    <div class="mt-3">
                        {{ $analisis->observaciones }}
                    </div>

                    @endif

                </div>

                @empty

                <div class="text-muted">
                    No hay análisis registrados.
                </div>

                @endforelse

            </div>

        </div>
        {{-- =========================================================
    PLAN DE ACCIÓN
========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h2 class="h5 mb-1">
                            Plan de acción
                        </h2>

                        <div class="text-muted small">
                            Actividades y evidencias asociadas.
                        </div>
                    </div>

                    <span class="badge bg-secondary">
                        {{ $accionCorrectiva->planesAccion->count() }}
                        {{ $accionCorrectiva->planesAccion->count() === 1 ? 'plan' : 'planes' }}
                    </span>

                </div>

            </div>

            <div class="card-body">

                @forelse($accionCorrectiva->planesAccion as $plan)

                <div class="border rounded mb-4">

                    {{-- Encabezado del plan --}}
                    <div class="p-3 bg-light border-bottom">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <div class="fw-semibold">
                                    Plan de acción · Ciclo {{ $plan->ciclo }}
                                </div>

                                <div class="text-muted small mt-1">

                                    Inicio:
                                    {{ $plan->fecha_inicio?->format('d/m/Y') }}

                                    @if($plan->fecha_cierre)
                                    · Cierre:
                                    {{ $plan->fecha_cierre->format('d/m/Y') }}
                                    @endif

                                </div>

                            </div>

                            <span class="badge
                            @if($plan->estado === 'completado')
                                bg-success
                            @elseif($plan->estado === 'en_proceso')
                                bg-warning text-dark
                            @else
                                bg-secondary
                            @endif
                        ">
                                {{ str_replace('_', ' ', ucfirst($plan->estado)) }}
                            </span>

                        </div>

                        @if($plan->observaciones)

                        <div class="mt-3 text-muted small">
                            {{ $plan->observaciones }}
                        </div>

                        @endif

                    </div>


                    {{-- Actividades --}}
                    <div class="p-3">

                        <div class="d-flex justify-content-between mb-3">

                            <div class="fw-semibold">
                                Actividades
                            </div>

                            <span class="badge bg-secondary">
                                {{ $plan->actividades->count() }}
                            </span>

                        </div>


                        @forelse($plan->actividades as $actividad)

                        @php

                        $actividadCompletada =
                        $actividad->estado === 'completada';

                        @endphp

                        <div class="border rounded p-3 mb-3">

                            <div class="d-flex justify-content-between gap-3">

                                <div class="flex-grow-1">

                                    <div class="fw-semibold">

                                        @if($actividadCompletada)
                                        <span class="text-success">
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


                                <div class="text-end">

                                    <span class="badge
                                        @if($actividadCompletada)
                                            bg-success
                                        @else
                                            bg-secondary
                                        @endif
                                    ">
                                        {{ ucfirst($actividad->estado) }}
                                    </span>

                                </div>

                            </div>


                            <div class="row mt-3 small">

                                <div class="col-md-4">

                                    <span class="text-muted">
                                        Fecha compromiso
                                    </span>

                                    <div class="fw-semibold">

                                        {{ $actividad->fecha_compromiso?->format('d/m/Y') ?? 'Sin fecha' }}

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <span class="text-muted">
                                        Fecha cumplimiento
                                    </span>

                                    <div class="fw-semibold">

                                        @if($actividad->fecha_cumplimiento)

                                        {{ $actividad->fecha_cumplimiento->format('d/m/Y') }}

                                        @else

                                        <span class="text-muted">
                                            Pendiente
                                        </span>

                                        @endif

                                    </div>

                                </div>


                                <div class="col-md-4">

                                    <span class="text-muted">
                                        Evidencias
                                    </span>

                                    <div class="fw-semibold">

                                        {{ $actividad->evidencias->count() }}

                                    </div>

                                </div>

                            </div>


                            {{-- Evidencias --}}
                            @if($actividad->evidencias->isNotEmpty())

                            <div class="mt-3 pt-3 border-top">

                                <div class="small fw-semibold mb-2">
                                    Evidencias
                                </div>

                                @foreach($actividad->evidencias as $evidencia)

                                <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">

                                    <div>

                                        <div class="fw-semibold small">
                                            {{ $evidencia->nombre_original }}
                                        </div>

                                        @if($evidencia->descripcion)

                                        <div class="text-muted small">
                                            {{ $evidencia->descripcion }}
                                        </div>

                                        @endif

                                    </div>

                                    <div class="small text-muted">

                                        {{ number_format($evidencia->tamano / 1024, 1) }} KB

                                    </div>

                                </div>

                                @endforeach

                            </div>

                            @endif

                        </div>

                        @empty

                        <div class="text-muted text-center py-3">
                            Este plan todavía no tiene actividades.
                        </div>

                        @endforelse

                    </div>

                </div>

                @empty

                <div class="text-muted text-center py-4">
                    No hay planes de acción registrados.
                </div>

                @endforelse

            </div>

        </div>
        {{-- =========================================================
    VERIFICACIÓN DE CIERRE
========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div>
                        <h2 class="h5 mb-1">
                            Verificación de cierre
                        </h2>

                        <div class="text-muted small">
                            Validación de la implementación del plan de acción.
                        </div>
                    </div>

                    <span class="badge bg-secondary">
                        {{ $accionCorrectiva->verificacionesCierre->count() }}
                    </span>

                </div>

            </div>

            <div class="card-body">

                @forelse($accionCorrectiva->verificacionesCierre as $verificacion)

                <div class="border rounded p-3">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="fw-semibold">
                                Ciclo {{ $verificacion->ciclo }}
                            </div>

                            <div class="text-muted small">
                                {{ $verificacion->fecha_verificacion?->format('d/m/Y') }}
                            </div>

                        </div>

                        <span class="badge
                        {{ $verificacion->resultado_verificacion === 'aprobada'
                            ? 'bg-success'
                            : 'bg-danger' }}
                    ">
                            {{ ucfirst($verificacion->resultado_verificacion) }}
                        </span>

                    </div>

                    <div class="row g-3 mt-2">

                        <div class="col-md-4">

                            <div class="small text-muted">
                                Acciones implementadas
                            </div>

                            <div class="fw-semibold">

                                @if($verificacion->acciones_implementadas)
                                <span class="text-success">✓ Sí</span>
                                @else
                                <span class="text-danger">✕ No</span>
                                @endif

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="small text-muted">
                                Evidencias completas
                            </div>

                            <div class="fw-semibold">

                                @if($verificacion->evidencias_completas)
                                <span class="text-success">✓ Sí</span>
                                @else
                                <span class="text-danger">✕ No</span>
                                @endif

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="small text-muted">
                                Implementación conforme
                            </div>

                            <div class="fw-semibold">

                                @if($verificacion->implementacion_conforme)
                                <span class="text-success">✓ Sí</span>
                                @else
                                <span class="text-danger">✕ No</span>
                                @endif

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

                    <div class="mt-3 pt-3 border-top small text-muted">

                        Verificado por:
                        <strong>
                            {{ $verificacion->verificadoPor?->name ?? 'Sin usuario' }}
                        </strong>

                    </div>

                </div>

                @empty

                <div class="text-muted text-center py-3">
                    No existe una verificación de cierre registrada.
                </div>

                @endforelse

            </div>

        </div>
        {{-- =========================================================
    ESPERA DE EFICACIA
========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h2 class="h5 mb-1">
                            Espera de eficacia
                        </h2>

                        <div class="text-muted small">
                            Periodo de observación de las acciones implementadas.
                        </div>

                    </div>

                    <span class="badge bg-secondary">
                        {{ $accionCorrectiva->esperasEficacia->count() }}
                    </span>

                </div>

            </div>

            <div class="card-body">

                @forelse($accionCorrectiva->esperasEficacia as $espera)

                <div class="border rounded p-3 mb-3">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="fw-semibold">
                                Ciclo {{ $espera->ciclo }}
                            </div>

                            <div class="small text-muted">
                                Periodo de observación
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

                    <div class="row g-3 mt-2">

                        <div class="col-md-4">

                            <div class="small text-muted">
                                Inicio
                            </div>

                            <div class="fw-semibold">
                                {{ $espera->fecha_inicio?->format('d/m/Y') }}
                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="small text-muted">
                                Fecha de verificación
                            </div>

                            <div class="fw-semibold">
                                {{ $espera->fecha_verificacion?->format('d/m/Y') }}
                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="small text-muted">
                                Responsable
                            </div>

                            <div class="fw-semibold">
                                {{ $espera->responsable?->name ?? 'Sin responsable' }}
                            </div>

                        </div>

                    </div>

                    @if($espera->observaciones)

                    <div class="mt-3">

                        <div class="small text-muted">
                            Observaciones
                        </div>

                        <div>
                            {{ $espera->observaciones }}
                        </div>

                    </div>

                    @endif

                </div>

                @empty

                <div class="text-muted text-center py-3">
                    No existe un periodo de espera de eficacia registrado.
                </div>

                @endforelse

            </div>

        </div>

        {{-- =========================================================
    VERIFICACIÓN DE EFICACIA
========================================================== --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h2 class="h5 mb-1">
                            Verificación de eficacia
                        </h2>

                        <div class="text-muted small">
                            Determinación de la eficacia de las acciones implementadas.
                        </div>

                    </div>

                    <span class="badge bg-secondary">
                        {{ $accionCorrectiva->verificacionesEficacia->count() }}
                    </span>

                </div>

            </div>

            <div class="card-body">

                @forelse($accionCorrectiva->verificacionesEficacia as $verificacion)

                @php
                $eficaz = $verificacion->resultado_eficaz;
                @endphp

                <div class="border rounded p-3 mb-3">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <div class="fw-semibold">
                                Ciclo {{ $verificacion->ciclo }}
                            </div>

                            <div class="small text-muted">
                                {{ $verificacion->fecha_verificacion?->format('d/m/Y') }}
                            </div>

                        </div>

                        <span class="badge {{ $eficaz ? 'bg-success' : 'bg-danger' }}">

                            {{ $eficaz ? 'Eficaz' : 'No eficaz' }}

                        </span>

                    </div>

                    <div class="row g-3 mt-2">

                        <div class="col-md-6">

                            <div class="small text-muted">
                                Criterios cumplidos
                            </div>

                            <div class="fw-semibold">

                                @if($verificacion->criterios_cumplidos)
                                <span class="text-success">✓ Sí</span>
                                @else
                                <span class="text-danger">✕ No</span>
                                @endif

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="small text-muted">
                                Resultado
                            </div>

                            <div class="fw-semibold
                            {{ $eficaz ? 'text-success' : 'text-danger' }}
                        ">

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

                    <div class="mt-3 pt-3 border-top small text-muted">

                        Verificado por:
                        <strong>
                            {{ $verificacion->verificadoPor?->name ?? 'Sin usuario' }}
                        </strong>

                    </div>

                </div>

                @empty

                <div class="text-muted text-center py-3">
                    No existe una verificación de eficacia registrada.
                </div>

                @endforelse

            </div>

        </div>


        {{-- =========================================================
            INFORMACIÓN TÉCNICA
        ========================================================== --}}

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h2 class="h5 mb-0">
                    Información de control
                </h2>

            </div>

            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-3">

                        <div class="text-muted small">
                            ID
                        </div>

                        <div class="fw-semibold">
                            {{ $accionCorrectiva->id }}
                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="text-muted small">
                            Ciclo actual
                        </div>

                        <div class="fw-semibold">
                            {{ $accionCorrectiva->ciclo_actual }}
                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="text-muted small">
                            Avance
                        </div>

                        <div class="fw-semibold">
                            {{ number_format($accionCorrectiva->porcentaje_avance, 2) }}%
                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="text-muted small">
                            Estado
                        </div>

                        <div class="fw-semibold">
                            {{ $accionCorrectiva->estado?->codigo }}
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>