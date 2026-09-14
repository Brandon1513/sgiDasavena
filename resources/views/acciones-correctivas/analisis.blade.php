 <x-app-layout>

     <div class="container-fluid py-4">

         {{-- Encabezado --}}
         <div class="d-flex justify-content-between align-items-start mb-4">

             <div>

                 <a
                     href="{{ route('acciones-correctivas.show', $accionCorrectiva) }}"
                     class="text-decoration-none">
                     ← Volver a la Acción Correctiva
                 </a>

                 <h1 class="h3 mt-3 mb-1">
                     Análisis de causa raíz
                 </h1>

                 <p class="text-muted mb-0">
                     Acción Correctiva {{ $accionCorrectiva->codigo }}
                 </p>

             </div>

             <div class="text-end">

                 <div class="small text-muted">
                     Ciclo actual
                 </div>

                 <div class="fs-3 fw-bold">
                     {{ $accionCorrectiva->ciclo_actual }}
                 </div>

             </div>

         </div>


         {{-- Información de la AC --}}
         <div class="card border-0 shadow-sm mb-4">

             <div class="card-body">

                 <div class="row g-4">

                     <div class="col-md-4">

                         <div class="small text-muted">
                             Acción Correctiva
                         </div>

                         <div class="fw-semibold">
                             {{ $accionCorrectiva->codigo }}
                         </div>

                     </div>

                     <div class="col-md-4">

                         <div class="small text-muted">
                             Estado
                         </div>

                         <span class="badge bg-primary">
                             {{ $accionCorrectiva->estado->nombre }}
                         </span>

                     </div>

                     <div class="col-md-4">

                         <div class="small text-muted">
                             Ciclo
                         </div>

                         <div class="fw-semibold">
                             {{ $accionCorrectiva->ciclo_actual }}
                         </div>

                     </div>

                 </div>

             </div>

         </div>


         {{-- =====================================================
            MOTIVO
        ====================================================== --}}
         @if(
         $accionCorrectiva->estado?->codigo === 'analisis'
         && $accionCorrectiva->ciclo_actual > 1
         )

         <div class="alert alert-warning border-0 shadow-sm mb-4">

             <div class="fw-semibold mb-1">
                 Nuevo análisis requerido
             </div>

             <div>
                 La verificación de eficacia del ciclo anterior
                 determinó que las acciones implementadas no fueron eficaces.
             </div>

         </div>

         @endif

         {{-- =====================================================
            ANÁLISIS DEL CICLO ACTUAL
        ====================================================== --}}

         <div class="card border-0 shadow-sm mb-4">

             <div class="card-header bg-white">

                 <h2 class="h5 mb-0">
                     Análisis · Ciclo {{ $accionCorrectiva->ciclo_actual }}
                 </h2>

             </div>

             <div class="card-body">

                 @forelse($accionCorrectiva->analisis as $analisis)

                 @if($analisis->ciclo == $accionCorrectiva->ciclo_actual)

                 <div class="border rounded p-3">

                     <div class="d-flex justify-content-between">

                         <div>

                             <div class="fw-semibold">
                                 Análisis iniciado
                             </div>

                             <div class="small text-muted">
                                 {{ $analisis->fecha_inicio?->format('d/m/Y') }}
                             </div>

                         </div>

                         <span class="badge bg-warning text-dark">
                             {{ ucfirst(str_replace('_', ' ', $analisis->estado)) }}
                         </span>

                     </div>

                 </div>

                 @endif

                 @empty

                 <div class="text-center py-4">

                     <div class="text-muted mb-3">
                         Todavía no existe un análisis para este ciclo.
                     </div>

                     @if($accionCorrectiva->estado?->codigo === 'analisis')

                     <form
                         method="POST"
                         action="{{ route(
                'acciones-correctivas.analisis.iniciar',
                $accionCorrectiva
            ) }}">

                         @csrf

                         <button
                             type="submit"
                             class="btn btn-primary">
                             Iniciar análisis
                         </button>

                     </form>

                     @endif

                 </div>

                 @endforelse

             </div>

         </div>


         {{-- =====================================================
    IDEAS
====================================================== --}}

         <div class="card border-0 shadow-sm mb-4">

             <div class="card-header bg-white">

                 <div class="d-flex justify-content-between align-items-center">

                     <div>
                         <h2 class="h5 mb-1">
                             Ideas / causas potenciales
                         </h2>

                         <div class="text-muted small">
                             Identificación de posibles causas mediante las 6M de Ishikawa.
                         </div>
                     </div>

                     <div class="d-flex align-items-center gap-2">

                         <span class="badge bg-secondary">
                             {{ $analisisActual?->ideas?->count() ?? 0 }}
                         </span>

                         @if($analisisActual?->estado === 'en_proceso')
                         <button
                             type="button"
                             class="btn btn-sm btn-primary"
                             onclick="toggleIdea()">
                             + Agregar idea
                         </button>
                         @endif

                     </div>

                 </div>

             </div>
             <div class="card-body">
                 @if($analisisActual?->estado === 'en_proceso')

                 <div
                     id="formIdea"
                     class="mb-4"
                     style="display: none;">

                     <div class="border rounded-3 p-4 bg-light">

                         <div class="fw-semibold mb-1">
                             Registrar idea / causa potencial
                         </div>

                         <div class="text-muted small mb-4">
                             Identifica una posible causa del problema y clasifícala dentro de las 6M.
                         </div>

                         <form
                             method="POST"
                             action="{{ route('acciones-correctivas.analisis.idea', $accionCorrectiva) }}">
                             @csrf

                             <div class="row g-3">

                                 <div class="col-12">

                                     <label class="form-label fw-semibold">
                                         Descripción de la causa potencial
                                     </label>

                                     <textarea
                                         name="descripcion"
                                         class="form-control"
                                         rows="3"
                                         required
                                         maxlength="2000"
                                         placeholder="Describe la posible causa identificada...">{{ old('descripcion') }}</textarea>

                                 </div>

                                 <div class="col-md-6">

                                     <label class="form-label fw-semibold">
                                         Categoría Ishikawa
                                     </label>

                                     <select
                                         name="categoria_ishikawa"
                                         class="form-select"
                                         required>
                                         <option value="">
                                             Seleccionar categoría
                                         </option>

                                         <option
                                             value="mano_obra"
                                             @selected(old('categoria_ishikawa')==='mano_obra' )>
                                             Mano de obra
                                         </option>

                                         <option
                                             value="metodo"
                                             @selected(old('categoria_ishikawa')==='metodo' )>
                                             Método
                                         </option>

                                         <option
                                             value="maquinaria"
                                             @selected(old('categoria_ishikawa')==='maquinaria' )>
                                             Maquinaria
                                         </option>

                                         <option
                                             value="materia_prima"
                                             @selected(old('categoria_ishikawa')==='materia_prima' )>
                                             Materia prima
                                         </option>

                                         <option
                                             value="medicion"
                                             @selected(old('categoria_ishikawa')==='medicion' )>
                                             Medición
                                         </option>

                                         <option
                                             value="medio_ambiente"
                                             @selected(old('categoria_ishikawa')==='medio_ambiente' )>
                                             Medio ambiente
                                         </option>

                                     </select>

                                 </div>

                                 <div class="col-md-6">

                                     <label class="form-label fw-semibold">
                                         ¿Es una causa probable?
                                     </label>

                                     <div class="form-check mt-2">

                                         <input
                                             type="hidden"
                                             name="es_causa_probable"
                                             value="0">

                                         <input
                                             class="form-check-input"
                                             type="checkbox"
                                             name="es_causa_probable"
                                             value="1"
                                             id="esCausaProbable"
                                             @checked(old('es_causa_probable', true))>

                                         <label
                                             class="form-check-label"
                                             for="esCausaProbable">
                                             Marcar como causa probable
                                         </label>

                                     </div>

                                 </div>

                                 <div class="col-12">

                                     <label class="form-label fw-semibold">
                                         Observaciones
                                     </label>

                                     <textarea
                                         name="observaciones"
                                         class="form-control"
                                         rows="2"
                                         maxlength="2000"
                                         placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>

                                 </div>

                             </div>

                             <div class="d-flex justify-content-end gap-2 mt-4">

                                 <button
                                     type="button"
                                     class="btn btn-light"
                                     onclick="toggleIdea()">
                                     Cancelar
                                 </button>

                                 <button
                                     type="submit"
                                     class="btn btn-primary">
                                     Guardar idea
                                 </button>

                             </div>

                         </form>

                     </div>

                 </div>

                 @endif

                 @forelse($analisisActual?->ideas ?? [] as $idea)

                 <div class="border rounded p-3 mb-3">

                     <div class="d-flex justify-content-between">

                         <div class="fw-semibold">
                             {{ $idea->descripcion }}
                         </div>

                         @if($idea->es_causa_probable)

                         <span class="badge bg-warning text-dark">
                             Causa probable
                         </span>

                         @endif

                     </div>
                     @if($idea->es_causa_probable)

                     @php
                     $yaTieneCincoPorques = $analisisActual
                     ->cincoPorques
                     ->contains('ac_idea_id', $idea->id);
                     @endphp

                     @if(!$yaTieneCincoPorques)

                     <div class="mt-3 pt-3 border-top">

                         <form
                             method="POST"
                             action="{{ route(
                    'acciones-correctivas.cinco-porques.iniciar',
                    $accionCorrectiva
                ) }}">

                             @csrf

                             <input
                                 type="hidden"
                                 name="idea_id"
                                 value="{{ $idea->id }}">

                             <div class="mb-2">

                                 <label class="form-label small">
                                     Título del análisis
                                 </label>

                                 <input
                                     type="text"
                                     name="titulo"
                                     class="form-control"
                                     value="Análisis de la causa: {{ $idea->descripcion }}"
                                     required
                                     maxlength="255">

                             </div>

                             <button
                                 type="submit"
                                 class="btn btn-outline-primary btn-sm">
                                 Iniciar 5 Porqués
                             </button>

                         </form>

                     </div>

                     @else

                     <div class="mt-3">

                         <span class="badge bg-info">
                             5 Porqués iniciado
                         </span>

                     </div>

                     @endif

                     @endif

                     @if($idea->categoria_ishikawa)

                     <div class="mt-2">

                         <span class="small text-muted">
                             Categoría Ishikawa:
                         </span>

                         <span class="fw-semibold">
                             {{ $idea->categoria_ishikawa }}
                         </span>

                     </div>

                     @endif

                     @if($idea->observaciones)

                     <div class="mt-2">

                         <div class="small text-muted">
                             Observaciones
                         </div>

                         <div>
                             {{ $idea->observaciones }}
                         </div>

                     </div>

                     @endif

                 </div>

                 @empty

                 <div class="text-muted text-center py-3">
                     No hay ideas registradas para este ciclo.
                 </div>

                 @endforelse

             </div>

         </div>
         {{-- =====================================================
    CAUSA RAÍZ
====================================================== --}}

         {{-- =====================================================
    CAUSA RAÍZ
====================================================== --}}

         <div class="card border-0 shadow-sm mb-4">

             <div class="card-header bg-white">

                 <h2 class="h5 mb-1">
                     Causa raíz
                 </h2>

                 <div class="text-muted small">
                     Causa identificada y estado de validación.
                 </div>

             </div>

             <div class="card-body">

                 @forelse($analisisActual?->causasRaiz ?? [] as $causa)

                 <div class="border rounded-3 p-3 mb-3">

                     {{-- =================================================
                    ENCABEZADO DE LA CAUSA
                ================================================== --}}

                     <div class="d-flex justify-content-between align-items-start gap-3">

                         <div class="fw-semibold">
                             {{ $causa->descripcion }}
                         </div>

                         @if($causa->estado_validacion === 'aprobada')

                         <span class="badge bg-success">
                             Aprobada
                         </span>

                         @elseif($causa->estado_validacion === 'rechazada')

                         <span class="badge bg-danger">
                             Rechazada
                         </span>

                         @elseif($causa->estado_validacion === 'pendiente')

                         <span class="badge bg-warning text-dark">
                             Pendiente
                         </span>

                         @endif

                     </div>


                     {{-- =================================================
                    DATOS DE PROPUESTA
                ================================================== --}}

                     @if($causa->fecha_propuesta)

                     <div class="small text-muted mt-2">

                         Propuesta:
                         {{ $causa->fecha_propuesta->format('d/m/Y') }}

                         @if($causa->propuestaPor)
                         · {{ $causa->propuestaPor->name }}
                         @endif

                     </div>

                     @endif


                     {{-- =================================================
                    VALIDACIÓN
                ================================================== --}}

                     @if($causa->estado_validacion === 'pendiente')

                     <div class="border rounded-3 p-3 mt-4 bg-light">

                         <div class="fw-semibold mb-1">
                             Validar causa raíz
                         </div>

                         <div class="text-muted small mb-3">
                             Revisa la causa propuesta y determina si debe aprobarse o rechazarse.
                         </div>

                         <form
                             method="POST"
                             action="{{ route(
                                'acciones-correctivas.causa-raiz.validar',
                                [
                                    'accionCorrectiva' => $accionCorrectiva,
                                    'causaRaiz' => $causa,
                                ]
                            ) }}">

                             @csrf

                             <div class="mb-3">

                                 <label class="form-label fw-semibold">
                                     Comentarios
                                 </label>

                                 <textarea
                                     name="comentarios"
                                     class="form-control"
                                     rows="3"
                                     maxlength="2000"
                                     placeholder="Comentarios de la validación. Obligatorio si se rechaza.">{{ old('comentarios') }}</textarea>

                             </div>

                             <div class="d-flex justify-content-end gap-2">

                                 <button
                                     type="submit"
                                     name="aprobada"
                                     value="0"
                                     class="btn btn-outline-danger">
                                     Rechazar causa
                                 </button>

                                 <button
                                     type="submit"
                                     name="aprobada"
                                     value="1"
                                     class="btn btn-success">
                                     Aprobar causa
                                 </button>

                             </div>

                         </form>

                     </div>

                     @endif


                     {{-- =================================================
                    DATOS DE VALIDACIÓN
                ================================================== --}}

                     @if($causa->fecha_validacion)

                     <div class="small text-muted mt-3">

                         Validación:
                         {{ $causa->fecha_validacion->format('d/m/Y') }}

                         @if($causa->validadoPor)
                         · {{ $causa->validadoPor->name }}
                         @endif

                     </div>

                     @endif


                     {{-- =================================================
                    COMENTARIOS DE VALIDACIÓN
                ================================================== --}}

                     @if($causa->comentarios_validacion)

                     <div class="mt-3">

                         <div class="small text-muted">
                             Comentarios de validación
                         </div>

                         <div>
                             {{ $causa->comentarios_validacion }}
                         </div>

                     </div>

                     @endif

                 </div>

                 @empty

                 <div class="text-muted text-center py-4">
                     No hay causa raíz registrada para este ciclo.
                 </div>

                 @endforelse

             </div>

         </div>



         {{-- =====================================================
            HISTORIAL DE CICLOS
        ====================================================== --}}

         <div class="card border-0 shadow-sm">

             <div class="card-header bg-white">

                 <h2 class="h5 mb-0">
                     Historial de análisis
                 </h2>

             </div>

             <div class="card-body">

                 @forelse($accionCorrectiva->analisis->sortByDesc('ciclo') as $analisis)

                 <div class="border rounded p-3 mb-3">

                     <div class="d-flex justify-content-between">

                         <div>

                             <div class="fw-semibold">
                                 Ciclo {{ $analisis->ciclo }}
                             </div>

                             <div class="small text-muted">
                                 Inicio:
                                 {{ $analisis->fecha_inicio?->format('d/m/Y') }}
                             </div>

                         </div>

                         <span class="badge bg-secondary">
                             {{ ucfirst(str_replace('_', ' ', $analisis->estado)) }}
                         </span>

                     </div>

                 </div>

                 @empty

                 <div class="text-muted">
                     No existen análisis registrados.
                 </div>

                 @endforelse

             </div>

         </div>

     </div>

     {{-- =====================================================
    ANÁLISIS DE 5 PORQUÉS
====================================================== --}}

     <div class="card border-0 shadow-sm mb-4">

         <div class="card-header bg-white">

             <div class="d-flex justify-content-between align-items-center">

                 <div>
                     <h2 class="h5 mb-1">
                         Análisis de 5 Porqués
                     </h2>

                     <div class="text-muted small">
                         Profundización de la causa mediante análisis causal.
                     </div>
                 </div>

                 <span class="badge bg-secondary">
                     {{ $analisisActual?->cincoPorques?->count() ?? 0 }}
                 </span>

             </div>

         </div>

         <div class="card-body">

             @forelse($analisisActual?->cincoPorques ?? [] as $cincoPorque)

             <div class="border rounded-3 p-3 mb-4">

                 {{-- CABECERA --}}
                 <div class="d-flex justify-content-between align-items-start">

                     <div>

                         <div class="fw-semibold">
                             {{ $cincoPorque->titulo }}
                         </div>

                         <div class="small text-muted mt-1">
                             Estado:
                             <strong>
                                 {{ ucfirst(str_replace('_', ' ', $cincoPorque->estado)) }}
                             </strong>
                         </div>

                     </div>

                     <span class="badge
                        {{ $cincoPorque->pasos->count() === 5
                            ? 'bg-success'
                            : 'bg-warning text-dark' }}">
                         {{ $cincoPorque->pasos->count() === 5
                            ? 'Completados'
                            : ucfirst(str_replace('_', ' ', $cincoPorque->estado)) }}
                     </span>

                 </div>


                 {{-- =====================================================
                    PASOS
                ====================================================== --}}

                 @if($cincoPorque->pasos->isNotEmpty())

                 <div class="mt-4">

                     <div class="fw-semibold mb-3">
                         Secuencia de análisis
                     </div>

                     @foreach($cincoPorque->pasos->sortBy('numero') as $paso)

                     <div class="d-flex gap-3 mb-3">

                         <div
                             class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 32px; height: 32px;">
                             {{ $paso->numero }}
                         </div>

                         <div class="flex-grow-1">

                             <div class="small text-muted">
                                 Porqué {{ $paso->numero }}
                             </div>

                             <div class="fw-semibold">
                                 {{ $paso->pregunta }}
                             </div>

                             @if($paso->respuesta)

                             <div class="mt-2">

                                 <div class="small text-muted">
                                     Respuesta
                                 </div>

                                 <div>
                                     {{ $paso->respuesta }}
                                 </div>

                             </div>

                             @endif

                         </div>

                     </div>

                     @endforeach

                 </div>

                 @else

                 <div class="alert alert-light mt-3 mb-0">
                     No hay pasos registrados para este análisis.
                 </div>

                 @endif


                 {{-- =====================================================
                    SIGUIENTE PORQUÉ
                ====================================================== --}}

                 @if($cincoPorque->estado === 'en_proceso')

                 @php
                 $numeroSiguiente = ($cincoPorque->pasos->max('numero') ?? 0) + 1;
                 @endphp

                 @if($numeroSiguiente <= 5)

                     <div class="border rounded-3 p-3 mt-4 bg-light">

                     <div class="fw-semibold mb-1">
                         Porqué {{ $numeroSiguiente }}
                     </div>

                     <div class="text-muted small mb-3">
                         ¿Por qué ocurrió el problema en este nivel?
                     </div>

                     <form
                         method="POST"
                         action="{{ route(
                                    'acciones-correctivas.cinco-porques.paso',
                                    [
                                        'accionCorrectiva' => $accionCorrectiva,
                                        'cincoPorque' => $cincoPorque,
                                    ]
                                ) }}">

                         @csrf

                         <div class="mb-3">

                             <label class="form-label fw-semibold">
                                 Respuesta
                             </label>

                             <textarea
                                 name="respuesta"
                                 class="form-control"
                                 rows="3"
                                 maxlength="2000"
                                 required
                                 placeholder="Describe por qué ocurrió el problema..."></textarea>

                         </div>

                         <div class="d-flex justify-content-end">

                             <button
                                 type="submit"
                                 class="btn btn-primary">
                                 Registrar porqué {{ $numeroSiguiente }}
                             </button>

                         </div>

                     </form>

             </div>

             @else

             <div class="alert alert-success mt-4 mb-0">
                 ✓ Los 5 Porqués fueron completados.
             </div>

             @endif

             @endif


             {{-- =====================================================
                    CAUSA RAÍZ
                ====================================================== --}}

             @if($cincoPorque->pasos->count() === 5)

             @php
             $causaRaiz = $cincoPorque->causaRaiz;
             @endphp

             @if(!$causaRaiz)

             <div class="border rounded-3 p-3 mt-4 bg-light">

                 <div class="fw-semibold mb-1">
                     Proponer causa raíz
                 </div>

                 <div class="text-muted small mb-3">
                     Con base en los 5 Porqués, registra la causa raíz identificada.
                 </div>

                 <form
                     method="POST"
                     action="{{ route(
                                    'acciones-correctivas.causa-raiz.proponer',
                                    [
                                        'accionCorrectiva' => $accionCorrectiva,
                                        'cincoPorque' => $cincoPorque,
                                    ]
                                ) }}">

                     @csrf

                     <div class="mb-3">

                         <label class="form-label fw-semibold">
                             Causa raíz
                         </label>

                         <textarea
                             name="descripcion"
                             class="form-control"
                             rows="4"
                             maxlength="2000"
                             required
                             placeholder="Describe la causa raíz identificada..."></textarea>

                     </div>

                     <div class="d-flex justify-content-end">

                         <button
                             type="submit"
                             class="btn btn-primary">
                             Proponer causa raíz
                         </button>

                     </div>

                 </form>

             </div>

             @else

             <div class="mt-4 pt-3 border-top">

                 <div class="small text-muted">
                     Causa raíz propuesta
                 </div>

                 <div class="fw-semibold mt-1">
                     {{ $causaRaiz->descripcion }}
                 </div>

                 <div class="mt-2">

                     <span class="badge
                                    {{ $causaRaiz->estado_validacion === 'aprobada'
                                        ? 'bg-success'
                                        : ($causaRaiz->estado_validacion === 'rechazada'
                                            ? 'bg-danger'
                                            : 'bg-warning text-dark') }}">
                         {{ ucfirst(str_replace('_', ' ', $causaRaiz->estado_validacion)) }}
                     </span>

                 </div>

             </div>

             @endif

             @endif

         </div>

         @empty

         <div class="text-muted text-center py-4">
             No hay análisis de 5 Porqués registrado para este ciclo.
         </div>

         @endforelse

     </div>

     </div>


     <script>
         function toggleIdea() {
             const formulario = document.getElementById('formIdea');

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