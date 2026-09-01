<?php

namespace App\Http\Controllers;

use App\Domains\Incidencias\Models\AccionCorrectiva;
use App\Domains\Incidencias\Models\EstadoAccionCorrectiva;
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

        $estados = EstadoAccionCorrectiva::query()
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
}
