<?php

namespace App\Http\Controllers;

use App\Mail\DocumentoNecesitaActualizacionMailable;
use App\Models\Documento;
use App\Models\SolicitudFormato;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DocumentoController extends Controller
{
    public function index()
    {
        // Ajusta: si filtras por área del usuario
        $user = auth()->user();

        $docs = Documento::query()
            ->when($user->hasRole('usuario') || $user->hasRole('jefe'), function ($q) use ($user) {
                // Si tu usuario tiene "area"
                if (!empty($user->area)) {
                    $q->where('area', $user->area);
                }
            })
            ->latest('id')
            ->paginate(15);

        return view('documentos.index', compact('docs'));
    }

    public function show(Documento $documento)
    {
        $documento->load([
            'versionVigente',
            'versiones' => fn($q) => $q->orderByDesc('id'),
            // si ya tienes revisiones:
            // 'versiones.revisiones' => fn($q) => $q->orderByDesc('id'),
        ]);

        $versiones = $documento->versiones; // ✅ para evitar "Variable indefinida $versiones"
        $vigente = $documento->versionVigente;

        return view('documentos.show', compact('documento', 'versiones', 'vigente'));
    }

    /**
     * BOTÓN Usuario/Jefe: crea solicitud de actualización prellenada con el documento seleccionado.
     */
    public function createUpdateRequest(Request $request, Documento $documento)
    {
        $user = auth()->user();

        // Regla sugerida: solo del mismo "area"
        if (($user->hasRole('usuario') || $user->hasRole('jefe')) && !empty($user->area)) {
            if ($documento->area !== $user->area) {
                abort(403, 'No puedes solicitar actualización de documentos de otra área.');
            }
        }

        // Validación mínima: comentarios obligatorios
        $request->validate([
            'comentarios' => 'required|string|max:2000',
        ]);

        // Crea solicitud “actualizacion” amarrada al mismo código
        $sol = SolicitudFormato::create([
            'user_id' => $user->id,
            'accion' => 'actualizacion',
            'estado' => 'pendiente',
            'jefe_id' => $user->jefe_id,
            'comentarios' => $request->comentarios,

            // ✅ arrastramos identidad
            'codigo_documento' => $documento->codigo,
            'nombre_documento' => $documento->nombre,
            'tipo_documento' => $documento->tipo_documento,
            'formato_el_pa' => $documento->formato_el_pa,
            'lugar_almacenamiento' => $documento->sharepoint_folder, // si aplica
        ]);

        return redirect()
            ->route('solicitudes.show', $sol->id)
            ->with('success', 'Solicitud de actualización creada.');
    }

    /**
     * BOTÓN Admin SGI: manda correo solicitando actualización.
     */
    public function notifyNeedsUpdate(Request $request, Documento $documento)
    {
        $user = auth()->user();
        if (!$user->hasRole('administrador_sgi')) {
            abort(403, 'No tienes permiso para notificar.');
        }

        $request->validate([
            'mensaje' => 'required|string|max:1000',
            'destino' => 'required|in:area,usuario,jefe', // 3 modos
            'usuario_id' => 'nullable|integer',
        ]);

        $mensaje = $request->mensaje;

        // Resolver destinatarios
        $emails = collect();

        if ($request->destino === 'area') {
            // todos los usuarios del área/departamento del documento
            if ($documento->area) {
                $emails = User::where('activo', 1)
                    ->where('area', $documento->area)
                    ->whereNotNull('email')
                    ->pluck('email');
            }
        }

        if ($request->destino === 'usuario') {
            $uid = (int) $request->usuario_id;
            $u = User::find($uid);
            if ($u?->email) $emails->push($u->email);
        }

        if ($request->destino === 'jefe') {
            // jefe del área: depende de cómo lo manejes.
            // Opción simple: usuarios del área con role('jefe')
            if ($documento->area) {
                $emails = User::role('jefe')
                    ->where('activo', 1)
                    ->where('area', $documento->area)
                    ->whereNotNull('email')
                    ->pluck('email');
            }
        }

        $emails = $emails->filter()->unique()->values();

        if ($emails->isEmpty()) {
            return back()->withErrors('No se encontraron destinatarios con email para ese criterio.');
        }

        foreach ($emails as $email) {
            Mail::to($email)->send(
                new DocumentoNecesitaActualizacionMailable(
                    $documento,
                    $mensaje,
                    $user->name
                )
            );
        }

        return back()->with('success', 'Notificación enviada correctamente.');
    }
}
