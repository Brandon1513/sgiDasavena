<x-app-layout>

    <div class="an-root relative min-h-screen">
        <div class="an-bg pointer-events-none fixed inset-0 z-0" style="background-image:url('https://dasavenasite.domcloud.dev/images/background-pattern.png');"></div>
        <div class="an-veil pointer-events-none fixed inset-0 z-[1]"></div>

        <div class="relative z-10 mx-auto flex max-w-[1200px] flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">

            {{-- =========================================================
                ENCABEZADO
            ========================================================== --}}
            <header class="an-card an-card-hero tilt-card reveal-3d relative overflow-hidden">
                <div class="gold-gleam absolute inset-x-0 top-0 z-[2] h-[3px]"></div>
                <div class="an-hero-dotgrid pointer-events-none absolute inset-0" aria-hidden="true"></div>

                <div class="relative z-[1] flex flex-col gap-4 p-6 sm:p-8">
                    <a href="{{ route('acciones-correctivas.show', $accionCorrectiva) }}" class="an-back-link">
                        <i class="ti ti-arrow-left"></i> Volver a la Acción Correctiva
                    </a>

                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <span class="an-eyebrow">Análisis de causa raíz</span>
                            <h1 class="mt-2 font-display text-2xl font-extrabold text-white sm:text-3xl">
                                {{ $accionCorrectiva->codigo }}
                            </h1>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="badge-pill badge-purple-solid">{{ $accionCorrectiva->estado->nombre }}</span>
                                <span class="badge-pill badge-gold-outline">
                                    <i class="ti ti-arrow-repeat"></i> Ciclo {{ $accionCorrectiva->ciclo_actual }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- =========================================================
                MOTIVO (nuevo ciclo)
            ========================================================== --}}
            @if(
            $accionCorrectiva->estado?->codigo === 'analisis'
            && $accionCorrectiva->ciclo_actual > 1
            )
            <div class="an-alert an-alert-warning reveal-3d">
                <i class="ti ti-alert-triangle"></i>
                <div>
                    <div class="mb-0.5 font-bold">Nuevo análisis requerido</div>
                    <div>La verificación de eficacia del ciclo anterior determinó que las acciones implementadas no fueron eficaces.</div>
                </div>
            </div>
            @endif

            {{-- =========================================================
                ANÁLISIS DEL CICLO ACTUAL
            ========================================================== --}}
            <section class="an-card tilt-card reveal-3d">
                <div class="an-card-header">
                    <div class="an-card-title">Análisis · Ciclo {{ $accionCorrectiva->ciclo_actual }}</div>
                </div>
                <div class="p-6 pt-4">
                    @forelse($accionCorrectiva->analisis as $analisis)
                    @if($analisis->ciclo == $accionCorrectiva->ciclo_actual)
                    <div class="an-subcard">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-indigo-950">Análisis iniciado</div>
                                <div class="mt-0.5 text-xs text-gray-400">{{ $analisis->fecha_inicio?->format('d/m/Y') }}</div>
                            </div>
                            <span class="badge-pill badge-amber">{{ ucfirst(str_replace('_', ' ', $analisis->estado)) }}</span>
                        </div>
                    </div>
                    @endif
                    @empty
                    <div class="an-empty">
                        <div class="mb-3">Todavía no existe un análisis para este ciclo.</div>
                        @if($accionCorrectiva->estado?->codigo === 'analisis')
                        <form method="POST" action="{{ route('acciones-correctivas.analisis.iniciar', $accionCorrectiva) }}">
                            @csrf
                            <button type="submit" class="btn-glass-primary">Iniciar análisis</button>
                        </form>
                        @endif
                    </div>
                    @endforelse
                </div>
            </section>

            {{-- =========================================================
                IDEAS / CAUSAS POTENCIALES
            ========================================================== --}}
            <section class="an-card tilt-card reveal-3d">
                <div class="an-card-header">
                    <div>
                        <div class="an-card-title">Ideas / causas potenciales</div>
                        <p class="an-card-sub">Identificación de posibles causas mediante las 6M de Ishikawa.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge-pill badge-slate">{{ $analisisActual?->ideas?->count() ?? 0 }}</span>
                        @if($analisisActual?->estado === 'en_proceso')
                        <button type="button" class="btn-glass-primary btn-glass-sm" onclick="toggleIdea()">
                            <i class="ti ti-plus"></i> Agregar idea
                        </button>
                        @endif
                    </div>
                </div>

                <div class="p-6 pt-4">
                    @if($analisisActual?->estado === 'en_proceso')
                    <div id="formIdea" class="mb-4" style="display:none;">
                        <div class="an-form-panel">
                            <div class="mb-1 font-semibold text-indigo-950">Registrar idea / causa potencial</div>
                            <p class="an-card-sub mb-4">Identifica una posible causa del problema y clasifícala dentro de las 6M.</p>

                            <form method="POST" action="{{ route('acciones-correctivas.analisis.idea', $accionCorrectiva) }}">
                                @csrf
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
                                    <div class="md:col-span-12">
                                        <label class="an-label">Descripción de la causa potencial</label>
                                        <textarea name="descripcion" class="an-field" rows="3" required maxlength="2000"
                                            placeholder="Describe la posible causa identificada...">{{ old('descripcion') }}</textarea>
                                    </div>
                                    <div class="md:col-span-6">
                                        <label class="an-label">Categoría Ishikawa</label>
                                        <select name="categoria_ishikawa" class="an-field" required>
                                            <option value="">Seleccionar categoría</option>
                                            <option value="mano_obra" @selected(old('categoria_ishikawa')==='mano_obra')>Mano de obra</option>
                                            <option value="metodo" @selected(old('categoria_ishikawa')==='metodo')>Método</option>
                                            <option value="maquinaria" @selected(old('categoria_ishikawa')==='maquinaria')>Maquinaria</option>
                                            <option value="materia_prima" @selected(old('categoria_ishikawa')==='materia_prima')>Materia prima</option>
                                            <option value="medicion" @selected(old('categoria_ishikawa')==='medicion')>Medición</option>
                                            <option value="medio_ambiente" @selected(old('categoria_ishikawa')==='medio_ambiente')>Medio ambiente</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-6">
                                        <label class="an-label">¿Es una causa probable?</label>
                                        <label class="an-inline-check">
                                            <input type="hidden" name="es_causa_probable" value="0">
                                            <input type="checkbox" name="es_causa_probable" value="1" id="esCausaProbable" @checked(old('es_causa_probable', true))>
                                            <span>Marcar como causa probable</span>
                                        </label>
                                    </div>
                                    <div class="md:col-span-12">
                                        <label class="an-label">Observaciones</label>
                                        <textarea name="observaciones" class="an-field" rows="2" maxlength="2000" placeholder="Observaciones adicionales...">{{ old('observaciones') }}</textarea>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" class="btn-glass-light" onclick="toggleIdea()">Cancelar</button>
                                    <button type="submit" class="btn-glass-primary">Guardar idea</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    @forelse($analisisActual?->ideas ?? [] as $idea)
                    <div class="an-subcard mb-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="font-semibold text-indigo-950">{{ $idea->descripcion }}</div>
                            @if($idea->es_causa_probable)
                            <span class="badge-pill badge-amber">Causa probable</span>
                            @endif
                        </div>

                        @if($idea->es_causa_probable)
                        @php
                        $yaTieneCincoPorques = $analisisActual->cincoPorques->contains('ac_idea_id', $idea->id);
                        @endphp
                        @if(!$yaTieneCincoPorques)
                        <div class="mt-3 border-t border-black/5 pt-3">
                            <form method="POST" action="{{ route('acciones-correctivas.cinco-porques.iniciar', $accionCorrectiva) }}">
                                @csrf
                                <input type="hidden" name="idea_id" value="{{ $idea->id }}">
                                <div class="mb-2">
                                    <label class="an-label">Título del análisis</label>
                                    <input type="text" name="titulo" class="an-field" value="Análisis de la causa: {{ $idea->descripcion }}" required maxlength="255">
                                </div>
                                <button type="submit" class="btn-glass-outline btn-glass-sm">Iniciar 5 Porqués</button>
                            </form>
                        </div>
                        @else
                        <div class="mt-3">
                            <span class="badge-pill badge-sky">5 Porqués iniciado</span>
                        </div>
                        @endif
                        @endif

                        @if($idea->categoria_ishikawa)
                        <div class="mt-2 text-xs text-gray-400">
                            Categoría Ishikawa: <span class="font-semibold text-indigo-950">{{ $idea->categoria_ishikawa }}</span>
                        </div>
                        @endif

                        @if($idea->observaciones)
                        <div class="mt-2">
                            <div class="text-xs text-gray-400">Observaciones</div>
                            <div class="text-sm text-gray-600">{{ $idea->observaciones }}</div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="an-empty">No hay ideas registradas para este ciclo.</div>
                    @endforelse
                </div>
            </section>

            {{-- =========================================================
                ANÁLISIS DE 5 PORQUÉS
            ========================================================== --}}
            <section class="an-card tilt-card reveal-3d">
                <div class="an-card-header">
                    <div>
                        <div class="an-card-title">Análisis de 5 Porqués</div>
                        <p class="an-card-sub">Profundización de la causa mediante análisis causal.</p>
                    </div>
                    <span class="badge-pill badge-slate">{{ $analisisActual?->cincoPorques?->count() ?? 0 }}</span>
                </div>

                <div class="p-6 pt-4">
                    @forelse($analisisActual?->cincoPorques ?? [] as $cincoPorque)
                    <div class="an-subcard mb-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-indigo-950">{{ $cincoPorque->titulo }}</div>
                                <div class="mt-1 text-xs text-gray-400">Estado: <strong class="text-indigo-950">{{ ucfirst(str_replace('_', ' ', $cincoPorque->estado)) }}</strong></div>
                            </div>
                            <span class="badge-pill {{ $cincoPorque->pasos->count() === 5 ? 'badge-emerald' : 'badge-amber' }}">
                                {{ $cincoPorque->pasos->count() === 5 ? 'Completados' : ucfirst(str_replace('_', ' ', $cincoPorque->estado)) }}
                            </span>
                        </div>

                        {{-- PASOS --}}
                        @if($cincoPorque->pasos->isNotEmpty())
                        <div class="mt-4">
                            <div class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">Secuencia de análisis</div>
                            @foreach($cincoPorque->pasos->sortBy('numero') as $paso)
                            <div class="mb-3 flex gap-3">
                                <div class="an-step-num">{{ $paso->numero }}</div>
                                <div class="flex-1">
                                    <div class="text-xs text-gray-400">Porqué {{ $paso->numero }}</div>
                                    <div class="font-semibold text-indigo-950">{{ $paso->pregunta }}</div>
                                    @if($paso->respuesta)
                                    <div class="mt-2">
                                        <div class="text-xs text-gray-400">Respuesta</div>
                                        <div class="text-sm text-gray-700">{{ $paso->respuesta }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="an-warning-inline mt-3">No hay pasos registrados para este análisis.</div>
                        @endif

                        {{-- SIGUIENTE PORQUÉ --}}
                        @if($cincoPorque->estado === 'en_proceso')
                        @php $numeroSiguiente = ($cincoPorque->pasos->max('numero') ?? 0) + 1; @endphp
                        @if($numeroSiguiente <= 5)
                        <div class="an-form-panel mt-4">
                            <div class="mb-1 font-semibold text-indigo-950">Porqué {{ $numeroSiguiente }}</div>
                            <p class="an-card-sub mb-3">¿Por qué ocurrió el problema en este nivel?</p>
                            <form method="POST" action="{{ route('acciones-correctivas.cinco-porques.paso', ['accionCorrectiva' => $accionCorrectiva, 'cincoPorque' => $cincoPorque]) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="an-label">Respuesta</label>
                                    <textarea name="respuesta" class="an-field" rows="3" maxlength="2000" required placeholder="Describe por qué ocurrió el problema..."></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="btn-glass-primary">Registrar porqué {{ $numeroSiguiente }}</button>
                                </div>
                            </form>
                        </div>
                        @else
                        <div class="an-alert an-alert-success mt-4">
                            <i class="ti ti-circle-check"></i> Los 5 Porqués fueron completados.
                        </div>
                        @endif
                        @endif

                        {{-- CAUSA RAÍZ (desde 5 porqués) --}}
                        @if($cincoPorque->pasos->count() === 5)
                        @php $causaRaiz = $cincoPorque->causaRaiz; @endphp
                        @if(!$causaRaiz)
                        <div class="an-form-panel mt-4">
                            <div class="mb-1 font-semibold text-indigo-950">Proponer causa raíz</div>
                            <p class="an-card-sub mb-3">Con base en los 5 Porqués, registra la causa raíz identificada.</p>
                            <form method="POST" action="{{ route('acciones-correctivas.causa-raiz.proponer', ['accionCorrectiva' => $accionCorrectiva, 'cincoPorque' => $cincoPorque]) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="an-label">Causa raíz</label>
                                    <textarea name="descripcion" class="an-field" rows="4" maxlength="2000" required placeholder="Describe la causa raíz identificada..."></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="btn-glass-primary">Proponer causa raíz</button>
                                </div>
                            </form>
                        </div>
                        @else
                        <div class="mt-4 border-t border-black/5 pt-3">
                            <div class="text-xs text-gray-400">Causa raíz propuesta</div>
                            <div class="mt-1 font-semibold text-indigo-950">{{ $causaRaiz->descripcion }}</div>
                            <div class="mt-2">
                                <span class="badge-pill {{ $causaRaiz->estado_validacion === 'aprobada' ? 'badge-emerald' : ($causaRaiz->estado_validacion === 'rechazada' ? 'badge-rose' : 'badge-amber') }}">
                                    {{ ucfirst(str_replace('_', ' ', $causaRaiz->estado_validacion)) }}
                                </span>
                            </div>
                        </div>
                        @endif
                        @endif
                    </div>
                    @empty
                    <div class="an-empty">No hay análisis de 5 Porqués registrado para este ciclo.</div>
                    @endforelse
                </div>
            </section>

            {{-- =========================================================
                CAUSA RAÍZ
            ========================================================== --}}
            <section class="an-card tilt-card reveal-3d">
                <div class="an-card-header">
                    <div>
                        <div class="an-card-title">Causa raíz</div>
                        <p class="an-card-sub">Causa identificada y estado de validación.</p>
                    </div>
                </div>

                <div class="p-6 pt-4">
                    @forelse($analisisActual?->causasRaiz ?? [] as $causa)
                    <div class="an-subcard mb-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="font-semibold text-indigo-950">{{ $causa->descripcion }}</div>
                            @if($causa->estado_validacion === 'aprobada')
                            <span class="badge-pill badge-emerald">Aprobada</span>
                            @elseif($causa->estado_validacion === 'rechazada')
                            <span class="badge-pill badge-rose">Rechazada</span>
                            @elseif($causa->estado_validacion === 'pendiente')
                            <span class="badge-pill badge-amber">Pendiente</span>
                            @endif
                        </div>

                        @if($causa->fecha_propuesta)
                        <div class="mt-2 text-xs text-gray-400">
                            Propuesta: {{ $causa->fecha_propuesta->format('d/m/Y') }}
                            @if($causa->propuestaPor) · {{ $causa->propuestaPor->name }} @endif
                        </div>
                        @endif

                        @if($causa->estado_validacion === 'pendiente')
                        <div class="an-form-panel mt-4">
                            <div class="mb-1 font-semibold text-indigo-950">Validar causa raíz</div>
                            <p class="an-card-sub mb-3">Revisa la causa propuesta y determina si debe aprobarse o rechazarse.</p>
                            <form method="POST" action="{{ route('acciones-correctivas.causa-raiz.validar', ['accionCorrectiva' => $accionCorrectiva, 'causaRaiz' => $causa]) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="an-label">Comentarios</label>
                                    <textarea name="comentarios" class="an-field" rows="3" maxlength="2000" placeholder="Comentarios de la validación. Obligatorio si se rechaza.">{{ old('comentarios') }}</textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="submit" name="aprobada" value="0" class="btn-glass-danger-outline">Rechazar causa</button>
                                    <button type="submit" name="aprobada" value="1" class="btn-glass-success">Aprobar causa</button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @if($causa->fecha_validacion)
                        <div class="mt-3 text-xs text-gray-400">
                            Validación: {{ $causa->fecha_validacion->format('d/m/Y') }}
                            @if($causa->validadoPor) · {{ $causa->validadoPor->name }} @endif
                        </div>
                        @endif

                        @if($causa->comentarios_validacion)
                        <div class="mt-3">
                            <div class="text-xs text-gray-400">Comentarios de validación</div>
                            <div class="text-sm text-gray-700">{{ $causa->comentarios_validacion }}</div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="an-empty">No hay causa raíz registrada para este ciclo.</div>
                    @endforelse
                </div>
            </section>

            {{-- =========================================================
                HISTORIAL DE ANÁLISIS
            ========================================================== --}}
            <section class="an-card tilt-card reveal-3d">
                <div class="an-card-header">
                    <div class="an-card-title">Historial de análisis</div>
                </div>
                <div class="p-6 pt-4">
                    @forelse($accionCorrectiva->analisis->sortByDesc('ciclo') as $analisis)
                    <div class="an-subcard mb-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-indigo-950">Ciclo {{ $analisis->ciclo }}</div>
                                <div class="mt-0.5 text-xs text-gray-400">Inicio: {{ $analisis->fecha_inicio?->format('d/m/Y') }}</div>
                            </div>
                            <span class="badge-pill badge-slate">{{ ucfirst(str_replace('_', ' ', $analisis->estado)) }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="an-empty">No existen análisis registrados.</div>
                    @endforelse
                </div>
            </section>

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

    .an-bg { background-size: cover; background-position: center; background-attachment: fixed; opacity: .55; }
    .an-veil {
        background: radial-gradient(ellipse 90% 55% at 50% -5%, rgba(255,255,255,.65) 0%, transparent 65%),
                    linear-gradient(180deg, rgba(250,246,251,.55) 0%, rgba(240,232,243,.7) 100%);
    }

    .reveal-3d {
        opacity: 0;
        transform: perspective(1200px) translateY(36px) rotateX(5deg);
        transform-origin: top center;
        transition: opacity .65s cubic-bezier(.22,1,.36,1), transform .65s cubic-bezier(.22,1,.36,1);
    }
    .reveal-3d.is-visible { opacity: 1; transform: perspective(1200px) translateY(0) rotateX(0deg); }
    .tilt-card { transition: transform .25s cubic-bezier(.22,1,.36,1), box-shadow .25s ease; will-change: transform; }

    .an-card {
        background: rgba(255,255,255,.68);
        border: 1px solid rgba(255,255,255,.7);
        border-radius: 24px;
        backdrop-filter: blur(20px) saturate(160%);
        -webkit-backdrop-filter: blur(20px) saturate(160%);
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 8px 32px rgba(106,44,117,.08);
    }
    .an-card-hero {
        background: linear-gradient(135deg, var(--purple-dark) 0%, var(--purple) 55%, var(--purple-light) 100%);
        border-color: rgba(255,255,255,.15);
        box-shadow: 0 12px 44px rgba(106,44,117,.3);
    }
    .an-hero-dotgrid {
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

    .an-back-link { display:inline-flex; align-items:center; gap:6px; font-size: 12.5px; font-weight:600; color: rgba(255,255,255,.6); text-decoration:none; transition: color .2s ease; width: fit-content; }
    .an-back-link:hover { color: #fff; }
    .an-eyebrow { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .14em; color: rgba(255,255,255,.55); }

    .an-card-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; padding: 22px 24px 4px; }
    .an-card-title { font-family: inherit; font-size: 15.5px; font-weight: 800; color: #1e1b4b; }
    .an-card-sub { font-size: 12.5px; color: #9ca3af; margin-top: 2px; }

    .an-subcard, .an-form-panel {
        background: rgba(255,255,255,.7);
        border: 1px solid rgba(255,255,255,.8);
        border-radius: 18px;
        backdrop-filter: blur(14px) saturate(150%);
        -webkit-backdrop-filter: blur(14px) saturate(150%);
        box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 4px 18px rgba(106,44,117,.06);
        padding: 18px;
    }
    .an-form-panel { background: rgba(255,255,255,.55); border-style: dashed; border-color: rgba(106,44,117,.25); }

    .an-empty { text-align:center; padding: 24px 12px; color: #9ca3af; font-size: 13.5px; }
    .an-warning-inline { border-radius: 12px; background: rgba(217,119,6,.1); border: 1px solid rgba(217,119,6,.25); color: #92400e; padding: 10px 14px; font-size: 12.5px; }

    .an-step-num {
        flex-shrink:0; width: 32px; height: 32px; border-radius: 50%;
        display:flex; align-items:center; justify-content:center; font-weight: 800; font-size: 13px;
        background: var(--purple); color: #fff;
    }

    /* ── Badges ── */
    .badge-pill { display:inline-flex; align-items:center; gap:5px; padding: 4px 12px; border-radius: 9999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; border: 1px solid transparent; }
    .badge-purple-solid { background: rgba(255,255,255,.16); color: #fff; border-color: rgba(255,255,255,.25); }
    .badge-purple  { background: rgba(106,44,117,.1); color: var(--purple); border-color: rgba(106,44,117,.2); }
    .badge-gold-outline { background: rgba(214,166,68,.15); color: var(--gold-dark); border-color: rgba(214,166,68,.35); }
    .badge-slate   { background: rgba(148,163,184,.15); color: #475569; border-color: rgba(148,163,184,.3); }
    .badge-sky     { background: rgba(14,165,233,.1); color: #0369a1; border-color: rgba(14,165,233,.25); }
    .badge-amber   { background: rgba(217,119,6,.1); color: #92400e; border-color: rgba(217,119,6,.25); }
    .badge-emerald { background: rgba(5,150,105,.1); color: #065f46; border-color: rgba(5,150,105,.25); }
    .badge-rose    { background: rgba(220,38,38,.1); color: #991b1b; border-color: rgba(220,38,38,.25); }

    /* ── Botones ── */
    .btn-glass-primary, .btn-glass-success, .btn-glass-light, .btn-glass-outline, .btn-glass-danger-outline {
        display:inline-flex; align-items:center; justify-content:center; gap:7px;
        padding: 9px 16px; border-radius: 12px; font-size: 12.5px; font-weight: 700;
        border: 1px solid transparent; cursor: pointer; text-decoration:none; white-space:nowrap;
        transition: transform .2s cubic-bezier(.22,1,.36,1), box-shadow .2s ease, filter .2s ease, background .2s ease;
    }
    .btn-glass-sm { padding: 6px 12px; font-size: 11.5px; }
    .btn-glass-primary { background: linear-gradient(135deg, var(--purple-light), var(--purple-dark)); color:#fff; box-shadow: 0 6px 18px rgba(106,44,117,.3); }
    .btn-glass-success  { background: linear-gradient(135deg, #34d399, #059669); color:#fff; box-shadow: 0 6px 18px rgba(5,150,105,.28); }
    .btn-glass-light { background: rgba(255,255,255,.6); color: #475569; border-color: rgba(0,0,0,.08); backdrop-filter: blur(8px); }
    .btn-glass-outline { background: rgba(106,44,117,.06); color: var(--purple); border-color: rgba(106,44,117,.25); }
    .btn-glass-danger-outline { background: rgba(220,38,38,.06); color: #dc2626; border-color: rgba(220,38,38,.25); }
    .btn-glass-primary:hover, .btn-glass-success:hover { transform: translateY(-2px); filter: brightness(1.05); }
    .btn-glass-light:hover, .btn-glass-outline:hover, .btn-glass-danger-outline:hover { transform: translateY(-2px); background: rgba(255,255,255,.85); }

    /* ── Alertas ── */
    .an-alert { display:flex; align-items:flex-start; gap:12px; padding: 14px 18px; border-radius: 16px; font-size: 13.5px; }
    .an-alert-success { background: rgba(5,150,105,.08); border: 1px solid rgba(5,150,105,.25); color: #065f46; }
    .an-alert-warning { background: rgba(217,119,6,.08); border: 1px solid rgba(217,119,6,.25); color: #92400e; }

    /* ── Campos ── */
    .an-label { display:block; margin-bottom: 6px; font-size: 12.5px; font-weight: 700; color: #374151; }
    .an-field {
        width: 100%; border-radius: 12px; border: 1px solid rgba(106,44,117,.18);
        background: rgba(255,255,255,.75); padding: 9px 12px; font-size: 13.5px; color: #1f2937;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .an-field:focus { outline:none; border-color: var(--purple); box-shadow: 0 0 0 3px rgba(106,44,117,.14); }

    .an-inline-check { display:flex; align-items:center; gap:8px; margin-top: 8px; font-size: 13px; color: #374151; cursor: pointer; }
    .an-inline-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--purple); }

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
        function toggleIdea() {
            const formulario = document.getElementById('formIdea');
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
                            setTimeout(() => entry.target.classList.add('is-visible'), idx * 30);
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: .08, rootMargin: '0px 0px -40px 0px' });
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
