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

        // ✅ actualización requiere documento_id (más limpio sin nullable)
        'documento_id' => 'required_if:accion,actualizacion|exists:documentos,id',

        'archivo' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls',
        'comentarios' => 'required|string|max:2000',

        'nombre_documento' => 'required_if:accion,nuevo_documento|nullable|string|max:255',
        'motivo_baja' => 'required_if:accion,baja|nullable|string|max:2000',
    ]);

    $user = auth()->user();

    $archivoPath = $request->hasFile('archivo')
        ? $request->file('archivo')->store('solicitudes', 'public')
        : null;

    // =========================
    // ✅ Si es actualización: cargar doc + seguridad + snapshot
    // =========================
    $doc = null;
    if ($request->accion === 'actualizacion') {
        $doc = \App\Models\Documento::findOrFail($request->documento_id);

        // Seguridad: por área (como acordamos)
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

        // para baja/nuevo_documento
        'nombre_documento' => $request->nombre_documento,
        'motivo_baja' => $request->motivo_baja,

        // ✅ guarda SIEMPRE el id validado cuando sea actualización
        'documento_id' => $request->accion === 'actualizacion' ? $request->documento_id : null,

        // ✅ snapshot
        'codigo_documento' => $doc?->codigo,
        'nombre_documento' => $doc?->nombre ?? $request->nombre_documento,
        'tipo_documento' => $doc?->tipo_documento,
        'formato_el_pa' => $doc?->formato_el_pa,
        'lugar_almacenamiento' => $doc?->sharepoint_folder, // ajusta si tu campo se llama diferente
        'estatus_documento' => $doc?->estatus,
    ]);

    // correo al jefe si existe
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

        $request->validate([
            'revision_actual' => 'required|string|max:50',
            'revision_anterior' => 'nullable|string|max:50',
            'liga_archivo' => 'required|url',
            'fecha_alta_sgi' => 'required|date',
            'observaciones_sgi' => 'nullable|string|max:1000',
            'accion' => 'required|in:atender,rechazar',

            'codigo_documento' => 'nullable|string|max:100',
            'nombre_documento' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:100',
            'formato_el_pa' => 'nullable|string|max:20',
            'folio_version' => 'nullable|string|max:50',
            'lugar_almacenamiento' => 'nullable|string|max:255',

            'fecha_version' => 'required_if:accion,atender|nullable|date',
            // ✅ NO la hagas required, porque tú haces fallback
            'fecha_revision' => 'nullable|date',

            'vigencia_version_dias' => 'required_if:accion,atender|nullable|integer|min:1',
            'vigencia_revision_dias' => 'required_if:accion,atender|nullable|integer|min:1',
        ]);

        $estado = $request->accion === 'atender' ? 'atendido' : 'rechazado_sgi';

        $data = [
            'estado' => $estado,
            'revision_actual' => $request->revision_actual,
            'revision_anterior' => $request->revision_anterior,
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
            'fecha_revision' => $request->fecha_revision,
            'vigencia_version_dias' => $request->vigencia_version_dias,
            'vigencia_revision_dias' => $request->vigencia_revision_dias,
        ];

        // ✅ cálculo correcto + guarda fecha_revision con fallback
        if ($estado === 'atendido') {
            $fechaVersion = $request->fecha_version ? Carbon::parse($request->fecha_version)->startOfDay() : null;
            $fechaRevision = $request->fecha_revision
                ? Carbon::parse($request->fecha_revision)->startOfDay()
                : $fechaVersion;

            $data['fecha_version']  = $fechaVersion?->toDateString();
            $data['fecha_revision'] = $fechaRevision?->toDateString();

            $data['fecha_vencimiento_version'] = $fechaVersion
                ? $fechaVersion->copy()->addDays((int)$request->vigencia_version_dias)->toDateString()
                : null;

            $data['fecha_vencimiento_revision'] = $fechaRevision
                ? $fechaRevision->copy()->addDays((int)$request->vigencia_revision_dias)->toDateString()
                : null;
        }

        DB::transaction(function () use ($solicitud, $estado, $data) {

            // 1) ✅ siempre guarda solicitud
            $solicitud->update($data);

            // 2) si se rechaza, termina aquí
            if ($estado !== 'atendido') {
                return;
            }

            // 3) código obligatorio
            $codigo = $solicitud->codigo_documento;
            if (!$codigo) {
                throw new \RuntimeException('Falta codigo_documento para publicar en Documentos.');
            }

            // 4) Documento identidad
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

            // 5) baja
            if ($solicitud->accion === 'baja') {
                $doc->estatus = 'baja';
                $doc->save();

                if ($doc->versionVigente) {
                    $doc->versionVigente->update(['estatus' => 'obsoleto']);
                    $doc->version_vigente_id = null;
                    $doc->save();
                }
                return;
            }

            // 6) actualización: cerrar vigente anterior
            if ($solicitud->accion === 'actualizacion' && $doc->versionVigente) {
                $doc->versionVigente->update(['estatus' => 'obsoleto']);
            }

            // 7) ✅ crear UNA sola versión vigente (con revisión ya calculada)
            $ver = DocumentoVersion::create([
                'documento_id' => $doc->id,
                'version' => $solicitud->folio_version ?: ('AUTO-' . now()->format('Ymd-His')),

                'revision_actual' => $solicitud->revision_actual,
                'revision_anterior' => $solicitud->revision_anterior,

                'liga_archivo' => $solicitud->liga_archivo,
                'lugar_almacenamiento' => $solicitud->lugar_almacenamiento,

                'fecha_version' => $solicitud->fecha_version,
                'vigencia_version_dias' => $solicitud->vigencia_version_dias,
                'fecha_vencimiento_version' => $solicitud->fecha_vencimiento_version,

                'fecha_revision' => $solicitud->fecha_revision,
                'vigencia_revision_dias' => $solicitud->vigencia_revision_dias,
                'fecha_vencimiento_revision' => $solicitud->fecha_vencimiento_revision,

                'estatus' => 'vigente',
                'publicado_por' => auth()->id(),
                'publicado_en' => now(),

                // si ya agregaste la columna en documento_versiones
                'observaciones_sgi' => $solicitud->observaciones_sgi,
            ]);

            $doc->version_vigente_id = $ver->id;
            $doc->estatus = 'vigente';
            $doc->save();
        });

        // correos...
        if ($request->has('usuarios_notificados')) {
            $usuarios = User::whereIn('id', $request->usuarios_notificados)->get();
            foreach ($usuarios as $usuario) {
                Mail::to($usuario->email)->send(new DivulgacionFormatoMailable($solicitud));
            }
        }

        Log::info('Solicitud finalizada', ['solicitud_id' => $solicitud->id]);

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

        // Seguridad: solo documentos de su área (usuario/jefe)
        if (($user->hasRole('usuario') || $user->hasRole('jefe')) && $doc->area !== $user->area) {
            abort(403, 'No puedes solicitar actualización de un documento fuera de tu departamento.');
        }

        $archivoPath = $request->file('archivo')->store('solicitudes', 'public');

        $solicitud = SolicitudFormato::create([
            'documento_id'    => $doc->id,   // ✅ lo importante
            'user_id'         => $user->id,
            'accion'          => 'actualizacion',
            'archivo_adjunto' => $archivoPath,
            'comentarios'     => $request->comentarios,
            'estado'          => 'pendiente',
            'jefe_id'         => $user->jefe_id,

            // snapshot (opcional pero útil)
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
