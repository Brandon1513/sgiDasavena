<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SolicitudFormato;
use Illuminate\Support\Facades\Log;
use App\Mail\NuevaSolicitudMailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\DivulgacionFormatoMailable;
use App\Mail\SolicitudAprobadaSgiMailable;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use Illuminate\Support\Facades\DB;
use App\Models\DocumentoRevision;




class SolicitudFormatoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        logger('Usuario autenticado:', ['id' => $user->id, 'name' => $user->name, 'roles' => $user->getRoleNames()]);

        $coleccionFinal = collect();

        // Jefe
        if ($user->hasRole('jefe')) {
            $jefeSolicitudes = SolicitudFormato::where(function ($query) use ($user) {
                $query->where('jefe_id', $user->id)
                    ->orWhere('user_id', $user->id);
            })
                ->whereIn('estado', ['atendido', 'pendiente', 'aprobado_jefe', 'rechazado_jefe'])
                ->with('usuario')
                ->get();

            $coleccionFinal = $coleccionFinal->merge($jefeSolicitudes);
        }

        // Usuario
        if ($user->hasRole('usuario')) {
            $usuarioSolicitudes = SolicitudFormato::where('user_id', $user->id)
                ->with('usuario')
                ->get();

            $coleccionFinal = $coleccionFinal->merge($usuarioSolicitudes);
        }

        // Administrador SGI
        if ($user->hasRole('administrador_sgi')) {
            $sgiSolicitudes = SolicitudFormato::whereIn('estado', ['aprobado_jefe', 'atendido', 'rechazado_sgi'])
                ->with('usuario')
                ->get();

            $coleccionFinal = $coleccionFinal->merge($sgiSolicitudes);
        }

        // Eliminar duplicados
        $coleccionFinal = $coleccionFinal->unique('id')->sortByDesc('created_at');

        // Filtros
        $nombre = request('nombre');
        $estado = request('estado');
        $desde  = request('desde');
        $hasta  = request('hasta');

        $coleccionFinal = $coleccionFinal->filter(function ($item) use ($nombre, $estado, $desde, $hasta) {
            if ($nombre && !str_contains(strtolower(optional($item->usuario)->name), strtolower($nombre))) return false;
            if ($estado && $item->estado !== $estado) return false;
            if ($desde && $item->created_at->lt($desde)) return false;
            if ($hasta && $item->created_at->gt($hasta)) return false;
            return true;
        });

        // Paginación manual
        $page = request()->get('page', 1);
        $perPage = 10;
        $items = $coleccionFinal->slice(($page - 1) * $perPage, $perPage)->values();

        $solicitudes = new LengthAwarePaginator($items, $coleccionFinal->count(), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('solicitudes.index', compact('solicitudes'));
    }

    public function create()
    {
        $user = auth()->user();

        $q = Documento::query()
            ->where('estatus', 'vigente')
            ->orderBy('codigo');

        // ✅ SGI/Administrador ven todos
        if ($user->hasRole('administrador_sgi') || $user->hasRole('administrador')) {
            $documentos = $q->get(['id', 'codigo', 'nombre', 'tipo_documento', 'formato_el_pa', 'area']);
            return view('solicitudes.create', compact('documentos'));
        }

        // ✅ Usuario/Jefe: filtra por área
        if (!empty($user->area)) {
            $q->where('area', $user->area);
        }

        $documentos = $q->get(['id', 'codigo', 'nombre', 'tipo_documento', 'formato_el_pa', 'area']);

        return view('solicitudes.create', compact('documentos'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'accion' => 'required|in:actualizacion,baja,nuevo_documento',
            'documento_id' => 'required_if:accion,actualizacion|exists:documentos,id',

            // ✅ más robusto (y tamaño)
            'archivo' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xlsx,xls',

            'comentarios' => 'required|string|max:2000',
            'nombre_documento' => 'required_if:accion,nuevo_documento|nullable|string|max:255',
            'motivo_baja' => 'required_if:accion,baja|nullable|string|max:2000',
        ]);

        $user = auth()->user();

        $archivoPath = null;
        if ($request->hasFile('archivo')) {
            $archivoPath = $request->file('archivo')->store('solicitudes', 'public');
        }

        $doc = null;
        if ($request->accion === 'actualizacion') {
            $doc = \App\Models\Documento::findOrFail($request->documento_id);

            if (($user->hasRole('usuario') || $user->hasRole('jefe')) && $doc->area !== $user->area) {
                abort(403, 'No puedes solicitar actualización de un documento fuera de tu área.');
            }
        }

        $solicitud = \App\Models\SolicitudFormato::create([
            'user_id' => $user->id,
            'accion' => $request->accion,
            'archivo_adjunto' => $archivoPath,
            'estado' => 'pendiente',
            'jefe_id' => $user->jefe_id,
            'comentarios' => $request->comentarios,

            // ✅ documento ligado SOLO en actualización
            'documento_id' => $request->accion === 'actualizacion' ? $request->documento_id : null,

            // ✅ nombre_documento UNA sola vez (sin duplicar key)
            'nombre_documento' => $request->accion === 'actualizacion'
                ? ($doc?->nombre)
                : $request->nombre_documento,

            // baja
            'motivo_baja' => $request->motivo_baja,

            // ✅ snapshot (si es actualización)
            'codigo_documento' => $doc?->codigo,
            'tipo_documento' => $doc?->tipo_documento,
            'formato_el_pa' => $doc?->formato_el_pa,

            // ⚠️ IMPORTANT: solo guarda esto si tu columna existe en solicitudes
            'lugar_almacenamiento' => $doc?->sharepoint_folder,
            'estatus_documento' => $doc?->estatus,
        ]);

        $jefe = $user->jefe;
        if ($jefe && $jefe->email) {
            \Mail::to($jefe->email)->send(new \App\Mail\NuevaSolicitudMailable($solicitud));
        }

        return redirect()
            ->route('solicitudes.show', $solicitud->id)
            ->with('success', 'Solicitud enviada correctamente.');
    }

    public function show(SolicitudFormato $solicitud)
    {
        $solicitud->load(['usuario', 'jefe', 'administrador_sgi', 'documento']);
        return view('solicitudes.show', compact('solicitud'));
    }

    public function approvalForm(SolicitudFormato $solicitud)
    {
        return view('solicitudes.approval_form', compact('solicitud'));
    }

    public function approveOrReject(Request $request, SolicitudFormato $solicitud)
    {
        $user = auth()->user();

        if (!$user->hasRole('jefe')) {
            abort(403, 'No tienes permiso para aprobar o rechazar esta solicitud.');
        }

        if ($solicitud->user_id === $user->id) {
            return redirect()->route('solicitudes.index')->withErrors('No puedes aprobar o rechazar tus propias solicitudes.');
        }

        if ($solicitud->jefe_id !== $user->id) {
            abort(403, 'No estás asignado como jefe de esta solicitud.');
        }

        $request->validate([
            'decision' => 'required|in:aprobado_jefe,rechazado_jefe',
            'observaciones_jefe' => 'nullable|string|max:1000',
        ]);

        $solicitud->update([
            'estado' => $request->decision,
            'observaciones_jefe' => $request->observaciones_jefe,
        ]);

        if ($request->decision === 'aprobado_jefe') {
            $administradores = User::role('administrador_sgi')->get();
            foreach ($administradores as $admin) {
                Mail::to($admin->email)->send(new SolicitudAprobadaSgiMailable($solicitud));
            }
        }

        return redirect()->route('solicitudes.index')->with('success', 'Decisión registrada correctamente.');
    }

    public function finalizeForm(SolicitudFormato $solicitud)
    {
        $usuarios = User::where('activo', 1)->get();
        return view('solicitudes.finalize_form', compact('solicitud', 'usuarios'));
    }

    public function finalize(Request $request, SolicitudFormato $solicitud)
    {
        if (!auth()->user()->hasRole('administrador_sgi')) {
            abort(403, 'No tienes permiso para finalizar.');
        }

        $tipoCambio = $request->input('tipo_cambio', 'revision'); // version | revision

        $request->validate([
            'accion' => 'required|in:atender,rechazar',
            'tipo_cambio' => 'nullable|in:version,revision',

            'liga_archivo' => 'required|url',
            'fecha_alta_sgi' => 'required|date',
            'observaciones_sgi' => 'nullable|string|max:1000',

            'codigo_documento' => 'nullable|string|max:100',
            'nombre_documento' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:100',
            'formato_el_pa' => 'nullable|string|max:20',
            'folio_version' => 'nullable|string|max:50',
            'lugar_almacenamiento' => 'nullable|string|max:255',

            'fecha_version' => 'required_if:accion,atender|nullable|date',
            'vigencia_version_dias' => 'required_if:accion,atender|nullable|integer|min:1',

            'revision_actual' => 'nullable|string|max:50',
            'revision_anterior' => 'nullable|string|max:50',
            'fecha_revision' => 'nullable|date',
            'vigencia_revision_dias' => 'nullable|integer|min:1',
        ]);

        $estado = $request->accion === 'atender' ? 'atendido' : 'rechazado_sgi';

        if ($estado === 'atendido' && $tipoCambio === 'revision') {
            if (!$request->filled('revision_actual')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'revision_actual' => 'La revisión actual es obligatoria cuando el tipo de cambio es revisión.',
                ]);
            }

            if (!$request->filled('vigencia_revision_dias')) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'vigencia_revision_dias' => 'La vigencia de revisión es obligatoria cuando el tipo de cambio es revisión.',
                ]);
            }
        }

        $data = [
            'estado' => $estado,

            // si solo cambia versión, conservar revisión previa de la solicitud
            'revision_actual' => $tipoCambio === 'revision'
                ? $request->revision_actual
                : $solicitud->revision_actual,

            'revision_anterior' => $tipoCambio === 'revision'
                ? $request->revision_anterior
                : $solicitud->revision_anterior,

            'liga_archivo' => $request->liga_archivo,
            'fecha_alta_sgi' => $request->fecha_alta_sgi,
            'observaciones_sgi' => $request->observaciones_sgi,
            'administrador_sgi_id' => auth()->id(),

            'codigo_documento' => $request->codigo_documento ?? $solicitud->codigo_documento,
            'nombre_documento' => $request->nombre_documento ?? $solicitud->nombre_documento,
            'tipo_documento' => $request->tipo_documento ?? $solicitud->tipo_documento,
            'formato_el_pa' => $request->formato_el_pa ?? $solicitud->formato_el_pa,
            'folio_version' => $request->folio_version ?? $solicitud->folio_version,
            'lugar_almacenamiento' => $request->lugar_almacenamiento ?? $solicitud->lugar_almacenamiento,

            'fecha_version' => $request->fecha_version,
            'vigencia_version_dias' => $request->vigencia_version_dias,

            // si solo cambia versión, conservar revisión previa
            'fecha_revision' => $tipoCambio === 'revision'
                ? $request->fecha_revision
                : $solicitud->fecha_revision,

            'vigencia_revision_dias' => $tipoCambio === 'revision'
                ? $request->vigencia_revision_dias
                : $solicitud->vigencia_revision_dias,
        ];

        if ($estado === 'atendido') {
            $fechaVersion = $request->fecha_version
                ? Carbon::parse($request->fecha_version)->startOfDay()
                : null;

            $data['fecha_version'] = $fechaVersion?->toDateString();
            $data['fecha_vencimiento_version'] = $fechaVersion
                ? $fechaVersion->copy()->addDays((int)$request->vigencia_version_dias)->toDateString()
                : null;

            if ($tipoCambio === 'revision') {
                $fechaRevision = $request->fecha_revision
                    ? Carbon::parse($request->fecha_revision)->startOfDay()
                    : $fechaVersion;

                $data['fecha_revision'] = $fechaRevision?->toDateString();
                $data['fecha_vencimiento_revision'] = $fechaRevision
                    ? $fechaRevision->copy()->addDays((int)$request->vigencia_revision_dias)->toDateString()
                    : null;
            } else {
                // solo versión: conservar vencimiento de revisión previo
                $data['fecha_revision'] = $solicitud->fecha_revision;
                $data['fecha_vencimiento_revision'] = $solicitud->fecha_vencimiento_revision;
            }
        }

        DB::transaction(function () use ($solicitud, $estado, $data, $tipoCambio) {

            // 1) Guardar solicitud
            $solicitud->update($data);

            // 2) Si se rechaza, terminar
            if ($estado !== 'atendido') {
                return;
            }

            // 3) Código obligatorio
            $codigo = $solicitud->codigo_documento;
            if (!$codigo) {
                throw new \RuntimeException('Falta codigo_documento para publicar en Documentos.');
            }

            // 4) Documento
            $doc = Documento::firstOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $solicitud->nombre_documento,
                    'tipo_documento' => $solicitud->tipo_documento,
                    'formato_el_pa' => $solicitud->formato_el_pa,
                    'area' => optional($solicitud->usuario)->area,
                    'estatus' => 'vigente',
                ]
            );

            $doc->fill([
                'nombre' => $solicitud->nombre_documento ?? $doc->nombre,
                'tipo_documento' => $solicitud->tipo_documento ?? $doc->tipo_documento,
                'formato_el_pa' => $solicitud->formato_el_pa ?? $doc->formato_el_pa,
                'area' => optional($solicitud->usuario)->area ?? $doc->area,
            ])->save();

            $vigenteAnterior = $doc->versionVigente;

            // 5) Baja
            if ($solicitud->accion === 'baja') {
                $doc->estatus = 'baja';
                $doc->save();

                DocumentoVersion::where('documento_id', $doc->id)
                    ->where('estatus', 'vigente')
                    ->update(['estatus' => 'obsoleto']);

                $doc->version_vigente_id = null;
                $doc->save();

                return;
            }

            // 6) Obsoletar todas las versiones vigentes del documento
            DocumentoVersion::where('documento_id', $doc->id)
                ->where('estatus', 'vigente')
                ->update(['estatus' => 'obsoleto']);

            // 7) Solo si cambia revisión, obsoletar revisiones vigentes anteriores
            if ($tipoCambio === 'revision') {
                DocumentoRevision::whereIn(
                    'documento_version_id',
                    DocumentoVersion::where('documento_id', $doc->id)->pluck('id')
                )
                    ->where('estatus', 'vigente')
                    ->update(['estatus' => 'obsoleta']);
            }

            // 8) Resolver datos de revisión para guardar en documento_versiones
            $revisionActualFinal = $tipoCambio === 'revision'
                ? $solicitud->revision_actual
                : ($vigenteAnterior?->revision_actual);

            $revisionAnteriorFinal = $tipoCambio === 'revision'
                ? $solicitud->revision_anterior
                : ($vigenteAnterior?->revision_anterior);

            $fechaRevisionFinal = $tipoCambio === 'revision'
                ? $solicitud->fecha_revision
                : ($vigenteAnterior?->fecha_revision);

            $vigenciaRevisionDiasFinal = $tipoCambio === 'revision'
                ? $solicitud->vigencia_revision_dias
                : ($vigenteAnterior?->vigencia_revision_dias);

            $fechaVencimientoRevisionFinal = $tipoCambio === 'revision'
                ? $solicitud->fecha_vencimiento_revision
                : ($vigenteAnterior?->fecha_vencimiento_revision);

            // 9) Crear nueva versión vigente
            $ver = DocumentoVersion::create([
                'documento_id' => $doc->id,
                'version' => $solicitud->folio_version ?: ('AUTO-' . now()->format('Ymd-His')),

                'revision_actual' => $revisionActualFinal,
                'revision_anterior' => $revisionAnteriorFinal,

                'liga_archivo' => $solicitud->liga_archivo,
                'lugar_almacenamiento' => $solicitud->lugar_almacenamiento,

                'fecha_version' => $solicitud->fecha_version,
                'vigencia_version_dias' => $solicitud->vigencia_version_dias,
                'fecha_vencimiento_version' => $solicitud->fecha_vencimiento_version,

                'fecha_revision' => $fechaRevisionFinal,
                'vigencia_revision_dias' => $vigenciaRevisionDiasFinal,
                'fecha_vencimiento_revision' => $fechaVencimientoRevisionFinal,

                'estatus' => 'vigente',
                'publicado_por' => auth()->id(),
                'publicado_en' => now(),

                'observaciones_sgi' => $solicitud->observaciones_sgi,
            ]);

            // 10) Crear revisión REAL solo si el tipo de cambio fue revisión
            if ($tipoCambio === 'revision' && $revisionActualFinal) {
                DocumentoRevision::create([
                    'documento_version_id' => $ver->id,

                    'revision_actual' => $revisionActualFinal,
                    'revision_anterior' => $revisionAnteriorFinal,

                    'fecha_revision' => $fechaRevisionFinal,
                    'vigencia_revision_dias' => $vigenciaRevisionDiasFinal ? (int)$vigenciaRevisionDiasFinal : null,
                    'fecha_vencimiento_revision' => $fechaVencimientoRevisionFinal,

                    'liga_archivo' => $solicitud->liga_archivo,
                    'lugar_almacenamiento' => $solicitud->lugar_almacenamiento,

                    'estatus' => 'vigente',
                    'registrado_por' => auth()->id(),
                    'registrado_en' => now(),
                ]);
            }

            // 11) Marcar documento con su nueva versión vigente
            $doc->version_vigente_id = $ver->id;
            $doc->estatus = 'vigente';
            $doc->save();
        });

        if ($request->has('usuarios_notificados')) {
            $usuarios = User::whereIn('id', $request->usuarios_notificados)->get();
            foreach ($usuarios as $usuario) {
                Mail::to($usuario->email)->send(new DivulgacionFormatoMailable($solicitud));
            }
        }

        Log::info('Solicitud finalizada', [
            'solicitud_id' => $solicitud->id,
            'tipo_cambio' => $tipoCambio,
        ]);

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud finalizada correctamente.');
    }

    public function solicitarActualizacionForm(Request $request)
    {
        $user = auth()->user();

        $documentos = Documento::query()
            ->where('area', $user->area)
            ->where('estatus', 'vigente')
            ->orderBy('codigo')
            ->get();

        $documentoSeleccionado = $request->integer('documento'); // ?documento=ID

        return view('solicitudes.solicitar_actualizacion', compact('documentos', 'documentoSeleccionado'));
    }


    public function solicitarActualizacionStore(Request $request)
    {
        $request->validate([
            'documento_id' => 'required|exists:documentos,id',
            'archivo'      => 'required|file|mimes:pdf,doc,docx,xlsx,xls',
            'comentarios'  => 'required|string|max:2000',
        ]);

        $user = auth()->user();
        $doc  = Documento::findOrFail($request->documento_id);


        if (($user->hasRole('usuario') || $user->hasRole('jefe')) && $doc->area !== $user->area) {
            abort(403, 'No puedes solicitar actualización de un documento fuera de tu departamento.');
        }

        $archivoPath = $request->file('archivo')->store('solicitudes', 'public');

        $solicitud = SolicitudFormato::create([
            'documento_id'    => $doc->id,
            'user_id'         => $user->id,
            'accion'          => 'actualizacion',
            'archivo_adjunto' => $archivoPath,
            'comentarios'     => $request->comentarios,
            'estado'          => 'pendiente',
            'jefe_id'         => $user->jefe_id,


            'codigo_documento' => $doc->codigo,
            'nombre_documento' => $doc->nombre,
            'tipo_documento'   => $doc->tipo_documento,
            'formato_el_pa'    => $doc->formato_el_pa,
            'lugar_almacenamiento' => $doc->sharepoint_folder,
            'estatus_documento' => $doc->estatus,
        ]);

        return redirect()
            ->route('solicitudes.show', $solicitud->id)
            ->with('success', 'Solicitud de actualización enviada correctamente.');
    }
}
