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
use App\Models\TipoDocumento;
use App\Notifications\SolicitudNotificacion;




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

        // ✅ Usuario/Jefe: filtra por área (sin área asignada no ve ninguno)
        $q->where('area', $user->area);

        $documentos = $q->get(['id', 'codigo', 'nombre', 'tipo_documento', 'formato_el_pa', 'area']);

        return view('solicitudes.create', compact('documentos'));
    }


    public function store(Request $request)
    {
        $rules = [
    'accion' => 'required|in:actualizacion,baja,nuevo_documento',

    'archivo' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xlsx,xls',

    'comentarios' => 'required|string|max:2000',

    'nombre_documento' => 'required_if:accion,nuevo_documento|nullable|string|max:255',

    'motivo_baja' => 'required_if:accion,baja|nullable|string|max:2000',
];

// Solo validar documento_id cuando aplica
if (in_array($request->accion, ['actualizacion', 'baja'])) {
    $rules['documento_id'] = 'required|exists:documentos,id';
}

$request->validate($rules);


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
            if (!$user->hasRole('administrador_sgi') && $doc->area !== $user->area) {
                abort(403, 'No puedes solicitar acción sobre un documento fuera de tu área.');
            }
        }

        //  crear solicitud
        $solicitud = \App\Models\SolicitudFormato::create([
            'user_id' => $user->id,
            'accion' => $request->accion,
            'archivo_adjunto' => $archivoPath,
            'estado' => 'pendiente',
            'jefe_id' => $user->jefe_id,
            'comentarios' => $request->comentarios,

            // ahora sí se guarda también en BAJA
            'documento_id' => in_array($request->accion, ['actualizacion', 'baja'])
                ? $request->documento_id
                : null,

            
            'nombre_documento' => in_array($request->accion, ['actualizacion', 'baja'])
                ? ($doc?->nombre)
                : $request->nombre_documento,

            // baja
            'motivo_baja' => $request->motivo_baja,

            
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
        $jefe?->notify(new SolicitudNotificacion($solicitud, "{$user->name} envió una nueva solicitud que requiere tu aprobación."));

        return redirect()
            ->route('solicitudes.show', $solicitud->id)
            ->with('success', 'Solicitud enviada correctamente.');
    }

    public function show(SolicitudFormato $solicitud)
    {
        $user = auth()->user();

        $puedeVer = $solicitud->user_id === $user->id
            || $solicitud->jefe_id === $user->id
            || $user->hasRole('administrador_sgi')
            || $user->hasRole('administrador');

        if (!$puedeVer) {
            abort(403, 'No tienes permiso para ver esta solicitud.');
        }

        $solicitud->load(['usuario', 'jefe', 'administrador_sgi', 'documento',]);
        return view('solicitudes.show', compact('solicitud'));
    }

    public function approvalForm(SolicitudFormato $solicitud)
    {
        $user = auth()->user();

        if (!$user->hasRole('jefe')) {
            abort(403, 'No tienes permiso para aprobar o rechazar esta solicitud.');
        }

        if ($solicitud->user_id === $user->id && !$user->hasRole('administrador_sgi')) {
            abort(403, 'No puedes aprobar o rechazar tus propias solicitudes.');
        }

        if ($solicitud->jefe_id !== $user->id && !$user->hasRole('administrador_sgi')) {
            abort(403, 'No estás asignado como jefe de esta solicitud.');
        }

        return view('solicitudes.approval_form', compact('solicitud'));
    }

    public function approveOrReject(Request $request, SolicitudFormato $solicitud)
    {
        $user = auth()->user();

        if (!$user->hasRole('jefe')) {
            abort(403, 'No tienes permiso para aprobar o rechazar esta solicitud.');
        }

        if ($solicitud->user_id === $user->id && !$user->hasRole('administrador_sgi')) {
            return redirect()->route('solicitudes.index')
                ->withErrors('No puedes aprobar o rechazar tus propias solicitudes.');
        }
        if ($solicitud->jefe_id !== $user->id && !$user->hasRole('administrador_sgi')) {
            return redirect()->route('solicitudes.index')
                ->withErrors('No estás asignado como jefe de esta solicitud.');
        }

        if ($solicitud->estado !== 'pendiente') {
            return redirect()->route('solicitudes.index')
                ->withErrors('Esta solicitud ya fue atendida y no puede volver a aprobarse o rechazarse.');
        }

        $request->validate([
            'decision' => 'required|in:aprobado_jefe,rechazado_jefe',
            'observaciones_jefe' => 'nullable|string|max:1000',
        ]);

        $solicitud->update([
            'estado' => $request->decision,
            'observaciones_jefe' => $request->observaciones_jefe,
            'aprobado_jefe_at' => $request->decision === 'aprobado_jefe' ? now() : null,
        ]);

        if ($request->decision === 'aprobado_jefe') {
            $administradores = User::role('administrador_sgi')->get();
            foreach ($administradores as $admin) {
                Mail::to($admin->email)->send(new SolicitudAprobadaSgiMailable($solicitud));
                $admin->notify(new SolicitudNotificacion($solicitud, 'Una solicitud fue aprobada por su jefe y espera atención de SGI.'));
            }
        } else {
            $solicitud->usuario?->notify(new SolicitudNotificacion($solicitud, 'Tu jefe rechazó tu solicitud.'));
        }

        return redirect()->route('solicitudes.index')->with('success', 'Decisión registrada correctamente.');
    }


    public function finalizeForm(SolicitudFormato $solicitud)
    {
        if (!auth()->user()->hasRole('administrador_sgi')) {
            abort(403);
        }

        if ($solicitud->estado !== 'aprobado_jefe') {
            return redirect()->route('solicitudes.index')
                ->withErrors('Esta solicitud ya fue procesada por SGI.');
        }

        $usuarios = User::where('activo', 1)->get();
        $solicitud->load('documento.versionVigente', 'usuario');

        $tipoDocumento = $solicitud->tipo_documento ?? $solicitud->documento?->tipo_documento;
        $area = $solicitud->documento?->area ?? $solicitud->usuario?->area;
        $carpetaSugerida = null;

        try {
            $carpetaSugerida = app(\App\Actions\SharePoint\PublishDocumentoVersion::class)
                ->sugerirCarpetaVigente($area, $tipoDocumento);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('No se pudo calcular la ubicación sugerida en SharePoint.', [
                'solicitud_id' => $solicitud->id,
                'error' => $e->getMessage(),
            ]);
        }

        return view('solicitudes.finalize_form', compact('solicitud', 'usuarios', 'carpetaSugerida'));
    }

    public function finalize(Request $request, SolicitudFormato $solicitud)
    {
        if (!auth()->user()->hasRole('administrador_sgi')) {
            abort(403);
        }

        if ($solicitud->estado !== 'aprobado_jefe') {
            return back()->withErrors('Esta solicitud ya fue procesada por SGI y no puede volver a finalizarse.');
        }

        $tipoCambio = $request->input('tipo_cambio', 'revision');
        $accion = $request->input('accion'); // 'atender' o 'rechazar'

        // 1. Validación condicional (Solo si se va a ATENDER)
        // Las solicitudes de BAJA no capturan Datos Oficiales: solo decomisionan
        // el documento existente, no requieren fechas/vigencia/archivo nuevos.
        if ($accion === 'atender' && $solicitud->accion !== 'baja') {
            $rules = [
                'liga_archivo' => 'required|url',
                'fecha_alta_sgi' => 'required|date',
                'codigo_documento' => 'required|string',
                'fecha_version' => 'required|date',
                'vigencia_version_dias' => 'required|integer|min:1',
                'archivo_oficial' => 'nullable|file|max:102400|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            ];

            if ($tipoCambio === 'revision') {
                $rules['revision_actual'] = 'required|string';
                $rules['vigencia_revision_dias'] = 'required|integer|min:1';
            }

            $request->validate($rules);
        }

        // Archivo oficial de la versión que se publica en SharePoint: el que
        // suba aquí el administrador SGI tiene prioridad; si no sube nada, se
        // usa como respaldo el archivo que el solicitante ya había adjuntado.
        // liga_archivo se conserva solo como referencia externa y no se usa
        // para descargar/subir nada automáticamente.
        $archivoOficialPublic = null;

        if ($accion === 'atender' && $solicitud->accion !== 'baja') {
            if ($request->hasFile('archivo_oficial')) {
                $archivoOficialPublic = $request->file('archivo_oficial')->store('documentos-oficiales', 'public');
            } elseif ($solicitud->archivo_adjunto) {
                $archivoOficialPublic = $solicitud->archivo_adjunto;
            }
        }

        // Carpeta de SharePoint donde se publica: la sugerida automáticamente
        // por tipo de documento (precargada en el form), o la que el
        // administrador_sgi haya elegido a mano con el selector/navegador de
        // carpetas reales. Nunca es texto libre: siempre viene de ahí.
        $carpetaVigentePath = trim((string) $request->input('sp_carpeta_vigente_path'));

        if ($archivoOficialPublic && $carpetaVigentePath === '') {
            return back()
                ->withErrors(['sp_carpeta_vigente_path' => 'No se pudo sugerir una ubicación automática para este tipo de documento: selecciona una carpeta de SharePoint.'])
                ->withInput();
        }

        // El administrador decide explícitamente si se crea una subcarpeta
        // con el código del documento dentro de la ubicación elegida (patrón
        // {Área}/{Tipo}/{Código}), o si el archivo se sube suelto
        // directamente ahí (patrón {Área}/{Tipo}, como ya existe en SGI).
        $crearSubcarpetaCodigo = $request->boolean('sp_crear_subcarpeta_codigo', true);

        // Nombre de archivo en SharePoint: si el administrador no escribe
        // uno, se autogenera (código_V_R_fecha) conservando la extensión real.
        $nombreArchivoManual = trim((string) $request->input('sp_nombre_archivo')) ?: null;

        $nuevaVersionParaPublicar = null;
        $documentoParaPublicar = null;
        $versionAnteriorParaPublicar = null;

        try {
            DB::transaction(function () use ($solicitud, $request, $tipoCambio, $accion, &$nuevaVersionParaPublicar, &$documentoParaPublicar, &$versionAnteriorParaPublicar) {

                // Si la acción es RECHAZAR, solo actualizamos estatus y salimos de la transacción
                if ($accion !== 'atender') {
                    $solicitud->update([
                        'estado' => 'rechazado_sgi',
                        'administrador_sgi_id' => auth()->id(),
                    ]);
                    $solicitud->usuario?->notify(new SolicitudNotificacion($solicitud, 'SGI rechazó tu solicitud.'));
                    return; // Corta la ejecución de la transacción aquí
                }

                // --- DE AQUÍ EN ADELANTE SOLO SE EJECUTA SI ES "ATENDER" ---

                $estado = 'atendido';

                // --- BAJA: no captura Datos Oficiales, solo decomisiona el documento ---
                if ($solicitud->accion === 'baja') {
                    if (!$solicitud->documento_id) {
                        throw new \Exception('La solicitud de baja no tiene documento asociado');
                    }

                    $doc = Documento::with('versiones')->findOrFail($solicitud->documento_id);

                    $doc->versiones()->update(['estatus' => 'obsoleto']);
                    DocumentoRevision::whereIn('documento_version_id', $doc->versiones->pluck('id'))
                        ->update(['estatus' => 'obsoleta']);

                    $doc->update(['estatus' => 'baja']);

                    $solicitud->update([
                        'documento_id' => $doc->id,
                        'estado' => $estado,
                        'atendido_at' => now(),
                        'administrador_sgi_id' => auth()->id(),
                    ]);
                    $solicitud->usuario?->notify(new SolicitudNotificacion($solicitud, 'SGI dio de baja el documento de tu solicitud.'));

                    return;
                }

                // Preparar fechas
                $fechaV = Carbon::parse($request->fecha_version);
                $vencimientoV = $fechaV->copy()->addDays((int)$request->vigencia_version_dias);

                // 2. Buscar o crear el documento principal
                // Aquí usamos el código del request porque ya pasó la validación
                $codigo = $request->codigo_documento;

                $doc = Documento::firstOrCreate(
                    ['codigo' => $codigo],
                    [
                        'nombre' => $request->nombre_documento ?? $solicitud->nombre_documento,
                        'area' => optional($solicitud->usuario)->area,
                        'estatus' => 'vigente'
                    ]
                );

                $vigenteAnterior = $doc->versionVigente;
                $versionAnteriorParaPublicar = $vigenteAnterior;

                // 4. Lógica de Revisiones
                if ($tipoCambio === 'revision') {
                    $fRev = $request->fecha_revision ? Carbon::parse($request->fecha_revision) : $fechaV;
                    $revActual = $request->revision_actual;
                    $revAnterior = $request->revision_anterior;
                    $vigenciaR = $request->vigencia_revision_dias;
                    $vencimientoR = $fRev->copy()->addDays((int)$vigenciaR);
                } else {
                    if ($vigenteAnterior) {
                        $revActual    = $vigenteAnterior->revision_actual;
                        $revAnterior  = $vigenteAnterior->revision_anterior;
                        $fRev         = $vigenteAnterior->fecha_revision;
                        $vencimientoR = $vigenteAnterior->fecha_vencimiento_revision;
                    } else {
                        $revActual    = $solicitud->revision_actual ?? '0';
                        $revAnterior  = $solicitud->revision_anterior ?? '0';
                        $fRev         = $solicitud->fecha_revision ?? $fechaV;
                        $vencimientoR = $solicitud->fecha_vencimiento_revision ?? $vencimientoV;
                    }
                }

                // 5. Actualizar Solicitud
                $solicitud->update([
                    'documento_id' => $doc->id,
                    'estado' => $estado,
                    'atendido_at' => now(),
                    'revision_actual' => $revActual,
                    'revision_anterior' => $revAnterior,
                    'fecha_version' => $fechaV,
                    'fecha_vencimiento_version' => $vencimientoV,
                    'fecha_revision' => $fRev,
                    'fecha_vencimiento_revision' => $vencimientoR,
                    'administrador_sgi_id' => auth()->id(),
                    'liga_archivo' => $request->liga_archivo,
                ]);
                $solicitud->usuario?->notify(new SolicitudNotificacion($solicitud, 'SGI atendió tu solicitud y publicó el documento.'));

                // 6. Crear Nueva Versión
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

                // 7. Auditoría y 8. Maestro
                if ($tipoCambio === 'revision') {
                    DocumentoRevision::create([
                        'documento_version_id' => $nuevaVersion->id,
                        'revision_actual' => $revActual,
                        'revision_anterior' => $revAnterior,
                        'fecha_revision' => $fRev,
                        'vigencia_revision_dias' => $vigenciaR,
                        'fecha_vencimiento_revision' => $vencimientoR,
                        'liga_archivo' => $request->liga_archivo,
                        'estatus' => 'vigente',
                        'registrado_por' => auth()->id(),
                    ]);
                }

                $versionesViejasIds = $doc->versiones()->where('id', '!=', $nuevaVersion->id)->pluck('id');

                $doc->versiones()->whereIn('id', $versionesViejasIds)->update(['estatus' => 'obsoleto']);

                // Las revisiones de las versiones que acaban de quedar obsoletas
                // también se marcan como obsoletas: antes se quedaban "vigente"
                // colgando de una versión ya retirada.
                DocumentoRevision::whereIn('documento_version_id', $versionesViejasIds)
                    ->update(['estatus' => 'obsoleta']);

                $doc->update([
                    'version_vigente_id' => $nuevaVersion->id,
                    'estatus' => 'vigente',
                ]);

                $nuevaVersionParaPublicar = $nuevaVersion;
                $documentoParaPublicar = $doc;
            });

            if ($archivoOficialPublic && $nuevaVersionParaPublicar && $documentoParaPublicar) {
                // archivo_storage queda como referencia del archivo local
                // usado para publicar, por si hay que reintentar más tarde.
                // sp_folder_path ya trae la carpeta decidida (sugerida o
                // elegida a mano): el job/la acción ya no la calculan.
                $nuevaVersionParaPublicar->update([
                    'sp_estado' => 'pendiente',
                    'sp_folder_path' => $carpetaVigentePath,
                    'archivo_storage' => $archivoOficialPublic,
                ]);

                \App\Jobs\PublicarVersionEnSharePoint::dispatch(
                    $documentoParaPublicar->id,
                    $nuevaVersionParaPublicar->id,
                    $archivoOficialPublic,
                    $versionAnteriorParaPublicar?->id,
                    $crearSubcarpetaCodigo,
                    $nombreArchivoManual,
                )->afterCommit();
            }

            // === ENVIAR CORREOS MASIVOS USANDO TU PROPIO MAILABLE ===
            if ($accion === 'atender' && $request->has('usuarios_notificados')) {
                $usuarios = User::whereIn('id', $request->usuarios_notificados)->get();

                foreach ($usuarios as $usuario) {
                    if ($usuario->email) {
                        // Enviamos usando la clase de tu mailable y le pasamos los datos de la solicitud
                        Mail::to($usuario->email)->send(new \App\Mail\DocumentoAltaMailable($solicitud));
                    }
                }
            }
            // =======================================================

            return redirect()->route('solicitudes.index')->with('success', 'Proceso completado correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors('Error al finalizar: ' . $e->getMessage())->withInput();
        }
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


        if (
            !$user->hasRole('administrador_sgi') &&
            ($user->hasRole('usuario') || $user->hasRole('jefe')) &&
            $doc->area !== $user->area
        ) {

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

        $user->jefe?->notify(new SolicitudNotificacion($solicitud, "{$user->name} envió una solicitud de actualización que requiere tu aprobación."));

        return redirect()
            ->route('solicitudes.show', $solicitud->id)
            ->with('success', 'Solicitud de actualización enviada correctamente.');
    }

    public function destroy(SolicitudFormato $solicitud)
    {
        // La ruta ya está protegida por role:administrador_sgi (routes/web.php);
        // aquí solo queda la regla de negocio real: no borrar lo ya procesado.
        // No permitir borrar si ya fue procesada por SGI
        if (in_array($solicitud->estado, ['atendido', 'rechazado_sgi'])) {
            return back()->withErrors('No puedes eliminar una solicitud que ya ha sido procesada por SGI.');
        }

        $solicitud->delete();

        return redirect()->route('solicitudes.index')->with('success', 'Solicitud eliminada correctamente.');
    }
}
