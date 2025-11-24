<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SolicitudFormato;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Mail\NuevaSolicitudMailable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\DivulgacionFormatoMailable;
use App\Mail\SolicitudAprobadaSgiMailable;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $desde = request('desde');
        $hasta = request('hasta');

        $coleccionFinal = $coleccionFinal->filter(function ($item) use ($nombre, $estado, $desde, $hasta) {
            // Filtrar por nombre del usuario
            if ($nombre && !str_contains(strtolower(optional($item->usuario)->name), strtolower($nombre))) {
                return false;
            }

            // Filtrar por estado
            if ($estado && $item->estado !== $estado) {
                return false;
            }

            // Filtrar por fecha (created_at)
            if ($desde && $item->created_at->lt($desde)) {
                return false;
            }
            if ($hasta && $item->created_at->gt($hasta)) {
                return false;
            }

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
        return view('solicitudes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'accion' => 'required|in:actualizacion,baja,nuevo_documento',
            'archivo' => 'nullable|file|mimes:pdf,doc,docx,xlsx,xls',
            'descripcion' => 'nullable|string',
            'comentarios' => 'nullable|string|max:2000',
        ]);

        $archivoPath = $request->hasFile('archivo')
            ? $request->file('archivo')->store('solicitudes', 'public')
            : null;

        $user = Auth::user();
        logger('Creando solicitud con jefe_id: ' . $user->jefe_id);

        $solicitud = SolicitudFormato::create([
            'user_id' => $user->id,
            'accion' => $request->accion,
            'archivo_adjunto' => $archivoPath,
            'estado' => 'pendiente',
            'jefe_id' => $user->jefe_id,
            'descripcion'     => $request->descripcion,   // 👈 se guarda descripción
            'comentarios'     => $request->comentarios,   // 👈 se guardan comentarios
        ]);

        $jefe = $user->jefe;

        if ($jefe && $jefe->email) {
            Mail::to($jefe->email)->send(new NuevaSolicitudMailable($solicitud));
        }



        return redirect()->route('solicitudes.index')->with('success', 'Solicitud enviada correctamente.');
    }

    public function show(SolicitudFormato $solicitud)
    {
        return view('solicitudes.show', compact('solicitud'));
    }
    public function approvalForm(SolicitudFormato $solicitud)
    {
        return view('solicitudes.approval_form', compact('solicitud'));
    }

    public function approveOrReject(Request $request, SolicitudFormato $solicitud)
    {
        $user = auth()->user();

        // ✅ 1. Solo alguien con rol jefe puede usar este flujo (coordinador)
        if (! $user->hasRole('jefe')) {
            abort(403, 'No tienes permiso para aprobar o rechazar esta solicitud.');
        }

        // ✅ 2. El jefe NO puede aprobar / rechazar sus propias solicitudes
        if ($solicitud->user_id === $user->id) {
            return redirect()
                ->route('solicitudes.index')
                ->withErrors('No puedes aprobar o rechazar tus propias solicitudes.');
        }

        // ✅ 3. (Opcional pero recomendable) Validar que esta solicitud realmente le pertenece como jefe
        if ($solicitud->jefe_id !== $user->id) {
            abort(403, 'No estás asignado como jefe de esta solicitud.');
        }

        // Validación original
        $request->validate([
            'decision' => 'required|in:aprobado_jefe,rechazado_jefe',
            'observaciones_jefe' => 'nullable|string|max:1000',
        ]);

        // Actualizar estado según la decisión del jefe
        $solicitud->update([
            'estado' => $request->decision,
            'observaciones_jefe' => $request->observaciones_jefe,
        ]);

        // Notificar al administrador_sgi solo si fue aprobado por jefe
        if ($request->decision === 'aprobado_jefe') {
            $administradores = \App\Models\User::role('administrador_sgi')->get();

            foreach ($administradores as $admin) {
                Mail::to($admin->email)->send(new SolicitudAprobadaSgiMailable($solicitud));
            }
        }

        return redirect()->route('solicitudes.index')->with('success', 'Decisión registrada correctamente.');
    }

    public function finalizeForm(SolicitudFormato $solicitud)
    {
        $usuarios = User::where('activo', 1)->get(); // Puedes agregar filtros si lo deseas
        return view('solicitudes.finalize_form', compact('solicitud', 'usuarios'));
    }

    public function finalize(Request $request, SolicitudFormato $solicitud)
    {
        $request->validate([
            'revision_actual' => 'required|string|max:50',
            'revision_anterior' => 'nullable|string|max:50',
            'liga_archivo' => 'required|url',
            'fecha_alta_sgi' => 'required|date',
            'observaciones_sgi' => 'nullable|string|max:1000',
            'accion' => 'required|in:atender,rechazar',
        ]);

        $estado = $request->accion === 'atender' ? 'atendido' : 'rechazado_sgi';

        $solicitud->update([
            'estado' => $estado,
            'revision_actual' => $request->revision_actual,
            'revision_anterior' => $request->revision_anterior,
            'liga_archivo' => $request->liga_archivo,
            'fecha_alta_sgi' => $request->fecha_alta_sgi,
            'observaciones_sgi' => $request->observaciones_sgi,
            'administrador_sgi_id' => auth()->id(),

        ]);
        if ($request->has('usuarios_notificados')) {
            $usuarios = User::whereIn('id', $request->usuarios_notificados)->get();

            foreach ($usuarios as $usuario) {
                Mail::to($usuario->email)->send(new DivulgacionFormatoMailable($solicitud));
            }
        }
        Log::info('Redireccionando después de finalizar', ['solicitud_id' => $solicitud->id]);


        return redirect()->route('solicitudes.index')->with('success', 'Solicitud finalizada correctamente.');
    }
}
