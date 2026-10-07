<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentoBajaMailable;
use App\Models\User;

class DocumentoVersionController extends Controller
{
    public function show(DocumentoVersion $version)
    {
        $version->load([
            'documento',
            // si tienes revisiones
            // 'revisiones' => fn($q) => $q->orderByDesc('id'),
        ]);

        $documento = $version->documento;

        return view('documentos.versiones.show', compact('documento', 'version'));
    }

public function marcarObsoleto(DocumentoVersion $version)
{
    $user = auth()->user();

    if (!$user->hasRole('administrador_sgi') && !$user->hasRole('administrador')) {
        abort(403);
    }

    $version->update([
        'estatus' => 'obsoleto'
    ]);

    if ($version->sp_item_id) {
        \App\Jobs\MoverVersionASharePointObsoletos::dispatch($version->documento_id, $version->id)->afterCommit();
    }

    // enviar correo a admins SGI
    $documento = $version->documento;
    $admins = User::role('administrador_sgi')->get();

    foreach ($admins as $admin) {
        if ($admin->email) {
            try {
                Mail::to($admin->email)->send(new DocumentoBajaMailable($documento));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('No se pudo enviar la notificación de versión obsoleta.', [
                    'documento_id' => $documento->id,
                    'email' => $admin->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    return back()->with('success', 'Versión marcada como obsoleta y notificada');
}

    /**
     * Reintenta publicar en SharePoint una versión cuyo sp_estado quedó en
     * "error" (o que nunca se llegó a publicar), reusando el archivo local
     * que se guardó en archivo_storage al despachar el job por primera vez.
     */
    public function reintentarPublicacion(DocumentoVersion $version)
    {
        $user = auth()->user();

        if (!$user->hasRole('administrador_sgi')) {
            abort(403);
        }

        if (!$version->archivo_storage) {
            return back()->withErrors('Esta versión no tiene un archivo local guardado para reintentar la publicación.');
        }

        $version->update(['sp_estado' => 'pendiente', 'sp_error' => null]);

        \App\Jobs\PublicarVersionEnSharePoint::dispatch(
            $version->documento_id,
            $version->id,
            $version->archivo_storage,
        )->afterCommit();

        return back()->with('success', 'Se volvió a encolar la publicación en SharePoint.');
    }
}
