<?php

namespace App\Http\Controllers;

use App\Domains\Incidencias\Models\AccionCorrectiva;
use App\Domains\Incidencias\Models\OrigenAccionCorrectiva;
use App\Models\User;
use Illuminate\Http\Request;

use App\Domains\Incidencias\Actions\AccionesDisponiblesAccionCorrectiva;
use App\Domains\Incidencias\Actions\IniciarAnalisis;
use App\Domains\Incidencias\Actions\AgregarIdeaAnalisis;
use App\Domains\Incidencias\Models\AcAnalisis;
use App\Domains\Incidencias\Actions\IniciarCincoPorques;
use App\Domains\Incidencias\Actions\AgregarPorque;
use App\Domains\Incidencias\Actions\ProponerCausaRaiz;
use App\Domains\Incidencias\Actions\ValidarCausaRaiz;
use App\Domains\Incidencias\Models\AcCincoPorque;
use App\Domains\Incidencias\Models\AcCausaRaiz;
use App\Domains\Incidencias\Actions\CambiarEstadoAccionCorrectiva;
use App\Domains\Incidencias\Actions\CrearContencion;
use App\Domains\Incidencias\Models\EstadoAccionCorrectiva as EstadoModel;
use App\Domains\Incidencias\Enums\EstadoAccionCorrectiva;
use App\Domains\Incidencias\Actions\CrearPlanAccion;
use App\Domains\Incidencias\Actions\AgregarActividadPlan;
use App\Domains\Incidencias\Models\AcPlanAccion;
use App\Domains\Incidencias\Actions\CrearEvidencia;
use App\Domains\Incidencias\Actions\CompletarActividad;
use App\Domains\Incidencias\Models\AcActividad;
use App\Domains\Incidencias\Actions\RegistrarVerificacionCierre;
use App\Domains\Incidencias\Actions\IniciarEsperaEficacia;
use App\Domains\Incidencias\Actions\RegistrarVerificacionEficacia;
use Illuminate\Support\Facades\DB;

