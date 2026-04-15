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

        // 🔥 ahora también obligatorio en BAJA
        'documento_id' => 'required_if:accion,actualizacion,baja|exists:documentos,id',

        'archivo' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xlsx,xls',

        'comentarios' => 'required|string|max:2000',
        'nombre_documento' => 'required_if:accion,nuevo_documento|nullable|string|max:255',
        'motivo_baja' => 'required_if:accion,baja|nullable|string|max:2000',
    ]);

    $user = auth()->user();

    // 📁 archivo
    $archivoPath = null;
    if ($request->hasFile('archivo')) {
        $archivoPath = $request->file('archivo')->store('solicitudes', 'public');
    }

    // 🔥 cargar documento para actualización Y baja
    $doc = null;
    if (in_array($request->accion, ['actualizacion', 'baja'])) {

        $doc = \App\Models\Documento::findOrFail($request->documento_id);

        // 🔒 validación por área
        if (($user->hasRole('usuario') || $user->hasRole('jefe')) && $doc->area !== $user->area) {
            abort(403, 'No puedes solicitar acción sobre un documento fuera de tu área.');
        }
    }

    // 🧾 crear solicitud
    $solicitud = \App\Models\SolicitudFormato::create([
        'user_id' => $user->id,
        'accion' => $request->accion,
        'archivo_adjunto' => $archivoPath,
        'estado' => 'pendiente',
        'jefe_id' => $user->jefe_id,
        'comentarios' => $request->comentarios,

        // 🔥 ahora sí se guarda también en BAJA
        'documento_id' => in_array($request->accion, ['actualizacion', 'baja'])
            ? $request->documento_id
            : null,

        // 🔥 también corregido para BAJA
        'nombre_documento' => in_array($request->accion, ['actualizacion', 'baja'])
            ? ($doc?->nombre)
            : $request->nombre_documento,

        // baja
        'motivo_baja' => $request->motivo_baja,

        // 📌 snapshot del documento (muy importante)
        'codigo_documento' => $doc?->codigo,
        'tipo_documento' => $doc?->tipo_documento,
        'formato_el_pa' => $doc?->formato_el_pa,
        'lugar_almacenamiento' => $doc?->sharepoint_folder,
        'estatus_documento' => $doc?->estatus,
    ]);

    // 📧 notificar jefe
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
        abort(403);
    }

    $tipoCambio = $request->input('tipo_cambio', 'revision');

    // Validación condicional (Solo si es Atender)
    if ($request->accion === 'atender') {
        $rules = [
            'liga_archivo' => 'required|url',
            'fecha_alta_sgi' => 'required|date',
            'codigo_documento' => 'required|string',
            'fecha_version' => 'required|date',
            'vigencia_version_dias' => 'required|integer|min:1',
        ];

        if ($tipoCambio === 'revision') {
            $rules['revision_actual'] = 'required|string';
            $rules['vigencia_revision_dias'] = 'required|integer|min:1';
        }

        $request->validate($rules);
    }

    DB::transaction(function () use ($solicitud, $request, $tipoCambio) {
        $estado = $request->accion === 'atender' ? 'atendido' : 'rechazado_sgi';
        
        // 1. Preparar fechas
        $fechaV = Carbon::parse($request->fecha_version);
        $vencimientoV = $fechaV->copy()->addDays((int)$request->vigencia_version_dias);

        // 2. Buscar o crear el documento principal
       // 🔥 CASO BAJA (NO crear documento)
if ($solicitud->accion === 'baja') {

    if (!$solicitud->documento_id) {
        throw new \Exception('La solicitud de baja no tiene documento asociado');
    }

    $doc = Documento::findOrFail($solicitud->documento_id);

} else {

    // ✅ SOLO crear documento en alta/actualización
    $codigo = $request->codigo_documento ?? $solicitud->codigo_documento;

    if (!$codigo) {
        throw new \Exception('El código del documento es obligatorio');
    }

    $doc = Documento::firstOrCreate(
        ['codigo' => $codigo],
        [
            'nombre' => $request->nombre_documento ?? $solicitud->nombre_documento,
            'area' => optional($solicitud->usuario)->area,
            'estatus' => 'vigente'
        ]
    );
}

        $vigenteAnterior = $doc->versionVigente;

        // 3. Lógica de Revisiones (Herencia o Nueva)
        if ($tipoCambio === 'revision') {
            $fRev = $request->fecha_revision ? Carbon::parse($request->fecha_revision) : $fechaV;
            $revActual = $request->revision_actual;
            $revAnterior = $request->revision_anterior;
            $vigenciaR = $request->vigencia_revision_dias;
            $vencimientoR = $fRev->copy()->addDays((int)$vigenciaR);
        } else {
            // Heredar del documento real si ya existe, si no, usar los de la solicitud
            $revActual = $vigenteAnterior ? $vigenteAnterior->revision_actual : $solicitud->revision_actual;
            $revAnterior = $vigenteAnterior ? $vigenteAnterior->revision_anterior : $solicitud->revision_anterior;
            $fRev = $vigenteAnterior ? $vigenteAnterior->fecha_revision : $solicitud->fecha_revision;
            $vencimientoR = $vigenteAnterior ? $vigenteAnterior->fecha_vencimiento_revision : $solicitud->fecha_vencimiento_revision;
            $vigenciaR = $vigenteAnterior ? $vigenteAnterior->vigencia_revision_dias : $solicitud->vigencia_revision_dias;
        }

        // 4. Actualizar Solicitud
        $solicitud->update([
            'estado' => $estado,
            'revision_actual' => $revActual,
            'revision_anterior' => $revAnterior,
            'fecha_version' => $fechaV,
            'fecha_vencimiento_version' => $vencimientoV,
            'fecha_revision' => $fRev,
            'fecha_vencimiento_revision' => $vencimientoR,
            'administrador_sgi_id' => auth()->id(),
            'liga_archivo' => $request->liga_archivo,
        ]);

        if ($estado !== 'atendido') return;

        // 5. Crear Versión
        $nuevaVersion = DocumentoVersion::create([
            'documento_id' => $doc->id,
            'version' => $request->folio_version ?? ('VER-' . now()->format('Ymd')),
            'revision_actual' => $revActual,
            'revision_anterior' => $revAnterior,
            'fecha_version' => $fechaV,
            'fecha_vencimiento_version' => $vencimientoV,
            'fecha_revision' => $fRev,
            'fecha_vencimiento_revision' => $vencimientoR,
            'estatus' => 'vigente',
            'liga_archivo' => $request->liga_archivo,
            'publicado_por' => auth()->id(),
        ]);

        // 6. Si es cambio de revisión, crear el registro en el historial de revisiones
        if ($tipoCambio === 'revision') {
            DocumentoRevision::create([
                'documento_version_id' => $nuevaVersion->id,
                'revision_actual' => $revActual,
                'fecha_revision' => $fRev,
                'estatus' => 'vigente',
                'registrado_por' => auth()->id(),
            ]);
            
            // Obsoletar revisiones pasadas
            DocumentoRevision::whereIn('documento_version_id', $doc->versiones->pluck('id'))
                ->where('id', '!=', $nuevaVersion->id)
                ->update(['estatus' => 'obsoleta']);
        }

        // 7. Actualizar Documento
        $doc->versiones()->where('id', '!=', $nuevaVersion->id)->update(['estatus' => 'obsoleto']);
        $doc->update([
            'version_vigente_id' => $nuevaVersion->id,
            'estatus' => ($solicitud->accion === 'baja') ? 'baja' : 'vigente'
        ]);
    });

    return redirect()->route('solicitudes.index')->with('success', 'Proceso completado.');
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