class AccionCorrectivaController extends Controller
{
    /**
     * Listado de acciones correctivas.
     */
    public function index(Request $request)
    {
        $query = AccionCorrectiva::query()
            ->with([
                'estado',
                'origen',
                'responsable',
            ])
            ->orderByDesc('id');

        /*
         * Filtro por estado
         */
        if ($request->filled('estado')) {
            $query->whereHas('estado', function ($q) use ($request) {
                $q->where('codigo', $request->estado);
            });
        }

        /*
         * Filtro por responsable
         */
        if ($request->filled('responsable')) {
            $query->where(
                'responsable_id',
                $request->responsable
            );
        }

        /*
         * Búsqueda
         */
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('codigo', 'like', "%{$buscar}%")
                    ->orWhere(
                        'descripcion',
                        'like',
                        "%{$buscar}%"
                    );
            });
        }

        $accionesCorrectivas = $query
            ->paginate(15)
            ->withQueryString();

        $estados = EstadoModel::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $responsables = User::query()
            ->where('activo', true)
            ->orderBy('name')
            ->get();

        return view(
            'acciones-correctivas.index',
            compact(
                'accionesCorrectivas',
                'estados',
                'responsables'
            )
        );
    }


    public function store(Request $request)
    {
        $datos = $request->validate([
            'descripcion' => [
                'required',
                'string',
                'max:2000',
            ],

            'origen_id' => [
                'required',
                'integer',
                'exists:origenes_acciones_correctivas,id',
            ],

            'responsable_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $accionCorrectiva = DB::transaction(function () use ($datos) {

            $prefijo = now()->format('y');

            $ultimoNumero = AccionCorrectiva::query()
                ->where('codigo', 'like', $prefijo . '-%')
                ->pluck('codigo')
                ->map(function ($codigo) use ($prefijo) {
                    return (int) str_replace(
                        $prefijo . '-',
                        '',
                        $codigo
                    );
                })
                ->max() ?? 0;

            $codigo = sprintf(
                '%s-%03d',
                $prefijo,
                $ultimoNumero + 1
            );

            $estadoBorrador = EstadoModel::query()
                ->where('codigo', EstadoAccionCorrectiva::BORRADOR->value)
                ->where('activo', true)
                ->firstOrFail();

            return AccionCorrectiva::create([
                'codigo' => $codigo,
                'descripcion' => $datos['descripcion'],
                'origen_id' => $datos['origen_id'],
                'responsable_id' => $datos['responsable_id'],
                'estado_id' => $estadoBorrador->id,
                'fecha_apertura' => now()->toDateString(),
                'ciclo_actual' => 1,
                'porcentaje_avance' => 0,
            ]);
        });

        return redirect()
            ->route(
                'acciones-correctivas.show',
                $accionCorrectiva
            )
            ->with(
                'success',
                'La Acción Correctiva fue creada correctamente.'
            );
    }


    public function create()
    {
        $origenes = OrigenAccionCorrectiva::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $responsables = User::query()
            ->where('activo', true)
            ->orderBy('name')
            ->get();

        return view(
            'acciones-correctivas.create',
            compact(
                'origenes',
                'responsables'
            )
        );
    }
    /**
     * Mostrar expediente de una Acción Correctiva.
     */
    public function show(
        AccionCorrectiva $accionCorrectiva,
        AccionesDisponiblesAccionCorrectiva $accionesDisponibles
    ) {
        $accionCorrectiva->load([
            'estado',
            'origen',
            'responsable',

            'contenciones',

            'analisis',

            'planesAccion.actividades.evidencias',
            'planesAccion.actividades.responsable',

            'verificacionesCierre.verificadoPor',

            'esperasEficacia',

            'verificacionesEficacia.verificadoPor',
        ]);

        $acciones = $accionesDisponibles->ejecutar(
            $accionCorrectiva
        );

        return view(
            'acciones-correctivas.show',
            compact(
                'accionCorrectiva',
                'acciones'
            )
        );
    }


    public function analisis(AccionCorrectiva $accionCorrectiva)
    {
        $accionCorrectiva->load([
            'estado',
            'origen',
            'responsable',

            'analisis.ideas',

            'analisis.cincoPorques.pasos',

            'analisis.causasRaiz.propuestaPor',
            'analisis.causasRaiz.validadoPor',
        ]);

        $analisisActual = $accionCorrectiva->analisis
            ->firstWhere(
                'ciclo',
                $accionCorrectiva->ciclo_actual
            );

        return view(
            'acciones-correctivas.analisis',
            compact(
                'accionCorrectiva',
                'analisisActual'
            )
        );
    }
    public function iniciarAnalisis(
        AccionCorrectiva $accionCorrectiva,
        IniciarAnalisis $iniciarAnalisis
    ) {
        $iniciarAnalisis->ejecutar(
            $accionCorrectiva,
            auth()->id()
        );

        return redirect()
            ->route(
                'acciones-correctivas.analisis',
                $accionCorrectiva
            )
            ->with(
                'success',
                'El análisis del ciclo actual fue iniciado correctamente.'
            );
    }
    public function agregarIdea(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        AgregarIdeaAnalisis $agregarIdea
    ) {
        $analisis = $accionCorrectiva->analisis()
            ->where(
                'ciclo',
                $accionCorrectiva->ciclo_actual
            )
            ->firstOrFail();

        $datos = $request->validate([
            'descripcion' => [
                'required',
                'string',
                'max:1000',
            ],

            'categoria_ishikawa' => [
                'required',
                'in:mano_obra,metodo,maquinaria,materia_prima,medicion,medio_ambiente',
            ],

            'es_causa_probable' => [
                'nullable',
                'boolean',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $agregarIdea->ejecutar(
            $analisis,
            $datos
        );

        return redirect()
            ->route(
                'acciones-correctivas.analisis',
                $accionCorrectiva
            )
            ->with(
                'success',
                'La idea fue agregada correctamente.'
            );
    }

    public function iniciarCincoPorques(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        IniciarCincoPorques $iniciarCincoPorques
    ) {
        $analisis = $accionCorrectiva->analisis()
            ->where(
                'ciclo',
                $accionCorrectiva->ciclo_actual
            )
            ->firstOrFail();

        $datos = $request->validate([
            'idea_id' => [
                'required',
                'integer',
                'exists:ac_ideas,id',
            ],

            'titulo' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $idea = $analisis->ideas()
            ->where('id', $datos['idea_id'])
            ->firstOrFail();

        $iniciarCincoPorques->ejecutar(
            $analisis,
            $idea,
            $datos['titulo']
        );

        return redirect()
            ->route(
                'acciones-correctivas.analisis',
                $accionCorrectiva
            )
            ->with(
                'success',
                'La cadena de 5 Porqués fue iniciada correctamente.'
            );
    }

    public function cambiarEstado(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        CambiarEstadoAccionCorrectiva $cambiarEstado
    ) {
        $request->validate([
            'estado' => ['required', 'string'],
        ]);

        try {
            $nuevoEstado = EstadoAccionCorrectiva::from(
                $request->input('estado')
            );

            $cambiarEstado->ejecutar(
                $accionCorrectiva,
                $nuevoEstado
            );

            return redirect()
                ->route('acciones-correctivas.show', $accionCorrectiva)
                ->with(
                    'success',
                    'El estado de la Acción Correctiva fue actualizado correctamente.'
                );
        } catch (\ValueError $e) {

            return back()
                ->withErrors([
                    'estado' => 'El estado seleccionado no es válido.',
                ])
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }


    public function crearContencion(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        CrearContencion $crearContencion
    ) {
        $datos = $request->validate([
            'descripcion' => ['required', 'string', 'max:2000'],
            'responsable_id' => ['required', 'integer', 'exists:users,id'],
            'fecha_implementacion' => ['required', 'date'],
            'estado' => ['required', 'in:pendiente,completada'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $crearContencion->ejecutar(
            $accionCorrectiva,
            $datos
        );

        return redirect()
            ->route('acciones-correctivas.show', $accionCorrectiva)
            ->with('success', 'La acción de contención fue registrada correctamente.');
    }

    public function agregarPorque(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        AcCincoPorque $cincoPorque,
        AgregarPorque $agregarPorque
    ) {
        $datos = $request->validate([
            'respuesta' => ['required', 'string', 'max:2000'],
        ]);

        $analisis = $accionCorrectiva->analisis()
            ->where('ciclo', $accionCorrectiva->ciclo_actual)
            ->firstOrFail();

        if ($cincoPorque->ac_analisis_id !== $analisis->id) {
            abort(404);
        }

        $agregarPorque->ejecutar(
            $cincoPorque,
            $datos['respuesta']
        );

        return redirect()
            ->route('acciones-correctivas.analisis', $accionCorrectiva)
            ->with('success', 'El porqué fue registrado correctamente.');
    }
    public function proponerCausaRaiz(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        AcCincoPorque $cincoPorque,
        ProponerCausaRaiz $proponerCausaRaiz
    ) {
        $datos = $request->validate([
            'descripcion' => ['required', 'string', 'max:2000'],
        ]);

        $analisis = $accionCorrectiva->analisis()
            ->where('ciclo', $accionCorrectiva->ciclo_actual)
            ->firstOrFail();

        if ($cincoPorque->ac_analisis_id !== $analisis->id) {
            abort(404);
        }

        $proponerCausaRaiz->ejecutar(
            $analisis,
            $cincoPorque,
            $datos['descripcion'],
            auth()->id()
        );

        return redirect()
            ->route('acciones-correctivas.analisis', $accionCorrectiva)
            ->with(
                'success',
                'La causa raíz fue propuesta correctamente.'
            );
    }

    public function validarCausaRaiz(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        AcCausaRaiz $causaRaiz,
        ValidarCausaRaiz $validarCausaRaiz
    ) {
        $datos = $request->validate([
            'aprobada' => ['required', 'boolean'],
            'comentarios' => ['nullable', 'string', 'max:2000'],
        ]);

        $analisis = $accionCorrectiva->analisis()
            ->where('ciclo', $accionCorrectiva->ciclo_actual)
            ->firstOrFail();

        if ($causaRaiz->ac_analisis_id !== $analisis->id) {
            abort(404);
        }

        $aprobada = (bool) $datos['aprobada'];

        if (!$aprobada && blank($datos['comentarios'] ?? null)) {
            return back()
                ->withErrors([
                    'comentarios' => 'Debes indicar el motivo del rechazo.',
                ])
                ->withInput();
        }

        $validarCausaRaiz->ejecutar(
            $causaRaiz,
            auth()->id(),
            $aprobada,
            $datos['comentarios'] ?? null
        );

        return redirect()
            ->route('acciones-correctivas.analisis', $accionCorrectiva)
            ->with(
                'success',
                $aprobada
                    ? 'La causa raíz fue aprobada correctamente.'
                    : 'La causa raíz fue rechazada correctamente.'
            );
    }

    public function crearPlanAccion(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        CrearPlanAccion $crearPlanAccion
    ) {
        $datos = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $crearPlanAccion->ejecutar(
            $accionCorrectiva,
            $datos['observaciones'] ?? null
        );

        return redirect()
            ->route('acciones-correctivas.show', $accionCorrectiva)
            ->with('success', 'El plan de acción fue creado correctamente.');
    }

    public function crearEvidencia(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        AcActividad $actividad,
        CrearEvidencia $crearEvidencia
    ) {
        $datos = $request->validate([
            'archivo' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                'max:20480',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $crearEvidencia->ejecutar(
            $actividad,
            $datos['archivo'],
            auth()->user(),
            $datos['descripcion'] ?? null
        );

        return redirect()
            ->route('acciones-correctivas.show', $accionCorrectiva)
            ->with(
                'success',
                'La evidencia fue registrada correctamente.'
            );
    }



    public function completarActividad(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        AcActividad $actividad,
        CompletarActividad $completarActividad
    ) {
        $datos = $request->validate([
            'observaciones' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $completarActividad->ejecutar(
            $actividad,
            $datos['observaciones'] ?? null
        );

        return redirect()
            ->route('acciones-correctivas.show', $accionCorrectiva)
            ->with(
                'success',
                'La actividad fue completada correctamente.'
            );
    }

    public function agregarActividad(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        AgregarActividadPlan $agregarActividadPlan
    ) {
        $datos = $request->validate([
            'descripcion' => ['required', 'string', 'max:2000'],
            'responsable_id' => ['required', 'integer', 'exists:users,id'],
            'fecha_compromiso' => ['required', 'date'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $plan = $accionCorrectiva->planesAccion()
            ->where('ciclo', $accionCorrectiva->ciclo_actual)
            ->firstOrFail();

        $agregarActividadPlan->ejecutar(
            $plan,
            $datos
        );

        return redirect()
            ->route('acciones-correctivas.show', $accionCorrectiva)
            ->with(
                'success',
                'La actividad fue agregada correctamente.'
            );
    }

    public function registrarVerificacionCierre(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        RegistrarVerificacionCierre $registrarVerificacionCierre
    ) {
        $datos = $request->validate([
            'acciones_implementadas' => ['required', 'boolean'],
            'evidencias_completas' => ['required', 'boolean'],
            'implementacion_conforme' => ['required', 'boolean'],
            'resultado' => ['nullable', 'string', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $registrarVerificacionCierre->ejecutar(
            $accionCorrectiva,
            auth()->id(),
            (bool) $datos['acciones_implementadas'],
            (bool) $datos['evidencias_completas'],
            (bool) $datos['implementacion_conforme'],
            $datos['resultado'] ?? null,
            $datos['observaciones'] ?? null
        );

        return redirect()
            ->route('acciones-correctivas.show', $accionCorrectiva)
            ->with(
                'success',
                'La verificación de cierre fue registrada correctamente.'
            );
    }

    public function iniciarEsperaEficacia(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        IniciarEsperaEficacia $iniciarEsperaEficacia
    ) {
        $datos = $request->validate([
            'responsable_id' => ['required', 'integer', 'exists:users,id'],
            'dias_espera' => ['required', 'integer', 'min:1'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $iniciarEsperaEficacia->ejecutar(
            $accionCorrectiva,
            (int) $datos['responsable_id'],
            (int) $datos['dias_espera'],
            $datos['observaciones'] ?? null
        );

        return redirect()
            ->route('acciones-correctivas.show', $accionCorrectiva)
            ->with('success', 'La espera de eficacia fue iniciada correctamente.');
    }

    public function registrarVerificacionEficacia(
        Request $request,
        AccionCorrectiva $accionCorrectiva,
        RegistrarVerificacionEficacia $registrarVerificacionEficacia
    ) {
        $datos = $request->validate([
            'criterios_cumplidos' => ['required', 'boolean'],
            'resultado_eficaz' => ['required', 'boolean'],
            'resultado' => ['required', 'string', 'max:2000'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]);

        $registrarVerificacionEficacia->ejecutar(
            $accionCorrectiva,
            auth()->id(),
            (bool) $datos['criterios_cumplidos'],
            (bool) $datos['resultado_eficaz'],
            $datos['resultado'],
            $datos['observaciones'] ?? null
        );

        return redirect()
            ->route('acciones-correctivas.show', $accionCorrectiva)
            ->with(
                'success',
                'La verificación de eficacia fue registrada correctamente.'
            );
    }
}
